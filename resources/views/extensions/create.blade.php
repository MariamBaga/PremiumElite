@extends('adminlte::page')
@section('title','Nouvelle extension')
@section('content_header')<h1>Nouvelle extension</h1>@stop

@section('content')
@include('partials.alerts')

<div class="card">
  <div class="card-body">
    <form method="POST" action="{{ route('extensions.store') }}" class="row g-3">
      @csrf
      <div class="col-md-3"><label>Code</label><input name="code" class="form-control" required></div>
      <div class="col-md-3"><label>Zone</label><input name="zone" class="form-control"></div>
      <div class="col-md-3">
        <label>Statut</label>
        <select name="statut" class="form-control">
          @foreach(['planifie','en_cours','termine'] as $s)
            <option value="{{ $s }}">{{ Str::headline($s) }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-3"><label>Foyers cibles</label><input type="number" name="foyers_cibles" class="form-control" value="0"></div>
      <div class="col-md-3"><label>ROI estimé</label><input type="number" step="0.01" name="roi_estime" class="form-control"></div>
      <div class="col-12">
        <label>Géométrie (GeoJSON)</label>
        <textarea name="geom" class="form-control" rows="4" placeholder='{"type":"LineString","coordinates":[[lon,lat],[lon,lat]]}'></textarea>
      </div>
      <div class="col-12 text-end">
        <a href="{{ route('extensions.index') }}" class="btn btn-secondary">Annuler</a>
        <button class="btn btn-primary">Enregistrer</button>
      </div>
    </form>
  </div>
</div>
@stop
