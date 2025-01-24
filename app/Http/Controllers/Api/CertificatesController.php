<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\ClientProfile;
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

public function index()
{
    // Eager load the related order and client profile
    $certificates = Certificate::with([
        'order.clientProfile:client_id,client_name,address'
    ])->get();

    $response = $certificates->map(function ($certificate) {
        return [
            'certificate_id' => $certificate->id,
            'certificate_reg_no' => $certificate->certificate_reg_no,
            'client_name' => $certificate->order->clientProfile->client_name ?? null,
            'client_address' => $certificate->order->clientProfile->address ?? null,
            'product_name' => $certificate->product_name, // Example field
            'next_surveillance' => $certificate->next_surveillance,
            'valid_until' => $certificate->valid_until,
            'status' => $certificate->status,
            
        ];
    });

    return response()->json($response, 200);
}


    public function getClientDetails(Request $request)
    {
        $request->validate([
            'certificate_reg_no' => 'required|string|exists:certificates,certificate_reg_no',
        ]);

        $certificateRegNo = $request->certificate_reg_no;

        // Find the certificate by certificate_reg_no
        $certificate = Certificate::where('certificate_reg_no', $certificateRegNo)->first();

        if (!$certificate) {
            return response()->json(['error' => 'Certificate not found'], 404);
        }

        // Find the related order
        $order = Order::find($certificate->order_id);

        if (!$order) {
            return response()->json(['error' => 'Order not found for the certificate'], 404);
        }

        // Find the related client profile
        $clientProfile = ClientProfile::where('client_id', $order->client_id)->first();

        if (!$clientProfile) {
            return response()->json(['error' => 'Client profile not found for the order'], 404);
        }

        // Construct the response data
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
            'certificate_reg_no' => $certificate->certificate_reg_no,
            'issue_no' => $certificate->issue_no,
            'initial_approval' => $certificate->initial_approval,
            'date_of_issue' => $certificate->date_of_issue,
            'next_surveillance' => $certificate->next_surveillance,
            'valid_until' => $certificate->valid_until,
            'status' => $certificate->status,
            'certificate_file' => $certificate->certificate_file,
        ];

        return response()->json($data, 200);
    }

    public function getOrderDetailsByCertificateRegNo(Request $request, $certificate_reg_no)
    {
        // Fetch the certificate with its related order
        $certificate = Certificate::where('certificate_reg_no', $certificate_reg_no)
            ->with('order') // Load the associated order
            ->first();

        if ($certificate && $certificate->order) {
            return response()->json([
                'status' => true,
                'data' => [
                    'client_id' => $certificate->order->client_id,
                    'order_id' => $certificate->order->id,
                    'product_name' => $certificate->order->product_name,
                ],
            ], 200);
        }

        return response()->json([
            'status' => false,
            'message' => 'Certificate or associated order not found.',
        ], 404);
    }
}


