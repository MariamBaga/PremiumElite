<div class="modal fade" id="depassementLineaireModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('dossiers.depassement.store') }}">
            @csrf
            <input type="hidden" name="dossier_id" id="depassementLineaireDossierId">

            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Dépassement Linéaire</h5>
                    <button type="button" class="btn-close" data-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Distance (m)</label>
                        <input type="number" name="distance" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Coordonnées GPS Abonné</label>
                        <input type="text" name="gps_abonne" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Coordonnées GPS PBO</label>
                        <input type="text" name="gps_pbo" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Nom du PBO</label>
                        <input type="text" name="nom_pbo" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Valider</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                </div>
            </div>
        </form>
    </div>
</div>
