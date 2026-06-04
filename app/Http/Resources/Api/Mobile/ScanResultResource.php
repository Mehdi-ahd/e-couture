<?php

namespace App\Http\Resources\Api\Mobile;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScanResultResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'scan_id' => $this->resource['scan_id'] ?? null,
            'status' => $this->resource['status'] ?? 'unknown',
            'source' => $this->resource['source'] ?? [],
            'cutout' => $this->resource['cutout'] ?? [],
            'depth' => $this->resource['depth'] ?? ['available' => false],
            'contours' => $this->resource['contours'] ?? [],
            'quality' => $this->resource['quality'] ?? [],
            'metadata' => $this->resource['metadata'] ?? [],
        ];
    }
}
