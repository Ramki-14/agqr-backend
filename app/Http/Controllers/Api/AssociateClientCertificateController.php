<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AssociateClientCertificate;
use App\Models\AssociateClientOrder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class AssociateClientCertificateController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:associate_client_orders,id',
            'associate_id' => 'required|exists:associative_login,id',
            'associate_name' => 'required|string|max:255',
            'product_name' => 'required|string|max:255',
            'product_description' => 'nullable|string',
            'certificate_reg_no' => 'required|string|unique:associate_client_certificate,certificate_reg_no',
            'issue_no' => 'required|string|max:255',
            'initial_approval' => 'required|date',
            'next_surveillance' => 'required|date|after_or_equal:initial_approval',
            'date_of_issue' => 'required|date',
            'valid_until' => 'required|date|after:date_of_issue',
            'certificate_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png,webp,doc,docx|max:20480',
           
        ]);

        // Handle file upload
        $certificateFilePath = null;
        if ($request->hasFile('certificate_file')) {
            $certificateFilePath =  $request->file('certificate_file')->store('certificates', 'public');
        }

        // Store data
        $certificate = AssociateClientCertificate::create([
            'order_id' => $request->order_id,
            'associate_id' => $request->associate_id,
            'associate_name' => $request->associate_name,
            'product_name' => $request->product_name,
            'product_description' => $request->product_description,
            'certificate_reg_no' => $request->certificate_reg_no,
            'issue_no' => $request->issue_no,
            'initial_approval' => $request->initial_approval,
            'next_surveillance' => $request->next_surveillance,
            'date_of_issue' => $request->date_of_issue,
            'valid_until' => $request->valid_until,
            'certificate_file' => $certificateFilePath,
        
        ]);

          // Update the order status to active
    $order = AssociateClientOrder::find($request->order_id);
    if ($order) {
        $order->update(['status' => 'active']);
    }

        return response()->json(['message' => 'Certificate created successfully.', 'data' => $certificate], 201);
    }

    public function checkCertificate(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'product_name' => 'required|string|max:255',
        ]);
    
        // Check if a certificate exists with the provided order_id and product_name
        $certificate = AssociateClientCertificate::where('order_id', $request->order_id)
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

    $certificate = AssociateClientCertificate::where('order_id', $request->order_id)
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
    $certificate = AssociateClientCertificate::find($id);

    if (!$certificate) {
        return response()->json(['message' => 'Certificate not found'], 404);
    }

    // Validate input
    $validator = Validator::make($request->all(), [
        'order_id' => 'required|exists:orders,id',
        'associate_id' => 'required|exists:associative_login,id',
        'associate_name' => 'required|string|max:255',
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

       // Handle file upload only if a new file is provided
       if ($request->hasFile('certificate_file')) {
        // Delete the old file if it exists
        if ($certificate->certificate_file && Storage::disk('public')->exists($certificate->certificate_file)) {
            Storage::disk('public')->delete($certificate->certificate_file);
        }

        // Save the new file
        $data['certificate_file'] = $request->file('certificate_file')->store('certificates', 'public');
    } else {
        // Keep the old file path
        $data['certificate_file'] = $certificate->certificate_file;
    }

    // Update the certificate with new data
    $certificate->update($data);

    return response()->json([
        'message' => 'Certificate updated successfully',
        'data' => $certificate,
    ], 200);
}
}

