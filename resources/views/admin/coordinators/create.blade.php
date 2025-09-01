{{-- resources/views/admin/coordinators/create.blade.php --}}
@extends('adminlte::page')

@section('title', 'Créer un Coordinateur')

@section('content_header')
    <h1>Créer un Coordinateur</h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.coordinators.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label for="name">Nom :</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                </div>

                <div class="mb-3">
                    <label for="email">Email :</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                </div>

                <div class="mb-3">
                    <label for="password">Mot de passe :</label>
                    <input type="password" name="password" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="password_confirmation">Confirmer le mot de passe :</label>
                    <input type="password" name="password_confirmation" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="role">Rôle :</label>
                    <select name="role" class="form-control" required>
                        @foreach(\Spatie\Permission\Models\Role::all() as $role)
                            <option value="{{ $role->name }}">{{ ucfirst($role->name) }}</option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="btn btn-success">Créer</button>
                <a href="{{ route('admin.coordinators.index') }}" class="btn btn-secondary">Annuler</a>
            </form>
        </div>
    </div>
</div>
@stop
