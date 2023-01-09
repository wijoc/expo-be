<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StoreResource extends JsonResource
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
        $array = [
            'id' => $this->store_id,
            'store_name' => $this->store_name,
            'store_image' => $this->image,
            'email' => $this->email,
            'phone' => $this->phone,
            'whatsapp' => $this->whatsapp,
            'description' => $this->description,
            'full_address' => $this->full_address,
            'district' => $this->district->name,
            'city' => $this->city->name,
            'city_ro_code' => $this->city->ro_api_code,
            'province' => $this->province->name
        ];

        if ($this->products && count($this->products) >= 0) {
            $array['products'] = $this->products;
        }

        return $array;
    }
}
