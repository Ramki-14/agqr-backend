<?php

namespace App\Http\Controllers\api;
use App\Models\ClientProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Models\UserLogin;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ClientProfileController extends Controller
{
    // Store a new client profile
    public function store(Request $request)
    {
        // Validate the incoming request
        $validator = Validator::make($request->all(), [
            'client_name' => 'required|string|max:255',
            'contact_person' => 'required|string|max:255',
            'email' => 'required|email|exists:user_login,email',
            'contact_no' => 'required|string|max:26',
            'address' => 'required|string',
            'category' => 'required|string',
            'gst_number' => 'string|max:20',
            'Audit_type' => 'required|string',
            'gst_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png,webp,doc,docx|max:20480', // 20 MB limit
            // 'status' => 'required|in:active,inactive,pending,following',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048', // If the image is provided, it should be an image
            'notes' => 'nullable|string',
        ],[
            'email.exists' => 'The client login with this email has not been created.', // Custom error message
        ]);

       // Retrieve user_login record by email
     $userLogin = UserLogin::where('email', $request->email)->first();

       // Check if userLogin was found, if not, return an error
       if (!$userLogin) {
        return response()->json(['errors' => ['email' => ['User login not found.']]], 400);
      }

        // Create a new client profile
        $clientProfile = new ClientProfile();
        $clientProfile->user_login_id = $userLogin->id;
        $clientProfile->client_name = $request->client_name;
        $clientProfile->contact_person = $request->contact_person;
        $clientProfile->email = $request->email;
        $clientProfile->contact_no = $request->contact_no;
        $clientProfile->address = $request->address;
        $clientProfile->category = $request->category;
        $clientProfile->gst_number = $request->gst_number;
        $clientProfile->Audit_type = $request->Audit_type;
        // $clientProfile->status = $request->status;
        $clientProfile->notes = $request->notes;

        if ($request->hasFile('gst_document')) {
            $gstDocumentPath = $request->file('gst_document')->store('documents/gstdocuments', 'public');
            $clientProfile->gst_document = $gstDocumentPath;
        }

        // Handle image upload if provided
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('images/clientimage', 'public');
            $clientProfile->image = $path;
        }

        // Save the client profile to the database
        $clientProfile->save();

        Log::info('Email value:', [$request->email]);

        return response()->json(['message' => 'Client profile created successfully', 'data' => $clientProfile], 200);
    }

    public function updateClientProfile(Request $request, $client_id)
    {
        // Retrieve the client profile by ID
        $clientProfile = ClientProfile::find($client_id);

        if (!$clientProfile) {
            return response()->json(['message' => 'Client profile not found'], 404);
        }
        if ($request->has('email') && $request->email !== $clientProfile->email) {

            $existingUser = UserLogin::where('email', $request->email)->where('id', '!=', $clientProfile->user_login_id)->first();

            if ($existingUser) {
                return response()->json(['errors' => ['email' => ['This email is already used by another user.']]], 400);
            }

            $userLogin = UserLogin::where('id', $clientProfile->user_login_id)->first();
            if (!$userLogin) {
                return response()->json(['errors' => ['email' => ['User login not found.']]], 400);
            }
            
            // Update only the email field in UserLogin
            $userLogin->email = $request->email;
            $userLogin->save();
        }
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'client_name' => 'nullable|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email' => [
                'nullable',
                'email',
                Rule::unique('client_profiles', 'email')->ignore($client_id, 'client_id'),
            ],
            'contact_no' => 'nullable|string|max:26',
            'address' => 'nullable|string',
            'category' => 'nullable|string',
            'gst_number' => 'string|max:20',
            'gst_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png,webp,doc,docx|max:20480', // 20 MB limit
            'Audit_type' => 'nullable|string',
            // 'status' => 'nullable|in:active,inactive,pending,following',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'notes' => 'nullable|string',
        ], [
            'email.exists' => 'The user login with this email has not been created.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        // Update fields if they are present
        if ($request->has('client_name')) $clientProfile->client_name = $request->client_name;
        if ($request->has('contact_person')) $clientProfile->contact_person = $request->contact_person;
        if ($request->has('email')) $clientProfile->email = $request->email;
        if ($request->has('contact_no')) $clientProfile->contact_no = $request->contact_no;
        if ($request->has('address')) $clientProfile->address = $request->address;
        if ($request->has('category')) $clientProfile->category = $request->category;
        if ($request->has('gst_number')) $clientProfile->gst_number = $request->gst_number;
        if ($request->has('Audit_type')) $clientProfile->Audit_type = $request->Audit_type;
        // if ($request->has('status')) $clientProfile->status = $request->status;
        if ($request->has('notes')) $clientProfile->notes = $request->notes;

        if ($request->hasFile('gst_document')) {
            if ($clientProfile->gst_document) {
                $existingDocumentPath = 'public/' . $clientProfile->gst_document;
                if (Storage::exists($existingDocumentPath)) {
                    Storage::delete($existingDocumentPath);
                }
            }
        
            $gstDocument = $request->file('gst_document');
            $documentName = uniqid('gst_') . '.' . $gstDocument->getClientOriginalExtension();
            $documentPath = $gstDocument->storeAs('documents/gstdocuments', $documentName, 'public');
            $clientProfile->gst_document = $documentPath;
        }
        // Handle image upload if provided
        if ($request->hasFile('image')) {
            if ($clientProfile->image) {
                $existingImagePath = 'public/' . $clientProfile->image;
                if (Storage::exists($existingImagePath)) {
                    Storage::delete($existingImagePath);
                }
            }

            $image = $request->file('image');
            $imageName = uniqid('client_') . '.' . $image->getClientOriginalExtension();
            $imagePath = $image->storeAs('images/clientimage', $imageName, 'public');
            $clientProfile->image = $imagePath;
        }

        // Save the changes
        $clientProfile->save();

        return response()->json([
            'status' => true,
            'message' => 'Client profile updated successfully',
            'data' => $clientProfile
        ], 200);
    }

    // Retrieve a single client profile by ID
    public function show($client_id)
    {
        $clientProfile = ClientProfile::find($client_id);

        if (!$clientProfile) {
            return response()->json(['message' => 'Client profile not found'], 404);
        }

        return response()->json(['data' => $clientProfile]);
    }

    // Retrieve all client profiles
    public function index()
    {
        $clientProfiles = ClientProfile::all();

        return response()->json(['data' => $clientProfiles]);
    }
}
