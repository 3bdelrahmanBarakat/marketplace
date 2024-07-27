<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Exception;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    use ApiResponses;

    /**
     * All users
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $users = UserResource::collection(User::all());
        return $this->success($users, 'Done', Response::HTTP_OK);
    }

    /**
     * Show specific user
     *
     * @param User $user
     * @return JsonResponse
     */



    public function show(): JsonResponse
    {
        try {
            $authenticatedUserId = Auth::id();
            $user = User::findOrFail($authenticatedUserId);
            return $this->success(new UserResource($user), 'Done', Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json($e, 500);
        }
    }


    /**
     * Update specific user
     *
     * @param Request $request
     * @param User $user
     * @return JsonResponse
     */
    // public function update(Request $request): JsonResponse
    // {
    //     $user = User::findOrFail(request('id'));
    //     if ($user->id !== Auth::user()->id) {
    //         return $this->failed('Not authorized to change this user data', 401);
    //     }

    //     $user->update($request->all());
    //     return $this->success(new UserResource($user), "Done", Response::HTTP_ACCEPTED);
    // }



    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|email|unique:users,email,'.$id,
            'password' => 'sometimes|confirmed',
            'roles' => 'required|array',
        ]);
    
        $input = $request->all();
        if(isset($input['password'])){
            $input['password'] = Hash::make($input['password']);
        }
    
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        
        $user->update($input);
        $user->syncRoles($request->input('roles'));
    
        return response()->json(['message' => 'User updated successfully'], 200);
    }








    /**
     * Destroy specific user.
     *
     * @param User $user
     * @return JsonResponse|bool
     */

    public function destroy(): JsonResponse
    {
        $user = User::findOrFail(request('id'));
        $user->delete();
        return $this->success(null, 'Deleted', Response::HTTP_NO_CONTENT);
    }


    /**
     * add Gift  for specific client.
     *
     * @param Request $request
     * @param User $user
     * @return JsonResponse
     */
    public function addGift(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required',
            'wallet' => 'required|numeric'
        ]);

        $user = User::where('email', $validated['email'])->first();

        if ($user) {
            $user->wallet += $validated['wallet'];
            $user->save();



            return response()->json([
                'data' => $user->wallet,
                'message' => 'Done'
            ], Response::HTTP_ACCEPTED);
        } else {
            return response()->json([
                'message' => 'User not found'
            ], Response::HTTP_NOT_FOUND);
        }
    }


    public function getWallet(): JsonResponse
    {
        $user = User::where('id', auth()->user()->id)->first();
        $users = User::get();
        $totalWallets = User::sum('wallet');
        return $totalWallets;

        return response()->json([
            'data' => $user->wallet,
            'user_data' =>  $users,
            'message' => 'Done'
        ]);
    }



    /**
     * share Points for specific client.
     *
     * @param Request $request
     * @param User $user
     *
     */
    public function share_points_wallet(Request $request)
    {


        $validated = $request->validate([
            'email' => 'required',
            'wallet' => 'required|numeric'
        ]);
        $user = User::where('id', auth()->user()->id)->first();
        $user_shared = User::where('email', $validated['email'])
            ->whereNotIn('id', [auth()->user()->id])
            ->first();

        if ($user->wallet != 0.0 && $user->wallet > 0.0) {


            if ($user_shared) {
                $user_shared->wallet += $validated['wallet'];
                $user_shared->save();
                $user->wallet = $user->wallet - $validated['wallet'];
                $user->save();
                return response()->json([
                    'data' => $user_shared,
                    'message' => 'Done'
                ]);
            } else {
                return response()->json([
                    'message' => 'User not found'
                ],404);
            }
        } else {
            return response()->json([
                'message' => 'wallet is empty'
            ],400);
        }
    }
}
