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
        $toArr = [
            'name' => $this->name,
            'image' => $this->image_path,
            'role' => $this->role
        ];

        $loggedInUser = auth()->guard('api')->user();

        if ($loggedInUser->role == 'su') {
            $toArr['email'] = $this->email;
            $toArr['email_prefix'] = $this->email_prefix;
            $toArr['phone'] = $this->phone;
            $toArr['phone_prefix'] = $this->phone_prefix;
        } else if ($loggedInUser->role == 'admin') {
            $toArr['email'] = $this->email_prefix;
            $toArr['phone'] = $this->phone_prefix;
        } else {
            if ($loggedInUser->id === $this->id) {
                $toArr['email'] = $this->email;
                $toArr['email_prefix'] = $this->email_prefix;
                $toArr['phone'] = $this->phone;
                $toArr['phone_prefix'] = $this->phone_prefix;
            }
        }

        return $toArr;
    }
}
