<?php

namespace App\Http\Requests\Api\Mobile;

use App\DTO\Scan\PatternScanOptions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PatternScanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'image' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,heic', 'max:22528'],
            'pattern_id' => ['nullable', 'string', 'max:191'],
            'client_id' => ['nullable', 'string', 'max:191'],
            'background_color' => ['nullable', 'string', 'regex:/^#?[0-9A-Fa-f]{6}$/'],
            'remove_bg_size' => ['sometimes', 'string', Rule::in(['preview', 'full', 'auto', 'medium', 'hd', '4k', 'regular', 'small'])],
            'crop' => ['sometimes', function (string $attribute, mixed $value, \Closure $fail): void {
                if ($value === '' || $value === null) {
                    return;
                }
                if (! in_array($value, [true, false, 1, 0, '1', '0', 'true', 'false', 'on', 'off', 'yes', 'no'], true)) {
                    $fail('The :attribute field must be true or false.');
                }
            }],
            'crop_margin' => ['nullable', 'string', 'max:32'],
        ];
    }

    public function options(): PatternScanOptions
    {
        $validated = $this->validated();

        return new PatternScanOptions(
            patternId: $validated['pattern_id'] ?? null,
            clientId: $validated['client_id'] ?? null,
            backgroundColor: $validated['background_color'] ?? null,
            removeBgSize: $validated['remove_bg_size'] ?? (string) config('services.remove_bg.default_size', 'preview'),
            crop: $this->boolean('crop'),
            cropMargin: $validated['crop_margin'] ?? null,
        );
    }
}
