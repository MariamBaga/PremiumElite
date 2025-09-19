@extends('adminlte::page')
@section('title','Tickets')
@section('content_header')
  <h1>Tickets</h1>
@stop

@section('content')
@include('partials.alerts')

<div class="card">
  <div class="card-body">
    <form method="GET" class="row g-2 mb-3">
      <div class="col-md-3">
        <select name="statut" class="form-control" onchange="this.form.submit()">
          <option value="">-- Statut --</option>
          @foreach(['ouvert'=>'Ouvert','en_cours'=>'En cours','resolu'=>'Résolu','ferme'=>'Fermé'] as $k=>$v)
            <option value="{{ $k }}" @selected(request('statut')===$k)>{{ $v }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-3">
        <select name="priorite" class="form-control" onchange="this.form.submit()">
          <option value="">-- Priorité --</option>
          @foreach(['faible'=>'Faible','normal'=>'Normal','haute'=>'Haute','critique'=>'Critique'] as $k=>$v)
            <option value="{{ $k }}" @selected(request('priorite')===$k)>{{ $v }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-6 text-end">
        @can('tickets.create')
          <a href="{{ route('tickets.create') }}" class="btn btn-primary">Nouveau ticket</a>
        @endcan
      </div>
    </form>

    <div class="table-responsive">
      <table class="table table-striped">
        <thead><tr>
          <th>Ref</th><th>Titre</th><th>Client</th><th>Équipe</th><th>Priorité</th><th>Statut</th><th>Créé</th><th class="text-end">Actions</th>
        </tr></thead>
        <tbody>
          @forelse($tickets as $t)
            <tr>
              <td>{{ $t->reference }}</td>
              <td>{{ $t->titre }}</td>
              <td>{{ $t->client?->displayName ?? '-' }}</td>
              <td>{{ $t->team?->name ?? '-' }}</td>
              <td><span class="badge bg-@php echo match($t->priorite){'faible'=>'secondary','normal'=>'primary','haute'=>'warning','critique'=>'danger',default=>'secondary'}; @endphp">{{ ucfirst($t->priorite) }}</span></td>
              <td><span class="badge bg-@php echo match($t->statut){'ouvert'=>'info','en_cours'=>'warning','resolu'=>'success','ferme'=>'secondary',default=>'secondary'}; @endphp">{{ Str::headline($t->statut) }}</span></td>
              <td>{{ $t->created_at->format('d/m/Y H:i') }}</td>
              <td class="text-end">
                <a href="{{ route('tickets.show',$t) }}" class="btn btn-sm btn-outline-secondary">Ouvrir</a>
              </td>
            </tr>
          @empty
            <tr><td colspan="8" class="text-center text-muted py-4">Aucun ticket</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{ $tickets->links('pagination::bootstrap-5') }}
  </div>
</div>
@stop
