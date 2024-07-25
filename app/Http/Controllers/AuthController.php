<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Models\User;
//use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {

        // Handle file uploads
        $profilePhotoPath = $request->file('profile_photo') ? $request->file('profile_photo')->store('profile_photos', 'public') : null;
        $certificatePath = $request->file('certificate') ? $request->file('certificate')->store('certificates', 'public') : null;

        // Create a new user
        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'profile_photo' => $profilePhotoPath,
            'certificate' => $certificatePath,
            'password' => Hash::make($request->password),
            'email_verified' => false,
            'email_verification_code' => null
        ]);

        // Create API token for the user
        $token = $user->createToken('Personal Access Token')->plainTextToken;

        // Return success response
        return response()->json([
            'user' => $user,
            'token' => $token,
        ], 201);
    }




}
