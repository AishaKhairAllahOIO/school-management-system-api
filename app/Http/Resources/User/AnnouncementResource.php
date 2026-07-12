<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnnouncementResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = $request->user();
        return [
            'id'          => $this->id,
            'audience'    => $this->audience,
            'title'       => $this->title,
            'description' => $this->description,
            'is_read'     => $user ? $this->readers->contains($user->id) : false,
            'created_at'  => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
