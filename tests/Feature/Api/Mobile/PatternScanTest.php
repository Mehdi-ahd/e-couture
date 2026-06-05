<?php

use App\DTO\Scan\RemoveBgPatternScanResult;
use App\Exceptions\Scan\RemoveBgPatternGatewayException;
use App\Models\User;
use App\Services\Scan\RemoveBgPatternGateway;
use Illuminate\Http\UploadedFile;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->token = $this->user->createToken('test-device')->plainTextToken;
});

function buildExpectedPayload(): array
{
    return [
        'scan_id' => 'test-scan-123',
        'status' => 'cutout_ready',
        'source' => [
            'original_url' => 'http://localhost/storage/mobile_pattern_scans/test-scan-123/original.jpg',
            'path' => 'mobile_pattern_scans/test-scan-123/original.jpg',
            'width' => 100,
            'height' => 100,
            'mime' => 'image/jpeg',
            'original_name' => 'pattern.jpg',
            'size' => 1024,
        ],
        'cutout' => [
            'url' => 'http://localhost/storage/mobile_pattern_scans/test-scan-123/cutout.png',
            'path' => 'mobile_pattern_scans/test-scan-123/cutout.png',
            'mime' => 'image/png',
            'width' => 100,
            'height' => 100,
            'channels' => 'rgba',
        ],
        'quality' => [
            'contour_available' => false,
            'contour_computed_by' => 'flutter',
        ],
        'metadata' => [
            'provider' => 'remove.bg',
            'workflow' => '2d_cutout',
            'remove_bg' => [
                'size' => 'preview',
                'format' => 'png',
                'channels' => 'rgba',
                'type' => 'auto',
            ],
            'pattern_id' => null,
            'client_id' => null,
            'background_color' => null,
            'computed_contours_by' => 'flutter',
        ],
    ];
}

it('returns cutout result for a valid image upload', function () {
    $gateway = $this->mock(RemoveBgPatternGateway::class, function ($mock) {
        $mock->shouldReceive('createCutout')
            ->once()
            ->andReturn(new RemoveBgPatternScanResult(buildExpectedPayload()));
    });

    $image = UploadedFile::fake()->image('pattern.jpg', 100, 100);

    $this->withHeader('Authorization', 'Bearer '.$this->token)
        ->postJson('/api/mobile/scan/pattern', [
            'image' => $image,
        ])
        ->assertOk()
        ->assertJsonStructure([
            'message',
            'data' => [
                'scan_id',
                'status',
                'source',
                'cutout' => ['url', 'path', 'mime', 'width', 'height', 'channels'],
                'quality',
                'metadata',
            ],
        ])
        ->assertJsonPath('data.scan_id', 'test-scan-123')
        ->assertJsonPath('data.status', 'cutout_ready');
});

it('accepts optional parameters', function () {
    $gateway = $this->mock(RemoveBgPatternGateway::class, function ($mock) {
        $mock->shouldReceive('createCutout')
            ->once()
            ->andReturn(new RemoveBgPatternScanResult(buildExpectedPayload()));
    });

    $image = UploadedFile::fake()->image('pattern.jpg', 100, 100);

    $this->withHeader('Authorization', 'Bearer '.$this->token)
        ->postJson('/api/mobile/scan/pattern', [
            'image' => $image,
            'pattern_id' => 'pat-123',
            'client_id' => 'cli-456',
            'background_color' => '#FFFFFF',
            'remove_bg_size' => 'hd',
            'crop' => true,
            'crop_margin' => '10%',
        ])
        ->assertOk()
        ->assertJsonPath('data.scan_id', 'test-scan-123');
});

it('rejects unauthenticated requests', function () {
    $image = UploadedFile::fake()->image('pattern.jpg');

    $this->postJson('/api/mobile/scan/pattern', [
        'image' => $image,
    ])->assertUnauthorized();
});

it('rejects requests without an image', function () {
    $this->withHeader('Authorization', 'Bearer '.$this->token)
        ->postJson('/api/mobile/scan/pattern', [])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['image']);
});

it('rejects invalid file types', function () {
    $file = UploadedFile::fake()->create('document.pdf', 100);

    $this->withHeader('Authorization', 'Bearer '.$this->token)
        ->postJson('/api/mobile/scan/pattern', [
            'image' => $file,
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['image']);
});

it('rejects invalid remove_bg_size values', function () {
    $image = UploadedFile::fake()->image('pattern.jpg');

    $this->withHeader('Authorization', 'Bearer '.$this->token)
        ->postJson('/api/mobile/scan/pattern', [
            'image' => $image,
            'remove_bg_size' => 'ultra',
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['remove_bg_size']);
});

it('accepts crop as empty string (treated as false)', function () {
    $this->mock(RemoveBgPatternGateway::class, function ($mock) {
        $mock->shouldReceive('createCutout')
            ->once()
            ->andReturn(new RemoveBgPatternScanResult(buildExpectedPayload()));
    });

    $image = UploadedFile::fake()->image('pattern.jpg');

    $this->withHeader('Authorization', 'Bearer '.$this->token)
        ->postJson('/api/mobile/scan/pattern', [
            'image' => $image,
            'crop' => '',
        ])
        ->assertOk();
});

it('accepts crop as string "false"', function () {
    $this->mock(RemoveBgPatternGateway::class, function ($mock) {
        $mock->shouldReceive('createCutout')
            ->once()
            ->andReturn(new RemoveBgPatternScanResult(buildExpectedPayload()));
    });

    $image = UploadedFile::fake()->image('pattern.jpg');

    $this->withHeader('Authorization', 'Bearer '.$this->token)
        ->postJson('/api/mobile/scan/pattern', [
            'image' => $image,
            'crop' => 'false',
        ])
        ->assertOk();
});

it('rejects invalid crop values', function () {
    $image = UploadedFile::fake()->image('pattern.jpg');

    $this->withHeader('Authorization', 'Bearer '.$this->token)
        ->postJson('/api/mobile/scan/pattern', [
            'image' => $image,
            'crop' => 'not-a-boolean',
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['crop']);
});

it('rejects invalid background_color format', function () {
    $image = UploadedFile::fake()->image('pattern.jpg');

    $this->withHeader('Authorization', 'Bearer '.$this->token)
        ->postJson('/api/mobile/scan/pattern', [
            'image' => $image,
            'background_color' => 'red',
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['background_color']);
});

it('returns gateway error response when gateway throws exception', function () {
    $this->mock(RemoveBgPatternGateway::class, function ($mock) {
        $mock->shouldReceive('createCutout')
            ->once()
            ->andThrow(new RemoveBgPatternGatewayException(
                'Remove.bg rejected the pattern cutout request.',
                503,
                ['provider_error' => 'API limit exceeded'],
            ));
    });

    $image = UploadedFile::fake()->image('pattern.jpg', 100, 100);

    $this->withHeader('Authorization', 'Bearer '.$this->token)
        ->postJson('/api/mobile/scan/pattern', [
            'image' => $image,
        ])
        ->assertStatus(503)
        ->assertJson([
            'message' => 'Échec du traitement du scan.',
            'error' => [
                'code' => 'REMOVE_BG_GATEWAY_ERROR',
                'retryable' => true,
            ],
        ]);
});
