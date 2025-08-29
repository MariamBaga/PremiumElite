@extends('adminlte::page')

{{-- Demande à laravel-adminlte de charger le plugin TempusDominus --}}
@section('plugins.TempusDominusBs4', true)

@section('title', 'Nouveau dossier')
@section('content_header')<h1>Nouveau dossier</h1>@stop
@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('dossiers.store') }}">
                @csrf
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Client</label>
                        <select name="client_id" class="form-control" required>
                            @foreach ($clients as $c)
                                <option value="{{ $c->id }}">{{ $c->displayName }} — {{ $c->telephone }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label>Type de service</label>
                        <select name="type_service" class="form-control" required>
                            <option value="residentiel">Résidentiel</option>
                            <option value="professionnel">Professionnel</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
    <label>Date planifiée</label>
    <div class="input-group date" id="dtp_planifiee" data-target-input="nearest">
        <input
            type="text"
            name="date_planifiee"
            class="form-control datetimepicker-input @error('date_planifiee') is-invalid @enderror"
            data-target="#dtp_planifiee"
            value="{{ old('date_planifiee', isset($dossier) ? optional($dossier->date_planifiee)->format('Y-m-d H:i') : '') }}"
            placeholder="YYYY-MM-DD HH:mm"
        />
        <div class="input-group-append" data-target="#dtp_planifiee" data-toggle="datetimepicker">
            <div class="input-group-text"><i class="far fa-clock"></i></div>
        </div>
    </div>
    @error('date_planifiee')
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
</div>


                    <div class="col-md-6 mb-3">
                        <label>PBO</label>
                        <input name="pbo" class="form-control">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>PM</label>
                        <input name="pm" class="form-control">
                    </div>
                    <div class="col-12 mb-3">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <button class="btn btn-primary">Enregistrer</button>
                <a href="{{ route('dossiers.index') }}" class="btn btn-secondary">Annuler</a>
            </form>
        </div>
    </div>




    @push('js')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                $('#dtp_planifiee').datetimepicker({
                    format: 'YYYY-MM-DD HH:mm'
                });
            });
        </script>
    @endpush


@stop
