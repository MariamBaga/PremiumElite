<div class="row g-3">
  <div class="col-12 col-xl-4">
    <div class="card h-100">
      <div class="card-header d-flex justify-content-between align-items-center">
        <span>Client</span>
        @isset($client)
          <a href="{{ route('clients.edit',$client) }}" class="btn btn-sm btn-outline-primary">Éditer</a>
        @endisset
      </div>
      <div class="card-body">
        <div class="mb-2"><strong>Nom/Affichage :</strong> {{ $fiche->displayName() }}</div>
        <div class="mb-2"><strong>Type :</strong> {{ ucfirst($fiche->type() ?? '—') }}</div>
        <div class="mb-2"><strong>Téléphone :</strong> {{ $fiche->telephone() ?? '—' }}</div>
        <div class="mb-2"><strong>Email :</strong> {{ $fiche->email() ?? '—' }}</div>
        <div class="mb-2"><strong>Localisation :</strong> {{ $fiche->localisation() ?? '—' }}</div>
        <div class="mb-2"><strong>N° ligne :</strong> {{ $fiche->numeroLigne() ?? '—' }}</div>
        <div class="mb-2"><strong>Point focal :</strong> {{ $fiche->pointFocal() ?? '—' }}</div>
        <div class="mb-2"><strong>Date paiement :</strong> {{ optional($fiche->datePaiement())->format('d/m/Y') ?: '—' }}</div>
        <div class="mb-2"><strong>Date affectation :</strong> {{ optional($fiche->dateAffectation())->format('d/m/Y') ?: '—' }}</div>
        <div class="mb-2"><strong>Zone :</strong> {{ $fiche->zone() ?? '—' }}</div>
      </div>
    </div>
  </div>

  <div class="col-12 col-xl-8">
    <div class="card h-100">
      <div class="card-header d-flex justify-content-between align-items-center">
        <span>Dossier</span>
        @if($fiche->hasDossier())
          <a href="{{ route('dossiers.show',$fiche->dossierId()) }}" class="btn btn-sm btn-outline-secondary">Ouvrir le dossier</a>
        @endif
      </div>
      <div class="card-body">
        @if($fiche->hasDossier())
          <div class="mb-2"><strong>Référence :</strong> {{ $fiche->reference() }}</div>
          <div class="mb-2"><strong>Type service :</strong> {{ ucfirst($fiche->typeService() ?? '—') }}</div>
          <div class="mb-2"><strong>Statut :</strong> <span class="badge bg-info">{{ $fiche->statut() ?? '—' }}</span></div>
          <div class="mb-2"><strong>Planifiée :</strong> {{ optional($fiche->datePlanifiee())->format('d/m/Y H:i') ?: '—' }}</div>
          <div class="mb-2"><strong>Technicien :</strong> {{ $fiche->technicienName() ?? '—' }}</div>
        @else
          <p class="text-muted">Aucun dossier rattaché à ce client.</p>
          @isset($client)
          <form method="POST" action="{{ route('dossiers.store') }}">
            @csrf
            <input type="hidden" name="client_id" value="{{ $client->id }}">
            <input type="hidden" name="reference" value="DR-{{ date('Y') }}-TEMP">
            <button class="btn btn-primary btn-sm">Créer un dossier</button>
          </form>
          @endisset
        @endif
      </div>
    </div>
  </div>
</div>
