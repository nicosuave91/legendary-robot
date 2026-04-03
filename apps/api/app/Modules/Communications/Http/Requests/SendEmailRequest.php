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
            'to' => ['required', 'array', 'min:1'],
            'to.*' => ['string', 'email'],
            'cc' => ['nullable', 'array'],
            'cc.*' => ['string', 'email'],
            'bcc' => ['nullable', 'array'],
            'bcc.*' => ['string', 'email'],
            'subject' => ['required', 'string', 'max:255'],
            'bodyText' => ['nullable', 'string', 'required_without:bodyHtml'],
            'bodyHtml' => ['nullable', 'string', 'required_without:bodyText'],
            'idempotencyKey' => ['nullable', 'string', 'max:100'],
            'retryOfMessageId' => ['nullable', 'string', 'max:100'],
            'attachments' => ['nullable', 'array', 'max:10'],
            'attachments.*' => ['file', 'max:25600', 'mimes:png,jpg,jpeg,gif,pdf,txt,doc,docx,xlsx,csv'],
        ];
    }
}
