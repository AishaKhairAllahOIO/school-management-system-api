<?php

namespace App\Http\Resources\Scheduale;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminScheduleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'is_perfect'     => $this->is_perfect,
            'quality_report' => $this->quality_report,
            'classes'        => $this->classes,
        ];
    }
}
