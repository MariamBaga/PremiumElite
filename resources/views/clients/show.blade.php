@extends('adminlte::page')

@section('title','Client #'.$client->id)
@section('content_header')
  <h1>
    Client #{{ $client->id }}
    <small class="text-muted">— {{ $client->displayName }}</small>
  </h1>
@stop

@section('content')
<div class="row">
  {{-- Colonne principale --}}
  <div class="col-lg-8">
    <div class="card mb-3">
      <div class="card-header">Informations</div>
      <div class="card-body">
        <dl class="row mb-0">
          <dt class="col-sm-3">Type</dt>
          <dd class="col-sm-9">{{ ucfirst($client->type) }}</dd>

          @if($client->type === 'professionnel')
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
            @if($client->adresse_ligne2) — {{ $client->adresse_ligne2 }} @endif
          </dd>

          <dt class="col-sm-3">Ville / Zone</dt>
          <dd class="col-sm-9">
            {{ $client->ville ?? '-' }} / {{ $client->zone ?? '-' }}
          </dd>

          <dt class="col-sm-3">Localisation</dt>
          <dd class="col-sm-9">
            {{ $client->localisation ?? '-' }}
          </dd>

          <dt class="col-sm-3">N° de ligne</dt>
          <dd class="col-sm-9 text-nowrap">{{ $client->numero_ligne ?? '-' }}</dd>

          <dt class="col-sm-3">Point focal</dt>
          <dd class="col-sm-9 text-nowrap">{{ $client->numero_point_focal ?? '-' }}</dd>

          <dt class="col-sm-3">Date de paiement</dt>
          <dd class="col-sm-9 text-nowrap">{{ optional($client->date_paiement)->format('d/m/Y') ?? '-' }}</dd>

          <dt class="col-sm-3">Date d’affectation</dt>
          <dd class="col-sm-9 text-nowrap">{{ optional($client->date_affectation)->format('d/m/Y') ?? '-' }}</dd>

          <dt class="col-sm-3">Coordonnées</dt>
          <dd class="col-sm-9">
            @if(!is_null($client->latitude) && !is_null($client->longitude))
              {{ $client->latitude }}, {{ $client->longitude }}
              <a class="btn btn-xs btn-outline-secondary ms-2"
                 target="_blank"
                 href="https://www.google.com/maps?q={{ $client->latitude }},{{ $client->longitude }}">
                 Voir sur la carte
              </a>
            @else
              -
            @endif
          </dd>
        </dl>
      </div>
    </div>

    {{-- (Optionnel) Dossiers liés --}}
    <div class="card mb-3">
      <div class="card-header">
        Dossiers associés
        <span class="badge bg-primary ms-1">{{ $client->dossiers_count ?? $client->dossiers()->count() }}</span>
      </div>
      <div class="card-body p-0">
        @php $dossiers = $client->dossiers()->latest()->limit(8)->get(); @endphp
        @if($dossiers->isEmpty())
          <div class="p-3 text-muted">Aucun dossier pour ce client.</div>
        @else
          <div class="table-responsive">
            <table class="table table-hover mb-0">
              <thead>
                <tr>
                  <th>Référence</th>
                  <th>Statut</th>
                  <th class="text-nowrap">Type</th>
                  <th class="text-nowrap">Planifiée</th>
                  <th class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody>
                @foreach($dossiers as $d)
                  <tr>
                    <td>{{ $d->reference }}</td>
                    <td>{{ \Illuminate\Support\Str::headline($d->statut?->value ?? $d->statut) }}</td>
                    <td class="text-nowrap">{{ ucfirst($d->type_service) }}</td>
                    <td class="text-nowrap">{{ optional($d->date_planifiee)->format('d/m/Y H:i') }}</td>
                    <td class="text-end">
                      <a href="{{ route('dossiers.show',$d) }}" class="btn btn-sm btn-outline-secondary">Ouvrir</a>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @endif
      </div>
      <div class="card-footer text-end">
        <a href="{{ route('dossiers.index', ['search' => $client->id]) }}" class="btn btn-outline-primary btn-sm">
          Voir tous les dossiers
        </a>
      </div>
    </div>
  </div>

  {{-- Colonne actions --}}
  <div class="col-lg-4">
    <div class="card mb-3">
      <div class="card-header">Actions</div>
      <div class="card-body d-flex gap-2">
        <a href="{{ route('clients.edit',$client) }}" class="btn btn-primary">Éditer</a>
        <form method="POST" action="{{ route('clients.destroy',$client) }}" class="ms-2"
              onsubmit="return confirm('Supprimer ce client ?')">
          @csrf @method('DELETE')
          <button class="btn btn-danger">Supprimer</button>
        </form>
      </div>
    </div>

    {{-- (Optionnel) Raccourcis --}}
    <div class="card">
      <div class="card-header">Raccourcis</div>
      <div class="card-body">
        <a href="{{ route('dossiers.create') }}?client_id={{ $client->id }}" class="btn btn-outline-primary w-100 mb-2">
          Créer un dossier de raccordement
        </a>
        <a href="tel:{{ $client->telephone }}" class="btn btn-outline-secondary w-100 mb-2" @disabled(empty($client->telephone))>
          Appeler le client
        </a>
        <a href="mailto:{{ $client->email }}" class="btn btn-outline-secondary w-100" @disabled(empty($client->email))>
          Envoyer un email
        </a>
      </div>
    </div>
  </div>
</div>
@stop
