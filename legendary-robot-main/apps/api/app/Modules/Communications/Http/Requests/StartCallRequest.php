<?php

declare(strict_types=1);

namespace App\Modules\Communications\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StartCallRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'toPhone' => ['nullable', 'string', 'max:40'],
            'purposeNote' => ['nullable', 'string', 'max:500'],
            'idempotencyKey' => ['nullable', 'string', 'max:100'],
            'retryOfCallLogId' => ['nullable', 'string', 'max:100'],
        ];
    }
}
