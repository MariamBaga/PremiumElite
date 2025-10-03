<table style="border-collapse: collapse; width: 100%; font-family: Arial, sans-serif;">
<thead>
    <tr style="background-color: #4CAF50; color: white; text-align: left;">
        <th>Client</th>
        <th>Contact</th>
        <th>Téléphone</th>
        <th>Statut</th>
        <th>Date RDV</th>
        <th>Port utilisé</th>
        <th>Linéaire câble tiré (m)</th>
        <th>Type de câble</th>
        <th>Ligne</th>
        <th>Localité</th>
        <th>Catégorie</th>
        <th>Rapport intervention</th>
        <th>Raison non activation</th>
        <th>Capture</th>
        <th>Dépassement distance</th>
        <th>GPS Abonné</th>
        <th>GPS PBO</th>
        <th>Nom PBO</th>
        <th>Date création</th>
    </tr>
</thead>
<tbody>
    @foreach ($dossiers as $dossier)
        <tr>
            <td>{{ $dossier->client->displayName ?? '-' }}</td>
            <td>{{ $dossier->contact ?? '-' }}</td>
            <td>{{ $dossier->client->telephone ?? '-' }}</td>
            <td>{{ \Illuminate\Support\Str::headline($dossier->statut?->value ?? $dossier->statut) }}</td>
            <td>{{ optional($dossier->date_planifiee)->format('d/m/Y H:i') }}</td>
            <td>{{ $dossier->port ?? '-' }}</td>
            <td>{{ $dossier->lineaire_m ?? '-' }}</td>
            <td>{{ $dossier->type_cable ?? '-' }}</td>
            <td>{{ $dossier->ligne ?? '-' }}</td>
            <td>{{ $dossier->localite ?? '-' }}</td>
            <td>{{ $dossier->categorie ?? '-' }}</td>
            <td>{{ $dossier->rapport_intervention ?? '-' }}</td>
            <td>{{ $dossier->raison_non_activation ?? '-' }}</td>
            <td>
                @if($dossier->capture_message)
                    <a href="{{ asset('storage/'.$dossier->capture_message) }}" target="_blank">Voir</a>
                @else
                    -
                @endif
            </td>
            <td>{{ $dossier->depassement_distance ?? '-' }}</td>
            <td>{{ $dossier->depassement_gps_abonne ?? '-' }}</td>
            <td>{{ $dossier->depassement_gps_pbo ?? '-' }}</td>
            <td>{{ $dossier->depassement_nom_pbo ?? '-' }}</td>
            <td>{{ $dossier->created_at->format('d/m/Y H:i') }}</td>
        </tr>
    @endforeach
</tbody>

</table>
