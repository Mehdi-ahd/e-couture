<?php

namespace App\DTO\Scan;

/**
 * DTO contenant le resultat du detourage via Remove.bg.
 * Contient les URLs et metadonnees de l image source et du cutout genere.
 */
final readonly class RemoveBgPatternScanResult
{
    public function __construct(public array $payload) {}

    public function toMobilePayload(): array
    {
        return [
            'scan_id' => $this->payload['scan_id'] ?? null,
            'status' => $this->payload['status'] ?? 'unknown',
            'source' => $this->payload['source'] ?? [],
            'cutout' => $this->payload['cutout'] ?? [],
            'depth' => ['available' => false],
            'contours' => [],
            'quality' => $this->payload['quality'] ?? [],
            'metadata' => $this->payload['metadata'] ?? [],
        ];
    }
}
