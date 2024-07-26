<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Traits\VerifiableCodeTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{

    public function register(RegisterRequest $request): JsonResponse
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


    public function login(LoginRequest $request): JsonResponse
    {
        $login = $request->login;
        $password = $request->password;

        $user = User::where('email', $login)->orWhere('phone_number', $login)->first();

        //Check the user and his password if they are correct.
        if (!$user || !Hash::check($password, $user->password)) {
            return response()->json(['message' => 'بيانات تسجيل الدخول غير صحيحة'], 401);
        }
        // Create API token for the user
        $token = $user->createToken('Personal Access Token')->plainTextToken;

        // Return success response
        return response()->json([
            'user' => $user,
            'token' => $token,
        ], 200);
    }

    public function logout(Request $request)
    {
        // Revoke the token that was used to authenticate the current request
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'تم تسجيل الخروج بنجاح']);
    }


}
