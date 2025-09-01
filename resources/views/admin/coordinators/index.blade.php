{{-- resources/views/admin/coordinators/index.blade.php --}}
@extends('adminlte::page')

@section('title', 'Liste des Coordinateurs')

@section('content_header')
    <h1>Coordinateurs</h1>
@stop

@section('content')
<div class="container-fluid">

    {{-- Filtre par rôle --}}
    <form method="GET" action="{{ route('admin.coordinators.index') }}" class="mb-3 d-flex gap-2 align-items-center">
        <label for="role" class="mb-0">Filtrer par rôle :</label>
        <select name="role" id="role" class="form-control" style="width:auto">
            <option value="">Tous</option>
            @foreach(\Spatie\Permission\Models\Role::all() as $role)
                <option value="{{ $role->name }}" {{ request('role') == $role->name ? 'selected' : '' }}>
                    {{ ucfirst($role->name) }}
                </option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-primary">Filtrer</button>
        <a href="{{ route('admin.coordinators.index') }}" class="btn btn-secondary">Réinitialiser</a>
    </form>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible">
            {{ session('success') }}
            <button type="button" class="close" data-bs-dismiss="alert">&times;</button>
        </div>
    @endif

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">Liste des coordinateurs</h3>
            <a href="{{ route('admin.coordinators.create') }}" class="btn btn-success">
                <i class="fas fa-plus"></i> Créer un coordinateur
            </a>
        </div>
        <div class="card-body table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Rôle(s)</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($coordinators as $coord)
                        <tr>
                            <td>{{ $coord->name }}</td>
                            <td>{{ $coord->email }}</td>
                            <td>
                                @foreach($coord->roles as $role)
                                    <span class="badge bg-info">{{ $role->name }}</span>
                                @endforeach
                            </td>
                            <td>
                                <a href="{{ route('admin.coordinators.edit', $coord->id) }}" class="btn btn-sm btn-warning">Éditer</a>
                                <form action="{{ route('admin.coordinators.destroy', $coord->id) }}" method="POST" style="display:inline-block">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer ce coordinateur ?')">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach

                    @if($coordinators->isEmpty())
                        <tr>
                            <td colspan="4" class="text-center">Aucun coordinateur trouvé.</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $coordinators->withQueryString()->links() }} {{-- pagination --}}
        </div>
    </div>
</div>
@stop
