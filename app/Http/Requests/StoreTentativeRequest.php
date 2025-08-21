<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTentativeRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array {
        return [
            'methode' => 'required|string|max:50',
            'resultat' => 'required|string|max:50',
            'notes' => 'nullable|string',
            'effectuee_le' => 'nullable|date'
        ];
    }
}
