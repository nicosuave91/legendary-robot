<?php

declare(strict_types=1);

namespace App\Modules\Applications\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class TransitionApplicationStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'targetStatus' => ['required', 'in:draft,submitted,in_review,approved,declined,withdrawn'],
            'submittedAt' => ['nullable', 'date'],
            'reason' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
