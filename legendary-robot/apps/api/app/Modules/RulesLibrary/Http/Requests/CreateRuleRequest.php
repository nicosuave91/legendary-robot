<?php

declare(strict_types=1);

namespace App\Modules\RulesLibrary\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class CreateRuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ruleKey' => ['required', 'string', 'max:120'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'moduleScope' => ['required', 'string', 'in:applications,disposition,communications,client'],
            'subjectType' => ['required', 'string', 'in:application,client,communication'],
            'triggerEvent' => ['required', 'string', 'max:120'],
            'severity' => ['required', 'string', 'in:info,warning,blocking'],
            'industryScope' => ['nullable', 'array'],
            'conditionDefinition' => ['required', 'array'],
            'actionDefinition' => ['required', 'array'],
            'executionLabel' => ['nullable', 'string', 'max:255'],
            'noteTemplate' => ['nullable', 'string'],
        ];
    }
}