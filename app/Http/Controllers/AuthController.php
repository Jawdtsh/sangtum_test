<?php

namespace App\Http\Controllers;

use App\Events\UserRegisteredEvent;
use App\Events\VerifyUserEvent;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\Verify2FaRequest;
use App\Http\Requests\verifyCodeRequest;
use App\Models\User;
use App\Traits\VerifiableCodeTrait;
//use http\Env\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    use VerifiableCodeTrait;
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
        $token = $user->createToken('Personal Access Token',['*'],now()->addMinutes(10))->plainTextToken;

        //fire event UserRegisteredEvent
        event(new UserRegisteredEvent($user));

        // Return success response
        return response()->json([
            'user' => $user,
            'token' => $token,
        ], 201);
    }


    public function login(LoginRequest $request)
    {
        $identifier  = $request->identifier;
        $password = $request->password;

        $user = User::where('email', $identifier )->orWhere('phone_number', $identifier )->first();

        //Check the user and his password if they are correct.
        if (!$user || !Hash::check($password, $user->password)) {
            return response()->json(['message' => 'بيانات تسجيل الدخول غير صحيحة'], 401);
        }

//        Verify email confirmation
        if(!$user->email_verified){
            return response()->json([
                'message'=>'يبدو انك لم تقم بتاكيد حسابك حتى الان, ارجو ان تقوم بتاكيد حسابك لتسجيل الدخول بنجاح'],
                403
            );
        }


        $code = self::generateCode();
        $user->update(['email_verification_code' => $code]);
        event(new VerifyUserEvent($user));

        return response()->json(['message'=>'لقد تم ارسال رمز 2fa لمتابعة تسجيل الدخول نرجو تاكيد الرمز']);



    }

    public function logout(Request $request): JsonResponse
    {
        // Revoke the token that was used to authenticate the current request
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'تم تسجيل الخروج بنجاح']);
    }

    public function refreshToken(Request $request): JsonResponse
    {
        if(Auth::user()) {
            $user = $request->user();
            $user->currentAccessToken()->delete();
            $newToken = $user->createToken('Personal Access Token', ['*'], now()->addMinutes(20))->plainTextToken;

            return response()->json(['token' => $newToken], 201);
        }
        return response()->json(['message'=>'يبدو بانك لم تقم بتسجيل الدخول']);

    }



    public function SendVerifyEmail(): JsonResponse
    {
        $code = self::generateCode();
        $user = Auth::user();

        if ($user instanceof User) {

            $user->update(['email_verification_code' => $code]);

            event(new VerifyUserEvent($user));

            return response()->json(['message'=>'لقد تم إرسال رمز التاكيد الى الايميل']);
        }
        return response()->json(['message' => 'لم يتم العثور على المستخدم، يرجى تسجيل الدخول'], 401);
    }

    public function verifyEmailCode(verifyCodeRequest $request): JsonResponse
    {

        $user = User::where('id',auth()->id())->first();

        if($request->email_verification_code === $user->email_verification_code){
            $user->email_verified = true;
            $user->save();
            return response()->json(['message'=>'لقد تم تاكيد حساب']);
        }

        return response()->json(['message'=>'يبدو بان الرمز الذي قمت بادخاله غير صحيحا'],400);
    }


    public function verify2FA(Verify2FaRequest $request): JsonResponse
    {

        $user = User::where('id', auth()->id())->first();

        //check code id success
        if ($request->Code2FA === $user->email_verification_code) {

            // Create API token for the user
            $token = $user->createToken('Personal Access Token',['*'],now()->addMinutes(10))->plainTextToken;


            return response()->json([
                'user' => $user,
                'token' => $token,
            ],201);
        }

        return response()->json(['message'=>'يبدو بان الرمز الذي ادخلته غير صحيح'],400);
    }

}
