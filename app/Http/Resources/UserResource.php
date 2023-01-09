<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        // return parent::toArray($request);
        return [
            'name' => $this->name,
            'email' => $this->email,
            'email_prefix' => $this->email_prefix,
            'phone' => $this->phone,
            'phone_prefix' => $this->phone_prefix,
            'image' => $this->image_path,
            'role' => $this->role
        ];
    }
}
