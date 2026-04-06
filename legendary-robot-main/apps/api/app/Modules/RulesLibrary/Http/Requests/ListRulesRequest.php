<?php

declare(strict_types=1);

namespace App\Modules\RulesLibrary\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ListRulesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'moduleScope' => ['nullable', 'string', 'in:applications,disposition,communications,client'],
            'status' => ['nullable', 'string', 'in:draft,published,retired'],
        ];
    }
}