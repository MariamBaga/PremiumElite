@extends('adminlte::page')

@section('title', 'Dossiers FTTH')

@section('content_header')
    <h1>Dossiers FTTH</h1>
@stop

@section('content')
<div class="card">
  <div class="card-body">
    <form method="GET" class="row g-2 mb-3">
      <div class="col-md-3">
        <select name="statut" class="form-control" onchange="this.form.submit()">
          <option value="">-- Statut --</option>
          @foreach($statuts as $value => $label)
            <option value="{{ $value }}" @selected(request('statut')===$value)>{{ $label }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-3">
        <select name="type_service" class="form-control" onchange="this.form.submit()">
          <option value="">-- Type de service --</option>
          <option value="residentiel" @selected(request('type_service')==='residentiel')>Résidentiel</option>
          <option value="professionnel" @selected(request('type_service')==='professionnel')>Professionnel</option>
        </select>
      </div>
      @can('dossiers.create')

      <div class="col">
        <a href="{{ route('dossiers.create') }}" class="btn btn-primary float-end">Nouveau dossier ftth</a>

      </div>
      @endcan
      <div class="col-md-3">
    <form action="{{ route('dossiers.import') }}" method="POST" enctype="multipart/form-data" class="d-flex gap-2">
        @csrf
        <input type="file" name="file" accept=".xlsx,.xls,.csv" class="form-control flex-grow-1" required>
        <button type="submit" class="btn btn-success">Importer</button>
    </form>
</div>


    </form>

    <table class="table table-striped">
      <thead><tr>
        <th>Réf.</th><th>Client</th><th>Type</th><th>Statut</th>
        <th>Technicien</th><th>Planifiée</th><th>Actions</th>
      </tr></thead>
      <tbody>
      @foreach($dossiers as $d)
        <tr>
          <td>{{ $d->reference }}</td>
          <td>{{ $d->client->displayName }}</td>
          <td>{{ ucfirst($d->type_service) }}</td>
          <td><span class="badge bg-info">{{ \App\Enums\StatutDossier::labels()[$d->statut->value] ?? $d->statut->value }}</span></td>
          <td>{{ $d->technicien?->name ?? '-' }}</td>
          <td>{{ optional($d->date_planifiee)->format('d/m/Y H:i') }}</td>
          <td>
            <a href="{{ route('dossiers.show',$d) }}" class="btn btn-sm btn-outline-secondary">Ouvrir</a>
          </td>
        </tr>
      @endforeach
      </tbody>
    </table>

    {{ $dossiers->withQueryString()->links() }}
  </div>
</div>
@stop
