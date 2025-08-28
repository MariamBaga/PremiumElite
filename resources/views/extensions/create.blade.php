@extends('adminlte::page')
@section('title','Nouvelle extension')
@section('content_header')<h1>Nouvelle extension</h1>@stop

@section('content')
<div class="card">
  <div class="card-body">
    <form method="POST" action="{{ route('extensions.store') }}">
      @csrf
      <div class="row">
        <div class="col-md-3 mb-3">
          <label>Code</label>
          <input name="code" class="form-control" required value="{{ old('code') }}">
        </div>
        <div class="col-md-3 mb-3">
          <label>Zone</label>
          <input name="zone" class="form-control" value="{{ old('zone') }}">
        </div>
        <div class="col-md-3 mb-3">
          <label>Statut</label>
          <select name="statut" class="form-control" required>
            @foreach($statuts as $k=>$v)
              <option value="{{ $k }}" @selected(old('statut')===$k)>{{ $v }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-3 mb-3">
          <label>Foyers ciblés</label>
          <input type="number" name="foyers_cibles" class="form-control" min="0" value="{{ old('foyers_cibles',0) }}">
        </div>

        <div class="col-md-3 mb-3">
          <label>ROI estimé</label>
          <input type="number" step="0.01" name="roi_estime" class="form-control" value="{{ old('roi_estime') }}">
        </div>

        <div class="col-12 mb-3">
          <label>Géométrie (GeoJSON)</label>
          <textarea name="geom" rows="6" class="form-control" placeholder='{"type":"LineString","coordinates":[[-7.95,12.65],[-7.96,12.66]]}'>{{ old('geom') }}</textarea>
          <small class="text-muted">Colle ici un GeoJSON valide (LineString/Polygon). Optionnel pour commencer.</small>
        </div>
      </div>

      <button class="btn btn-primary">Enregistrer</button>
      <a href="{{ route('extensions.index') }}" class="btn btn-secondary">Annuler</a>
    </form>
  </div>
</div>
@stop
