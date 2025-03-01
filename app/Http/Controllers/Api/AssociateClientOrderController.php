<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AssociateClient;
use App\Models\AssociateClientCertificate;
use App\Models\AssociateClientOrder;
use App\Models\AssociatePayment;
use App\Models\AssociatePaymentHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssociateClientOrderController extends Controller
{
       

public function store(Request $request)
{
    $validated = $request->validate([
        'associate_company' => 'nullable|string',
        'associate_id' => 'required|integer',
        'associate_name' => 'required|string',
        'associate_payment' => 'required|numeric|min:0',
        'client_id' => 'required|integer',
        'orders' => 'required|array',
        'orders.*.product_name' => 'required|string',
        'orders.*.product_description' => 'nullable|string',
        'orders.*.sgst_amount' => 'nullable|numeric',
        'orders.*.cgst_amount' => 'nullable|numeric',
        'orders.*.igst_amount' => 'nullable|numeric',
        'orders.*.rate' => 'required|numeric',
        'orders.*.gst_amount' => 'nullable|numeric',
        'orders.*.total_amount' => 'required|numeric',
        'orders.*.balance_amount' => 'required|numeric',
        'orders.*.audit_type' => 'required|string',
        'orders.*.invoice_number' => 'required|string',
    ]);

    $client = AssociateClient::find($validated['client_id']);
    if (!$client) {
        return response()->json(['message' => 'Client not found'], 404);
    }
    DB::beginTransaction();
    try {
    foreach ($validated['orders'] as $order) {
        // Step 1: Check if the associate exists
        $associatePayment = AssociatePayment::where('associate_id', $validated['associate_id'])
            ->where('associate_name', $validated['associate_name'])
            ->first();

        if (!$associatePayment) {
            // Step 2: If the associate does not exist, create a new payment record
            AssociatePayment::create([
                'associate_id' => $validated['associate_id'],
                'associate_name' => $validated['associate_name'],
                'associate_company' => $validated['associate_company'] ?? null,
                'principal_amount' => $validated['associate_payment'],  // Set principal amount to total_amount
                'returned_amount' => 0,  // Initially returned amount is 0
                'outstanding_amount' => $validated['associate_payment'],  // Outstanding is equal to principal amount
            ]);
        } else {
            // Step 3: If the associate exists, update the principal amount
            $associatePayment->update([
                'principal_amount' => $associatePayment->principal_amount + $validated['associate_payment'],  // Add total_amount to the existing principal_amount
                'returned_amount' => $associatePayment->principal_amount + 0,  // Set returned amount to 0
                'outstanding_amount' => $associatePayment->outstanding_amount + $validated['associate_payment'],  // Recalculate outstanding_amount
            ]);
        }

        // Step 4: Store the order in the associate_client_orders table
        AssociateClientOrder::create([
            'client_id' => $validated['client_id'],
            'product_name' => $order['product_name'],
            'product_description' => $order['product_description'] ?? null,
            'sgst_amount' => $order['sgst_amount'] ?? null,
            'cgst_amount' => $order['cgst_amount'] ?? null,
            'igst_amount' => $order['igst_amount'] ?? null,
            'rate' => $order['rate'],
            'gst_amount' => $order['gst_amount'] ?? null,
            'total_amount' => $order['total_amount'],
            'balance_amount' => $order['balance_amount'],
            'audit_type' => $order['audit_type'],
            'associate_company' => $validated['associate_company'] ?? null,
            'associate_name' => $validated['associate_name'],
            'associate_id' => $validated['associate_id'],
        ]);


         // Save to the associate_payment_histories table
         AssociatePaymentHistory::create([
            'associate_id' => $associatePayment->associate_id,
            'associate_name' => $associatePayment->associate_name,
            'associate_company' => $associatePayment->associate_company,
            'client_id' => $client->id,
            'client_name' => $client->client_name,
            'product_name' => $order['product_name'],
            'total_amount' => $order['total_amount'],
          'description' => 'Amount Added by ' . $order['total_amount'], 
        ]);

    }
    DB::commit();
    return response()->json(['message' => 'Orders saved successfully'], 201);
}catch (\Exception $e) {
    DB::rollBack();
    return response()->json(['message' => 'Failed to save orders', 'error' => $e->getMessage()], 500);
}

}

public function getClientOrdersByClientId($client_id)
{
    // Retrieve orders for the given client_id
    $orders = AssociateClientOrder::where('client_id', $client_id)->get();

    if ($orders->isEmpty()) {
        return response()->json(['message' => 'No orders found for this client.'], 404);
    }

    return response()->json(['orders' => $orders], 200);
}


public function updateAssociateOrder(Request $request)
{
    // Validate incoming data
    $request->validate([
        'id' => 'required|exists:associate_client_orders,id',
        'client_id' => 'required|exists:associate_client,id',
        'associate_id' => 'required|exists:associate_client,associate_id',
        'rate' => 'required|numeric',
        'audit_type' => 'required|string',
        'sgst_amount' => 'required|numeric',
        'cgst_amount' => 'required|numeric',
        'igst_amount' => 'required|numeric',
        'gst_amount' => 'required|numeric',
        'total_amount' => 'required|numeric',
        'balance_amount' => 'required|numeric',
    ]);

    // Find the order by ID and client_id
    $order = AssociateClientOrder::where('id', $request->id)
        ->where('client_id', $request->client_id)
        ->first();

    if (!$order) {
        return response()->json(['message' => 'Order not found for the specified client.'], 404);
    }

    // Check if the audit type has changed
    $isAuditTypeChanged = $order->audit_type !== $request->audit_type;

    // Store the old total amount
    $oldTotalAmount = $order->total_amount;

    // Update the order with the provided data
    $order->update([
        'rate' => $request->rate,
        'audit_type' => $request->audit_type,
        'sgst_amount' => $request->sgst_amount,
        'cgst_amount' => $request->cgst_amount,
        'igst_amount' => $request->igst_amount,
        'gst_amount' => $request->gst_amount,
        'total_amount' => $request->total_amount,
        'balance_amount' => $request->total_amount,
    ]);

    $associatePayment = AssociatePayment::where('associate_id', $request->associate_id)->first();

    if ($associatePayment) {

        $amountDifference = $request->total_amount - $oldTotalAmount;
        $description = "";

        if ($isAuditTypeChanged) {
           // If the audit type has changed, add the new total amount to principal and outstanding
            $associatePayment->principal_amount += $request->total_amount;
            $associatePayment->outstanding_amount += $request->total_amount;
            $description = "Audit type changed, added full total amount " . $request->total_amount . " to principal.";   
        } else {
            if ($amountDifference > 0) {
                $associatePayment->principal_amount += $amountDifference;
                $associatePayment->outstanding_amount += $amountDifference;
                $description = "Principal amount increased by $amountDifference.";
            } elseif ($amountDifference < 0) {
                $associatePayment->principal_amount += $amountDifference; // Subtract as the difference is negative
                $associatePayment->outstanding_amount += $amountDifference;
                $description = "Principal amount decreased by $amountDifference.";
            }
        }

        $associatePayment->save();

         // Create a history record
         AssociatePaymentHistory::create([
            'associate_id' => $request->associate_id,
            'associate_name' => $associatePayment->associate_name,
            'associate_company' => $associatePayment->associate_company,
            'client_id' => $request->client_id,
            'client_name' => AssociateClient::find($request->client_id)->client_name ?? 'N/A',
            'product_name' => $order->product_name,
            'total_amount' => $amountDifference,
            'description' => $description,
            
        ]);
    }

    return response()->json(['message' => 'Order updated successfully.', 'order' => $order], 200);
}

public function showAssociateClientOrders(Request $request)
    {
        // Validate the request data
        $request->validate([
            'client_id' => 'required|exists:associate_client,id',
            'order_ids' => 'required|array',
            'order_ids.*' => 'exists:associate_client_orders,id',
        ]);

        // Extract data from the request
        $clientId = $request->client_id;
        $orderIds = $request->order_ids;

        // Fetch client details
        $client = AssociateClient::where('id', $clientId)->first();

        if (!$client) {
            return response()->json(['error' => 'Client not found'], 404);
        }

        // Fetch order details
        $orders = AssociateClientOrder::whereIn('id', $orderIds)
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


public function showOrderDetails(Request $request)
{
    // Validate incoming data
    $validated = $request->validate([
        'order_id' => 'required|integer',
        'product_name' => 'required|string',
    ]);

    // Find the order based on order_id and product_name
    $order = AssociateClientOrder::where('id', $validated['order_id'])
                  ->where('product_name', $validated['product_name'])
                  ->first();

    // If the order does not exist, return an error
    if (!$order) {
        return response()->json(['message' => 'Order not found'], 404);
    }

    // Get client profile based on client_id from the order
    $clientProfile = AssociateClient::where('id', $order->client_id)->first();

    // If client profile not found, return an error
    if (!$clientProfile) {
        return response()->json(['message' => 'Client profile not found'], 404);
    }

    // Prepare the basic data to send back to the frontend
    $data = [
        'client_name' => $clientProfile->client_name,     
        'client_gst_no' => $clientProfile->client_gst_no,
        'client_gst_document' => $clientProfile->client_gst_document,
        'address' => $clientProfile->address,
        'associate_name' => $order->associate_name,
        'product_name' => $order->product_name,
        'audit_type' => $order->audit_type,
        'product_description' => $order->product_description,
        'total_amount' => $order->total_amount,
    ];

    // Check if certificate exists for the order
    $certificate = AssociateClientCertificate::where('order_id', $validated['order_id'])
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
            'certificate_file' => $certificate->certificate_file,
            'status' => $certificate->status,
        ]);
    }

    return response()->json($data, 200);
}

}
