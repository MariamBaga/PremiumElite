@extends('adminlte::page')

@section('title', 'Coordinateurs')

@section('content_header')
    <h1>Coordinateurs</h1>
@stop

@section('content')
<div class="container-fluid">

    @if(session('success'))
        <div class="alert alert-success alert-dismissible">
            {{ session('success') }}
            <button type="button" class="close" data-bs-dismiss="alert">&times;</button>
        </div>
    @endif

    <div class="mb-3">
        <a href="{{ route('admin.coordinators.create') }}" class="btn btn-primary">Créer un coordinateur</a>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Liste des coordinateurs</h3>
        </div>
        <div class="card-body table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Rôle</th>
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
                </tbody>
            </table>
        </div>
    </div>
</div>
@stop
