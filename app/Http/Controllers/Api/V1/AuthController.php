<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Jobs\UserCreated;
use App\Models\Client;
use App\Models\User;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    use ApiResponses;

    /**
     * Admin & User Login.
     *
     * @param LoginRequest $request
     * @return JsonResponse
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $request->validated($request->all());

        if (!Auth::attempt($request->only('email', 'password'))) {
            return $this->failed('Wrong Credentials', Response::HTTP_UNAUTHORIZED);
        }

        $user = User::where('email', $request->input('email'))->first();

        $token = $user->createToken('API token of ' . $user->name)->plainTextToken;

        $data = ['token' => $token, 'user' => new UserResource($user)];

        return $this->success($data, 'Done', Response::HTTP_ACCEPTED);
    }

    /**
     * Admin & User Registration.
     *
     * @param RegisterRequest $request
     * @return JsonResponse
     */
    public function registration(RegisterRequest $request): JsonResponse
    {
        $request->validated($request->all());
        $user = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
            'type' => 'client'
        ]);

        Client::create([
            'display_name' => $request->input('name'),
            'phone' => $request->input('phone'),
            'user_id' => $user->id,
            'wallet' => 0
        ]);

        $token = $user->createToken('API token of ' . $user->name)->plainTextToken;

        $data = ['token' => $token, 'user' => new UserResource($user)];

        UserCreated::dispatch($user->toArray())->onQueue('messageing');

        return $this->success($data, 'Done', Response::HTTP_CREATED);
    }

    /**
     * Admin & User Logout.
     *
     * @return JsonResponse
     */
    public function logout(): JsonResponse
    {
        Auth::user()->currentAccessToken()->delete();
        return $this->success(null, 'You have successfully been logged out.', Response::HTTP_NO_CONTENT);
    }

    public function fcmToken(Request $request)
    {
        auth()->user()->update([
            'fcm_token' => $request->fcm_token,
        ]);

        return response()->json([
            'status' => 'success',
        ], 200);
    }
}
