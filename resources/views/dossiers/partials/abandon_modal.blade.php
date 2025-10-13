<!-- Modal Abandon -->
<div class="modal fade" id="abandonModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('dossiers.storeAbandon') }}">
            @csrf
            <input type="hidden" name="dossier_id" id="abandonDossierId">

            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Abandon du Dossier</h5>
                    <button type="button" class="btn-close" data-dismiss="modal" aria-label="Fermer"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label for="raison_abandon" class="form-label">Raison de l'abandon</label>
                        <textarea name="raison_abandon" id="raison_abandon" class="form-control" rows="4" required></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-danger">Confirmer l'abandon</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                </div>
            </div>
        </form>
    </div>
</div>
