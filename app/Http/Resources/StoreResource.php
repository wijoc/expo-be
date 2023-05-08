<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use TimeHelp;
use Carbon\Carbon;

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
            'domain' => $this->domain,
            'store_name' => $this->store_name,
            'city' => $this->city->name,
            'city_ro_code' => $this->city->ro_api_code,
            'province' => $this->province->name,
            'store_image_path' => 'storage/'.$this->image_path,
            'store_image_mime' => $this->image_mime
        ];

        $this->email ? $array['email'] = $this->email : false;
        $this->phone ? $array['phone'] = $this->phone : false;
        $this->whatsapp ? $array['whatsapp'] = $this->whatsapp : false;
        $this->description ? $array['description'] = $this->description : false;
        $this->full_address ? $array['full_address'] = $this->full_address : false;

        if ($this->created_tz !== 'SYSTEM') {
            $array['registered_at'] = Carbon::parse($this->created_at)->format('c');
        } else {
            $createdTimezone = $this->created_tz !== 'SYSTEM' ? $this->created_tz : $this->tz;
            $array['registered_at'] = TimeHelp::convertTz($this->created_at, $createdTimezone, 'UTC');
        }

        if ($this->updated_tz !== 'SYSTEM') {
            $array['last_updated_at'] = Carbon::parse($this->updated_at)->format('c');
        } else {
            $updatedTimezone = $this->updated_tz !== 'SYSTEM' ? $this->updated_tz : $this->tz;
            $array['last_updated_at'] = TimeHelp::convertTz($this->updated_at, $updatedTimezone, 'UTC');
        }

        if ($this->products && count($this->products) >= 0) {
            $array['products'] = $this->products;
        }

        return $array;
    }
}
