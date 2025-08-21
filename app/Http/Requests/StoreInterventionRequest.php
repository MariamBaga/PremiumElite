<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInterventionRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array {
        return [
            'debut' => 'nullable|date',
            'fin' => 'nullable|date|after_or_equal:debut',
            'etat' => 'required|in:en_cours,realisee,suspendue',
            'observations' => 'nullable|string',
            'metriques' => 'nullable|array',
        ];
    }
}
