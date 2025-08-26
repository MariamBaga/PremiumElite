@extends('adminlte::page')
@section('title','Dossier '.$dossier->reference)
@section('content_header')<h1>Dossier {{ $dossier->reference }}</h1>@stop
@section('content')
<div class="row">
  <div class="col-lg-8">
    <div class="card mb-3">
      <div class="card-header">Informations</div>
      <div class="card-body">
        <dl class="row">
          <dt class="col-sm-3">Client</dt><dd class="col-sm-9">{{ $dossier->client->displayName }} ({{ $dossier->client->telephone }})</dd>
          <dt class="col-sm-3">Statut</dt><dd class="col-sm-9"><span class="badge bg-info">{{ \App\Enums\StatutDossier::labels()[$dossier->statut->value] }}</span></dd>
          <dt class="col-sm-3">Technicien</dt><dd class="col-sm-9">{{ $dossier->technicien?->name ?? '-' }}</dd>
          <dt class="col-sm-3">Planifiée</dt><dd class="col-sm-9">{{ optional($dossier->date_planifiee)->format('d/m/Y H:i') ?? '-' }}</dd>
          <dt class="col-sm-3">Réalisée</dt><dd class="col-sm-9">{{ optional($dossier->date_realisation)->format('d/m/Y H:i') ?? '-' }}</dd>
          <dt class="col-sm-3">Notes</dt><dd class="col-sm-9">{{ $dossier->description ?? '-' }}</dd>
        </dl>
      </div>
    </div>

    <div class="card mb-3">
      <div class="card-header">Historique des statuts</div>
      <div class="card-body table-responsive">
        <table class="table">
          <thead><tr><th>Date</th><th>De</th><th>À</th><th>Par</th><th>Commentaire</th></tr></thead>
          <tbody>
            @foreach($dossier->statuts as $h)
              <tr>
                <td>{{ $h->created_at->format('d/m/Y H:i') }}</td>
                <td>{{ \App\Enums\StatutDossier::labels()[$h->ancien_statut] ?? '-' }}</td>
                <td>{{ \App\Enums\StatutDossier::labels()[$h->nouveau_statut] ?? $h->nouveau_statut }}</td>
                <td>{{ $h->user?->name ?? '-' }}</td>
                <td>{{ $h->commentaire ?? '-' }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>

    <div class="card mb-3">
      <div class="card-header">Tentatives de contact</div>
      <div class="card-body">
        <form method="POST" action="{{ route('dossiers.tentatives.store',$dossier) }}" class="row g-2 mb-3">
          @csrf
          <div class="col-md-3"><input name="methode" class="form-control" placeholder="appel/sms/email" required></div>
          <div class="col-md-3"><input name="resultat" class="form-control" placeholder="joignable..." required></div>
          <div class="col-md-4"><input name="notes" class="form-control" placeholder="notes"></div>
          <div class="col-md-2"><button class="btn btn-outline-primary w-100">Ajouter</button></div>
        </form>
        <ul class="list-group">
          @foreach($dossier->tentatives->sortByDesc('effectuee_le') as $t)
            <li class="list-group-item d-flex justify-content-between">
              <span><strong>{{ $t->methode }}</strong> — {{ $t->resultat }} ({{ $t->effectuee_le->format('d/m/Y H:i') }})</span>
              <span class="text-muted">{{ $t->user?->name }}</span>
            </li>
          @endforeach
        </ul>
      </div>
    </div>

    <div class="card mb-3">
      <div class="card-header">Interventions</div>
      <div class="card-body">
        <form method="POST" action="{{ route('dossiers.interventions.store',$dossier) }}" class="row g-2 mb-3">
          @csrf
          <div class="col-md-3"><input type="datetime-local" name="debut" class="form-control" ></div>
          <div class="col-md-3"><input type="datetime-local" name="fin" class="form-control" ></div>
          <div class="col-md-3">
            <select name="etat" class="form-control">
              <option value="en_cours">En cours</option>
              <option value="realisee">Réalisée</option>
              <option value="suspendue">Suspendue</option>
            </select>
          </div>
          <div class="col-md-3"><button class="btn btn-outline-primary w-100">Ajouter</button></div>
          <div class="col-12 mt-2">
            <textarea name="observations" class="form-control" placeholder="Observations" rows="2"></textarea>
          </div>
        </form>
        <ul class="list-group">
          @foreach($dossier->interventions()->latest()->get() as $i)
            <li class="list-group-item">
              <strong>{{ ucfirst($i->etat) }}</strong> — {{ $i->debut?->format('d/m/Y H:i') }} → {{ $i->fin?->format('d/m/Y H:i') }}
              <span class="float-end">{{ $i->technicien?->name }}</span>
              <div class="text-muted">{{ $i->observations }}</div>
            </li>
          @endforeach
        </ul>
      </div>
    </div>
  </div>

  @can('dossiers.assign')
<div class="card mb-3">
  <div class="card-header">Affectation à une équipe</div>
  <div class="card-body">
    <form method="POST" action="{{ route('dossiers.assign-team', $dossier) }}" class="row g-2">
      @csrf
      <div class="col-md-8">
        <select name="assigned_team_id" class="form-control">
          <option value="">— Aucune équipe —</option>
          @foreach(\App\Models\Team::orderBy('name')->get() as $t)
            <option value="{{ $t->id }}" @selected($dossier->assigned_team_id == $t->id)>
              {{ $t->name }} @if($t->zone) — {{ $t->zone }} @endif
            </option>
          @endforeach
        </select>
      </div>
      <div class="col-md-4">
        <button class="btn btn-outline-primary w-100">Enregistrer</button>
      </div>
    </form>
    @error('assigned_team_id') <div class="text-danger mt-2">{{ $message }}</div> @enderror

    @if($dossier->team)
      <div class="mt-2 text-muted">Équipe actuelle : <strong>{{ $dossier->team->name }}</strong></div>
    @endif
  </div>
</div>
@endcan

  <div class="col-lg-4">
    <div class="card mb-3">
      <div class="card-header">Affectation & Statut</div>
      <div class="card-body">
        @can('assign', \App\Models\DossierRaccordement::class)
        <form method="POST" action="{{ route('dossiers.assign',$dossier) }}" class="mb-3">
          @csrf
          <div class="mb-2">
            <label>Technicien</label>
            <select name="assigned_to" class="form-control">
              <option value="">--</option>
              @foreach(\App\Models\User::role('technicien')->get() as $u)
                <option value="{{ $u->id }}" @selected($dossier->assigned_to===$u->id)>{{ $u->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="mb-2">
            <label>Date planifiée</label>
            <input type="datetime-local" name="date_planifiee" class="form-control" value="{{ optional($dossier->date_planifiee)->format('Y-m-d\TH:i') }}">
          </div>
          <button class="btn btn-outline-secondary w-100">Enregistrer</button>
        </form>
        @endcan

        @can('updateStatus', $dossier)
        <form method="POST" action="{{ route('dossiers.status',$dossier) }}">
          @csrf
          <div class="mb-2">
            <label>Nouveau statut</label>
            <select name="statut" class="form-control" required>
              @foreach(\App\Enums\StatutDossier::labels() as $value=>$label)
                <option value="{{ $value }}" @selected($dossier->statut->value===$value)>{{ $label }}</option>
              @endforeach
            </select>
          </div>
          <div class="mb-2">
            <label>Commentaire</label>
            <textarea name="commentaire_statut" class="form-control" rows="2" placeholder="Optionnel"></textarea>
          </div>
          <button class="btn btn-primary w-100">Mettre à jour le statut</button>
        </form>
        @endcan
      </div>
    </div>
  </div>
</div>
@stop
