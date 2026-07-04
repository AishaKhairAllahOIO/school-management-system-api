<?php

namespace App\Http\Resources\RoleAndPermission;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'name'             => $this->name,
            'description'      => $this->description ?? '',
            'isSystem'         => (bool) $this->is_system,
            'isActive'         => (bool) $this->is_active,
            
            // استخراج الإحصائيات التي حسبناها في السيرفيس
            'statistics' => [
                'totalPermissions'  => $this->statistics['total_permissions'] ?? 0,
                'moduleAccessCount' => $this->statistics['module_access_count'] ?? 0,
                'fullAccessCount'   => $this->statistics['full_access_count'] ?? 0,
                'restrictedCount'   => $this->statistics['limited_access_count'] ?? 0, // الفرونت يسميها restricted
            ]];
    }
}