<?php

declare(strict_types=1);

namespace App\Modules\RulesLibrary\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateRuleDraftRequest extends FormRequest
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
            'moduleScope' => ['sometimes', 'string', 'in:applications,disposition,communications,client'],
            'subjectType' => ['sometimes', 'string', 'in:application,client,communication'],
            'triggerEvent' => ['sometimes', 'string', 'max:120'],
            'severity' => ['sometimes', 'string', 'in:info,warning,blocking'],
            'industryScope' => ['nullable', 'array'],
            'conditionDefinition' => ['sometimes', 'array'],
            'actionDefinition' => ['sometimes', 'array'],
            'executionLabel' => ['nullable', 'string', 'max:255'],
            'noteTemplate' => ['nullable', 'string'],
        ];
    }
}