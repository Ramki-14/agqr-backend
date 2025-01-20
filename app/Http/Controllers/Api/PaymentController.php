<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
//     public function savePayment(Request $request)
// {
//     $validatedData = $request->validate([
//         'client_id' => 'required|exists:client_profiles,client_id',
//         'order_id' => 'required|exists:orders,id',
//         'client_name' => 'required|string',
//         'product_name' => 'required|string',
//         'product_description' => 'nullable|string',
//         'rate' => 'required|numeric',
//         'sgst' => 'required|numeric',
//         'cgst' => 'required|numeric',
//         'igst' => 'required|numeric',
//         'payable_amount' => 'required|numeric',
//         'received_amount' => 'required|numeric',
//         'balance' => 'required|numeric',
//         'payment_method' => 'required|string',
//         'payment_date' => 'required|date',
//     ]);

//     // Save payment data
//     $payment = Payment::create($validatedData);

//     // Update the order balance
//     $order = Order::where('id', $request->order_id)
//         ->where('client_id', $request->client_id)
//         ->first();

//     if ($order) {
//         $order->balance_amount = $validatedData['balance'];
//         $order->save();
//     }

//     return response()->json(['message' => 'Payment saved and order updated successfully.']);
// }

public function savePayment(Request $request)
{
    $validatedData = $request->validate([
        'client_id' => 'required|exists:client_profiles,client_id',
        'order_id' => 'required|exists:orders,id',
        'client_name' => 'required|string',
        'product_name' => 'required|string',
        'product_description' => 'nullable|string',
        'rate' => 'required|numeric',
        'sgst' => 'required|numeric',
        'cgst' => 'required|numeric',
        'igst' => 'required|numeric',
        'payable_amount' => 'required|numeric',
        'received_amount' => 'required|numeric',
        'balance' => 'required|numeric',
        'payment_method' => 'required|string',
        'payment_date' => 'required|date',
    ]);

    // Check if a payment record exists for the given client_id and order_id
    $payment = Payment::where('client_id', $validatedData['client_id'])
        ->where('order_id', $validatedData['order_id'])
        ->first();

    if ($payment) {
        // Update the existing payment record
        $payment->update([
            'rate' => $validatedData['rate'],
            'sgst' => $validatedData['sgst'],
            'cgst' => $validatedData['cgst'],
            'igst' => $validatedData['igst'],
            'payable_amount' => $validatedData['payable_amount'],
            'received_amount' => $validatedData['received_amount'],
            'balance' => $validatedData['balance'],
            'payment_method' => $validatedData['payment_method'],
            'payment_date' => $validatedData['payment_date'],
            'client_name' => $validatedData['client_name'],
            'product_name' => $validatedData['product_name'],
            'product_description' => $validatedData['product_description'],
        ]);
    } else {
        // Create a new payment record if no match is found
        $payment = Payment::create($validatedData);
    }

    // Update the order balance
    $order = Order::where('id', $validatedData['order_id'])
        ->where('client_id', $validatedData['client_id'])
        ->first();

    if ($order) {
        $order->balance_amount = $validatedData['balance'];
        $order->save();
    }

    return response()->json(['message' => 'Payment record updated or created successfully, and order updated.']);
}



}
