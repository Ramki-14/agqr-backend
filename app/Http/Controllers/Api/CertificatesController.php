<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class CertificatesController extends Controller
{
    public function store(Request $request)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id',
            'product_name' => 'required|string|max:255',
            'product_description' => 'required|string',
            'certificate_reg_no' => 'required|string|max:255',
            'issue_no' => 'required|string|max:255',
            'initial_approval' => 'required|date',
            'next_surveillance' => 'required|date',
            'date_of_issue' => 'required|date',
            'valid_until' => 'required|date',
            'certificate_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png,webp,doc,docx|max:20480', // 20 MB limit
        ]);
    
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
    
        $data = $request->all();
    
        // Handle file upload
        if ($request->hasFile('certificate_file')) {
            $data['certificate_file'] = $request->file('certificate_file')->store('certificates', 'public');
        }

    
        $certificate = Certificate::create($data);
    
          // Update the order status to active
    $order = Order::find($request->order_id);
    if ($order) {
        $order->update(['status' => 'active']);
    }

        return response()->json([
            'message' => 'Certificate created successfully',
            'data' => $certificate,
        ], 201);
    }
    

    public function checkCertificate(Request $request)
{
    $request->validate([
        'order_id' => 'required|exists:orders,id',
        'product_name' => 'required|string|max:255',
    ]);

    // Check if a certificate exists with the provided order_id and product_name
    $certificate = Certificate::where('order_id', $request->order_id)
                              ->where('product_name', $request->product_name)
                              ->first();

    if ($certificate) {
        return response()->json([
            'exists' => true,
            'certificate' => $certificate
        ]);
    } else {
        return response()->json(['exists' => false], 404);
    }
}

public function fetchCertificate(Request $request)
{
    $request->validate([
        'order_id' => 'required',
        'product_name' => 'required',
    ]);

    $certificate = Certificate::where('order_id', $request->order_id)
        ->where('product_name', $request->product_name)
        ->first();

    if (!$certificate) {
        return response()->json(['message' => 'Certificate not found'], 404);
    }

    return response()->json($certificate);
}
public function update(Request $request, $id)
{
    // Find the certificate by ID
    $certificate = Certificate::find($id);

    if (!$certificate) {
        return response()->json(['message' => 'Certificate not found'], 404);
    }

    // Validate input
    $validator = Validator::make($request->all(), [
        'order_id' => 'required|exists:orders,id',
        'product_name' => 'required|string|max:255',
        'product_description' => 'required|string',
        'certificate_reg_no' => 'required|string|max:255',
        'issue_no' => 'required|string|max:255',
        'initial_approval' => 'required|date',
        'next_surveillance' => 'required|date',
        'date_of_issue' => 'required|date',
        'valid_until' => 'required|date',
        'certificate_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png,webp,doc,docx|max:20480', // 20 MB limit
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    $data = $request->all();

    // Handle file upload
    if ($request->hasFile('certificate_file')) {
        // Delete the old file if it exists
        if ($certificate->certificate_file && Storage::disk('public')->exists($certificate->certificate_file)) {
            Storage::disk('public')->delete($certificate->certificate_file);
        }

        // Save the new file
        $data['certificate_file'] = $request->file('certificate_file')->store('certificates', 'public');
    }

    // Update the certificate with new data
    $certificate->update($data);

    return response()->json([
        'message' => 'Certificate updated successfully',
        'data' => $certificate,
    ], 200);
}



}
