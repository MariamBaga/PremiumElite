@extends('adminlte::page')

@section('title', 'Dossier Abonné #' . $client->id)
@section('content_header')
<h1>
    Dossier Abonné #{{ $client->id }}
    <small class="text-muted">— {{ $client->displayName }}</small>
</h1>
@stop

@section('content')
<div class="row">
    {{-- =================== Colonne principale =================== --}}
    <div class="col-lg-8">
        {{-- Informations client --}}
        <div class="card mb-3">
            <div class="card-header">Informations</div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-3">Type</dt>
                    <dd class="col-sm-9">{{ ucfirst($client->type) }}</dd>

                    @if ($client->type === 'professionnel')
                        <dt class="col-sm-3">Raison sociale</dt>
                        <dd class="col-sm-9">{{ $client->raison_sociale ?? '-' }}</dd>
                    @else
                        <dt class="col-sm-3">Nom</dt>
                        <dd class="col-sm-9">{{ $client->nom ?? '-' }}</dd>
                        <dt class="col-sm-3">Prénom</dt>
                        <dd class="col-sm-9">{{ $client->prenom ?? '-' }}</dd>
                    @endif

                    <dt class="col-sm-3">Téléphone</dt>
                    <dd class="col-sm-9 text-nowrap">{{ $client->telephone ?? '-' }}</dd>

                    <dt class="col-sm-3">Email</dt>
                    <dd class="col-sm-9 text-nowrap">{{ $client->email ?? '-' }}</dd>

                    <dt class="col-sm-3">Adresse</dt>
                    <dd class="col-sm-9">
                        {{ $client->adresse_ligne1 }}
                        @if ($client->adresse_ligne2) — {{ $client->adresse_ligne2 }} @endif
                    </dd>

                    <dt class="col-sm-3">Ville / Zone</dt>
                    <dd class="col-sm-9">{{ $client->ville ?? '-' }} / {{ $client->zone ?? '-' }}</dd>

                    <dt class="col-sm-3">Localisation</dt>
                    <dd class="col-sm-9">
                        @if ($client->latitude && $client->longitude)
                            {{ $client->latitude }}, {{ $client->longitude }}
                            <a target="_blank" href="https://www.google.com/maps?q={{ $client->latitude }},{{ $client->longitude }}"
                               class="btn btn-xs btn-outline-secondary ms-2">Voir sur la carte</a>
                        @else - @endif
                    </dd>

                    <dt class="col-sm-3">N° de ligne</dt>
                    <dd class="col-sm-9 text-nowrap">{{ $client->numero_ligne ?? '-' }}</dd>

                    <dt class="col-sm-3">Point focal</dt>
                    <dd class="col-sm-9 text-nowrap">{{ $client->numero_point_focal ?? '-' }}</dd>

                    <dt class="col-sm-3">Date de paiement</dt>
                    <dd class="col-sm-9 text-nowrap">{{ optional($client->date_paiement)->format('d/m/Y') ?? '-' }}</dd>

                    <dt class="col-sm-3">Date d’affectation</dt>
                    <dd class="col-sm-9 text-nowrap">{{ optional($client->date_affectation)->format('d/m/Y') ?? '-' }}</dd>
                </dl>
            </div>
        </div>

        {{-- Dossiers associés --}}
        @can('dossiers.view')
        <div class="card mb-3">
            <div class="card-header">
                Dossiers associés <span class="badge bg-primary ms-1">{{ $client->dossiers_count ?? $client->dossiers()->count() }}</span>
            </div>
            <div class="card-body p-0">
                @php $dossiers = $client->dossiers()->latest()->limit(8)->get(); @endphp
                @if ($dossiers->isEmpty())
                    <div class="p-3 text-muted">Aucun dossier pour ce client.</div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Référence</th>
                                    <th>Statut</th>
                                    <th>Type</th>
                                    <th class="text-nowrap">Planifiée</th>
                                   
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($dossiers as $d)
                                    <tr>
                                        <td>{{ $d->reference }}</td>
                                        <td>{{ \Illuminate\Support\Str::headline($d->statut?->value ?? $d->statut) }}</td>
                                        <td class="text-nowrap">{{ ucfirst($d->type_service) }}</td>
                                        <td class="text-nowrap">{{ optional($d->date_planifiee)->format('d/m/Y H:i') }}</td>

                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
            <div class="card-footer text-end">
                @can('dossiers.view')
                    <a href="{{ route('dossiers.index', ['search' => $client->id]) }}" class="btn btn-outline-primary btn-sm">
                        Voir tous les dossiers
                    </a>
                @endcan
            </div>
        </div>
        @endcan

        {{-- Boucle sur tous les dossiers pour inclure le show partiel --}}
        @foreach($client->dossiers as $dossier)
            @include('dossiers.show', ['dossier' => $dossier])
        @endforeach
    </div>

    {{-- =================== Colonne actions =================== --}}
    <div class="col-lg-4">
        {{-- Actions sur le client --}}
        <div class="card mb-3">
            <div class="card-header">Actions</div>
            <div class="card-body d-flex flex-column gap-2">
                <a href="{{ route('clients.edit', $client) }}" class="btn btn-primary">Éditer</a>
                <form method="POST" action="{{ route('clients.destroy', $client) }}" onsubmit="return confirm('Supprimer ce client ?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-danger w-100 mt-1">Supprimer</button>
                </form>
            </div>
        </div>

        {{-- Raccourcis --}}
        <div class="card">
            <div class="card-header">Raccourcis</div>
            <div class="card-body d-flex flex-column gap-2">
                <!-- @can('dossiers.create')
                    <a href="{{ route('dossiers.create') }}?client_id={{ $client->id }}" class="btn btn-outline-primary w-100">
                        Créer un dossier de raccordement
                    </a>
                @endcan -->
                <a href="tel:{{ $client->telephone }}" class="btn btn-outline-secondary w-100" @disabled(empty($client->telephone))>Appeler le client</a>
                <a href="mailto:{{ $client->email }}" class="btn btn-outline-secondary w-100" @disabled(empty($client->email))>Envoyer un email</a>
            </div>
        </div>
    </div>
</div>
@stop
