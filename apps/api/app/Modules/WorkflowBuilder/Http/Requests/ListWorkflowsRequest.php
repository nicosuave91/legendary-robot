<?php

declare(strict_types=1);

namespace App\Modules\WorkflowBuilder\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ListWorkflowsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['nullable', 'string', 'in:draft,published,retired'],
        ];
    }
}