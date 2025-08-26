@extends('adminlte::page')
@section('title','Modifier extension '.$extension->code)
@section('content_header')<h1>Modifier {{ $extension->code }}</h1>@stop

@section('content')
@include('partials.alerts')

<div class="card">
  <div class="card-body">
    <form method="POST" action="{{ route('extensions.update',$extension) }}" class="row g-3">
      @csrf @method('PUT')
      <div class="col-md-3"><label>Code</label><input name="code" class="form-control" value="{{ $extension->code }}" required></div>
      <div class="col-md-3"><label>Zone</label><input name="zone" class="form-control" value="{{ $extension->zone }}"></div>
      <div class="col-md-3">
        <label>Statut</label>
        <select name="statut" class="form-control">
          @foreach(['planifie','en_cours','termine'] as $s)
            <option value="{{ $s }}" @selected($extension->statut===$s)>{{ Str::headline($s) }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-3"><label>Foyers cibles</label><input type="number" name="foyers_cibles" class="form-control" value="{{ $extension->foyers_cibles }}"></div>
      <div class="col-md-3"><label>ROI estimé</label><input type="number" step="0.01" name="roi_estime" class="form-control" value="{{ $extension->roi_estime }}"></div>
      <div class="col-12">
        <label>Géométrie (GeoJSON)</label>
        <textarea name="geom" class="form-control" rows="4">{{ json_encode($extension->geom, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</textarea>
      </div>
      <div class="col-12 text-end">
        <a href="{{ route('extensions.show',$extension) }}" class="btn btn-secondary">Annuler</a>
        <button class="btn btn-primary">Mettre à jour</button>
      </div>
    </form>
  </div>
</div>
@stop
