<?php

declare(strict_types=1);

namespace App\Modules\WorkflowBuilder\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateWorkflowDraftRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'triggerDefinition' => ['sometimes', 'array'],
            'stepsDefinition' => ['sometimes', 'array'],
        ];
    }
}