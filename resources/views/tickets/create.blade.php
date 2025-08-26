@extends('adminlte::page')
@section('title','Nouveau ticket')
@section('content_header')<h1>Nouveau ticket</h1>@stop

@section('content')
@include('partials.alerts')

<div class="card">
  <div class="card-body">
    <form method="POST" action="{{ route('tickets.store') }}" class="row g-3">
      @csrf

      <div class="col-md-4">
        <label>Client</label>
        <select name="client_id" class="form-control">
          <option value="">-- Aucun --</option>
          @foreach($clients as $c)
            <option value="{{ $c->id }}" @selected(old('client_id')==$c->id)>{{ $c->displayName }} — {{ $c->telephone }}</option>
          @endforeach
        </select>
      </div>

      <div class="col-md-4">
        <label>Dossier</label>
        <select name="dossier_id" class="form-control">
          <option value="">-- Aucun --</option>
          @foreach($dossiers as $d)
            <option value="{{ $d->id }}" @selected(old('dossier_id')==$d->id)>{{ $d->reference }} — {{ $d->client?->displayName }}</option>
          @endforeach
        </select>
      </div>

      <div class="col-md-4">
        <label>Équipe assignée</label>
        <select name="assigned_team_id" class="form-control">
          <option value="">-- Aucune --</option>
          @foreach($teams as $t)
            <option value="{{ $t->id }}" @selected(old('assigned_team_id')==$t->id)>{{ $t->name }}</option>
          @endforeach
        </select>
      </div>

      <div class="col-md-3">
        <label>Type</label>
        <select name="type" class="form-control" required>
          @foreach(['panne'=>'Panne','signalement'=>'Signalement','maintenance'=>'Maintenance'] as $k=>$v)
            <option value="{{ $k }}" @selected(old('type')==$k)>{{ $v }}</option>
          @endforeach
        </select>
      </div>

      <div class="col-md-3">
        <label>Priorité</label>
        <select name="priorite" class="form-control" required>
          @foreach(['faible'=>'Faible','normal'=>'Normal','haute'=>'Haute','critique'=>'Critique'] as $k=>$v)
            <option value="{{ $k }}" @selected(old('priorite')==$k)>{{ $v }}</option>
          @endforeach
        </select>
      </div>

      <div class="col-md-6">
        <label>Titre</label>
        <input name="titre" class="form-control" value="{{ old('titre') }}" required>
      </div>

      <div class="col-12">
        <label>Description</label>
        <textarea name="description" class="form-control" rows="4">{{ old('description') }}</textarea>
      </div>

      <div class="col-12 text-end">
        <a href="{{ route('tickets.index') }}" class="btn btn-secondary">Annuler</a>
        <button class="btn btn-primary">Créer</button>
      </div>
    </form>
  </div>
</div>
@stop
