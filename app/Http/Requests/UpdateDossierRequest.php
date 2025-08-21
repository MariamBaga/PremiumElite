<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDossierRequest extends FormRequest
{
    public function authorize(): bool { return true; } // policy dans le contrôleur
    public function rules(): array {
        return [
            'pbo' => 'nullable|string|max:100',
            'pm'  => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:30',
            'date_planifiee' => 'nullable|date',
            'assigned_to' => 'nullable|exists:users,id',
        ];
    }
}
