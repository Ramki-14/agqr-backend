<?php

use App\Http\Controllers\Api\AssociateClientCertificateController;
use App\Http\Controllers\Api\AssociateClientController;
use App\Http\Controllers\Api\AssociatePaymentController;
use App\Http\Controllers\Api\AssociateClientOrderController;
use App\Http\Controllers\Api\CertificatesController;
use App\Http\Controllers\Api\CGSTController;
use App\Http\Controllers\Api\ClientProfileController;
use App\Http\Controllers\Api\CommandController;
use App\Http\Controllers\Api\FileController;
use App\Http\Controllers\Api\IGSTController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PasswordResetController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\SGSTController;
use App\Http\Controllers\Api\TaxController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;  // Corrected
use App\Models\Admin;
use App\Models\AssociativeLogin;
use App\Models\UserLogin;
use Illuminate\Support\Facades\Log;

/*
|---------------------------------------------------------------------------
| API Routes
|---------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('/register', [UserController::class, 'createUser']);

Route::post('/login', [UserController::class, 'loginUser']);


// Middleware to protect routes for different user types
Route::post('/run-certificate-status', [CommandController::class, 'runCertificateStatus'])->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        $user = $request->user();

        $user->makeHidden('password');
       
        if ($user instanceof Admin) {
            return response()->json(['status' => true, 'user' => $user], 200);
        } elseif ($user instanceof UserLogin) {
            return response()->json(['status' => true, 'user' => $user], 200);
        } elseif ($user instanceof AssociativeLogin) {
            return response()->json(['status' => true, 'user' => $user], 200);
        }

        return response()->json(['status' => false, 'message' => 'User type not recognized'], 403);
    });
});



Route::middleware('auth:sanctum')->group(function () {
    // Existing routes...
    
    Route::post('/admin/create', [UserController::class, 'createAdmin']);
    Route::post('/user/create', [UserController::class, 'createUserLogin']);
    Route::post('/associative/create', [UserController::class, 'createAssociativeLogin']);
});
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/admins', [UserController::class, 'getAllAdmins']);
    Route::get('/users', [UserController::class, 'getAllUsers']);
    Route::get('/associates', [UserController::class, 'getAllAssociates']);
    Route::get('/associative/company-names', [UserController::class, 'getCompanyNames']);
    Route::post('/associates/by-company', [UserController::class, 'getassociateNamesByCompany']);

Route::get('/associative/names', [UserController::class, 'getNames']);
    Route::get('/user/details', [UserController::class, 'getUserByEmail']);
    Route::get('/user/details/{id}', [UserController::class, 'getUserById']);
    Route::get('/associate/details', [UserController::class, 'getAssociateByEmail']);
    Route::get('/associate/details/{id}', [UserController::class, 'getAssociateByID']);
    Route::post('/update/user/{id}', [UserController::class, 'updateUserById']);
    Route::post('/update/associate/{id}', [UserController::class, 'updateAssociateById']);
    Route::post('/update/admin/{id}', [UserController::class, 'updateAdminById']);
    Route::get('/get_DashboardStats', [UserController::class, 'dashboardStats']);
});

Route::middleware('auth:sanctum')->post('/admin/{id}', [UserController::class, 'updateAdmin']);

Route::post('/forgot-password', [PasswordResetController::class, 'forgotPassword']);
Route::get('/reset-password', function () {
    return view('auth.reset-password'); // Your view for the password reset form
})->name('password.reset');

Route::post('/reset-password', [PasswordResetController::class, 'resetPassword']);

Route::post('/update-user', [UserController::class, 'updateUser'])->middleware('auth:sanctum');

// Route::post('test-update', function (Request $request) {
//     Log::info('Test Request Data:', $request->all());
//     return response()->json([
//         'status' => true,
//         'message' => 'Test Data received',
//         'data' => $request->input('name')
//     ]);
// });

Route::middleware('auth:sanctum')->group(function () {
    // Routes that require authentication
    Route::post('/create-client-profiles', [ClientProfileController::class, 'store']); // Create Client Profile
    Route::post('/update-client-profiles/{client_id}', [ClientProfileController::class, 'updateClientProfile']); // Update Client Profile
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/associate-client', [AssociateClientController::class, 'store']);
    Route::get('associate/client-name', [AssociateClientController::class, 'getAllClientNames']);
Route::post('validate-associate-client', [AssociateClientController::class, 'validateAssociateClient']);
Route::get('/associate-client-details/{id}', [AssociateClientController::class, 'getAssociateClientById']);
Route::post('/associate-client-update/{id}', [AssociateClientController::class, 'update']);
Route::get('/associate-clients', [AssociateClientController::class, 'index']);
  
});

Route::middleware('auth:sanctum')->group(function () {
    // Routes that require authentication
    Route::get('/client-profiles/{client_id}', [ClientProfileController::class, 'show']); // Retrieve Client Profile
    Route::get('/client-profiles', [ClientProfileController::class, 'index']); // Get all client profiles
});


Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/products', [ProductController::class, 'index']); // List products
    Route::post('/products', [ProductController::class, 'store']); // Add a product
    Route::post('/edit-products/{id}', [ProductController::class, 'update']); // Update a product
    Route::delete('/products/{id}', [ProductController::class, 'destroy']); // Delete a product

});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/addsgst', [SGSTController::class, 'store']);
    Route::get('/allsgst', [SGSTController::class, 'getAll']);

    Route::post('/addcgst', [CGSTController::class, 'store']);
    Route::get('/allcgst', [CGSTController::class, 'getAll']);

    Route::post('/addigst', [IGSTController::class, 'store']);
    Route::get('/alligst', [IGSTController::class, 'getAll']);
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders/{client_id}', [OrderController::class, 'getOrdersByClientId']);
    Route::post('/payment-details', [OrderController::class, 'showPaymentOptions']);
    Route::delete('/orders/delete', [OrderController::class, 'deleteOrder']);
    Route::post('/orders/update', [OrderController::class, 'updateOrder']);
    Route::post('/order-details', [OrderController::class, 'showOrderDetails']);

});
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/save-payment-details', [PaymentController::class, 'savePayment']);
});


Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/certificates', [CertificatesController::class, 'store']);
    Route::get('/all-certificates', [CertificatesController::class, 'index']);
    Route::post('/check-certificate', [CertificatesController::class, 'checkCertificate']);
    Route::post('/certificates/fetch', [CertificatesController::class, 'fetchCertificate']);
    Route::post('/certificates/{id}', [CertificatesController::class, 'update']);
    Route::post('/get-client-details', [CertificatesController::class, 'getClientDetails']);
});
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/associate-client-orders', [AssociateClientOrderController::class, 'store']);
    Route::get('/associate-client-orders/{client_id}', [AssociateClientOrderController::class, 'getClientOrdersByClientId']);
    Route::post('/associate-orders/update', [AssociateClientOrderController::class, 'updateAssociateOrder']);
    Route::post('/associate-orders-details', [AssociateClientOrderController::class, 'showAssociateClientOrders']);
    Route::post('/associate-client-order-details', [AssociateClientOrderController::class, 'showOrderDetails']);
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/associate-payments', [AssociatePaymentController::class, 'index']); // Get all
    Route::post('/payment-recipts-save', [AssociatePaymentController::class, 'store']); // Create payment receipt
    Route::get('/payment-recipts', [AssociatePaymentController::class, 'getPaymentRecipts']); // Get all payment receipts
    Route::get('/payment-recipts-history', [AssociatePaymentController::class, 'getAssociatePaymentReceipts']); // Get all payment receipts
});
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/associate-client-certificate', [AssociateClientCertificateController::class, 'store']);
    Route::post('/associate-check-certificate', [AssociateClientCertificateController::class, 'checkCertificate']);
    Route::post('/associate-certificates/fetch', [AssociateClientCertificateController::class, 'fetchCertificate']);
    Route::post('/ba-certificates-update/{id}', [AssociateClientCertificateController::class, 'update']);
});

Route::get('/download-file/{filePath}', [FileController::class, 'downloadFile'])
    ->where('filePath', '.*');


// Fallback for unauthenticated users
Route::get('/auth', function (Request $request) {
    return response()->json(['message' => 'Please login first']);
})->name('auth');