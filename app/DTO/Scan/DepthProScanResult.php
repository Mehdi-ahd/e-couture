<?php

namespace App\DTO\Scan;

final readonly class DepthProScanResult
{
    public function __construct(public array $payload) {}

    public function toMobilePayload(): array
    {
        return [
            'scan_id' => $this->payload['scan_id'] ?? null,
            'status' => $this->payload['status'] ?? 'unknown',
            'source' => $this->payload['image'] ?? [],
            'depth' => $this->payload['depth'] ?? ['available' => false],
            'contours' => $this->payload['contours'] ?? [],
            'quality' => $this->payload['quality'] ?? [],
            'metadata' => $this->payload['processing'] ?? [],
        ];
    }
}
