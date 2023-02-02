<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\ImageResource;
use TimeHelp;
use Carbon\Carbon;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $createdTimezone = $this->created_tz !== 'SYSTEM' ? $this->created_tz : $this->tz;
        $updatedTimezone = $this->updated_tz !== 'SYSTEM' ? $this->updated_tz : $this->tz;

        $array = [
            'id' => $this->id,
            'uuid' => $this->product_uuid,
            'name' => $this->product_name,
            'condition' => ($this->condition === 'N' ? 'Baru' : 'Bekas / Pre-Loved'),
            'price_initial' => $this->initial_price,
            'price_net' => $this->net_price,
            'discount_percent' => $this->disc_percent,
            'discount_price' => $this->disc_price,
            'weight_in_gram' => $this->weight_g,
            'minimal_purchase' => $this->min_purchase,
            'store_id' => $this->store_id,
            'category_id' => $this->category_id,
            'category_name' => $this->category_name,
            'is_sub_category' => ($this->is_sub_category === 0? false : true),
            'category_parent_id' => $this->category_parent_id,
            'category_parent_name' => $this->category_parent_name,
            'city' => $this->city_name,
            'province' => $this->province_name,
            'images' => ImageResource::collection($this->image)
        ];

        if ($this->created_tz !== 'SYSTEM') {
            $array['created_at'] = Carbon::parse($this->created_at)->format('c');
        } else {
            $createdTimezone = $this->created_tz !== 'SYSTEM' ? $this->created_tz : $this->tz;
            $array['created_at'] = TimeHelp::convertTz($this->created_at, $createdTimezone, 'UTC');
        }

        if ($this->updated_tz !== 'SYSTEM') {
            $array['last_updated_at'] = Carbon::parse($this->updated_at)->format('c');
        } else {
            $updatedTimezone = $this->updated_tz !== 'SYSTEM' ? $this->updated_tz : $this->tz;
            $array['last_updated_at'] = TimeHelp::convertTz($this->updated_at, $updatedTimezone, 'UTC');
        }

        return $array;
    }
}
