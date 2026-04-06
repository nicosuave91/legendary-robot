<?php

declare(strict_types=1);

namespace App\Modules\Disposition\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class TransitionClientDispositionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'targetDispositionCode' => ['required', 'string', 'max:100'],
            'reason' => ['nullable', 'string', 'max:1000'],
            'acknowledgeWarnings' => ['nullable', 'boolean'],
        ];
    }
}
