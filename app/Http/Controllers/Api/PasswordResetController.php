<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Mail;
use App\Models\Admin;
use App\Models\UserLogin;
use App\Models\AssociativeLogin;
use App\Notifications\PasswordResetNotification;
use Illuminate\Support\Facades\Hash;

class PasswordResetController extends Controller
{
    /**
     * Handle forgot password request
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function forgotPassword(Request $request)
    {
        // Validate the input
        $request->validate([
            'email' => 'required|email',
        ]);

        // Check if email exists in Admin, UserLogin, or AssociativeLogin tables
        $user = Admin::where('email', $request->email)->first() ??
                UserLogin::where('email', $request->email)->first() ??
                AssociativeLogin::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Email not found in our records.'
            ], 404);
        }

        // Generate password reset token
        $token = app('auth.password.broker')->createToken($user);
       
        try {
            DB::table('password_resets')->insert([
                'email' => $request->email,
                'token' => $token,
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error saving token: ' . $e->getMessage()], 500);
        }


        // Send password reset notification
        $user->notify(new PasswordResetNotification($token, $request->email));

        return response()->json([
            'status' => true,
            'message' => 'Password reset link has been sent to your email.'
        ]);
    }

    public function resetPassword(Request $request)
    {
        // Validate the request input
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:6',
        ]);
    
        // Retrieve the user by email from one of your tables (Admin, UserLogin, AssociativeLogin)
        $user = Admin::where('email', $request->email)->first() ??
                UserLogin::where('email', $request->email)->first() ??
                AssociativeLogin::where('email', $request->email)->first();
    
        // Check if the user exists
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
    
        // Fetch the password reset entry from the 'password_resets' table by email
        $passwordReset = DB::table('password_resets')
            ->where('email', $request->email)
            ->first();
    
        // Check if the token exists and is valid
        if (!$passwordReset || !Hash::check($request->token, $passwordReset->token)) {
            return response()->json(['message' => 'Invalid or expired token'], 400);
        }
    
        $user->update(['password' =>  Hash::make($request->password)]);
    
        DB::table('password_resets')->where('email', $request->email)->delete();
    
        return response()->json(['message' => 'Password reset successfully'], 200);
    }

    // public function resetPassword(Request $request)
    // {
    //     $request->validate([
    //         'token' => 'required',
    //         'email' => 'required|email',
    //         'password' => 'required|confirmed|min:6',
    //     ]);
    
    //     $user = Admin::where('email', $request->email)->first() ??
    //             UserLogin::where('email', $request->email)->first() ??
    //             AssociativeLogin::where('email', $request->email)->first();
    
    //     if (!$user) {
    //         return response()->json(['message' => 'User not found'], 404);
    //     }
    
    //     // Check if the token is valid
    //     $passwordReset = DB::table('password_resets')
    //         ->where('email', $request->email)
    //         ->where('token', $request->token)
    //         ->first();
    
    //     if (!$passwordReset) {
    //         return response()->json(['message' => 'Invalid token'], 400);
    //     }
    
    //     // Update the password (no hashing here because you're not hashing passwords)
    //     $user->update(['password' => $request->password]);
    
    //     // Delete the token after successful password reset
    //     DB::table('password_resets')->where('email', $request->email)->delete();
    
    //     return response()->json(['message' => 'Password reset successfully']);
    // }
}
