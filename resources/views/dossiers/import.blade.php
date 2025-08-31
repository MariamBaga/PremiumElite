@extends('adminlte::page')

@section('title','Importer des dossiers')

@section('content_header')
    <h1>Importer des dossiers</h1>
@stop

@section('content')
<div class="container">
    <h3>Importer des dossiers FTTH</h3>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form action="{{ route('dossiers.import') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="mb-3">
            <label for="file" class="form-label">Fichier Excel</label>
            <input type="file" name="file" id="file" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-success">Importer</button>
    </form>
</div>
@endsection
@stop
