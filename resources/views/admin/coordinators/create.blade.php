{{-- resources/views/admin/coordinators/create.blade.php --}}
@extends('adminlte::page')

@section('title', 'Créer Coordinateur')

@section('content_header')
    <h1>Créer un coordinateur</h1>
@stop

@section('content')
<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible">
            {{ session('success') }}
            <button type="button" class="close" data-bs-dismiss="alert">&times;</button>
        </div>
    @endif

    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Informations du coordinateur</h3>
        </div>
        <form action="{{ route('admin.coordinators.store') }}" method="POST">
            @csrf
            <div class="card-body">

                <div class="form-group">
                    <label for="name">Nom</label>
                    <input type="text" name="name" class="form-control" placeholder="Nom du coordinateur" value="{{ old('name') }}" required>
                    @error('name') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" name="email" class="form-control" placeholder="Email" value="{{ old('email') }}" required>
                    @error('email') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" name="password" class="form-control" placeholder="Mot de passe" required>
                    @error('password') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label for="password_confirmation">Confirmer le mot de passe</label>
                    <input type="password" name="password_confirmation" class="form-control" placeholder="Confirmer le mot de passe" required>
                </div>

                <div class="form-group">
                    <label for="role">Rôle attribué :</label>
                    <select name="role" id="role" class="form-control" required>
                        @foreach(\Spatie\Permission\Models\Role::all() as $r)
                            <option value="{{ $r->name }}" {{ old('role') == $r->name ? 'selected' : '' }}>
                                {{ ucfirst($r->name) }}
                            </option>
                        @endforeach
                    </select>
                    @error('role') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Créer</button>
            </div>
        </form>
    </div>
</div>
@stop

@section('css')
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/adminlte.min.css') }}">
@stop

@section('js')
<script>
    // Scripts spécifiques si nécessaire
</script>
@stop
