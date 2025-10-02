@extends('adminlte::page')

@section('title', 'Nouveaux Rendez-vous')

@section('content_header')
    <h1>Nouveaux Rendez-vous</h1>
@stop

@push('css')
<style>
.scroll-top-wrapper {
    overflow-x: scroll !important;
    overflow-y: hidden;
    height: 16px;
    background: #f8f9fa;
    border-bottom: 1px solid #ccc;
    scrollbar-color: #007bff #e9ecef;
    scrollbar-width: thin;
}
.scroll-top-wrapper::-webkit-scrollbar {
    height: 10px;
}
.scroll-top-wrapper::-webkit-scrollbar-thumb {
    background: #007bff;
    border-radius: 4px;
}
.scroll-top-wrapper::-webkit-scrollbar-track {
    background: #e9ecef;
}
</style>
@endpush


@section('content')
<div class="card">
    <div class="card-body">
        {{-- Wrapper pour scrollbar horizontale en haut --}}
        <div class="scroll-top-wrapper mb-1" style="overflow-x:auto; overflow-y:hidden; height:20px;"></div>

        <div class="table-responsive" style="max-height:600px; overflow-y:auto; overflow-x:hidden;">
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
                        @php $d = $c->lastDossier; @endphp
                        <tr>
                            <td>{{ $i + $clients->firstItem() }}</td>
                            <td class="text-truncate" style="max-width:220px;">{{ $c->displayName }}</td>
                            <td class="text-nowrap">{{ $d?->ligne ?? $c->numero_ligne }}</td>
                            <td class="text-nowrap">{{ $d?->contact ?? $c->telephone }}</td>
                            <td class="text-nowrap">{{ $d?->service_acces }}</td>
                            <td class="text-nowrap">{{ $d?->localite }}</td>
                            <td class="text-nowrap">{{ $d?->categorie }}</td>
                            <td class="text-nowrap">{{ optional($d?->date_reception_raccordement)->format('d/m/Y') }}</td>
                            <td class="text-nowrap">{{ optional($d?->date_fin_travaux)->format('d/m/Y') }}</td>
                            <td class="text-nowrap">{{ $d?->port }}</td>
                            <td class="text-nowrap">{{ $d?->pbo_lineaire_utilise }}</td>
                            <td class="text-nowrap">{{ $d?->nb_poteaux_implantes }}</td>
                            <td class="text-nowrap">{{ $d?->nb_armements_poteaux }}</td>
                            <td class="text-nowrap">{{ $d?->statut_label }}</td>
                            <td class="text-nowrap">{{ $d?->taux_reporting_j1 }}</td>
                             <td class="text-nowrap">
    @if ($d?->statut?->value === \App\Enums\StatutDossier::ACTIVE->value)
        <span class="badge bg-success">Oui</span>
    @else
        <span class="badge bg-secondary">Non</span>
    @endif
</td>
                            <td class="text-truncate" style="max-width:220px;">{{ $d?->observation }}</td>
                            <td class="text-nowrap">{{ $d?->pilote_raccordement }}</td>
                            <td class="text-end">
                                <div class="d-flex flex-wrap gap-1 justify-content-end align-items-center">
                                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('clients.show', $c) }}">Ouvrir</a>
                                    @can('clients.edit')
                                        <a class="btn btn-sm btn-outline-primary" href="{{ route('clients.edit', $c) }}">Éditer</a>
                                    @endcan



                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $clients->links('pagination::bootstrap-5') }}
        </div>


    </div>
</div>
@stop

@push('css')
<style>
    .dataTables_wrapper .dataTables_length select {
        padding-right: 24px;
    }
    .table td,
    .table th {
        white-space: nowrap;
    }
</style>
@endpush

@push('js')
<script>
    $('#dossiersTable').DataTable({
        paging: false,
        searching: false,
        info: false,
        ordering: false,
        responsive: true,
        autoWidth: false,
    });

    // Scroll horizontal synchronisé
    const topWrapper = document.querySelector('.scroll-top-wrapper');
    const tableWrapper = document.querySelector('.table-responsive');
    const cloneTable = tableWrapper.querySelector('table').cloneNode(true);
    cloneTable.style.visibility = 'hidden';
    cloneTable.style.pointerEvents = 'none';
    topWrapper.appendChild(cloneTable);

    topWrapper.addEventListener('scroll', () => {
        tableWrapper.scrollLeft = topWrapper.scrollLeft;
    });
    tableWrapper.addEventListener('scroll', () => {
        topWrapper.scrollLeft = tableWrapper.scrollLeft;
    });


</script>
@endpush

