<?php

declare(strict_types=1);

namespace App\Modules\WorkflowBuilder\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class PublishWorkflowRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'publishNotes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}