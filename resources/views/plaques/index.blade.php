@extends('adminlte::page')
@section('title','Plaques')
@section('content_header')<h1>Plaques</h1>@stop

@section('content')
@include('partials.alerts')

<div class="card">
  <div class="card-body">
    <form method="GET" class="row g-2 mb-3">
      <div class="col-md-3">
        <select name="statut" class="form-control" onchange="this.form.submit()">
          <option value="">-- Statut --</option>
          @foreach(['etude'=>'Étude','gc'=>'GC/Fourettage','pose_pbo_pm'=>'Pose PBO/PM','tirage'=>'Tirage','tests'=>'Tests','service'=>'En service'] as $k=>$v)
            <option value="{{ $k }}" @selected(request('statut')===$k)>{{ $v }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-6"></div>
      <div class="col-md-3 text-end">
        @can('plaques.update')
          <a href="{{ route('plaques.create') }}" class="btn btn-primary">Nouvelle plaque</a>
        @endcan
      </div>
    </form>

    <div class="table-responsive">
      <table class="table table-hover">
        <thead><tr>
          <th>Code</th><th>Nom</th><th>Zone</th><th>Statut</th><th>Couverture</th><th>Foyers</th><th>PBO</th><th class="text-end">Actions</th>
        </tr></thead>
        <tbody>
          @forelse($plaques as $p)
            <tr>
              <td>{{ $p->code }}</td>
              <td>{{ $p->nom }}</td>
              <td>{{ $p->zone }}</td>
              <td>{{ Str::headline($p->statut) }}</td>
              <td>{{ $p->coverage }}%</td>
              <td>{{ $p->foyers_raccordables }}</td>
              <td>{{ $p->pbo_installes }}</td>
              <td class="text-end">
                <a href="{{ route('plaques.show',$p) }}" class="btn btn-sm btn-outline-secondary">Ouvrir</a>
                @can('plaques.update')
                  <a href="{{ route('plaques.edit',$p) }}" class="btn btn-sm btn-outline-primary">Éditer</a>
                @endcan
              </td>
            </tr>
          @empty
            <tr><td colspan="8" class="text-center text-muted py-4">Aucune plaque</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{ $plaques->links('pagination::bootstrap-5') }}
  </div>
</div>
@stop
