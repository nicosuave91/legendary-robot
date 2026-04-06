<?php

declare(strict_types=1);

namespace App\Modules\Imports\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'importType' => ['required', 'string', 'in:clients'],
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:10240'],
        ];
    }
}
