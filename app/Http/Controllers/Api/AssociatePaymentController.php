<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\PaymentReceiptMail;
use Illuminate\Http\Request;
use App\Models\AssociatePayment;
use App\Models\AssociatePaymentHistory;
use App\Models\AssociatePaymentReceipt;
use App\Models\AssociativeLogin;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AssociatePaymentController extends Controller
{
    public function index()
    {
        $payments = AssociatePayment::all();
        return response()->json($payments, 200);
    }

    
    // public function store(Request $request)
    // {
    //     $validatedData = $request->validate([
    //         'associate_id' => 'required|exists:associative_login,id',
    //         'associate_name' => 'required|string|max:255',
    //         'associate_company' => 'nullable|string|max:255',
    //         'received_amount' => 'required|numeric|min:0',
    //         'received_date' => 'required|date',
    //         'received_method' => 'required|string|max:255',
    //     ]);

    //      // Begin database transaction
    //    DB::beginTransaction();

    //  try {
    //     // Create a payment receipt
    //     $paymentReceipt = AssociatePaymentReceipt::create($validatedData);

    //     // Retrieve the associate payment row
    //     $associatePayment = AssociatePayment::where('associate_id', $validatedData['associate_id'])->first();

    //     if (!$associatePayment) {
    //         return response()->json([
    //             'message' => 'Associate payment record not found.',
    //         ], 404);
    //     }

    //     // Update the returned_amount and outstanding_amount
    //     $associatePayment->returned_amount += $validatedData['received_amount'];
    //     $associatePayment->outstanding_amount -= $validatedData['received_amount'];

    //     // Ensure outstanding_amount doesn't become negative
    //     if ($associatePayment->outstanding_amount < 0) {
    //         $associatePayment->outstanding_amount = 0;
    //     }

    //     // Save the updated associate payment record
    //     $associatePayment->save();

    //     // Commit the transaction
    //     DB::commit();

    //     return response()->json([
    //         'message' => 'Payment receipt created and associate payment updated successfully.',
    //         'payment_receipt' => $paymentReceipt,
    //     ], 201);
    //  } catch (\Exception $e) {
    //     // Rollback the transaction on error
    //     DB::rollBack();

    //     return response()->json([
    //         'message' => 'Failed to create payment receipt and update associate payment.',
    //         'error' => $e->getMessage(),
    //     ], 500);
    //  }
    // }

   

public function store(Request $request)
{
    $validatedData = $request->validate([
        'associate_id' => 'required|exists:associative_login,id',
        'associate_name' => 'required|string|max:255',
        'associate_company' => 'nullable|string|max:255',
        'received_amount' => 'required|numeric|min:0',
        'received_date' => 'required|date',
        'received_method' => 'required|string|max:255',
    ]);

    // Begin database transaction
    DB::beginTransaction();

    try {
        // Create a payment receipt
        $paymentReceipt = AssociatePaymentReceipt::create($validatedData);

        // Retrieve the associate payment row
        $associatePayment = AssociatePayment::where('associate_id', $validatedData['associate_id'])->first();

        if (!$associatePayment) {
            return response()->json([
                'message' => 'Associate payment record not found.',
            ], 404);
        }

        // Update the returned_amount and outstanding_amount
        $associatePayment->returned_amount += $validatedData['received_amount'];
        $associatePayment->outstanding_amount -= $validatedData['received_amount'];

        // Ensure outstanding_amount doesn't become negative
        if ($associatePayment->outstanding_amount < 0) {
            $associatePayment->outstanding_amount = 0;
        }

        // Save the updated associate payment record
        $associatePayment->save();

        // Retrieve the associate's email from the associative_login table
        $associateEmail = AssociativeLogin::where('id', $validatedData['associate_id'])->value('email');

        if ($associateEmail) {
            // Send email to the associate
            Mail::to($associateEmail)->send(new PaymentReceiptMail([
                'receipt_number' => $paymentReceipt->id,
                'received_amount' => $validatedData['received_amount'],
                'received_date' => $validatedData['received_date'],
                'received_method' => $validatedData['received_method'],
                'associate_name' => $validatedData['associate_name'],
            ]));
        }

        // Commit the transaction
        DB::commit();

        return response()->json([
            'message' => 'Payment receipt created, associate payment updated, and email sent successfully.',
            'payment_receipt' => $paymentReceipt,
        ], 201);
    } catch (\Exception $e) {
        // Rollback the transaction on error
        Log::error('Error creating payment receipt: '.$e->getMessage());
        DB::rollBack();

        return response()->json([
            'message' => 'Failed to create payment receipt, update associate payment, or send email.',
            'error' => $e->getMessage(),
        ], 500);
    }
}


    public function getPaymentRecipts()
    {
        $paymentReceipts = AssociatePaymentReceipt::all();

        return response()->json([
            'message' => 'Payment receipts retrieved successfully.',
            'data' => $paymentReceipts,
        ]);
    }

    public function getAssociatePaymentReceipts(Request $request)
{
    $associateId = $request->input('associate_id');

    if (!$associateId) {
        return response()->json([
            'message' => 'Associate ID is required.',
        ], 400); // Bad Request
    }

    // Filter the payment receipts by associate_id
    $paymentReceipts = AssociatePaymentReceipt::where('associate_id', $associateId)->get();

    if ($paymentReceipts->isEmpty()) {
        return response()->json([
            'message' => 'No payment receipts found for the given Associate ID.',
        ], 404); // Not Found
    }

    return response()->json([
        'message' => 'Payment receipts retrieved successfully.',
        'data' => $paymentReceipts,
    ]);
}

public function principalhistory(Request $request)
{
    $associateId = $request->input('associate_id');

    if (!$associateId) {
        return response()->json([
            'message' => 'Associate ID is required.',
        ], 400); // Bad Request
    }

    // Filter the payment receipts by associate_id
    $paymentReceipts = AssociatePaymentHistory::where('associate_id', $associateId)->get();

    if ($paymentReceipts->isEmpty()) {
        return response()->json([
            'message' => 'No payment receipts found for the given Associate ID.',
        ], 404); // Not Found
    }

    return response()->json([
        'message' => 'Payment receipts retrieved successfully.',
        'data' => $paymentReceipts,
    ]);
}
}
