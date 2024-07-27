<?php

namespace App\Http\Requests\RolesAndPermission;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class StoreRolesAndAdminPermissionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // if ($this->user()->can('ads index')) {
        //     return true;
        // }
        return true;
    }


    // public function authorize(): bool
    // {
    //     $user = Auth::user();

    //     if (!$user) {
    //         return false;
    //     }

    //     return $user->can('ads index');
    // }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'permissions' => 'required',
            'permissions.*' => 'exists:permissions,name',
            'name' => 'required', 'unique:roles,name', 'max:60',
        ];
    }
}
