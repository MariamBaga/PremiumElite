@extends('adminlte::page')
@section('title','Nouvelle plaque')
@section('content_header')<h1>Nouvelle plaque</h1>@stop

@section('content')
@include('partials.alerts')

<div class="card">
  <div class="card-body">
    <form method="POST" action="{{ route('plaques.store') }}" class="row g-3">
      @csrf
      <div class="col-md-3"><label>Code</label><input name="code" class="form-control" required></div>
      <div class="col-md-3"><label>Nom</label><input name="nom" class="form-control"></div>
      <div class="col-md-3"><label>Zone</label><input name="zone" class="form-control"></div>
      <div class="col-md-3">
        <label>Statut</label>
        <select name="statut" class="form-control">
          @foreach(['etude','gc','pose_pbo_pm','tirage','tests','service'] as $s)
            <option value="{{ $s }}">{{ Str::headline($s) }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-3"><label>Foyers raccordables</label><input type="number" name="foyers_raccordables" class="form-control" value="0"></div>
      <div class="col-md-3"><label>PBO installés</label><input type="number" name="pbo_installes" class="form-control" value="0"></div>
      <div class="col-md-3"><label>Couverture %</label><input type="number" step="0.01" name="coverage" class="form-control" value="0"></div>
      <div class="col-12">
        <label>Géométrie (GeoJSON)</label>
        <textarea name="geom" class="form-control" rows="4" placeholder='{"type":"Polygon","coordinates":[...]}'></textarea>
      </div>
      <div class="col-12 text-end">
        <a href="{{ route('plaques.index') }}" class="btn btn-secondary">Annuler</a>
        <button class="btn btn-primary">Enregistrer</button>
      </div>
    </form>
  </div>
</div>
@stop
