<?php

namespace App\Http\Resources\Staff;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StaffFinancialContractResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
      return [
            'id'             => $this->id,
            'staff_id'       => $this->staff_id,
            'academic_year'  => $this->academic_year_id,
            'salary_type'    => $this->salary_type, // per_period أو fixed_monthly
            'salary_amount'  => $this->salary_amount,
            'created_at'     => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
