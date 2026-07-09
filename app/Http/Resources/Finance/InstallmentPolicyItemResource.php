<?php

namespace App\Http\Resources\Finance;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InstallmentPolicyItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return       [
            'id'                => (string) $this->id,
            'installmentNumber' => (int) $this->installment_number,
            'title'             => $this->title,
            'percentage'        => (float) $this->percentage,
            'dueMonth'          => (int) $this->due_month,
            'dueDay'            => (int) $this->due_day,
            'createdAt'         => $this->created_at?->toIso8601String(),
            'updatedAt'         => $this->updated_at?->toIso8601String(),
        ];
    }
}
