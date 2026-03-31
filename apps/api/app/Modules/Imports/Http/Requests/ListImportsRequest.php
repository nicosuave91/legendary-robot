<?php

declare(strict_types=1);

namespace App\Modules\Imports\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ListImportsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['nullable', 'string', 'in:uploaded,validation_queued,validating,ready_to_commit,validation_failed,commit_queued,committing,committed,commit_failed'],
            'importType' => ['nullable', 'string', 'in:clients'],
        ];
    }
}
