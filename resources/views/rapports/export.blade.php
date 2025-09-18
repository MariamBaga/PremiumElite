<table>
    <thead>
        <tr>
            <th>Client</th>
            <th>Téléphone</th>
            <th>Statut</th>
            <th>Date RDV</th>
            <th>Rapport</th>
            <th>Date création</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($dossiers as $dossier)
            <tr>
                <td>{{ $dossier->client->displayName ?? '-' }}</td>
                <td>{{ $dossier->client->telephone ?? '-' }}</td>
                <td>{{ \Illuminate\Support\Str::headline($dossier->statut?->value ?? $dossier->statut) }}</td>
                <td>{{ optional($dossier->date_planifiee)->format('d/m/Y') }}</td>
                <td>{{ $dossier->rapport_intervention ?? '-' }}</td>
                <td>{{ $dossier->created_at->format('d/m/Y H:i') }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
