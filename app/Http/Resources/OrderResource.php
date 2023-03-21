<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use TimeHelp;
use Carbon\Carbon;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $array = [
            'id' => $this->id,
            'order_no' => $this->order_code,
            'order_date' => Carbon::parse($this->order_date)->format('c'),
            'discount_percent' => $this->disc_percent,
            'discount_price' => $this->disc_price,
            'delivery_method' => $this->delivery_method,
            'delivery_courier' => $this->courier->name,
            'delivery_service' => $this->courier->name.' - '.$this->delivery_service,
            'delivery estimate' => $this->delivery_etd,
            'delivery_note' => $this->delivery_note,
            'delivery_fee' => $this->delivery_fee,
            'tracking_number' => $this->tracking_number,
            'total_weigth' => $this->total_weight_g,
            'subtotal_cart' => $this->total_cart,
            'total_payment' => $this->total_payment,
            'payment_method' => $this->payment_method,
            'payment_status' => $this->payment_status == 0 ? 'Dibayar' : 'Belum Bayar',
            'payment_date' => $this->payment_date ? Carbon::parse($this->payment_date)->format('c') : null,
            'due_date' => $this->due_date,
            'return' => $this->return_status,
            'return_date' => $this->return_at,
            'return_timezone' => $this->return_tz,
            'store' => (object) [
                'id' => $this->orderstore->id,
                'store_name' => $this->orderstore->store_name,
                'domain' => $this->orderstore->domain
            ],
            'delivery_address' => (object) [
                'id' => $this->orderaddress->id,
                'recipient' => $this->orderaddress->recipient_name,
                'postal_code' => $this->orderaddress->postal_code,
                'note' => $this->orderaddress->note,
                'full_address' => $this->orderaddress->full_address,
                'district' => $this->orderaddress->district,
                'city' => $this->orderaddress->city,
                'province' => $this->orderaddress->province
            ],
        ];

        switch ($this->order_status) {
            case 'W':
                $array['order_status'] = 'Menunggu pembayaran';
                break;
            case 'A':
                $array['order_status'] = 'Dibayar, menunggu konfirmasi pembayaran';
                break;
            case 'S':
                $array['order_status'] = 'Proses pengiriman';
                break;
            case 'F':
                $array['order_status'] = 'Order selesai';
                break;
        }

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

        return $array;
    }
}
