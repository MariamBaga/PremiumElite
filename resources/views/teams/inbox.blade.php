@extends('adminlte::page')
@section('title','Corbeille / Boîte équipe — '.$team->name)
@section('content_header')
  <h1>Corbeille équipe — {{ $team->name }}</h1>
@stop

@section('content')
@include('partials.alerts')

<div class="card">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead>
          <tr>
            <th>Réf.</th>
            <th>Client</th>
            <th>Statut dossier</th>
            <th>État équipe</th>
            <th>Raison / Date</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($items as $it)
            @php $d = $it->dossier; @endphp
            <tr>
              <td><a href="{{ route('dossiers.show',$d) }}">{{ $d->reference }}</a></td>
              <td>
                {{ $d->client?->displayName }}
                <div class="text-muted small">{{ $d->client?->telephone }}</div>
              </td>
              <td class="text-nowrap">{{ \Illuminate\Support\Str::headline($d->statut?->value ?? $d->statut) }}</td>
              <td>
                @if($it->etat==='en_cours') <span class="badge bg-secondary">En cours</span>
                @elseif($it->etat==='contrainte') <span class="badge bg-warning">Contrainte</span>
                @elseif($it->etat==='reporte') <span class="badge bg-info">Reporté</span>
                @elseif($it->etat==='cloture') <span class="badge bg-success">Clôturé</span>
                @endif
              </td>
              <td class="small">
                @if($it->etat==='reporte')
                  <div>Report au : {{ optional($it->date_report)->format('d/m/Y H:i') }}</div>
                @endif
                @if($it->motif)
                  <div class="text-muted">{{ $it->motif }}</div>
                @endif
              </td>
              <td class="text-end">
                {{-- Clôturer --}}
                @can('teams.manage-members')
                <form method="POST" action="{{ route('teams.inbox.close', [$team,$d]) }}" class="d-inline">
                  @csrf
                  <input type="hidden" name="motif" value="Installation OK, client activé">
                  <button class="btn btn-sm btn-success"
                          onclick="return confirm('Clôturer ce dossier ?')">Clôturer</button>
                </form>
                @endcan

                {{-- Contrainte (modal simple) --}}
                @can('teams.manage-members')
                <button class="btn btn-sm btn-warning" data-toggle="modal" data-target="#contraintModal-{{ $it->id }}">
                  Contrainte
                </button>
                <div class="modal fade" id="contraintModal-{{ $it->id }}" tabindex="-1" aria-hidden="true">
                  <div class="modal-dialog">
                    <form method="POST" action="{{ route('teams.inbox.constraint', [$team,$d]) }}" class="modal-content">
                      @csrf
                      <div class="modal-header"><h5 class="modal-title">Contrainte — {{ $d->reference }}</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button></div>
                      <div class="modal-body">
                        <textarea name="motif" class="form-control" rows="3" placeholder="Décrire la contrainte" required></textarea>
                      </div>
                      <div class="modal-footer">
                        <button class="btn btn-primary">Enregistrer</button>
                      </div>
                    </form>
                  </div>
                </div>
                @endcan

                {{-- Reporter --}}
                @can('teams.manage-members')
                <button class="btn btn-sm btn-info" data-toggle="modal" data-target="#reportModal-{{ $it->id }}">
                  Reporter
                </button>
                <div class="modal fade" id="reportModal-{{ $it->id }}" tabindex="-1" aria-hidden="true">
                  <div class="modal-dialog">
                    <form method="POST" action="{{ route('teams.inbox.reschedule', [$team,$d]) }}" class="modal-content">
                      @csrf
                      <div class="modal-header"><h5 class="modal-title">Reporter — {{ $d->reference }}</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button></div>
                      <div class="modal-body">
                        <div class="mb-2">
                          <label>Nouvelle date</label>
                          <input type="datetime-local" name="date_report" class="form-control" required>
                        </div>
                        <div class="mb-2">
                          <label>Motif (optionnel)</label>
                          <textarea name="motif" class="form-control" rows="2"></textarea>
                        </div>
                      </div>
                      <div class="modal-footer">
                        <button class="btn btn-primary">Replanifier</button>
                      </div>
                    </form>
                  </div>
                </div>
                @endcan
              </td>
            </tr>
          @empty
            <tr><td colspan="6" class="text-center text-muted p-4">Aucun dossier dans la corbeille.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
  <div class="card-footer">
    {{ $items->links() }}
  </div>
</div>
@stop
