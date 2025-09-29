{{-- Titre du rapport --}}
<h2 style="text-align: center; font-family: Arial, sans-serif; color: #333;">
    Rapport d’activité
</h2>

{{-- Statuts sélectionnés (optionnel) --}}
@if(!empty($selectedStatuses))
    <p style="text-align: center; font-family: Arial, sans-serif; color: #555;">
        Statuts sélectionnés : {{ implode(', ', $selectedStatuses) }}
    </p>
@endif









<table style="border-collapse: collapse; width: 100%; font-family: Arial, sans-serif;">
    <thead>
        <tr style="background-color: #4CAF50; color: white; text-align: left;">
            <th style="border: 1px solid #ddd; padding: 8px;">Client</th>
            <th style="border: 1px solid #ddd; padding: 8px;">Contact</th>
            <th style="border: 1px solid #ddd; padding: 8px;">Téléphone</th>
            <th style="border: 1px solid #ddd; padding: 8px;">Statut</th>
            <th style="border: 1px solid #ddd; padding: 8px;">Date RDV</th>
            <th style="border: 1px solid #ddd; padding: 8px;">Port utilisé</th>
            <th style="border: 1px solid #ddd; padding: 8px;">Linéaire câble tiré (m)</th>
            <th style="border: 1px solid #ddd; padding: 8px;">Type de câble</th>
            <th style="border: 1px solid #ddd; padding: 8px;">Ligne</th>
            <th style="border: 1px solid #ddd; padding: 8px;">Localité</th>
            <th style="border: 1px solid #ddd; padding: 8px;">Catégorie</th>
            <th style="border: 1px solid #ddd; padding: 8px;">Rapport intervention</th>
            <th style="border: 1px solid #ddd; padding: 8px;">Date création</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($dossiers as $dossier)
            <tr style="border: 1px solid #ddd; padding: 8px; background-color: {{ $loop->even ? '#f2f2f2' : 'white' }};">
                <td style="border: 1px solid #ddd; padding: 8px;">{{ $dossier->client->displayName ?? '-' }}</td>
                <td style="border: 1px solid #ddd; padding: 8px;">{{ $dossier->contact ?? '-' }}</td>
                <td style="border: 1px solid #ddd; padding: 8px;">{{ $dossier->client->telephone ?? '-' }}</td>
                <td style="border: 1px solid #ddd; padding: 8px;">{{ \Illuminate\Support\Str::headline($dossier->statut?->value ?? $dossier->statut) }}</td>
                <td style="border: 1px solid #ddd; padding: 8px;">{{ optional($dossier->date_planifiee)->format('d/m/Y H:i') }}</td>
                <td style="border: 1px solid #ddd; padding: 8px;">{{ $dossier->port ?? '-' }}</td>
                <td style="border: 1px solid #ddd; padding: 8px;">{{ $dossier->lineaire_m ?? '-' }}</td>
                <td style="border: 1px solid #ddd; padding: 8px;">{{ $dossier->type_cable ?? '-' }}</td>
                <td style="border: 1px solid #ddd; padding: 8px;">{{ $dossier->ligne ?? '-' }}</td>
                <td style="border: 1px solid #ddd; padding: 8px;">{{ $dossier->localite ?? '-' }}</td>
                <td style="border: 1px solid #ddd; padding: 8px;">{{ $dossier->categorie ?? '-' }}</td>
                <td style="border: 1px solid #ddd; padding: 8px;">{{ $dossier->rapport_intervention ?? '-' }}</td>
                <td style="border: 1px solid #ddd; padding: 8px;">{{ $dossier->created_at->format('d/m/Y H:i') }}</td>
            </tr>
        @endforeach
    </tbody>
</table>



