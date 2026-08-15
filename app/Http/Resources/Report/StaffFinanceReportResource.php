<?php

namespace App\Http\Resources\Report;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StaffFinanceReportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
       return [
            'total_payrolls_processed' => $this['total_payrolls_processed'],
            'total_net_salaries_paid'  => $this['total_net_salaries_paid'],
            'average_salary_paid'      => $this['average_salary_paid'],
        ];
    }
}
