<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\RolesAndPermission\AddRoleToUserRequest;
use Illuminate\Http\Request;
use Exception;
use Spatie\Permission\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AddRoleToUserContoller extends Controller

{
    public function addRoleToUser(Request $request)
    {
        $request->validate([
            'model_id' => 'required|exists:users,id|unique:model_has_roles,model_id,NULL,id,role_id,' . $request->input('role_id'),
            'role_id' => 'required|exists:roles,id',
        ]);
        try {
            $user = User::findOrFail($request->input('model_id'));
            $role = Role::findOrFail($request->input('role_id'));
            DB::table('model_has_roles')->insert([
                'model_id' => $user->id,
                'role_id' => $role->id,
                'model_type' => 'App\Models\User',
            ]);
            return response()->json(['message' => 'Role added to user successfully'], 201);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function removeRoleFromUser(Request $request)
    {
        $request->validate([
            'model_id' => 'required|exists:users,id',
        ]);
        try {
            DB::table('model_has_roles')
                ->where('model_id', $request->input('model_id'))
                ->delete();
            return response()->json(['message' => 'All role assignments for the user have been removed'], 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
