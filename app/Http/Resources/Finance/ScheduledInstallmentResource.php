<?php

namespace App\Http\Resources\Finance;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScheduledInstallmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => (string) $this->id,
            'installmentNumber' => (int) $this->installment_number,
            'title'             => $this->title,
            'amountDue'         => (float) $this->amount_due,
            'amountPaid'        => (float) $this->amount_paid,
            'dueDate'           => $this->due_date ? $this->due_date->format('Y-m-d') : null,
            'status'            => $this->status, // pending, paid, overdue
            'createdAt'                  => $this->created_at ? \Carbon\Carbon::parse($this->created_at)->toIso8601String() : null,
            'updatedAt'                  => $this->updated_at ? \Carbon\Carbon::parse($this->updated_at)->toIso8601String() : null,
        ];
    }
}