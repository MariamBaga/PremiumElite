<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Enums\StatutDossier;

class UpdateStatutRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array {
        $statuts = implode(',', array_column(StatutDossier::cases(), 'value'));
        return [
            'statut' => 'required|in:'.$statuts,
            'commentaire_statut' => 'nullable|string|max:1000'
        ];
    }
}
