<?php

declare(strict_types=1);

namespace App\Modules\Communications\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class SendEmailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'to' => ['nullable', 'array', 'min:1', 'required_without:retryOfMessageId'],
            'to.*' => ['string', 'email'],
            'cc' => ['nullable', 'array'],
            'cc.*' => ['string', 'email'],
            'bcc' => ['nullable', 'array'],
            'bcc.*' => ['string', 'email'],
            'subject' => ['nullable', 'string', 'max:255', 'required_without:retryOfMessageId'],
            'bodyText' => ['nullable', 'string', 'required_without_all:bodyHtml,retryOfMessageId'],
            'bodyHtml' => ['nullable', 'string', 'required_without_all:bodyText,retryOfMessageId'],
            'idempotencyKey' => ['nullable', 'string', 'max:100'],
            'retryOfMessageId' => ['nullable', 'string', 'max:100'],
            'attachments' => ['nullable', 'array', 'max:10'],
            'attachments.*' => ['file', 'max:25600', 'mimes:png,jpg,jpeg,gif,pdf,txt,doc,docx,xlsx,csv'],
        ];
    }
}
