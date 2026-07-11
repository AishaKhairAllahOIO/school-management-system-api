<?php

namespace App\Http\Resources\Setting;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SchoolImageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'   => (string) $this->id,
            'url'  => str_starts_with($this->url, 'http') ? $this->url : asset('storage/' . $this->url),
            'name' => $this->name,
        ];
    }
}
