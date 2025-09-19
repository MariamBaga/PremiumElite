@extends('adminlte::page')

@section('title', 'Toutes les notifications')

@section('content_header')
    <h1>Toutes les notifications</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Liste complète des notifications</span>
        @if(auth()->user()->unreadNotifications->count())
            <form action="{{ route('notifications.readAll') }}" method="POST" class="m-0">
                @csrf
                <button type="submit" class="btn btn-sm btn-primary">
                    Marquer tout comme lu
                </button>
            </form>
        @endif
    </div>

    <div class="card-body table-responsive">
        <table class="table table-hover table-striped align-middle">
            <thead>
                <tr>
                    <th>Message</th>
                    <th>Dossier</th>
                    <th>Date RDV</th>
<th>Création</th>

                    <th>État</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
@forelse ($notifications as $notif)
    <tr @if($notif->read_at === null) class="table-warning" @endif>
        <td>{{ $notif->data['message'] ?? 'Notification' }}</td>
        <td>
            @if(isset($notif->data['dossier_id']))
                <a href="{{ route('clients.show', $notif->data['dossier_id']) }}"
                   class="btn btn-sm btn-outline-info">
                    Voir le dossier
                </a>
            @else
                -
            @endif
        </td>
        <td>
            {{-- Nouvelle colonne pour la date de RDV --}}
            @if(isset($notif->data['date_rdv']))
                {{ \Carbon\Carbon::parse($notif->data['date_rdv'])->format('d/m/Y H:i') }}
            @else
                -
            @endif
        </td>
        <td>{{ $notif->created_at->format('d/m/Y H:i') }}</td>
        <td>
            @if($notif->read_at)
                <span class="badge badge-success">Lue</span>
            @else
                <span class="badge badge-warning">Non lue</span>
            @endif
        </td>
        <td>
            @if(!$notif->read_at)
                <form action="{{ route('notifications.read', $notif->id) }}" method="POST" class="d-inline">
                    @csrf
                    <button class="btn btn-sm btn-outline-primary">
                        Marquer comme lue
                    </button>
                </form>
            @endif
        </td>
    </tr>
@empty
    <tr>
        <td colspan="6" class="text-center text-muted">Aucune notification pour l’instant.</td>
    </tr>
@endforelse
</tbody>

        </table>
    </div>

    @if ($notifications->hasPages())
        <div class="card-footer">
            {{ $notifications->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>
@stop
