@extends('adminlte::page')
@section('title','Nouvelle équipe')
@section('content_header')<h1>Nouvelle équipe</h1>@stop

@section('content')
<div class="card">
  <div class="card-body">
    @if ($errors->any())
      <div class="alert alert-danger">
        <ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
      </div>
    @endif

    <form method="POST" action="{{ route('teams.store') }}">
      @csrf
      <div class="row">
        <div class="col-md-4 mb-3">
          <label>Nom de l’équipe</label>
          <input name="name" class="form-control" required value="{{ old('name') }}">
        </div>
        <div class="col-md-4 mb-3">
          <label>Zone</label>
          <input name="zone" class="form-control" value="{{ old('zone') }}">
        </div>
        <div class="col-md-12 mb-3">
          <label>Description</label>
          <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
        </div>

        <div class="col-md-6 mb-3">
          <label>Membres</label>
          <select name="members[]" class="form-control" multiple size="8">
            @foreach($users as $u)
              <option value="{{ $u->id }}" @selected(collect(old('members',[]))->contains($u->id))>
                {{ $u->name }} — {{ $u->email }}
              </option>
            @endforeach
          </select>
          <small class="text-muted">Maintenir Ctrl/⌘ pour multi-sélectionner.</small>
        </div>

        <div class="col-md-6 mb-3">
          <label>Chef d’équipe</label>
          <select name="lead_id" class="form-control">
            <option value="">— Aucun —</option>
            @foreach($users as $u)
              <option value="{{ $u->id }}" @selected(old('lead_id')==$u->id)>{{ $u->name }}</option>
            @endforeach
          </select>
          <small class="text-muted">Sera automatiquement ajouté aux membres.</small>
        </div>
      </div>


      <div class="col-md-12 mb-3">
  <label>Dossiers à assigner (optionnel)</label>
  <select name="dossier_ids[]" class="form-control" multiple size="8">
    @foreach($dossiers as $d)
      <option value="{{ $d->id }}">
        {{ $d->reference }} — {{ $d->client?->displayName }} — {{ ucfirst($d->type_service) }}
      </option>
    @endforeach
  </select>
  <small class="text-muted">Seuls les dossiers non assignés sont listés.</small>
</div>


      <button class="btn btn-primary">Créer</button>
      <a href="{{ route('teams.index') }}" class="btn btn-secondary">Annuler</a>
    </form>
  </div>
</div>
@stop
