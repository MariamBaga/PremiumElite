<!-- Modal Implantation Poteau -->
<div class="modal fade" id="implantationPoteauModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('dossiers.implantation.store') }}" id="implantationPoteauForm">
            @csrf
            <input type="hidden" name="dossier_id" id="implantationPoteauDossierId">

            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Implantation Poteau</h5>
                    <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label for="gps_abonne" class="form-label">Coordonnées GPS Abonné</label>
                        <input type="text" name="gps_abonne" id="gps_abonne" class="form-control" required>
                    </div>
                    <div class="mb-3">
    <label for="gps_fat" class="form-label">Coordonnées GPS FAT</label>
    <input type="text" name="gps_fat" id="gps_fat" class="form-control">
</div>

                    <div class="mb-3">
                        <label for="date_rdv" class="form-label">Date du rendez-vous</label>
                        <input type="date" name="date_rdv" id="date_rdv" class="form-control" required>
                    </div>

                    <!-- Champs optionnels (désactivés pour le moment)
                    <div class="mb-3">
                        <label for="gps_pbo" class="form-label">Coordonnées GPS PBO</label>
                        <input type="text" name="gps_pbo" id="gps_pbo" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label for="nom_pbo" class="form-label">Nom du PBO</label>
                        <input type="text" name="nom_pbo" id="nom_pbo" class="form-control">
                    </div>
                    -->
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Valider</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                </div>
            </div>
        </form>
    </div>
</div>
