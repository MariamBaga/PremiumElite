@extends('adminlte::page')

@section('title', 'RDV Manqués')

@section('content_header')
    <h1>Rendez-vous Manqués</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        {{-- FILTRE --}}
        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-3">
                <input name="search" class="form-control" placeholder="Recherche (nom, tel, email…)"
                       value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <select name="type" class="form-control" onchange="this.form.submit()">
                    <option value="">-- Type --</option>
                    <option value="residentiel" @selected(request('type') === 'residentiel')>Résidentiel</option>
                    <option value="professionnel" @selected(request('type') === 'professionnel')>Professionnel</option>
                </select>
            </div>
            <div class="col-md-2 text-end">
                <button class="btn btn-outline-primary w-100">Filtrer</button>
            </div>
        </form>

        {{-- TABLEAU --}}
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Client</th>
                        <th>Téléphone</th>
                        <th>Type</th>
                        <th>Localité</th>
                        <th>Date planifiée</th>
                        <th>Statut</th>
                        <th>Observation</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($dossiers as $i => $d)
                        <tr class="highlight-red">
                            <td>{{ $i + $dossiers->firstItem() }}</td>
                            <td>{{ $d->client?->nom }}</td>
                            <td>{{ $d->client?->telephone }}</td>
                            <td>{{ $d->type_service }}</td>
                            <td>{{ $d->client?->localisation }}</td>
                            <td>{{ optional($d->date_planifiee)->format('d/m/Y') }}</td>
                            <td>{{ $d->statut_label }}</td>
                            <td>{{ $d->observation }}</td>
                            <td>
                                <a href="{{ route('clients.show', $d->client_id) }}"
                                   class="btn btn-sm btn-outline-secondary">Voir</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center">Aucun rendez-vous manqué trouvé.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $dossiers->links() }}
    </div>
</div>
@stop
