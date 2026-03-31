<?php

declare(strict_types=1);

namespace App\Modules\Applications\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'productType' => ['required', 'string', 'max:120'],
            'ownerUserId' => ['nullable', 'string', 'max:255'],
            'externalReference' => ['nullable', 'string', 'max:255'],
            'amountRequested' => ['nullable', 'numeric', 'min:0'],
            'submittedAt' => ['nullable', 'date'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
