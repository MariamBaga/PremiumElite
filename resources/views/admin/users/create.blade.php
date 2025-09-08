{{-- resources/views/admin/users/create.blade.php --}}
@extends('adminlte::page')

@section('title', 'Créer un Utilisateur')

@section('content_header')
    <h1>Créer un Utilisateur</h1>
@stop

@section('content')
<div class="container-fluid">
    {{-- Affichage des erreurs --}}
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.users.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label for="name">Nom :</label>
                    <input type="text" name="name" id="name" class="form-control"
                           value="{{ old('name') }}" required>
                    @error('name')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="email">Email :</label>
                    <input type="email" name="email" id="email" class="form-control"
                           value="{{ old('email') }}" required>
                    @error('email')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="password">Mot de passe :</label>
                    <input type="password" name="password" id="password" class="form-control" required>
                    @error('password')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="password_confirmation">Confirmer le mot de passe :</label>
                    <input type="password" name="password_confirmation" id="password_confirmation"
                           class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="role">Rôle :</label>
                    <select name="role" id="role" class="form-control" required>
                        @foreach(\Spatie\Permission\Models\Role::all() as $role)
                            <option value="{{ $role->name }}" {{ old('role') == $role->name ? 'selected' : '' }}>
                                {{ ucfirst($role->name) }}
                            </option>
                        @endforeach
                    </select>
                    @error('role')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <button type="submit" class="btn btn-success">Créer</button>
                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Annuler</a>
            </form>
        </div>
    </div>
</div>
@stop
