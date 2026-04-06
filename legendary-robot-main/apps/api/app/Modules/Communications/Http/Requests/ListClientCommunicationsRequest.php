<?php

declare(strict_types=1);

namespace App\Modules\Communications\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ListClientCommunicationsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'channel' => ['nullable', 'string', 'in:all,sms,email,voice'],
            'status' => ['nullable', 'string', 'in:all,pending,failed'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:100'],
            'cursor' => ['nullable', 'string', 'max:200'],
        ];
    }
}
