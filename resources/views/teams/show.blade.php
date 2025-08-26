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

    {{-- Membres --}}
    <div class="card mb-3">
      <div class="card-header">Membres ({{ $team->members->count() }})</div>
      <div class="card-body p-0">
        @if($team->members->isEmpty())
          <div class="p-3 text-muted">Aucun membre pour l’instant.</div>
        @else
          <table class="table table-hover mb-0">
            <thead><tr><th>Nom</th><th>Email</th><th>Rôle</th><th class="text-end">Actions</th></tr></thead>
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

      @can('teams.manage-members')
      <div class="card-footer">
        <form method="POST" action="{{ route('teams.members.add',$team) }}" class="row g-2">
          @csrf
          <div class="col-md-8">
            <select name="user_id" class="form-control" required>
              <option value="">— Ajouter un utilisateur —</option>
              @foreach(\App\Models\User::orderBy('name')->get() as $u)
                @if(!$team->members->contains('id',$u->id))
                  <option value="{{ $u->id }}">{{ $u->name }} — {{ $u->email }}</option>
                @endif
              @endforeach
            </select>
          </div>
          <div class="col-md-4">
            <button class="btn btn-outline-primary w-100">Ajouter</button>
          </div>
        </form>
      </div>
      @endcan
    </div>

    {{-- Dossiers assignés à l’équipe (si la colonne existe) --}}
    @if (Schema::hasColumn('dossiers_raccordement','assigned_team_id'))
    <div class="card">
      <div class="card-header">Dossiers assignés</div>
      <div class="card-body p-0">
        @php $dossiers = $team->dossiers()->with('client')->latest()->limit(10)->get(); @endphp
        @if($dossiers->isEmpty())
          <div class="p-3 text-muted">Aucun dossier assigné à cette équipe.</div>
        @else
          <table class="table table-hover mb-0">
            <thead><tr><th>Référence</th><th>Client</th><th>Statut</th><th>Planifiée</th><th></th></tr></thead>
            <tbody>
              @foreach($dossiers as $d)
                <tr>
                  <td>{{ $d->reference }}</td>
                  <td>{{ $d->client?->displayName }}</td>
                  <td>{{ \Illuminate\Support\Str::headline($d->statut?->value ?? $d->statut) }}</td>
                  <td class="text-nowrap">{{ optional($d->date_planifiee)->format('d/m/Y H:i') }}</td>
                  <td class="text-end">
                    @can('dossiers.view')
                      <a href="{{ route('dossiers.show',$d) }}" class="btn btn-sm btn-outline-secondary">Ouvrir</a>
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

  <div class="col-lg-4">
    <div class="card">
      <div class="card-header">Actions</div>
      <div class="card-body d-flex gap-2">
        @can('teams.update')
          <a href="{{ route('teams.edit',$team) }}" class="btn btn-primary">Éditer</a>
        @endcan
        @can('teams.delete')
        <form method="POST" action="{{ route('teams.destroy',$team) }}" class="ms-2"
              onsubmit="return confirm('Mettre cette équipe en corbeille ?')">
          @csrf @method('DELETE')
          <button class="btn btn-danger">Mettre en corbeille</button>
        </form>
        @endcan
      </div>
    </div>
  </div>
</div>
@stop
