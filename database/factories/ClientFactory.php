<?php

namespace Database\Factories;

use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClientFactory extends Factory
{
    protected $model = Client::class;

  // database/factories/ClientFactory.php

public function definition(): array
{
    $faker = $this->faker;
    $type  = $faker->randomElement(['residentiel', 'professionnel']);

    $base = [
        'type'               => $type,
        'telephone'          => $faker->phoneNumber(),
        'email'              => $faker->unique()->safeEmail(),
        'adresse_ligne1'     => $faker->streetAddress(),
        'adresse_ligne2'     => $faker->optional()->secondaryAddress(),
        'ville'              => $faker->city(),
        'zone'               => $faker->word(),
        'latitude'           => $faker->latitude(),
        'longitude'          => $faker->longitude(),
        'numero_ligne'       => $faker->optional()->numerify('12########'),
        'numero_point_focal' => $faker->optional()->numerify('6########'),
        'localisation'       => $faker->optional()->bothify('AKA-PL###'),

        // ✅ important: optional() peut renvoyer null, donc on nullsafe
        'date_paiement'      => $faker->optional()->dateTimeBetween('-20 days', 'now')?->format('Y-m-d'),
        'date_affectation'   => $faker->optional()->dateTimeBetween('-15 days', 'now')?->format('Y-m-d'),

        'metadonnees'        => $faker->optional()->randomElement([
            ['crm_id' => $faker->uuid(), 'canal' => 'téléphone'],
            ['crm_id' => $faker->uuid(), 'canal' => 'whatsapp'],
            null
        ]),
    ];

    if ($type === 'professionnel') {
        return $base + [
            'nom'            => null,
            'prenom'         => null,
            'raison_sociale' => $faker->company(),
        ];
    }

    return $base + [
        'nom'            => $faker->lastName(),
        'prenom'         => $faker->firstName(),
        'raison_sociale' => null,
    ];
}


    /** États pratiques si tu veux forcer un type dans tes seeders */
    public function residentiel(): self
    {
        return $this->state(function () {
            return [
                'type'             => 'residentiel',
                'nom'              => $this->faker->lastName(),
                'prenom'           => $this->faker->firstName(),
                'raison_sociale'   => null,
            ];
        });
    }

    public function professionnel(): self
    {
        return $this->state(function () {
            return [
                'type'             => 'professionnel',
                'nom'              => null,
                'prenom'           => null,
                'raison_sociale'   => $this->faker->company(),
            ];
        });
    }
}
