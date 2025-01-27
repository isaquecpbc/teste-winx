<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Employee as EmployeeResource;
use App\Http\Resources\User as UserResource;
use App\Http\Resources\Company as CompanyResource;

class Employee extends JsonResource
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
            'id'                => $this->id,
            'responsibility'    => $this->responsibility,
            'admission_at'      => $this->admission_at->format('d/m/Y'),
            'phone'             => $this->phone,
            'user'              => new UserResource($this->user),
        ];
    }
}
