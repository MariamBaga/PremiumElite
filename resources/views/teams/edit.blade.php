@extends('adminlte::page')
@section('title','Éditer équipe')
@section('content_header')
<h1>Éditer l’équipe : {{ $team->name }}</h1>
@stop

@section('content')
<div class="card">
  <div class="card-body">
    @if (session('success'))
      <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
      <div class="alert alert-danger">
        <ul class="mb-0">
          @foreach ($errors->all() as $e)
            <li>{{ $e }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <form method="POST" action="{{ route('teams.update',$team) }}">
      @csrf @method('PUT')
      <div class="row">
        <div class="col-md-4 mb-3">
          <label>Nom</label>
          <input name="name" class="form-control" required value="{{ old('name',$team->name) }}">
        </div>
        <div class="col-md-4 mb-3">
          <label>Zone</label>
          <input name="zone" class="form-control" value="{{ old('zone',$team->zone) }}">
        </div>
        <div class="col-md-12 mb-3">
          <label>Description</label>
          <textarea name="description" class="form-control" rows="3">{{ old('description',$team->description) }}</textarea>
        </div>

        {{-- Membres texte --}}
        <div class="col-md-6 mb-3">
          <label>Membres (texte)</label>
          <textarea name="members_names" class="form-control" rows="6" placeholder="Un membre par ligne ou séparé par une virgule">@if(old('members_names')){{ old('members_names') }}@else{{ implode("\n", json_decode($team->members_names ?? '[]', true)) }}@endif</textarea>
          <small class="text-muted">Liste des noms des membres non-utilisateurs.</small>
        </div>

        {{-- Membres utilisateurs --}}
        <div class="col-md-6 mb-3">
          <label>Membres (utilisateurs)</label>
          <select name="members[]" class="form-control" multiple size="8">
            @php $current = $team->members->pluck('id')->all(); @endphp
            @foreach($users as $u)
              <option value="{{ $u->id }}" @selected(in_array($u->id, old('members',$current)))>
                {{ $u->name }} — {{ $u->email }}
              </option>
            @endforeach
          </select>
        </div>

        {{-- Chef --}}
        <div class="col-md-6 mb-3">
          <label>Chef d’équipe</label>
          <select name="lead_id" class="form-control">
            <option value="">— Aucun —</option>
            @foreach($users as $u)
              <option value="{{ $u->id }}" @selected(old('lead_id',$team->lead_id)==$u->id)>{{ $u->name }}</option>
            @endforeach
          </select>
          <small class="text-muted">Un seul chef par équipe (mis à jour automatiquement).</small>
        </div>

        {{-- Dossiers assignés --}}
        @php $selected = $team->dossiers->pluck('id')->all(); @endphp
        <div class="col-md-12 mb-3">
          <label>Dossiers assignés</label>
          <select name="dossier_ids[]" class="form-control" multiple size="10">
            @foreach($dossiers as $d)
              <option value="{{ $d->id }}" @selected(in_array($d->id, old('dossier_ids', $selected)))>
                {{ $d->reference }} — {{ $d->client?->displayName }} — {{ ucfirst($d->type_service) }}
                @if(!is_null($d->assigned_team_id) && $d->assigned_team_id !== $team->id)
                  (⚠️ déjà assigné)
                @endif
              </option>
            @endforeach
          </select>
          <small class="text-muted">
            Non sélectionné = retiré de l’équipe. Sélectionner = assigner à cette équipe.
          </small>
        </div>
      </div>

      <button class="btn btn-primary">Enregistrer</button>
      <a href="{{ route('teams.show',$team) }}" class="btn btn-secondary">Annuler</a>
    </form>
  </div>
</div>
@stop
