@extends('adminlte::page')

@section('title', 'Injoignables') <!-- ou 'Injoignables' -->
@section('content_header')
    <h1>Injoignables</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table id="dossiersTable" class="table table-striped table-hover align-middle w-100">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>CLIENT</th>
                        <th>LIGNE</th>
                        <th>Contact</th>
                        <th>Service</th>
                        <th>LOCALITE</th>
                        <th>Catégorie</th>
                        <th>Réception</th>
                        <th>Fin Travaux</th>
                        <th>Port</th>
                        <th>PBO / Linéaire</th>
                        <th>Poteaux</th>
                        <th>Armements</th>
                        <th>Statut</th>
                        <th>Reporting J+1</th>
                        <th>ACTIVE</th>
                        <th>Observation</th>
                        <th>Pilote</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($clients as $i => $c)
                        @php $d = $c->dossiers->first(); @endphp
                        <tr>
                            <td>{{ $i+1 }}</td>
                            <td>{{ $c->displayName }}</td>
                            <td>{{ $d?->ligne ?? $c->numero_ligne }}</td>
                            <td>{{ $d?->contact ?? $c->telephone }}</td>
                            <td>{{ $d?->service_acces }}</td>
                            <td>{{ $d?->localite }}</td>
                            <td>{{ $d?->categorie }}</td>
                            <td>{{ optional($d?->date_reception_raccordement)->format('d/m/Y') }}</td>
                            <td>{{ optional($d?->date_fin_travaux)->format('d/m/Y') }}</td>
                            <td>{{ $d?->port }}</td>
                            <td>{{ $d?->pbo_lineaire_utilise }}</td>
                            <td>{{ $d?->nb_poteaux_implantes }}</td>
                            <td>{{ $d?->nb_armements_poteaux }}</td>
                            <td>{{ $d?->statut_label }}</td>
                            <td>{{ $d?->taux_reporting_j1 }}</td>
                            <td>
                                @if($d?->is_active) <span class="badge bg-success">Oui</span>
                                @else <span class="badge bg-secondary">Non</span>
                                @endif
                            </td>
                            <td>{{ $d?->observation }}</td>
                            <td>{{ $d?->pilote_raccordement }}</td>
                            <td class="text-end">
                                <a class="btn btn-sm btn-outline-secondary" href="{{ route('clients.show', $c) }}">Ouvrir</a>
                                @can('clients.edit')
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('clients.edit', $c) }}">Éditer</a>
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@stop

@push('js')
<script>
$(function () {
    $('#dossiersTable').DataTable({
        responsive: true,
        autoWidth: false,
        pageLength: 25,
        lengthMenu: [[10,25,50,100,-1],[10,25,50,100,'Tous']],
        order: [[0,'asc']],
        language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json' },
        dom: 'Bfrtip',
        buttons: ['copy','csv','excel','pdf','print','colvis']
    });
});
</script>
@endpush
