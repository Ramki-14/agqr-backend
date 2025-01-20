<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AssociateClient;
use App\Models\AssociativeLogin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AssociateClientController extends Controller

{
    protected function authorizeRequest()
    {
        if (!Auth::guard('sanctum')->check()) {
            abort(response()->json(['status' => false, 'message' => 'Unauthorized'], 403));
        }
    }

    public function store(Request $request)
    {
        // Validate incoming request data
        $request->validate([
            'account_type' => 'required|string',
            'client_name' => 'required|string',
            'address' => 'required|string',
            'client_gst_no' => 'nullable|string',
            'Client_gst_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png,webp,doc,docx|max:20480', // 20 MB limit
            'company_name' => 'nullable|string',
            'gst_number' => 'nullable|string',
            'associate_name' => 'required|string',
        ]);

        $data = $request->all();

          // Handle file upload for client_gst_document
     if ($request->hasFile('client_gst_document')) {
        $gstDocumentPath = $request->file('client_gst_document')->store('documents/gstdocuments', 'public');
        $data['client_gst_document'] = $gstDocumentPath; // Add the path to the data array
     }

        $associateClient = AssociateClient::create($data);

        // Return response
        return response()->json(['message' => 'Associate client created successfully',
            'client_id' => $associateClient->id], 201);
    }

    public function getAllClientNames()
    {
        // Fetch all client names
        $clients = AssociateClient::select('client_name')->distinct()->get();

        // Check if there are any clients
        if ($clients->isEmpty()) {
            return response()->json(['message' => 'No clients found'], 404);
        }

        // Return the list of client names
        return response()->json(['clients' => $clients], 200);
    }

    public function validateAssociateClient(Request $request)
    {
        // Validate incoming request data
        $request->validate([
            'associate_name' => 'required|string',
            'client_name' => 'required|string',
        ]);
    
        // Verify the associate name in the associative_login table and get the associate_id
        $associate = AssociativeLogin::where('name', $request->associate_name)->first();
    
        if (!$associate) {
            return response()->json(['message' => 'Associate not found in Associative Login table'], 404);
        }
    
        $associateId = $associate->id;
    
        // Verify the client name in the associate_client table with the retrieved associate_id
        $associateClient = AssociateClient::where('associate_name', $request->associate_name)->where('client_name', $request->client_name)->first();
    
        if ($associateClient) {
            return response()->json([
                'message' => 'Client found',
                'associate_id' => $associateId,
                'client_id' => $associateClient->id,
            ], 200);
        }
    
        // If no client found, return an error message
        return response()->json(['message' => 'No registered client found with this associate and client name'], 404);
    }
    
    public function getAssociateClientById(Request $request, $id)
    {
        $this->authorizeRequest();

        $user = AssociateClient::find($id);

        if ($user) {
            return response()->json(['status' => true, 'data' => $user], 200);
        } else {
            return response()->json(['status' => false, 'message' => 'User not found'], 404);
        }
    }

      // Retrieve all client profiles
      public function index()
      {
          $associateClients = AssociateClient::all();
  
          return response()->json(['data' => $associateClients]);
      }

}
