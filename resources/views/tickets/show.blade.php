@extends('adminlte::page')
@section('title','Ticket '.$ticket->reference)
@section('content_header')
  <h1>Ticket {{ $ticket->reference }}</h1>
@stop

@section('content')
@include('partials.alerts')

<div class="row">
  <div class="col-lg-8">
    <div class="card mb-3">
      <div class="card-header">Détails</div>
      <div class="card-body">
        <dl class="row mb-0">
          <dt class="col-sm-3">Titre</dt><dd class="col-sm-9">{{ $ticket->titre }}</dd>
          <dt class="col-sm-3">Client</dt><dd class="col-sm-9">{{ $ticket->client?->displayName ?? '-' }}</dd>
          <dt class="col-sm-3">Dossier</dt><dd class="col-sm-9">{{ $ticket->dossier?->reference ?? '-' }}</dd>
          <dt class="col-sm-3">Équipe</dt><dd class="col-sm-9">{{ $ticket->team?->name ?? '-' }}</dd>
          <dt class="col-sm-3">Type</dt><dd class="col-sm-9">{{ ucfirst($ticket->type) }}</dd>
          <dt class="col-sm-3">Priorité</dt><dd class="col-sm-9"><span class="badge bg-@php echo match($ticket->priorite){'faible'=>'secondary','normal'=>'primary','haute'=>'warning','critique'=>'danger',default=>'secondary'}; @endphp">{{ ucfirst($ticket->priorite) }}</span></dd>
          <dt class="col-sm-3">Statut</dt><dd class="col-sm-9"><span class="badge bg-@php echo match($ticket->statut){'ouvert'=>'info','en_cours'=>'warning','resolu'=>'success','ferme'=>'secondary',default=>'secondary'}; @endphp">{{ Str::headline($ticket->statut) }}</span></dd>
          <dt class="col-sm-3">Créé</dt><dd class="col-sm-9">{{ $ticket->created_at->format('d/m/Y H:i') }}</dd>
          <dt class="col-sm-3">Description</dt><dd class="col-sm-9">{{ $ticket->description ?? '-' }}</dd>
        </dl>
      </div>
    </div>

    {{-- Commentaires --}}
    <div class="card">
      <div class="card-header">Commentaires</div>
      <div class="card-body">
        <form action="{{ route('tickets.update',$ticket) }}" method="POST" class="row g-2 mb-3">
          @csrf @method('PUT')
          <div class="col-md-3">
            <label class="form-label">Statut</label>
            <select name="statut" class="form-control">
              @foreach(['ouvert'=>'Ouvert','en_cours'=>'En cours','resolu'=>'Résolu','ferme'=>'Fermé'] as $k=>$v)
                <option value="{{ $k }}" @selected($ticket->statut===$k)>{{ $v }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Priorité</label>
            <select name="priorite" class="form-control">
              @foreach(['faible'=>'Faible','normal'=>'Normal','haute'=>'Haute','critique'=>'Critique'] as $k=>$v)
                <option value="{{ $k }}" @selected($ticket->priorite===$k)>{{ $v }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label">Équipe</label>
            <select name="assigned_team_id" class="form-control">
              <option value="">-- Aucune --</option>
              @foreach(\App\Models\Team::orderBy('name')->get() as $t)
                <option value="{{ $t->id }}" @selected($ticket->assigned_team_id==$t->id)>{{ $t->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-2 d-flex align-items-end">
            <button class="btn btn-outline-primary w-100">Mettre à jour</button>
          </div>
        </form>

        {{-- Liste messages --}}
        <ul class="list-group">
          @forelse($ticket->comments()->latest()->get() as $c)
            <li class="list-group-item">
              <div class="small text-muted">{{ $c->created_at->format('d/m/Y H:i') }} — {{ $c->user?->name ?? 'Système' }}</div>
              <div>{{ $c->message }}</div>
            </li>
          @empty
            <li class="list-group-item text-muted">Aucun commentaire.</li>
          @endforelse
        </ul>

        {{-- Ajouter un commentaire (route simple) --}}
        <form action="{{ route('tickets.update',$ticket) }}" method="POST" class="mt-3">
          @csrf @method('PUT')
          <input type="hidden" name="statut" value="{{ $ticket->statut }}">
          <input type="hidden" name="priorite" value="{{ $ticket->priorite }}">
          <div class="input-group">
            <textarea name="comment_message" class="form-control" rows="2" placeholder="Ajouter un commentaire..."></textarea>
            <button class="btn btn-primary">Envoyer</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  {{-- Actions rapides --}}
  <div class="col-lg-4">
    <div class="card">
      <div class="card-header">Actions</div>
      <div class="card-body">
        <a href="{{ route('tickets.index') }}" class="btn btn-outline-secondary w-100 mb-2">Retour à la liste</a>
        @if($ticket->dossier)
          <a href="{{ route('dossiers.show',$ticket->dossier) }}" class="btn btn-outline-primary w-100 mb-2">Ouvrir le dossier</a>
        @endif
        @if($ticket->client)
          <a href="{{ route('clients.show',$ticket->client) }}" class="btn btn-outline-primary w-100">Ouvrir le client</a>
        @endif
      </div>
    </div>
  </div>
</div>
@stop
