@extends('adminlte::page')
@section('title','Corbeille équipes')
@section('content_header')<h1>Corbeille — Équipes</h1>@stop

@section('content')
<div class="card">
  <div class="card-body">
    @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif

    <table class="table table-hover">
      <thead>
        <tr>
          <th>Nom</th>
          <th>Zone</th>
          <th>Supprimée le</th>
          <th class="text-end">Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($teams as $t)
          <tr>
            <td>{{ $t->name }}</td>
            <td>{{ $t->zone ?? '—' }}</td>
            <td class="text-nowrap">{{ optional($t->deleted_at)->format('d/m/Y H:i') }}</td>
            <td class="text-end">
              @can('teams.restore')
              <form method="POST" action="{{ route('teams.restore',$t->id) }}" class="d-inline">
                @csrf
                <button class="btn btn-sm btn-outline-primary" onclick="return confirm('Restaurer cette équipe ?')">Restaurer</button>
              </form>
              @endcan

              @can('teams.force-delete')
              <form method="POST" action="{{ route('teams.force-delete',$t->id) }}" class="d-inline">
                @csrf @method('DELETE')
                <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Suppression DÉFINITIVE ?')">Supprimer définitivement</button>
              </form>
              @endcan
            </td>
          </tr>


    
        @empty
          <tr><td colspan="4" class="text-muted">Corbeille vide.</td></tr>
        @endforelse
      </tbody>
    </table>

    {{ $teams->links() }}
  </div>
</div>
@stop
