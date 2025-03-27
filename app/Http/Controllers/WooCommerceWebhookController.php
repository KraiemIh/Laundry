<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Events\OrderCreated;
use App\Models\Order;

class WooCommerceWebhookController extends Controller
{
    // Service mappings with only service ID
    private $serviceMappings = [
        'Sofa/Couch' => ['id' => 6],
        'Majlis Side Pillow' => ['id' => 7],
        'Majlis Pillow' => ['id' => 8],
        'Traditional Majlis Sofa' => ['id' => 9],
        'Tent Per ( SQ.M )' => ['id' => 10],
        'Flag' => ['id' => 11],
        'Cloves' => ['id' => 12],
        'Mosllaia' => ['id' => 13],
        'Back Bag S/M/L' => ['id' => 14],
        'Chair Cover' => ['id' => 15],
        'Table Cover' => ['id' => 16],
        'Bathrobe' => ['id' => 17],
        'Matress Rotector' => ['id' => 18],
        'Light Matress' => ['id' => 19],
        'Sofa Cover S/L' => ['id' => 20],
        'Carpet Per SQ.M' => ['id' => 21],
        'Curtain S/L' => ['id' => 22],
        'Quilt S/M/L' => ['id' => 23],
        'Blanket S/M/L' => ['id' => 24],
        'Pillow Case' => ['id' => 25],
        'Pillow' => ['id' => 26],
        'Bed Cover' => ['id' => 27],
        'Bed Sheet S/L' => ['id' => 28],
        'Towel S/M/L' => ['id' => 29],
        'Farawah' => ['id' => 30],
        'Leather Shoes' => ['id' => 31],
        'Military Suit' => ['id' => 32],
        'Lab Coat / Apron' => ['id' => 33],
        'Baby Dress' => ['id' => 34],
        'Ladies Dress Long' => ['id' => 35],
        'Ladies Dress Short' => ['id' => 36],
        'Overall' => ['id' => 37],
        'Police Dress' => ['id' => 38],
        'Neck Tie' => ['id' => 39],
        'Men Suit 3 Pieces' => ['id' => 40],
        'Men Suit 2 pieces' => ['id' => 41],
        'Overcoat' => ['id' => 42],
        'Bra' => ['id' => 43],
        'Sport Pants / Short' => ['id' => 44],
        'Sport Shoes' => ['id' => 45],
        'Pyjama' => ['id' => 46],
        'Blouse Silk' => ['id' => 47],
        'Blouse' => ['id' => 48],
        'Skirt Pleated' => ['id' => 49],
        'Skirt' => ['id' => 50],
        'Sweater' => ['id' => 51],
        'Jacket' => ['id' => 52],
        'Hat' => ['id' => 53],
        'Socks' => ['id' => 54],
        'Under Pants' => ['id' => 55],
        'Under Shirt' => ['id' => 56],
        'Short' => ['id' => 57],
        'Pants/ Trouser' => ['id' => 58],
        'T-shirt' => ['id' => 59],
        'Shirt' => ['id' => 60],
        'Vest' => ['id' => 61],
        'Besht' => ['id' => 62],
        'Sary' => ['id' => 63],
        'Scarf' => ['id' => 64],
        'Jalabiya' => ['id' => 65],
        'Abaya' => ['id' => 66],
        'Serwal' => ['id' => 67],
        'Traditional Cap' => ['id' => 68],
        'Wool Ghatra' => ['id' => 69],
        'Ghatra' => ['id' => 70],
        'Wool Thobe' => ['id' => 71],
        'Color Thobe' => ['id' => 72],
        'Thobe' => ['id' => 73],
        'Bronze Package' => ['id' => 74],
        'Silver Package' => ['id' => 75],
        'Gold Package' => ['id' => 76]
    ];

    // Order type mapping
    private $orderTypeMappings = [
        'Dry Clean' => 1,
        'Pressing' => 2,
        'Wash and Press' => 3
    ];

    public function handleWooCommerceOrder(Request $request)
    {
        try {
            Log::info('WooCommerce webhook received', ['payload' => $request->all()]);
            $data = $request->json()->all() ?: $request->all();

            if (empty($data['id']) || empty($data['billing'])) {
                return response()->json(['error' => 'Missing required fields'], 400);
            }

            $lineItems = $data['line_items'] ?? [];
            $serviceId = null;
            $serviceName = null;
            $orderType = null;

            foreach ($lineItems as $item) {
                $itemName = strip_tags($item['name']); // Remove HTML tags
                
                // Split the name by " - " to separate service and order type
                $parts = explode(" - ", $itemName);
                $cleanServiceName = trim($parts[0]); // The first part is the service name
                $cleanOrderType = isset($parts[1]) ? trim($parts[1]) : null; // The second part is the order type
                
                // Match service name
                if (isset($this->serviceMappings[$cleanServiceName])) {
                    $serviceId = $this->serviceMappings[$cleanServiceName]['id'];
                    $serviceName = $cleanServiceName;
                }

                // Match order type
                if ($cleanOrderType && isset($this->orderTypeMappings[$cleanOrderType])) {
                    $orderType = $this->orderTypeMappings[$cleanOrderType];
                }
            }

            if ($serviceId === null) {
                Log::warning('No matching service found', ['line_items' => $lineItems]);
                return response()->json(['error' => 'No matching service found'], 400);
            }

            $lastOrderId = DB::table('orders')->max('id') ?? 0;
            $newOrderId = $lastOrderId + 1;
            $orderNumber = 'ORD-' . str_pad($newOrderId, 4, '0', STR_PAD_LEFT);

            $billing = $data['billing'] ?? [];
            $customerName = trim(($billing['first_name'] ?? '') . ' ' . ($billing['last_name'] ?? '')) ?: 'Unknown Customer';

            $order = Order::create([
                'id' => $newOrderId,
                'order_number' => $orderNumber,
                'customer_id' => $data['customer_id'] ?? null,
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
                'status' => 1,
                'order_type' => $orderType ?? 1, 
                'order_source' => 'woocommerce',
            ]);

            event(new OrderCreated($order));

            return response()->json(['success' => true, 'message' => 'Order processed successfully', 'order_id' => $newOrderId], 200);

        } catch (\Exception $e) {
            Log::error('Error processing WooCommerce webhook', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Error processing order'], 500);
        }
    }
}
