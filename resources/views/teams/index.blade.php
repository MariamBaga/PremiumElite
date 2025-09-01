@extends('adminlte::page')
@section('title','Équipes')
@section('content_header')
  <h1>Équipes</h1>
@stop
@section('content')
<div class="card">
  <div class="card-body">
    <form method="GET" class="row g-2 mb-3">
      <div class="col-md-4">
        <input name="search" class="form-control" placeholder="Rechercher (nom / zone)" value="{{ request('search') }}">
      </div>
      <div class="col-md-4">
        <div class="form-check mt-2">
          <input class="form-check-input" type="checkbox" name="only_trashed" value="1" onchange="this.form.submit()" @checked(request('only_trashed'))>
          <label class="form-check-label">Afficher la corbeille</label>
        </div>
      </div>
      <div class="col-md-4 text-end">
        @can('teams.create')
        <a href="{{ route('teams.create') }}" class="btn btn-primary">Nouvelle équipe</a>
        @endcan
        @can('teams.restore')
        <a href="{{ route('teams.trash') }}" class="btn btn-outline-secondary">Corbeille</a>
        @endcan


      </div>


    </form>




    <table class="table table-hover">
      <thead><tr><th>Nom</th><th>Zone</th><th>Chef</th><th>Membres</th><th class="text-end">Actions</th></tr></thead>
      <tbody>
      @foreach($teams as $t)
        <tr>
          <td>{{ $t->name }}</td>
          <td>{{ $t->zone ?? '-' }}</td>
          <td>{{ $t->lead?->name ?? '—' }}</td>
          <td>{{ $t->members()->count() }}</td>
          <td class="text-end">
            @can('teams.view')<a class="btn btn-sm btn-outline-secondary" href="{{ route('teams.show',$t) }}">Ouvrir</a>@endcan
            @can('teams.update')<a class="btn btn-sm btn-outline-primary" href="{{ route('teams.edit',$t) }}">Éditer</a>@endcan

       
<a class="btn btn-sm btn-outline-dark" href="{{ route('teams.inbox',$t) }}">Corbeille équipe</a>


            @can('teams.delete')
            <form class="d-inline" method="POST" action="{{ route('teams.destroy',$t) }}" onsubmit="return confirm('Mettre en corbeille ?')">
              @csrf @method('DELETE')
              <button class="btn btn-sm btn-outline-danger">Corbeille</button>
            </form>
            @endcan
          </td>
        </tr>
      @endforeach
      </tbody>
    </table>

    {{ $teams->links() }}
  </div>
</div>
@stop
