<?php

namespace App\Services\Scan;

use App\DTO\Scan\PatternScanOptions;
use App\DTO\Scan\RemoveBgPatternScanResult;
use App\Exceptions\Scan\RemoveBgPatternGatewayException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

/**
 * Service d appel a l API Remove.bg pour le detourage des patrons.
 * Supprime l arriere plan des images de patrons et genere un fichier PNG avec transparence.
 */
class RemoveBgPatternGateway
{
    public function createCutout(UploadedFile $image, PatternScanOptions $options): RemoveBgPatternScanResult
    {
        $scanId = Str::uuid()->toString();
        $disk = Storage::disk('public');
        $directory = "mobile_pattern_scans/{$scanId}";
        $imagePath = $image->getRealPath();

        if (! $imagePath) {
            throw new RemoveBgPatternGatewayException('Unable to read uploaded image.');
        }

        if (! config('removebg.api_key')) {
            throw new RemoveBgPatternGatewayException(
                'Remove.bg API key is missing. Set REMOVE_BG_API_KEY in the backend environment.',
                422,
                ['config_key' => 'REMOVE_BG_API_KEY'],
            );
        }

        $sourceDimensions = $this->dimensionsFromPath($imagePath);
        $originalExtension = strtolower($image->getClientOriginalExtension() ?: $image->extension() ?: 'jpg');
        $originalPath = $image->storeAs($directory, "original.{$originalExtension}", 'public');

        try {
            $cutoutBytes = removebg()
                ->file($imagePath, $image->getClientOriginalName() ?: 'pattern-scan.jpg')
                ->body($options->toRemoveBgBody())
                ->get();
        } catch (Throwable $exception) {
            throw new RemoveBgPatternGatewayException(
                message: 'Remove.bg rejected the pattern cutout request.',
                statusCode: 503,
                context: ['provider_error' => $exception->getMessage()],
                previous: $exception,
            );
        }

        if (! $this->looksLikePng($cutoutBytes)) {
            throw new RemoveBgPatternGatewayException(
                message: 'Remove.bg returned an unexpected response.',
                statusCode: 503,
                context: ['expected' => 'png'],
            );
        }

        $cutoutPath = "{$directory}/cutout.png";
        $disk->put($cutoutPath, $cutoutBytes);

        return new RemoveBgPatternScanResult([
            'scan_id' => $scanId,
            'status' => 'cutout_ready',
            'source' => [
                'original_url' => $this->publicUrl($disk->url($originalPath)),
                'path' => $originalPath,
                'width' => $sourceDimensions['width'],
                'height' => $sourceDimensions['height'],
                'mime' => $image->getMimeType(),
                'original_name' => $image->getClientOriginalName(),
                'size' => $image->getSize(),
            ],
            'cutout' => [
                'url' => $this->publicUrl($disk->url($cutoutPath)),
                'path' => $cutoutPath,
                'mime' => 'image/png',
                'width' => $this->dimensionsFromBytes($cutoutBytes)['width'],
                'height' => $this->dimensionsFromBytes($cutoutBytes)['height'],
                'channels' => 'rgba',
            ],
            'quality' => [
                'contour_available' => false,
                'contour_computed_by' => 'flutter',
            ],
            'metadata' => [
                'provider' => 'remove.bg',
                'workflow' => '2d_cutout',
                'remove_bg' => $options->toRemoveBgBody(),
                'pattern_id' => $options->patternId,
                'client_id' => $options->clientId,
                'background_color' => $options->backgroundColor,
                'computed_contours_by' => 'flutter',
            ],
        ]);
    }

    private function dimensionsFromPath(string $path): array
    {
        $size = @getimagesize($path);

        return [
            'width' => is_array($size) ? ($size[0] ?? null) : null,
            'height' => is_array($size) ? ($size[1] ?? null) : null,
        ];
    }

    private function dimensionsFromBytes(string $bytes): array
    {
        $size = @getimagesizefromstring($bytes);

        return [
            'width' => is_array($size) ? ($size[0] ?? null) : null,
            'height' => is_array($size) ? ($size[1] ?? null) : null,
        ];
    }

    private function looksLikePng(string $bytes): bool
    {
        return str_starts_with($bytes, "\x89PNG\r\n\x1a\n");
    }

    private function publicUrl(string $pathOrUrl): string
    {
        if (str_starts_with($pathOrUrl, 'http://') || str_starts_with($pathOrUrl, 'https://')) {
            return $pathOrUrl;
        }

        return url($pathOrUrl);
    }
}
