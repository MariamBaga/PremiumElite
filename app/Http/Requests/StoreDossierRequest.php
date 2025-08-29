<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDossierRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()->can('dossiers.create'); }
    public function rules(): array {
        return [
            'client_id' => 'required|exists:clients,id',
            'type_service' => 'required|in:residentiel,professionnel',
            'pbo' => 'nullable|string|max:100',
            'pm'  => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:30',
            'date_planifiee' => 'nullable|date_format:d/m/Y',

            'assigned_to' => 'nullable|exists:users,id',
        ];
    }
}
