<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class User extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id'            => $this->id,
            'name'          => $this->name,
            'email'         => $this->email,
            'updated_at'    => $this->updated_at ? $this->updated_at->format('d/m/Y') : $this->created_at->format('d/m/Y'),
            'company'       => $this->company?->name,
            'employee_info' => $this->employee,
        ];
    }
}
