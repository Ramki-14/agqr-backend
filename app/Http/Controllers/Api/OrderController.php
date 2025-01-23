<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\ClientProfile;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        // Validate the incoming data
        $request->validate([
            'client_id' => 'required|exists:client_profiles,client_id',
            'orders' => 'required|array',
            'orders.*.product_name' =>  [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) use ($request) {
                    // Extract the order index
                    $orderIndex = explode('.', $attribute)[1];
    
                    // Get the client_id for validation
                    $clientId = $request->client_id;
    
                    // Check if the product already exists for this client
                    if (Order::where('client_id', $clientId)->where('product_name', $value)->exists()) {
                        $fail("The product '{$value}' has already been ordered by this client.");
                    }
                }
            ],
            'orders.*.product_description' => 'required|string|max:255',
            'orders.*.audit_type' => 'required|string|max:255',
            'orders.*.invoice_number' => 'required|string|max:255',
            'orders.*.sgst_amount' => 'required|numeric',
            'orders.*.cgst_amount' => 'required|numeric',
            'orders.*.igst_amount' => 'required|numeric',
            'orders.*.rate' => 'required|numeric',
            'orders.*.gst_amount' => 'required|numeric',
            'orders.*.total_amount' => 'required|numeric',
            'orders.*.balance_amount' => 'required|numeric',
        ]);

        // Store each order in the database
        foreach ($request->orders as $orderData) {
            Order::create([
                'client_id' => $request->client_id,
                'product_name' => $orderData['product_name'],
                'product_description' => $orderData['product_description'],
                'audit_type' => $orderData['audit_type'],
                'invoice_number' => $orderData['invoice_number'],
                'sgst_amount' => $orderData['sgst_amount'],
                'cgst_amount' => $orderData['cgst_amount'],
                'igst_amount' => $orderData['igst_amount'],
                'rate' => $orderData['rate'],
                'gst_amount' => $orderData['gst_amount'],
                'total_amount' => $orderData['total_amount'],
                'balance_amount' => $orderData['balance_amount'],
            ]);
        }

        return response()->json(['message' => 'Orders saved successfully.'], 201);
 
    }

    public function getOrdersByClientId($client_id)
    {
        // Retrieve orders for the given client_id
        $orders = Order::where('client_id', $client_id)->get();

        if ($orders->isEmpty()) {
            return response()->json(['message' => 'No orders found for this client.'], 404);
        }

        return response()->json(['orders' => $orders], 200);
    }

    public function getOrderDetails(Request $request)
    {
        // Validate the request data
        $request->validate([
            'id' => 'required|exists:orders,id',
            'product_name' => 'required|string',
        ]);
    
        // Find the order by id and product_name
        $order = Order::where('id', $request->id)
            ->where('product_name', $request->product_name)
            ->first();
    
        // Check if the order exists
        if ($order) {
            return response()->json([
                'status' => true,
                'data' => $order,
            ], 200);
        }
    
        return response()->json([
            'status' => false,
            'message' => 'Order not found for the provided details.',
        ], 404);
    }

    
//payment details geting
public function showPaymentOptions(Request $request)
    {
        // Validate the request data
        $request->validate([
            'client_id' => 'required|exists:client_profiles,client_id',
            'order_ids' => 'required|array',
            'order_ids.*' => 'exists:orders,id',
        ]);

        // Extract data from the request
        $clientId = $request->client_id;
        $orderIds = $request->order_ids;

        // Fetch client details
        $client = ClientProfile::where('client_id', $clientId)->first();

        if (!$client) {
            return response()->json(['error' => 'Client not found'], 404);
        }

        // Fetch order details
        $orders = Order::whereIn('id', $orderIds)
            ->where('client_id', $clientId)
            ->get();

        if ($orders->isEmpty()) {
            return response()->json(['error' => 'No orders found for the provided IDs'], 404);
        }

        // Return the response
        return response()->json([
            'client_name' => $client->client_name,
            'address' => $client->address,
            'orders' => $orders,
        ]);
    }

  

public function updateOrder(Request $request)
{
    // Validate incoming data
    $request->validate([
        'id' => 'required|exists:orders,id',
        'client_id' => 'required|exists:client_profiles,client_id',
        'rate' => 'required|numeric',
        'sgst_amount' => 'required|numeric',
        'audit_type' => 'required|string',
        'cgst_amount' => 'required|numeric',
        'igst_amount' => 'required|numeric',
        'gst_amount' => 'required|numeric',
        'total_amount' => 'required|numeric',
        'balance_amount' => 'required|numeric',
    ]);

    // Find the order by ID and client_id
    $order = Order::where('id', $request->id)
        ->where('client_id', $request->client_id)
        ->first();

    if (!$order) {
        return response()->json(['message' => 'Order not found for the specified client.'], 404);
    }

    // Update the order with the provided data
    $order->update([
        'rate' => $request->rate,
        'sgst_amount' => $request->sgst_amount,
        'audit_type' => $request->audit_type,
        'cgst_amount' => $request->cgst_amount,
        'igst_amount' => $request->igst_amount,
        'gst_amount' => $request->gst_amount,
        'total_amount' => $request->total_amount,
        'balance_amount' => $request->total_amount,
        
    ]);

    return response()->json(['message' => 'Order updated successfully.', 'order' => $order], 200);
}
public function deleteOrder(Request $request)
{
    // Validate the request data
    $request->validate([
        'id' => 'required|exists:orders,id',
        'client_id' => 'required|exists:client_profiles,client_id',
    ]);

    // Find the order by id and client_id
    $order = Order::where('id', $request->id)
        ->where('client_id', $request->client_id)
        ->first();

    // Check if the order exists
    if (!$order) {
        return response()->json(['message' => 'Order not found for the provided client.'], 404);
    }

    // Delete the order
    $order->delete();

    // Verify that the order is deleted
    $isDeleted = !Order::where('id', $request->id)
        ->where('client_id', $request->client_id)
        ->exists();

    if ($isDeleted) {
        return response()->json(['message' => 'Order deleted successfully.'], 200);
    } else {
        return response()->json(['message' => 'Failed to delete the order.'], 500);
    }
}

// app/Http/Controllers/OrderController.php

public function showOrderDetails(Request $request)
{
    // Validate incoming data
    $validated = $request->validate([
        'order_id' => 'required|integer',
        'product_name' => 'required|string',
    ]);

    // Find the order based on order_id and product_name
    $order = Order::where('id', $validated['order_id'])
                  ->where('product_name', $validated['product_name'])
                  ->first();

    // If the order does not exist, return an error
    if (!$order) {
        return response()->json(['message' => 'Order not found'], 404);
    }

    // Get client profile based on client_id from the order
    $clientProfile = ClientProfile::where('client_id', $order->client_id)->first();

    // If client profile not found, return an error
    if (!$clientProfile) {
        return response()->json(['message' => 'Client profile not found'], 404);
    }

    // Prepare the basic data to send back to the frontend
    $data = [
        'client_name' => $clientProfile->client_name,
        'contact_person' => $clientProfile->contact_person,
        'email' => $clientProfile->email,
        'contact_no' => $clientProfile->contact_no,
        'gst_number' => $clientProfile->gst_number,
        'gst_document' => $clientProfile->gst_document,
        'address' => $clientProfile->address,
        'product_name' => $order->product_name,
        'product_description' => $order->product_description,
        'audit_type' => $order->audit_type,
        'total_amount' => $order->total_amount,
        'balance_amount' => $order->balance_amount,
    ];

    // Check if certificate exists for the order
    $certificate = Certificate::where('order_id', $validated['order_id'])
                              ->where('product_name', $validated['product_name'])
                              ->first();

    // If certificate exists, add certificate details to the response
    if ($certificate) {
        $data = array_merge($data, [
            'certificate_reg_no' => $certificate->certificate_reg_no,
            'issue_no' => $certificate->issue_no,
            'initial_approval' => $certificate->initial_approval,
            'date_of_issue' => $certificate->date_of_issue,
            'next_surveillance' => $certificate->next_surveillance,
            'valid_until' => $certificate->valid_until,
            'status' => $certificate->status,
            'certificate_file' => $certificate->certificate_file,
        ]);
    }

    return response()->json($data, 200);
}

}



