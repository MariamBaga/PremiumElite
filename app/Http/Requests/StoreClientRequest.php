<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreClientRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array {
        return [
            'type' => 'required|in:residentiel,professionnel',
            'nom'  => 'required_if:type,residentiel|nullable|string',
            'prenom' => 'required_if:type,residentiel|nullable|string',
            'raison_sociale' => 'required_if:type,professionnel|nullable|string',
            'telephone' => 'nullable|string|max:25',
            'email' => 'nullable|email',
            'adresse_ligne1' => 'required|string',
            'adresse_ligne2' => 'nullable|string',
            'ville' => 'nullable|string',
            'zone' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ];
    }
}
