@extends('adminlte::page')
@section('title','Modifier plaque '.$plaque->code)
@section('content_header')<h1>Modifier {{ $plaque->code }}</h1>@stop

@section('content')
@include('partials.alerts')

<div class="card">
  <div class="card-body">
    <form method="POST" action="{{ route('plaques.update',$plaque) }}" class="row g-3">
      @csrf @method('PUT')
      <div class="col-md-3"><label>Code</label><input name="code" class="form-control" value="{{ $plaque->code }}" required></div>
      <div class="col-md-3"><label>Nom</label><input name="nom" class="form-control" value="{{ $plaque->nom }}"></div>
      <div class="col-md-3"><label>Zone</label><input name="zone" class="form-control" value="{{ $plaque->zone }}"></div>
      <div class="col-md-3">
        <label>Statut</label>
        <select name="statut" class="form-control">
          @foreach(['etude','gc','pose_pbo_pm','tirage','tests','service'] as $s)
            <option value="{{ $s }}" @selected($plaque->statut===$s)>{{ Str::headline($s) }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-3"><label>Foyers raccordables</label><input type="number" name="foyers_raccordables" class="form-control" value="{{ $plaque->foyers_raccordables }}"></div>
      <div class="col-md-3"><label>PBO installés</label><input type="number" name="pbo_installes" class="form-control" value="{{ $plaque->pbo_installes }}"></div>
      <div class="col-md-3"><label>Couverture %</label><input type="number" step="0.01" name="coverage" class="form-control" value="{{ $plaque->coverage }}"></div>
      <div class="col-12">
        <label>Géométrie (GeoJSON)</label>
        <textarea name="geom" class="form-control" rows="4">{{ json_encode($plaque->geom, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</textarea>
      </div>
      <div class="col-12 text-end">
        <a href="{{ route('plaques.show',$plaque) }}" class="btn btn-secondary">Annuler</a>
        <button class="btn btn-primary">Mettre à jour</button>
      </div>
    </form>
  </div>
</div>
@stop
