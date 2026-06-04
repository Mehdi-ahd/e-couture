<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MobileScanController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        /** @var Authenticatable|null $user */
        $user = $request->user();

        $validated = $request->validate([
            'client_id' => ['nullable', 'string', 'max:191'],
            'images' => ['required', 'array', 'min:1'],
            'images.*' => ['file', 'image', 'max:5120'],
        ]);

        $files = $request->file('images', []);
        $saved = [];

        foreach ($files as $file) {
            $path = $file->store('mobile_scans', 'public');
            $saved[] = [
                'path' => $path,
                'url' => Storage::disk('public')->url($path),
                'original_name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
            ];
        }

        $sessionId = Str::uuid()->toString();

        // Minimal response: echo back session metadata and saved image URLs.
        return response()->json([
            'message' => 'Scan reçu.',
            'data' => [
                'session' => [
                    'id' => $sessionId,
                    'client_id' => $validated['client_id'] ?? null,
                    'uploaded_at' => now()->toIso8601String(),
                    'images' => $saved,
                ],
            ],
        ], 201);
    }
}
