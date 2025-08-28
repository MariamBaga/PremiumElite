@extends('adminlte::page')
@section('title','Modifier '.$e->code)
@section('content_header')<h1>Modifier {{ $e->code }}</h1>@stop

@section('content')
<div class="card">
  <div class="card-body">
    <form method="POST" action="{{ route('extensions.update',$e) }}">
      @csrf @method('PUT')
      <div class="row">
        <div class="col-md-3 mb-3">
          <label>Code</label>
          <input name="code" class="form-control" required value="{{ old('code',$e->code) }}">
        </div>
        <div class="col-md-3 mb-3">
          <label>Zone</label>
          <input name="zone" class="form-control" value="{{ old('zone',$e->zone) }}">
        </div>
        <div class="col-md-3 mb-3">
          <label>Statut</label>
          <select name="statut" class="form-control" required>
            @foreach($statuts as $k=>$v)
              <option value="{{ $k }}" @selected(old('statut',$e->statut)===$k)>{{ $v }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-3 mb-3">
          <label>Foyers ciblés</label>
          <input type="number" name="foyers_cibles" class="form-control" min="0" value="{{ old('foyers_cibles',$e->foyers_cibles) }}">
        </div>

        <div class="col-md-3 mb-3">
          <label>ROI estimé</label>
          <input type="number" step="0.01" name="roi_estime" class="form-control" value="{{ old('roi_estime',$e->roi_estime) }}">
        </div>

        <div class="col-12 mb-3">
          <label>Géométrie (GeoJSON)</label>
          <textarea name="geom" rows="6" class="form-control">{{ old('geom', $e->geom ? json_encode($e->geom) : '') }}</textarea>
          <small class="text-muted">Colle ici un GeoJSON valide. Laisse vide si tu n’en as pas encore.</small>
        </div>
      </div>

      <button class="btn btn-primary">Mettre à jour</button>
      <a href="{{ route('extensions.show',$e) }}" class="btn btn-secondary">Annuler</a>
    </form>
  </div>
</div>
@stop
