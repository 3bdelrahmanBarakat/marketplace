<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\RolesAndPermission\StoreRolesAndAdminPermissionRequest;
use App\Http\Requests\RolesAndPermission\UpdateRolesAndAdminPermissionRequest;
use App\Http\Resources\RolesAndPermisson\RolesResource;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Validation\ValidationException;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:role-list')->only(['index', 'show']);
        $this->middleware('permission:role-create')->only(['store']);
        $this->middleware('permission:role-edit')->only(['update']);
        $this->middleware('permission:role-delete')->only(['destroy']);
    }


    public function index()
    {
        try {
            $roles = Role::paginate(10);
            return RolesResource::collection($roles);
        } catch (Exception $e) {
            return response()->json($e, 500);
        }
    }

    public function store(StoreRolesAndAdminPermissionRequest $request)
    {
        try {
            $data = $request->validated();
            $role = Role::create([
                'name' => $data['name'],
                'guard_name' => 'web'
            ]);
            $role->givePermissionTo($data['permissions']);
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
            return response()->json(['data' => new RolesResource($role)], 201);
        } catch (Exception $e) {
            return response()->json($e, 500);
        }
    }



    public function show($id)
    {
        try {
            $role = Role::findOrFail($id);
            if (!$role) {
                return response()->json(['error' => 'Role not found'], 404);
            }
            return new RolesResource($role);
        } catch (Exception $e) {
            return response()->json($e, 500);
        }
    }

    public function update(UpdateRolesAndAdminPermissionRequest $request, Role $role)
    {
        try {
            $validatedData = $request->validated();
            $role->update([
                'name' => $validatedData['name'],
                'guard_name' => 'web'
            ]);
            $permissions = $validatedData['permissions'] ?? [];
            $role->syncPermissions($permissions);
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
            return response()->json(['data' => new RolesResource($role)], 200);
        } catch (Exception $e) {
            return response()->json($e, 500);
        }
    }


    public function destroy($id)
    {
        try {
            $role = Role::findOrFail($id);
            if (!$role) {
                return response()->json(['error' => 'Role not found'], 404);
            }
            $role->delete();
            return response()->json(['message' => 'Role deleted successfully'], 200);
        } catch (Exception $e) {
            return response()->json($e, 500);
        }
    }
}
