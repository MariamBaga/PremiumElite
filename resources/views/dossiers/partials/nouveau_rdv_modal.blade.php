<div class="modal fade" id="nouveauRdvModal" tabindex="-1" aria-labelledby="nouveauRdvModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <form id="nouveauRdvForm" method="POST" action="{{ route('dossiers.nouveau_rdv') }}">
                    @csrf
                    <input type="hidden" name="dossier_id" id="nouveauRdvDossierId">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Nouveau rendez-vous</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="date_rdv" class="form-label">Date et heure du rendez-vous</label>
                                <input type="datetime-local" name="date_rdv" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="commentaire_rdv" class="form-label">Commentaire</label>
                                <textarea name="commentaire_rdv" class="form-control" rows="3"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">Enregistrer</button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>

                        </div>
                    </div>
                </form>
            </div>
        </div>
