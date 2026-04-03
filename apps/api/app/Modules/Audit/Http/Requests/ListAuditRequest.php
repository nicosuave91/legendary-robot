<?php

declare(strict_types=1);

namespace App\Modules\Audit\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ListAuditRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'action' => ['nullable', 'string', 'max:150'],
            'subjectType' => ['nullable', 'string', 'max:150'],
            'subjectId' => ['nullable', 'string', 'max:150'],
            'actorId' => ['nullable', 'string', 'max:150'],
            'correlationId' => ['nullable', 'string', 'max:50'],
            'q' => ['nullable', 'string', 'max:255'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
        ];
    }
}
