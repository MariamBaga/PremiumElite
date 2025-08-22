<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateClientRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'type'            => 'required|in:residentiel,professionnel',
            'nom'             => 'nullable|string|max:100',
            'prenom'          => 'nullable|string|max:100',
            'raison_sociale'  => 'nullable|string|max:150',
            'telephone'       => 'nullable|string|max:25',
            'email'           => 'nullable|email|max:150',
            'adresse_ligne1'  => 'required|string|max:200',
            'adresse_ligne2'  => 'nullable|string|max:200',
            'ville'           => 'nullable|string|max:100',
            'zone'            => 'nullable|string|max:100',
            'numero_ligne'        => 'nullable|string|max:50',
            'numero_point_focal'  => 'nullable|string|max:50',
            'localisation'        => 'nullable|string|max:100',
            'date_paiement'       => 'nullable|date',
            'date_affectation'    => 'nullable|date',
            'latitude'        => 'nullable|numeric',
            'longitude'       => 'nullable|numeric',
            'metadonnees'     => 'nullable|array',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($v) {
            $type = $this->input('type','residentiel');
            if ($type === 'residentiel' && empty(trim($this->input('nom').$this->input('prenom')))) {
                $v->errors()->add('nom', 'Nom et/ou prénom requis pour un client résidentiel.');
            }
            if ($type === 'professionnel' && empty($this->input('raison_sociale'))) {
                $v->errors()->add('raison_sociale', 'La raison sociale est requise pour un client professionnel.');
            }
        });
    }
}
