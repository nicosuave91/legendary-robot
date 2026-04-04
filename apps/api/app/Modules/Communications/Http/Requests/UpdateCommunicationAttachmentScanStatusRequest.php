<?php

declare(strict_types=1);

namespace App\Modules\Communications\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateCommunicationAttachmentScanStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'string', 'in:pending,clean,rejected,quarantined'],
            'engine' => ['nullable', 'string', 'max:100'],
            'detail' => ['nullable', 'string', 'max:2000'],
            'quarantineReason' => ['nullable', 'string', 'max:255', 'required_if:status,rejected,quarantined'],
        ];
    }
}
