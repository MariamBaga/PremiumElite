{{-- resources/views/admin/users/index.blade.php --}}
@extends('adminlte::page')

@section('title', 'Liste des Utilisateurs')

@section('content_header')
    <h1>Utilisateurs</h1>
@stop

@section('content')
<div class="container-fluid">

    {{-- Filtre par rôle --}}
    <form method="GET" action="{{ route('admin.users.index') }}" class="mb-3 d-flex gap-2 align-items-center">
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
        <!-- <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Réinitialiser</a> -->
    </form>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible">
            {{ session('success') }}
            <button type="button" class="close" data-bs-dismiss="alert">&times;</button>
        </div>
    @endif

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">Liste des utilisateurs</h3>
            <a href="{{ route('admin.users.create') }}" class="btn btn-success">
                <i class="fas fa-plus"></i> Créer un utilisateur
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
                    @forelse($users as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>
                                @foreach($user->roles as $role)
                                    <span class="badge bg-info">{{ $role->name }}</span>
                                @endforeach
                            </td>
                            <td>
                                <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-sm btn-warning">Éditer</a>
                                <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" style="display:inline-block">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer cet utilisateur ?')">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">Aucun utilisateur trouvé.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $users->withQueryString()->links('pagination::bootstrap-5')}} {{-- pagination --}}
        </div>
    </div>
</div>
@stop
