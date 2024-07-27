<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\RolesAndPermisson\PermissionsResource;
use Illuminate\Http\Request;
use Exception;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function index()
    {
        try {
            $permissions = Permission::all();
            return PermissionsResource::collection($permissions);
        } catch (Exception $e) {
            return response()->json($e, 500);
        }
    }
}
