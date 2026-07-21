<?php

namespace App\DTO\Scan;

/**
 * DTO contenant les options de configuration pour un scan de patron.
 * Permet de personnaliser la taille, le recadrage et la couleur d arriere plan.
 */
final readonly class PatternScanOptions
{
    public function __construct(
        public ?string $patternId = null,
        public ?string $clientId = null,
        public ?string $backgroundColor = null,
        public string $removeBgSize = 'preview',
        public bool $crop = false,
        public ?string $cropMargin = null,
    ) {}

    public function toRemoveBgBody(): array
    {
        $body = [
            'size' => $this->removeBgSize,
            'format' => 'png',
            'channels' => 'rgba',
            'type' => 'auto',
        ];

        if ($this->crop) {
            $body['crop'] = '1';
        }

        if ($this->crop && $this->cropMargin !== null) {
            $body['crop_margin'] = $this->cropMargin;
        }

        return $body;
    }
}
