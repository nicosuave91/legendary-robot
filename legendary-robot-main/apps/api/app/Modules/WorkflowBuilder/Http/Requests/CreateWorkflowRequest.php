<?php

declare(strict_types=1);

namespace App\Modules\WorkflowBuilder\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class CreateWorkflowRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'workflowKey' => ['required', 'string', 'max:120'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'triggerDefinition' => ['required', 'array'],
            'stepsDefinition' => ['required', 'array'],
        ];
    }
}