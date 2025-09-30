@if($rdvDossiers->isNotEmpty())
<div class="modal fade" id="rdvModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Rendez-vous en attente</h5>
                <button type="button" class="btn-close" data-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <ul class="list-group">
                    @foreach($rdvDossiers as $dossier)
                        <li class="list-group-item">
                            <strong>{{ $dossier->reference }}</strong> — Client : {{ $dossier->client->nom ?? 'N/A' }}{{ $dossier->client->prenom ?? 'N/A' }} <br>
                            Date prévue : {{ $dossier->date_planifiee ? $dossier->date_planifiee->format('d/m/Y H:i') : 'Non défini' }}
                        </li>
                    @endforeach
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

<script>
   
</script>
@endif
