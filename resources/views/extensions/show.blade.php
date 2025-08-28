@extends('adminlte::page')
@section('title','Extension '.$e->code)
@section('content_header')<h1>Extension {{ $e->code }}</h1>@stop

@section('content')
<div class="row">
  <div class="col-lg-8">
    <div class="card mb-3">
      <div class="card-header">Informations</div>
      <div class="card-body">
        <dl class="row mb-0">
          <dt class="col-sm-3">Code</dt><dd class="col-sm-9">{{ $e->code }}</dd>
          <dt class="col-sm-3">Zone</dt><dd class="col-sm-9">{{ $e->zone ?? '-' }}</dd>
          <dt class="col-sm-3">Statut</dt><dd class="col-sm-9">{{ \Illuminate\Support\Str::headline($e->statut) }}</dd>
          <dt class="col-sm-3">Foyers ciblés</dt><dd class="col-sm-9">{{ $e->foyers_cibles }}</dd>
          <dt class="col-sm-3">ROI estimé</dt><dd class="col-sm-9">{{ $e->roi_estime ? number_format($e->roi_estime,2,',',' ') : '-' }}</dd>
          <dt class="col-sm-3">GeoJSON</dt><dd class="col-sm-9"><pre class="mb-0" style="white-space:pre-wrap">{{ $e->geom ? json_encode($e->geom, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) : '—' }}</pre></dd>
        </dl>
      </div>
    </div>
  </div>

  <div class="col-lg-4">
    <div class="card">
      <div class="card-header">Actions</div>
      <div class="card-body d-flex gap-2">
        <a href="{{ route('extensions.edit',$e) }}" class="btn btn-primary">Éditer</a>
        <form method="POST" action="{{ route('extensions.destroy',$e) }}" onsubmit="return confirm('Supprimer cette extension ?')">
          @csrf @method('DELETE')
          <button class="btn btn-danger">Supprimer</button>
        </form>
      </div>
    </div>
  </div>
</div>
@stop
