<?php

namespace App\Http\Resources\Web;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SchoolLawResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'createdAt' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->format('Y-m-d-H-i-s'),

        ];
    }
}
