@extends('adminlte::page')

@section('title','Clients')
@section('content_header')
  <h1>Clients</h1>
@stop

@section('content')
<div class="card">
  <div class="card-body">
    <form method="GET" class="row g-2 mb-3">
      <div class="col-md-3">
        <select name="type" class="form-control" onchange="this.form.submit()">
          <option value="">-- Type --</option>
          <option value="residentiel" @selected(request('type')==='residentiel')>Résidentiel</option>
          <option value="professionnel" @selected(request('type')==='professionnel')>Professionnel</option>
        </select>
      </div>
      <div class="col-md-5">
        <input name="search" class="form-control" placeholder="Rechercher (nom, téléphone, email...)" value="{{ request('search') }}">
      </div>
      <div class="col-md-4 text-end">
        <a href="{{ route('clients.create') }}" class="btn btn-primary">Nouveau client</a>
      </div>
    </form>

    <table class="table table-striped">
      <thead>
        <tr>
          <th>#</th><th>Nom / Raison sociale</th><th>Type</th><th>Téléphone</th><th>Email</th><th>Ville</th><th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @foreach($clients as $c)
          <tr>
            <td>{{ $c->id }}</td>
            <td>
              @if($c->type === 'professionnel')
                {{ $c->raison_sociale }}
              @else
                {{ trim(($c->prenom ?? '').' '.($c->nom ?? '')) }}
              @endif
            </td>
            <td>{{ ucfirst($c->type) }}</td>
            <td>{{ $c->telephone }}</td>
            <td>{{ $c->email }}</td>
            <td>{{ $c->ville }}</td>
            <td>
              <a class="btn btn-sm btn-outline-secondary" href="{{ route('clients.show',$c) }}">Ouvrir</a>
              <a class="btn btn-sm btn-outline-primary" href="{{ route('clients.edit',$c) }}">Éditer</a>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>

    {{ $clients->links() }}
  </div>
</div>
@stop
