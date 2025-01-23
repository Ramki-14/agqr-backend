<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\UserLogin;
use App\Models\AssociativeLogin;
use App\Models\ClientProfile;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    /**
     * Create User
     * @param Request $request
     * @return Admin | UserLogin | AssociativeLogin
     */
    public function createAdmin(Request $request)
    {
        $currentUser = Auth::user(); 

        // Check if the current user is an admin
        if (!($currentUser instanceof Admin)) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
        }
        // Admin can create a new admin
        $this->validate($request, [
            'name' => 'required',
            'email' => [
                'required',
                'email',
                function ($attribute, $value, $fail) {
                    if (
                        Admin::where('email', $value)->exists() ||
                        AssociativeLogin::where('email', $value)->exists() ||
                        UserLogin::where('email', $value)->exists()
                    ) {
                        $fail('The email has already been taken.');
                    }
                },
            ],
            'password' => 'required',
            'contact_no' => 'required',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048', 
            'role' => 'required|in:admin', // Only admins can create admins
        ]);
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('images/users', 'public');
        }

        $email = $request->email;
        $password = $request->password;
    
        \Mail::to($email)->send(new \App\Mail\UserCredentialsMail($email, $password));
    
    
        $admin = Admin::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'contact_no' => $request->contact_no,
            'image' => $imagePath,
            'role' => $request->role,
            
        ]);
    
        return response()->json([
            'status' => true,
            'message' => 'Admin created successfully',
        ], 201);
    }
    
    public function createUserLogin(Request $request)
    {
        $currentUser = Auth::user();
        if (!($currentUser instanceof Admin || $currentUser instanceof AssociativeLogin)) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
        }
    
        // Admin or associative can create a user login
        $this->validate($request, [
            'name' => 'required',
            'email' => [
                'required',
                'email',
                function ($attribute, $value, $fail) {
                    if (
                        Admin::where('email', $value)->exists() ||
                        AssociativeLogin::where('email', $value)->exists() ||
                        UserLogin::where('email', $value)->exists()
                    ) {
                        $fail('The email has already been taken.');
                    }
                },
            ],
            'password' => 'required',
            'contact_no' => 'required',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048', 
            'role' => 'required|in:user', // Only users can be created
            'category' => 'required',  // New validation for category
           
        ]);
       
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('images/users', 'public');
        }

        
        $email = $request->email;
        $password = $request->password;
    
        \Mail::to($email)->send(new \App\Mail\UserCredentialsMail($email, $password));

        $user = UserLogin::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'contact_no' => $request->contact_no,
            'category' => $request->category, 
            'image' => $imagePath,
            'role' => $request->role,
        ]);
    
        return response()->json([
            'status' => true,
            'message' => 'User created successfully',
            'data' => [
        'user_login_id' => $user->id, // Replace with the actual user ID or any required data
    ],
        ], 201);
    }

    
    public function createAssociativeLogin(Request $request)
    {
        $currentUser = Auth::user();
        if (!($currentUser instanceof Admin)) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
        }
        // Admin can create associative user
        $this->validate($request, [
            'name' => 'required',
            'email' => [
                'required',
                'email',
                function ($attribute, $value, $fail) {
                    if (
                        Admin::where('email', $value)->exists() ||
                        AssociativeLogin::where('email', $value)->exists() ||
                        UserLogin::where('email', $value)->exists()
                    ) {
                        $fail('The email has already been taken.');
                    }
                },
            ],
            'password' => 'required',
            'gst_number' => 'nullable', // GST number validation
            'company_name' => 'nullable|string|max:255',
            'contact_no' => 'required',
            'account_type' => 'required',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048', 
            'role' => 'required|in:associative', // Only associative can be created
        ]);
    
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('images/users', 'public');
        }

        
        $email = $request->email;
        $password = $request->password;
    
        \Mail::to($email)->send(new \App\Mail\UserCredentialsMail($email, $password));

        $associative = AssociativeLogin::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'contact_no' => $request->contact_no,
            'gst_number' => $request->gst_number,
            'company_name' => $request->company_name,
            'account_type' => $request->account_type,
            'image' => $imagePath,
            'role' => $request->role,
        ]);
    
        return response()->json([
            'status' => true,
            'message' => 'Associative user created successfully',
        ], 201);
    }
    
    /**
     * Login The User
     * @param Request $request
     * @return Admin | UserLogin | AssociativeLogin
     */
    public function loginUser(Request $request)
    {
        try {
            // Validate the input
            $validateUser = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required',
            ]);

            if ($validateUser->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validateUser->errors()
                ], 401);
            }

            $user = null;
            $userType = null;

            // Check the Admin table first
            $user = Admin::where('email', $request->email)->first();
            if ($user) {
                $userType = 'admin';
            }

            // If not found in Admin, check UserLogin table
            if (!$user) {
                $user = UserLogin::where('email', $request->email)->first();
                if ($user) {
                    $userType = 'user';
                }
            }

            // If not found in UserLogin, check AssociativeLogin table
            if (!$user) {
                $user = AssociativeLogin::where('email', $request->email)->first();
                if ($user) {
                    $userType = 'associative';
                }
            }

            if ($user) {
                // Validate the password (since you're not using hashing, we use direct comparison)
                if ($user && Hash::check($request->password, $user->password)) {
                    // Create Sanctum token
                    $token = $user->createToken('auth_token')->plainTextToken;

                    return response()->json([
                        'status' => true,
                        'message' => 'Login successful',
                        'token' => $token,
                        'user' => [
                            'id' => $user->id,
                            'name' => $user->name,
                            'email' => $user->email,
                            'contact_no' => $user->contact_no,
                            'image' => $user->image,
                            'role' => $user->role  // Fetch role directly from the database
                        ]
                    ], 200);
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => 'Invalid credentials',
                    ], 401);
                }
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'User not found',
                ], 404);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
    public function getAllAdmins()
    {
        $this->authorizeRequest();
        $admins = Admin::all();
        return response()->json(['status' => true, 'data' => $admins], 200);
    }
    
    public function getAllUsers()
    {
        $this->authorizeRequest();
        $users = UserLogin::all();
        return response()->json(['status' => true, 'data' => $users], 200);
    }
    
    public function getAllAssociates()
    {
        $this->authorizeRequest();
        $associates = AssociativeLogin::all();
        return response()->json(['status' => true, 'data' => $associates], 200);
    }

    public function getCompanyNames()
    {
        // Fetch distinct company names
        $companyNames = AssociativeLogin::select('company_name')->whereNotNull('company_name')->distinct()->get();

        // Check if any company names are found
        if ($companyNames->isEmpty()) {
            return response()->json(['message' => 'No company names found'], 404);
        }

        // Return the company names
        return response()->json(['company_names' => $companyNames], 200);
    }

   // Method to get all names and their corresponding GST numbers
public function getNames()
 {
    // Fetch distinct names along with their gst_number
    $namesWithGst = AssociativeLogin::select('id', 'name', 'gst_number')->distinct()->get();

    // Check if any names are found
    if ($namesWithGst->isEmpty()) {
        return response()->json(['message' => 'No names found'], 404);
    }

    // Return the names along with their gst_number
    return response()->json(['names' => $namesWithGst], 200);
}

public function getassociateNamesByCompany(Request $request)
{
    $this->authorizeRequest();
    
    // Validate the incoming request
    $validatedData = $request->validate([
        'company_name' => 'required|string',
    ]);

    // Fetch associate names and GST numbers by company name
    $associates = AssociativeLogin::where('company_name', $validatedData['company_name'])
        ->select('id','name', 'gst_number')
        ->distinct()
        ->get();

    // Check if any associates are found
    if ($associates->isEmpty()) {
        return response()->json(['message' => 'No associates found for the given company name'], 404);
    }

    // Return the associates' names and GST numbers
    return response()->json(['associates' => $associates], 200);
}

    public function getUserByEmail(Request $request)
    {
        $this->authorizeRequest();
        
        // Find the user by email in user_login table
        $user = UserLogin::where('email', $request->email)->first();
    
        if ($user) {
            // Check if the email exists in client_profiles table
            $clientProfile = ClientProfile::where('email', $request->email)->first();
            
            if ($clientProfile) {
                // Email exists in both tables
                return response()->json([
                    'status' => true,
                    'data' => [
                        'user_login_id' => $user->id,
                        'client_id' => $clientProfile->client_id,
                        'email' => $user->email
                    ]
                ], 200);
            } else {
                // Email exists only in user_login table
                return response()->json([
                    'status' => true,
                    'data' => [
                        'user_login_id' => $user->id,
                        'email' => $user->email
                    ]
                ], 200);
            }
        } else {
            // Email not found in either table
            return response()->json([
                'status' => false,
                'message' => 'Client not found'
            ], 404);
        }
    }
    
    public function getUserById(Request $request, $id)
    {
        $this->authorizeRequest();

        $user = UserLogin::find($id);

        if ($user) {
            return response()->json(['status' => true, 'data' => $user->makeHidden('password')], 200);
        } else {
            return response()->json(['status' => false, 'message' => 'User not found'], 404);
        }
    }
   
    public function getAssociateByEmail(Request $request)
    {
        $this->authorizeRequest();
        
        $associate = AssociativeLogin::where('email', $request->email)->first();
        
        if ($associate) {
            return response()->json(['status' => true, 'data' => $associate], 200);
        } else {
            return response()->json(['status' => false, 'message' => 'Associate not found'], 404);
        }
    }

    public function getAssociateByID(Request $request, $id)
    {
        $this->authorizeRequest(); // Ensure the request is authorized
    

    
        // Fetch the associate by ID
        $associate = AssociativeLogin::find($id);
    
        if ($associate) {
            return response()->json([
                'status' => true,
                'data' => $associate,
            ], 200);
        }
    
        // Fallback in case the associate is unexpectedly not found
        return response()->json([
            'status' => false,
            'message' => 'Associate not found',
        ], 404);
    }
    public function getAssociateEmailByID(Request $request, $id)
    {
        $this->authorizeRequest(); // Ensure the request is authorized
    
        // Fetch the associate by ID
        $associate = AssociativeLogin::find($id);
    
        if ($associate) {
            return response()->json([
                'status' => true,
                'email' => $associate->email, // Fetch the email field
            ], 200);
        }
    
        // Fallback in case the associate is not found
        return response()->json([
            'status' => false,
            'message' => 'Associate not found',
        ], 404);
    }

    public function updateAdmin(Request $request, $id)
{
    $currentUser = Auth::user();

    // Check if the current user is an admin
    if (!($currentUser instanceof Admin)) {
        return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
    }

    $admin = Admin::find($id);

    if (!$admin) {
        return response()->json(['status' => false, 'message' => 'Admin not found'], 404);
    }

    // Validate the request
    $this->validate($request, [
        'name' => 'nullable',
        'email' => [
            'nullable',
            'email',
            function ($attribute, $value, $fail) use ($admin) {
                if (
                    Admin::where('email', $value)->where('id', '!=', $admin->id)->exists() ||
                    AssociativeLogin::where('email', $value)->exists() ||
                    UserLogin::where('email', $value)->exists()
                ) {
                    $fail('The email has already been taken.');
                }
            },
        ],
        'password' => 'nullable|min:6',
        'contact_no' => 'nullable',
        'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        'role' => 'nullable|in:admin',
    ]);

    // Update the admin's details
    if ($request->has('name')) {
        $admin->name = $request->name;
    }

    if ($request->has('email')) {
        $admin->email = $request->email;
    }

    if ($request->has('password')) {
        $admin->password = Hash::make($request->password);
    }

    if ($request->has('contact_no')) {
        $admin->contact_no = $request->contact_no;
    }

    if ($request->has('role')) {
        $admin->role = $request->role;
    }

    if ($request->hasFile('image')) {
        // Delete the old image if it exists
        if ($admin->image) {
            Storage::disk('public')->delete($admin->image);
        }

        // Store the new image
        $admin->image = $request->file('image')->store('images/users', 'public');
    }

    $admin->save();

    return response()->json([
        'status' => true,
        'message' => 'Admin updated successfully',
        'admin' => $admin,
    ], 200);
}

    
    // Helper method to check for a valid token
    protected function authorizeRequest()
    {
        if (!Auth::guard('sanctum')->check()) {
            abort(response()->json(['status' => false, 'message' => 'Unauthorized'], 403));
        }
    }



    

    public function updateUser(Request $request)
    {
        $currentUser = Auth::user();
    
       
    
        // Validate the request data
        $this->validate($request, [
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:admins,email,' . $currentUser->id, // Update email validation to ignore current user
            'contact_no' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:8', // Optionally, you may want to hash this
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);
    
        // Update fields if they are present
        if ($request->has('name')) {
            $currentUser->name = $request->name;
        }
        if ($request->has('email')) {
            $currentUser->email = $request->email;
        };
        if ($request->has('contact_no')) {
            $currentUser->contact_no = $request->contact_no;
        };
        if ($request->has('password')) {
            $currentUser->password = Hash::make($request->password); // Hashing the password
        };
        if ($request->hasFile('image')) {
            // If there's already an image, delete the old one first
            if ($currentUser->image) {
                $imagePath = 'public/' . $currentUser->image;
                if (Storage::exists($imagePath)) {
                    Storage::delete($imagePath);
                }
            }
        
            $image = $request->file('image');
            $imageName = uniqid('user_') . '.' . $image->getClientOriginalExtension();
        
            $imagePath = $image->storeAs('images/users', $imageName, 'public');
        
            $currentUser->image = $imagePath;
        };
     
        $currentUser->save();
    
        return response()->json([
            'status' => true,
            'message' => 'User updated successfully',
            'user' => $currentUser
        ], 200);
    }
    
    public function updateUserById(Request $request, $id)
    {
        // Find the user by ID in the user_login table
        $user = UserLogin::find($id);
    
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'User not found'], 404);
        }
    
        // Store the old email for reference
        $oldEmail = $user->email;
    
        // Validate the request data
        $this->validate($request, [
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:user_login,email,' . $user->id,
            'contact_no' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:8',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);
    
        // Update user fields
        $user->name = $request->name ?? $user->name;
        if ($request->email && $request->email !== $user->email) {
            $user->email = $request->email;
    
            // Check and update the email in ClientProfile table
            $clientProfile = ClientProfile::where('email', $oldEmail)->first();
            if ($clientProfile) {
                $clientProfile->email = $request->email;
                $clientProfile->save();
            }
        }
        $user->contact_no = $request->contact_no ?? $user->contact_no;
    
        if ($request->password) {
            $user->password = Hash::make($request->password);
        }
    
        if ($request->hasFile('image')) {
            if ($user->image) {
                $oldImagePath = 'public/' . $user->image;
                if (Storage::exists($oldImagePath)) {
                    Storage::delete($oldImagePath);
                }
            }
    
            $newImage = $request->file('image');
            $imageName = uniqid('user_') . '.' . $newImage->getClientOriginalExtension();
            $imagePath = $newImage->storeAs('images/users', $imageName, 'public');
            $user->image = $imagePath;
        }
    
        $user->save();
    
        return response()->json(['status' => true, 'message' => 'User updated successfully', 'user' => $user], 200);
    }
    
    
    
    public function updateAssociateById(Request $request, $id)
    {
        // Find the associate by ID in the associative_login table
        $associate = AssociativeLogin::find($id);
    
        if (!$associate) {
            return response()->json(['status' => false, 'message' => 'Associate not found'], 404);
        }
    
        // Validate the request data
        $this->validate($request, [
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:associative_login,email,' . $associate->id,
            'contact_no' => 'nullable|string|max:20',
            'gst_number' => 'nullable', // Validate GST format (15 digits)
            'company_name' => 'nullable|string|max:255',
            'account_type' => 'nullable|string|max:255',
            'password' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);
    
        // Update associate fields
        $associate->name = $request->name ?? $associate->name;
        $associate->email = $request->email ?? $associate->email;
        $associate->contact_no = $request->contact_no ?? $associate->contact_no;
        $associate->gst_number = $request->gst_number ?? $associate->gst_number;
        $associate->company_name = $request->company_name ?? $associate->company_name;
        $associate->account_type = $request->account_type ?? $associate->account_type;
    
        if ($request->password) {
            $associate->password = Hash::make($request->password);
        }
    
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($associate->image) {
                $oldImagePath = 'public/' . $associate->image;
                if (Storage::exists($oldImagePath)) {
                    Storage::delete($oldImagePath);
                }
            }
    
            // Store new image
            $newImage = $request->file('image');
            $imageName = uniqid('associate_') . '.' . $newImage->getClientOriginalExtension();
            $imagePath = $newImage->storeAs('images/associates', $imageName, 'public');
            $associate->image = $imagePath;
        }
    
        $associate->save();
    
        return response()->json([
            'status' => true,
            'message' => 'Associate updated successfully',
            'associate' => $associate,
        ], 200);
    }
    

public function updateAdminById(Request $request, $id)
{
    // Find the admin by ID in the admin table
    $admin = Admin::find($id);

    if (!$admin) {
        return response()->json(['status' => false, 'message' => 'Admin not found'], 404);
    }

    // Validate the request data
    $this->validate($request, [
        'name' => 'nullable|string|max:255',
        'email' => 'nullable|email|unique:admins,email,' . $admin->id,
        'contact_no' => 'nullable|string|max:20',
        'password' => 'nullable|string|min:8',
        'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
    ]);

    // Update admin fields
    $admin->name = $request->name ?? $admin->name;
    $admin->email = $request->email ?? $admin->email;
    $admin->contact_no = $request->contact_no ?? $admin->contact_no;

    if ($request->password) {
        $admin->password = Hash::make($request->password);
    }

    if ($request->hasFile('image')) {
        if ($admin->image) {
            $oldImagePath = 'public/' . $admin->image;
            if (Storage::exists($oldImagePath)) {
                Storage::delete($oldImagePath);
            }
        }

        $newImage = $request->file('image');
        $imageName = uniqid('admin_') . '.' . $newImage->getClientOriginalExtension();
        $imagePath = $newImage->storeAs('images/admins', $imageName, 'public');
        $admin->image = $imagePath;
    }

    $admin->save();

    return response()->json(['status' => true, 'message' => 'Admin updated successfully', 'admin' => $admin], 200);
}

public function dashboardStats ()
{
    // Client counts by type
    $directClients = UserLogin::where('category', 'direct')->count();
    $walkInClients = UserLogin::where('category', 'walkin')->count();
    $consultantClients = UserLogin::where('category', 'consultant')->count();
    $agqrClients = UserLogin::where('category', ['direct', 'walkin', 'consultant'])->count();


    // Associate clients
    $associateClients = UserLogin::whereNotIn('category', ['direct', 'walkin', 'consultant'])->count();

    // Associates count by type
    $companyAssociates = AssociativeLogin::where('account_type', 'company')->count();
    $individualAssociates = AssociativeLogin::where('account_type', 'individual')->count();

    // Total counts
    $totalClients = UserLogin::count();
    $totalAssociates = AssociativeLogin::count();
    $adminCount = Admin::count();

 // Total profiles (sum of all counts)
     $totalProfiles = $totalClients + $totalAssociates + $adminCount;


    // Return response
    return response()->json([
        'clients' => [
            'direct' => $directClients,
            'walkin' => $walkInClients,
            'consultant' => $consultantClients,
            'agqr_clients' => $agqrClients,
            'associate_clients' => $associateClients,
            'total' => $totalClients,
        ],
        'associates' => [
            'company' => $companyAssociates,
            'individual' => $individualAssociates,
            'total' => $totalAssociates,
        ],
        'admins' => $adminCount,
        'total_profile' => $totalProfiles
    ]);
}


}
