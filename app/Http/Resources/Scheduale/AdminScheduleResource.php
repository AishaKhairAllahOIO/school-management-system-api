<?php

namespace App\Http\Resources\Scheduale;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminScheduleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'quality_report' => $this->quality_report,
            'classes'        => $this->classes,
        ];
    }
}
