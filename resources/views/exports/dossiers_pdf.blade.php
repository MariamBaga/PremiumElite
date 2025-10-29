<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dossiers Exportés</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ccc; padding: 5px; text-align: left; }
        th { background-color: #4CAF50; color: white; }
        h2 { text-align: center; color: #333; }
    </style>
</head>
<body>
    <h2>Liste des Dossiers</h2>
    <table>
        <thead>
            <tr>
                <th>Client</th>
                <th>Téléphone</th>
                <th>Statut</th>
                <th>Date RDV</th>
                <th>Port</th>
                <th>Linéaire (m)</th>
                <th>Type câble</th>
                <th>Localité</th>
            </tr>
        </thead>
        <tbody>
            @foreach($dossiers as $dossier)
            <tr>
                <td>{{ $dossier->client->displayName ?? '-' }}</td>
                <td>{{ $dossier->client->telephone ?? '-' }}</td>
                <td>{{ $dossier->statut }}</td>
                <td>{{ $dossier->date_planifiee ?? '-' }}</td>
                <td>{{ $dossier->port ?? '-' }}</td>
                <td>{{ $dossier->lineaire_m ?? '-' }}</td>
                <td>{{ $dossier->type_cable ?? '-' }}</td>
                <td>{{ $dossier->localite ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
