<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'company_id' => (int) $this->id,
            'display_name' => (string) $this->display_name?$this->display_name:null,
            'phone' => (string) $this->phone,
            'profile' => (string) $this->profile,
            'address' => (string) $this->address,
            'status' => (string) $this->status,
            'name'=> $this->user->name,
            'email'=> $this->user->email,
            'user_id'=>$this->user->id
        ];
    }
}
