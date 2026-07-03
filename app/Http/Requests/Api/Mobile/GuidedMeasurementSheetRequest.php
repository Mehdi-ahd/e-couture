<?php

namespace App\Http\Requests\Api\Mobile;

use Illuminate\Foundation\Http\FormRequest;

class GuidedMeasurementSheetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'face_url' => ['required', 'url', 'max:2048'],
            'dos_url' => ['required', 'url', 'max:2048'],
            'profil_url' => ['required', 'url', 'max:2048'],
        ];
    }
}
