<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\checkOtpRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Models\User;
use Hash;
use Ichtrojan\Otp\Models\Otp as ModelsOtp;
use Ichtrojan\Otp\Otp;
use Request;

class ResetPasswordController extends Controller
{
    private $otp;

    public function __construct()
    {
        $this->otp = new Otp;
    }

    public function checkOtp(checkOtpRequest $request)
    {
        $otp2 = ModelsOtp::where('identifier', $request->email)->
        where('token', $request->otp)->
        first();
        if (!$otp2) {
            return response()->json(['error' => 'Otp is not found'], 404);
        }

        if ($otp2->valid == 0) {
            return response()->json(['error' => 'Otp is invalid'], 401);
        }
    
        return response()->json(['message' => 'OTP verified successfully'], 200);
    }

    public function PasswordReset(ResetPasswordRequest $request)
    {
        $otp2 = $this->otp->validate($request->email, $request->otp);
        if(! $otp2->status){
            return response()->json(['error' => $otp2], 401);
        }
        $user = User::where('email', $request->email)->first();
        $user->fill([
            'password' => Hash::make($request->password)
            ])->save();
        $user->tokens()->delete();
        $success['success'] = true;
        return response()->json($success, 200);
    }

}
