<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Events\OrderCreated;
use App\Models\Order;
use App\Models\Customer;
use App\Models\Service;
use App\Models\ServiceType;

class WooCommerceWebhookController extends Controller
{
    public function handleWooCommerceOrder(Request $request)
    {
        try {
            Log::info('WooCommerce webhook received', ['payload' => $request->all()]);
            $data = $request->json()->all() ?: $request->all();

            if (empty($data['id']) || empty($data['billing'])) {
                return response()->json(['error' => 'Missing required fields'], 400);
            }

            $lineItems = $data['line_items'] ?? [];
            if (empty($lineItems)) {
                return response()->json(['error' => 'No line items found'], 400);
            }

            $lastOrderId = DB::table('orders')->max('id') ?? 0;
            $newOrderId = $lastOrderId + 1;
            $orderNumber = 'ORD-' . str_pad($newOrderId, 4, '0', STR_PAD_LEFT);

            $billing = $data['billing'] ?? [];
            $customerName = trim(($billing['first_name'] ?? '') . ' ' . ($billing['last_name'] ?? '')) ?: 'Unknown Customer';

            $customer = Customer::where('email', $billing['email'])->first();

            if (!$customer) {
                $customer = Customer::firstOrCreate(
                    ['phone' => $billing['phone']],
                    [
                        'name' => $customerName,
                        'email' => $billing['email'] ?? null,
                        'address' => implode(', ', array_filter([
                            $billing['address_1'] ?? '',
                            $billing['address_2'] ?? '',
                            $billing['city'] ?? '',
                            $billing['state'] ?? '',
                            $billing['postcode'] ?? '',
                            $billing['country'] ?? ''
                        ])),
                        'created_by' => 1,
                        'is_active' => true,
                    ]
                );
            }

            $order = Order::create([
                'id' => $newOrderId,
                'order_number' => $orderNumber,
                'customer_id' => $customer->id,
                'customer_name' => $customerName,
                'phone_number' => $billing['phone'] ?? '',
                'address' => implode(', ', array_filter([
                    $billing['address_1'] ?? '',
                    $billing['address_2'] ?? '',
                    $billing['city'] ?? '',
                    $billing['state'] ?? '',
                    $billing['postcode'] ?? '',
                    $billing['country'] ?? ''
                ])),
                'order_date' => Carbon::parse($data['date_created'])->format('Y-m-d H:i:s'),
                'sub_total' => $data['subtotal'] ?? 0,
                'total' => $data['total'] ?? 0,
                'discount' => $data['discount_total'] ?? 0,
                'tax_amount' => $data['total_tax'] ?? 0,
                'status' => 0,
                'order_source' => 'woocommerce',
            ]);

            foreach ($lineItems as $item) {
                $itemName = strip_tags($item['name']);
                $parts = explode(" - ", $itemName);
                $cleanServiceName = trim($parts[0]);
                $serviceTypeName = isset($parts[1]) ? trim($parts[1]) : null;

                $service = Service::where('service_name', $cleanServiceName)->first();
                $serviceType = ServiceType::where('service_type_name', $serviceTypeName)->first();

                if ($service && $serviceType) {
                    DB::table('order_details')->insert([
                        'order_id' => $order->id,
                        'service_id' => $service->id, 
                       // 'service_type_id' => $serviceType->id,
                        'service_name' => $serviceTypeName,
                       // 'service_type_name' => $serviceType->name,
                        'service_price' => $item['price'] ?? 0,
                        'service_quantity' => $item['quantity'] ?? 1,
                        'service_detail_total' => ($item['price'] ?? 0) * ($item['quantity'] ?? 1),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }



            event(new OrderCreated($order));

            return response()->json(['success' => true, 'message' => 'Order processed successfully', 'order_id' => $newOrderId], 200);

        } catch (\Exception $e) {
            Log::error('Error processing WooCommerce webhook', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Error processing order'], 500);
        }
    }
}

