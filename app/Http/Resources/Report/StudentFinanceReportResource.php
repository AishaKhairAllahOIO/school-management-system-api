<?php

namespace App\Http\Resources\Report;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentFinanceReportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
       return [
            'total_expected_revenue'   => $this['total_expected_revenue'],
            'total_collected_revenue'  => $this['total_collected_revenue'],
            'total_outstanding_amount' => $this['total_outstanding_amount'],
            'overall_collection_rate'  => $this['overall_collection_rate'] . '%',
            'total_payments_count'     => $this['total_payments_count'],
        ];
    }
}
