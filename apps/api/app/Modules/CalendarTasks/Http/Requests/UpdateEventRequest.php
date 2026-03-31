<?php

declare(strict_types=1);

namespace App\Modules\CalendarTasks\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateEventRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:160'],
            'description' => ['sometimes', 'nullable', 'string'],
            'eventType' => ['sometimes', 'required', 'in:appointment,follow_up,document_review,call,deadline,task_batch'],
            'status' => ['sometimes', 'required', 'in:scheduled,completed,cancelled'],
            'startsAt' => ['sometimes', 'required', 'date'],
            'endsAt' => ['nullable', 'date'],
            'isAllDay' => ['sometimes', 'boolean'],
            'location' => ['sometimes', 'nullable', 'string', 'max:255'],
            'clientId' => ['sometimes', 'nullable', 'string', 'max:255'],
            'ownerUserId' => ['sometimes', 'nullable', 'string', 'max:255'],
            'metadata' => ['sometimes', 'nullable', 'array'],
        ];
    }
}
