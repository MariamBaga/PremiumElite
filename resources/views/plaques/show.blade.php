@extends('adminlte::page')
@section('title','Plaque '.$plaque->code)
@section('content_header')<h1>Plaque {{ $plaque->code }}</h1>@stop

@section('content')
@include('partials.alerts')

<div class="row">
  <div class="col-lg-8">
    <div class="card mb-3">
      <div class="card-header">Informations</div>
      <div class="card-body">
        <dl class="row mb-0">
          <dt class="col-sm-3">Nom</dt><dd class="col-sm-9">{{ $plaque->nom ?? '-' }}</dd>
          <dt class="col-sm-3">Zone</dt><dd class="col-sm-9">{{ $plaque->zone ?? '-' }}</dd>
          <dt class="col-sm-3">Statut</dt><dd class="col-sm-9">{{ Str::headline($plaque->statut) }}</dd>
          <dt class="col-sm-3">Couverture</dt><dd class="col-sm-9">{{ $plaque->coverage }} %</dd>
          <dt class="col-sm-3">Foyers</dt><dd class="col-sm-9">{{ $plaque->foyers_raccordables }}</dd>
          <dt class="col-sm-3">PBO</dt><dd class="col-sm-9">{{ $plaque->pbo_installes }}</dd>
          <dt class="col-sm-3">Geom</dt><dd class="col-sm-9"><code class="small">{{ Str::limit(json_encode($plaque->geom), 120) }}</code></dd>
        </dl>
      </div>
    </div>
  </div>

  <div class="col-lg-4">
    <div class="card">
      <div class="card-header">Actions</div>
      <div class="card-body">
        <a href="{{ route('plaques.index') }}" class="btn btn-outline-secondary w-100 mb-2">Retour</a>
        @can('plaques.update')
          <a href="{{ route('plaques.edit',$plaque) }}" class="btn btn-primary w-100">Éditer</a>
        @endcan
      </div>
    </div>
  </div>
</div>
@stop
