<?php

declare(strict_types=1);

namespace App\Modules\Communications\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class SendSmsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'body' => ['nullable', 'string', 'max:2000', 'required_without_all:attachments,retryOfMessageId'],
            'toPhone' => ['nullable', 'string', 'max:40'],
            'idempotencyKey' => ['nullable', 'string', 'max:100'],
            'retryOfMessageId' => ['nullable', 'string', 'max:100'],
            'attachments' => ['nullable', 'array', 'max:5'],
            'attachments.*' => ['file', 'max:20480', 'mimes:png,jpg,jpeg,gif,pdf,txt,doc,docx'],
        ];
    }
}
