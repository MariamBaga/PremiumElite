@extends('adminlte::page')
@section('title','Extensions')
@section('content_header')<h1>Extensions</h1>@stop

@section('content')
@include('partials.alerts')

<div class="card">
  <div class="card-body">
    <form method="GET" class="row g-2 mb-3">
      <div class="col-md-3">
        <select name="statut" class="form-control" onchange="this.form.submit()">
          <option value="">-- Statut --</option>
          @foreach(['planifie'=>'Planifié','en_cours'=>'En cours','termine'=>'Terminé'] as $k=>$v)
            <option value="{{ $k }}" @selected(request('statut')===$k)>{{ $v }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-6"></div>
      <div class="col-md-3 text-end">
        @can('extensions.update')
          <a href="{{ route('extensions.create') }}" class="btn btn-primary">Nouvelle extension</a>
        @endcan
      </div>
    </form>

    <div class="table-responsive">
      <table class="table table-hover">
        <thead><tr>
          <th>Code</th><th>Zone</th><th>Statut</th><th>Foyers cibles</th><th>ROI estimé</th><th class="text-end">Actions</th>
        </tr></thead>
        <tbody>
          @forelse($extensions as $e)
            <tr>
              <td>{{ $e->code }}</td>
              <td>{{ $e->zone }}</td>
              <td>{{ Str::headline($e->statut) }}</td>
              <td>{{ $e->foyers_cibles }}</td>
              <td>{{ $e->roi_estime ? number_format($e->roi_estime,2,',',' ') : '-' }}</td>
              <td class="text-end">
                <a href="{{ route('extensions.show',$e) }}" class="btn btn-sm btn-outline-secondary">Ouvrir</a>
                @can('extensions.update')
                  <a href="{{ route('extensions.edit',$e) }}" class="btn btn-sm btn-outline-primary">Éditer</a>
                @endcan
              </td>
            </tr>
          @empty
            <tr><td colspan="6" class="text-center text-muted py-4">Aucune extension</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{ $extensions->links() }}
  </div>
</div>
@stop
