<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ImageResource extends JsonResource
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
            'id' => $this->id,
            'product_uuid' => $this->product_uuid,
            'mime' => $this->mime,
            'path' => $this->path,
            // 'added_tz' => $this->created_tz,
            'added_at' => $this->created_at
        ];
    }
}
