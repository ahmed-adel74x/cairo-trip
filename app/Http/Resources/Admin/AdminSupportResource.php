<?php

namespace App\Http\Resources\Admin;

use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminSupportResource extends JsonResource
{
    use ApiResponseTrait;

    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'user'        => $this->user ? [
                'id'    => $this->user->id,
                'name'  => $this->user->name,
                'email' => $this->user->email,
            ] : null,
            'name'        => $this->name,
            'email'       => $this->email,
            'phone'       => $this->phone,
            'problem'     => $this->problem,
            'admin_reply' => $this->admin_reply,
            'status'      => $this->status,
            'status_label'=> $this->getStatusLabel($this->status),
            'created_at'  => $this->created_at->toISOString(),
            'updated_at'  => $this->updated_at->toISOString(),
        ];
    }
}