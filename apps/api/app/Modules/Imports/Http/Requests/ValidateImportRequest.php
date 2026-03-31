<?php

declare(strict_types=1);

namespace App\Modules\Imports\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ValidateImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [];
    }
}
