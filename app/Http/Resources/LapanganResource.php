<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LapanganResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'category' => $this->category,
            'description' => $this->description,
            'price' => (float) $this->price,
            'weekday_price' => $this->weekday_price ? (float) $this->weekday_price : null,
            'weekend_price' => $this->weekend_price ? (float) $this->weekend_price : null,
            'peak_hour_start' => $this->peak_hour_start,
            'peak_hour_end' => $this->peak_hour_end,
            'peak_hour_multiplier' => $this->peak_hour_multiplier ? (float) $this->peak_hour_multiplier : null,
            'images' => $this->images ?? [],
            'status' => $this->status,
            'status_label' => match($this->status) {
                1 => 'Active',
                0 => 'Inactive',
                2 => 'Under Maintenance',
                default => 'Unknown'
            },
            'operational_hours' => $this->getOperationalHours(),
            'is_maintenance' => (bool) $this->is_maintenance,
            'maintenance_info' => $this->getMaintenanceInfo(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
