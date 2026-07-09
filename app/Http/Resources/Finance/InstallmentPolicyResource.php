<?php

namespace App\Http\Resources\Finance;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InstallmentPolicyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
          return [
            'id'                => (string) $this->id,
            'name'              => $this->name,
            'installmentsCount' => (int) $this->installments_count,
            'items'             => InstallmentPolicyItemResource::collection($this->whenLoaded('items')),
            'createdAt'         => $this->created_at?->toIso8601String(),
            'updatedAt'         => $this->updated_at?->toIso8601String(),
        ];
    }
}
