<?php

declare(strict_types=1);

namespace App\Modules\CalendarTasks\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreEventRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string'],
            'eventType' => ['required', 'in:appointment,follow_up,document_review,call,deadline,task_batch'],
            'status' => ['nullable', 'in:scheduled,completed,cancelled'],
            'startsAt' => ['required', 'date'],
            'endsAt' => ['nullable', 'date', 'after_or_equal:startsAt'],
            'isAllDay' => ['nullable', 'boolean'],
            'location' => ['nullable', 'string', 'max:255'],
            'clientId' => ['nullable', 'string', 'max:255'],
            'ownerUserId' => ['nullable', 'string', 'max:255'],
            'metadata' => ['nullable', 'array'],
            'tasks' => ['nullable', 'array'],
            'tasks.*.title' => ['required_with:tasks', 'string', 'max:160'],
            'tasks.*.description' => ['nullable', 'string'],
            'tasks.*.assignedUserId' => ['nullable', 'string', 'max:255'],
            'tasks.*.isRequired' => ['nullable', 'boolean'],
            'tasks.*.sortOrder' => ['nullable', 'integer', 'min:0'],
            'tasks.*.dueAt' => ['nullable', 'date'],
            'tasks.*.metadata' => ['nullable', 'array'],
        ];
    }
}
