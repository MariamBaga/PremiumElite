@extends('adminlte::page')
@section('title','Extension '.$extension->code)
@section('content_header')<h1>Extension {{ $extension->code }}</h1>@stop

@section('content')
@include('partials.alerts')

<div class="row">
  <div class="col-lg-8">
    <div class="card mb-3">
      <div class="card-header">Informations</div>
      <div class="card-body">
        <dl class="row mb-0">
          <dt class="col-sm-3">Zone</dt><dd class="col-sm-9">{{ $extension->zone ?? '-' }}</dd>
          <dt class="col-sm-3">Statut</dt><dd class="col-sm-9">{{ Str::headline($extension->statut) }}</dd>
          <dt class="col-sm-3">Foyers cibles</dt><dd class="col-sm-9">{{ $extension->foyers_cibles }}</dd>
          <dt class="col-sm-3">ROI estimé</dt><dd class="col-sm-9">{{ $extension->roi_estime ? number_format($extension->roi_estime,2,',',' ') : '-' }}</dd>
          <dt class="col-sm-3">Geom</dt><dd class="col-sm-9"><code class="small">{{ Str::limit(json_encode($extension->geom), 120) }}</code></dd>
        </dl>
      </div>
    </div>
  </div>

  <div class="col-lg-4">
    <div class="card">
      <div class="card-header">Actions</div>
      <div class="card-body">
        <a href="{{ route('extensions.index') }}" class="btn btn-outline-secondary w-100 mb-2">Retour</a>
        @can('extensions.update')
          <a href="{{ route('extensions.edit',$extension) }}" class="btn btn-primary w-100">Éditer</a>
        @endcan
      </div>
    </div>
  </div>
</div>
@stop
