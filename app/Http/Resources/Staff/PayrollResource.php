<?php

namespace App\Http\Resources\Staff;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PayrollResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
       return [
            'id'           => $this->id,
            'staff_id'     => $this->staff_id,
            'contract_id'  => $this->contract_id,
            'salary_type'  => $this->whenLoaded('contract', fn() => $this->contract->salary_type),
            'year'         => $this->year,
            'month'        => $this->month,
            'payment_date' => $this->payment_date,
            'net_salary'   => $this->net_salary,
            'created_at'   => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
