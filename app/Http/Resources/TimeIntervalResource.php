<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TimeIntervalResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'intervalable_id' => $this->intervalable_id,
            'intervalable_type' => $this->intervalable_type,
            'start_time' => $this->start_time,
            'finish_time' => $this->finish_time,
            'duration' => $this->duration,
        ];
    }
}
