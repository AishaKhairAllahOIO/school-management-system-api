<?php

namespace App\Http\Resources\Finance;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FinancialAccountResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                         => (string) $this->id,
            'studentId'                  => (string) $this->student_id,
            'academicYearId'             => (string) $this->academic_year_id,
            
            'totalRequiredAmount'        => (float) $this->total_required_amount,
            'remainingBalance'           => (float) $this->remaining_balance,
            'paymentStatus'              => $this->payment_status,
            'contractActivationSnapshot' => $this->contract_activation_snapshot,
            
            'feePlan'                    => new FeePlanResource($this->whenLoaded('feePlan')),
            'installmentPolicy'          => new InstallmentPolicyResource($this->whenLoaded('installmentPolicy')),
            'installments'               => ScheduledInstallmentResource::collection($this->whenLoaded('scheduledInstallments')),
            
            'createdAt'                  => $this->created_at ? \Carbon\Carbon::parse($this->created_at)->toIso8601String() : null,
            'updatedAt'                  => $this->updated_at ? \Carbon\Carbon::parse($this->updated_at)->toIso8601String() : null,
        ];
    }
}