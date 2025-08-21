<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateClientRequest extends FormRequest
{
    public function authorize(): bool { return auth()->check(); }

    public function rules(): array
    {
        return [
            'type'            => 'required|in:residentiel,professionnel',
            'nom'             => 'required_if:type,residentiel|nullable|string|max:100',
            'prenom'          => 'required_if:type,residentiel|nullable|string|max:100',
            'raison_sociale'  => 'required_if:type,professionnel|nullable|string|max:150',
            'telephone'       => 'nullable|string|max:25',
            'email'           => 'nullable|email|max:150',
            'adresse_ligne1'  => 'required|string|max:200',
            'adresse_ligne2'  => 'nullable|string|max:200',
            'ville'           => 'nullable|string|max:100',
            'zone'            => 'nullable|string|max:100',
            'latitude'        => 'nullable|numeric',
            'longitude'       => 'nullable|numeric',
        ];
    }
}
