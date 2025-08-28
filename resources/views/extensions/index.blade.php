@extends('adminlte::page')
@section('title','Extensions FTTH')
@section('content_header')
  <h1>Extensions FTTH</h1>
@stop

@section('content')
<div class="card">
  <div class="card-body">
    <form method="GET" class="row g-2 mb-3">
      <div class="col-md-3">
        <input name="q" class="form-control" placeholder="Recherche (code/zone)" value="{{ request('q') }}">
      </div>
      <div class="col-md-3">
        <input name="zone" class="form-control" placeholder="Zone" value="{{ request('zone') }}">
      </div>
      <div class="col-md-3">
        <select name="statut" class="form-control" onchange="this.form.submit()">
          <option value="">-- Statut --</option>
          @foreach($statuts as $k=>$v)
            <option value="{{ $k }}" @selected(request('statut')===$k)>{{ $v }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-3 text-end">
        <a href="{{ route('extensions.create') }}" class="btn btn-primary">Nouvelle extension</a>
      </div>
    </form>

    <div class="table-responsive">
      <table class="table table-hover">
        <thead><tr>
          <th>Code</th><th>Zone</th><th>Statut</th><th>Foyers ciblés</th><th>ROI estimé</th><th class="text-end">Actions</th>
        </tr></thead>
        <tbody>
          @foreach($extensions as $e)
            <tr>
              <td>{{ $e->code }}</td>
              <td>{{ $e->zone ?? '-' }}</td>
              <td>{{ \Illuminate\Support\Str::headline($e->statut) }}</td>
              <td>{{ $e->foyers_cibles }}</td>
              <td>{{ $e->roi_estime ? number_format($e->roi_estime,2,',',' ') : '-' }}</td>
              <td class="text-end">
                <a href="{{ route('extensions.show',$e) }}" class="btn btn-sm btn-outline-secondary">Ouvrir</a>
                <a href="{{ route('extensions.edit',$e) }}" class="btn btn-sm btn-outline-primary">Éditer</a>
                <form method="POST" action="{{ route('extensions.destroy',$e) }}" class="d-inline" onsubmit="return confirm('Supprimer ?')">
                  @csrf @method('DELETE')
                  <button class="btn btn-sm btn-outline-danger">Supprimer</button>
                </form>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    {{ $extensions->links() }}
  </div>
</div>
@stop
