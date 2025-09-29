@extends('adminlte::page')
@section('title','Équipe '.$team->name)
@section('content_header')
  <h1>
    {{ $team->name }}
    <small class="text-muted">— {{ $team->zone ?? 'Zone n/c' }}</small>
  </h1>
@stop

@section('content')
<div class="row">
  <div class="col-lg-8">

    {{-- Informations de l'équipe --}}
    <div class="card mb-3">
      <div class="card-header">Informations</div>
      <div class="card-body">
        <dl class="row mb-0">
          <dt class="col-sm-3">Nom</dt><dd class="col-sm-9">{{ $team->name }}</dd>
          <dt class="col-sm-3">Zone</dt><dd class="col-sm-9">{{ $team->zone ?? '—' }}</dd>
          <dt class="col-sm-3">Chef</dt><dd class="col-sm-9">{{ $team->lead?->name ?? '—' }}</dd>
          <dt class="col-sm-3">Description</dt><dd class="col-sm-9">{{ $team->description ?? '—' }}</dd>
        </dl>
      </div>
    </div>

    {{-- Membres texte (non-utilisateurs) --}}
    <div class="card mb-3">
      <div class="card-header">Membres </div>
      <div class="card-body">
        @php
            $members = $team->members_names;
            if (is_string($members)) {
                $members = json_decode($members,true) ?? [];
            } elseif (!is_array($members)) {
                $members = [];
            }
        @endphp

        @if(!empty($members))
            <ul class="list-group mb-3">
                @foreach($members as $member)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        {{ $member }}
                        <!-- @can('teams.manage-members')
                            <form method="POST" action="{{ route('teams.remove-member-text', $team) }}" class="m-0 p-0">
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="name" value="{{ $member }}">
                                <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Retirer ce membre ?')">Supprimer</button>
                            </form>
                        @endcan -->
                    </li>
                @endforeach
            </ul>
        @else
            <div class="text-muted mb-3">Aucun membre texte.</div>
        @endif

        <!-- @can('teams.manage-members')
          <form method="POST" action="{{ route('teams.add-member-text', $team) }}" class="d-flex gap-2">
            @csrf
            <input type="text" name="name" class="form-control" placeholder="Ajouter un membre texte" required>
            <button class="btn btn-success">Ajouter</button>
          </form>
        @endcan -->
      </div>
    </div>

    {{-- Membres utilisateurs --}}
    <div class="card mb-3">
      <div class="card-header">Membres utilisateurs ({{ $team->members->count() }})</div>
      <div class="card-body p-0">
        @if($team->members->isEmpty())
          <div class="p-3 text-muted">Aucun membre utilisateur.</div>
        @else
          <table class="table table-hover mb-0">
            <thead>
              <tr>
                <th>Nom</th>
                <th>Email</th>
                <th>Rôle</th>
                <th class="text-end">Actions</th>
              </tr>
            </thead>
            <tbody>
              @foreach($team->members as $m)
                <tr>
                  <td>{{ $m->name }}</td>
                  <td class="text-nowrap">{{ $m->email }}</td>
                  <td>
                    @if($team->lead_id === $m->id)
                      <span class="badge bg-primary">Chef d’équipe</span>
                    @else
                      Membre
                    @endif
                  </td>
                  <td class="text-end">
                    @can('teams.assign-lead')
                      @if($team->lead_id !== $m->id)
                        <form method="POST" action="{{ route('teams.set-lead', [$team,$m]) }}" class="d-inline">
                          @csrf
                          <button class="btn btn-sm btn-outline-primary" onclick="return confirm('Définir comme chef ?')">
                            Définir chef
                          </button>
                        </form>
                      @endif
                    @endcan

                    @can('teams.manage-members')
                      <form method="POST" action="{{ route('teams.members.remove', [$team,$m]) }}" class="d-inline">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Retirer ce membre ?')">
                          Retirer
                        </button>
                      </form>
                    @endcan
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        @endif
      </div>
    </div>

    {{-- Dossiers assignés --}}
    {{-- Dossiers assignés --}}
@if (Schema::hasColumn('dossiers_raccordement','assigned_team_id'))
  <div class="card">
    <div class="card-header">Dossiers assignés</div>
    <div class="card-body p-0">
      @php $dossiers = $team->dossiers()->with('client')->latest()->get(); @endphp
      @if($dossiers->isEmpty())
        <div class="p-3 text-muted">Aucun dossier assigné à cette équipe.</div>
      @else
        <table class="table table-hover mb-0">
          <thead>
            <tr>
              <th>Référence</th>
              <th>Client</th>
              <th>Statut</th>
              <th>Planifiée</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            @foreach($dossiers as $d)
              <tr>
                <td>{{ $d->reference }}</td>
                <td>{{ $d->client?->displayName }}</td>
                <td>{{ \Illuminate\Support\Str::headline($d->statut?->value ?? $d->statut) }}</td>
                <td class="text-nowrap">{{ optional($d->date_planifiee)->format('d/m/Y H:i') }}</td>
                <td class="text-end">
                  @can('dossiers.view')
                    <a href="{{ route('clients.show',$d) }}" class="btn btn-sm btn-outline-secondary">Ouvrir</a>
                  @endcan

                  @can('teams.manage-members')
                    <form method="POST" action="{{ route('teams.remove-dossier', [$team, $d]) }}" class="d-inline">
                      @csrf @method('DELETE')
                      <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Retirer ce dossier de l’équipe ?')">Retirer</button>
                    </form>
                  @endcan
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      @endif
    </div>
  </div>
@endif


  </div>

  {{-- Actions + création d’utilisateur --}}
  <div class="col-lg-4">
    <div class="card">
      <div class="card-header">Actions</div>
      <div class="card-body d-flex gap-2 flex-column">
        @can('teams.update')
          <a href="{{ route('teams.edit',$team) }}" class="btn btn-primary mb-2">Éditer l’équipe</a>
        @endcan

      </div>
    </div>
  </div>
</div>
@stop
