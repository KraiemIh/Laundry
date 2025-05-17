<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Events\OrderCreated;
use App\Models\Order;
use App\Models\Customer;

class WebhookController extends Controller
{
    public function handleWordPressForm(Request $request)
    {
        try {
            // Log the incoming data for debugging
            Log::info('WordPress form webhook received', ['payload' => $request->all()]);
            
            // Get the data (either from JSON or form data)
            $data = $request->json()->all() ?: $request->all();
            
            // Validate that required fields exist
            if (empty($data['Name']) || empty($data['Service'])) {
                return response()->json(['error' => 'Missing required fields'], 400);
            }

            // Map service names to order types and IDs
            $serviceMappings = [
                'Laundry & Ironing' => ['type' => 1, 'id' => 1],
                'Dry Cleaning' => ['type' => 2, 'id' => 2],
                'Shoe Revival' => ['type' => 3, 'id' => 3],
                'Carpet Rejuvenation' => ['type' => 4, 'id' => 4],
                'Tent Refresh' => ['type' => 5, 'id' => 5],
            ];

            // Extract service mapping
            $service = $data['Service'];
            $orderType = $serviceMappings[$service]['type'] ?? null;
            $serviceId = $serviceMappings[$service]['id'] ?? null;

            // Get the last order ID and increment
            $lastOrderId = DB::table('orders')->max('id') ?? 0;
            $newOrderId = $lastOrderId + 1;

            // Format pickup date (handling potential format issues)
            $pickupDate = null;
            if (!empty($data['Pick-Up Date'])) {
                try {
                    $pickupDate = Carbon::createFromFormat('d/m/Y', $data['Pick-Up Date'])->format('Y-m-d');
                } catch (\Exception $e) {
                    Log::warning('Could not parse pickup date', ['date' => $data['Pick-Up Date'], 'error' => $e->getMessage()]);
                    $pickupDate = null;
                }
            }

            // Format pickup time (handling potential format issues)
            $pickupTime = null;
            if (!empty($data['Pick-Up Time'])) {
                try {
                    $pickupTime = Carbon::createFromFormat('h:i A', $data['Pick-Up Time'])->format('H:i:s');
                } catch (\Exception $e) {
                    Log::warning('Could not parse pickup time', ['time' => $data['Pick-Up Time'], 'error' => $e->getMessage()]);
                    $pickupTime = $data['Pick-Up Time']; // Keep the original format if parsing fails
                }
            }

            // Generate order number
            $orderNumber = 'ORD-' . str_pad($newOrderId, 4, '0', STR_PAD_LEFT);
            $customer = Customer::where('email', $data['E-mail Address'])->first();
            if (!$customer) {

            $customer = Customer::create([
                'name' => $data['Name'] ?? 'Unknown Customer',
                'phone' => $data['Phone Number'] ?? '',
                'email' => $data['E-mail Address'] ?? null,
                'tax_number' => $data['Tax Number'] ?? null,
                'address' => $data['Address'] ?? '',
                'created_by' => 1, // Auth::user()->id si tu utilises l’authentification
                'is_active' => true,
            ]);
}
            // Insert into orders table
            $order = Order::create([
                'id' => $newOrderId,
                'order_number' => $orderNumber,
                'customer_id' => $customer->id,
                'customer_name' => $data['Name'] ?? 'Unknown Customer',
                'phone_number' => $data['Phone Number'] ?? '',
                'address' => $data['Address'] ?? '',
                'order_date' => Carbon::parse($data['Submission Create Date'])->format('Y-m-d H:i:s') ?? null,
                'delivery_date' => $pickupDate,
                'delivery_time' => $pickupTime,
                'sub_total' => 0,
                'addon_total' => 0,
                'discount' => 0,
                'tax_percentage' => 0,
                'tax_amount' => 0,
                'total' => 0,
                'note' => null,
                'status' => 0,
                'order_type' => $orderType,
                'created_by' => 1,
                'financial_year_id' => 1,
                'order_source' => 'website',
            ]);

            // Trigger OrderCreated event
            event(new OrderCreated($order));
            
            // Insert into order_details table
            DB::table('order_details')->insert([
                'order_id' => $newOrderId,
                'service_id' => $serviceId,
                'service_name' => $data['Service'] ?? null,
                'service_price' => 0.00,
                'service_quantity' => 1,
                'service_detail_total' => 0.00,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Form data processed successfully',
                'order_id' => $newOrderId
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error processing WordPress form webhook', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error processing form data: ' . $e->getMessage()
            ], 500);
        }
    }
}
