<div class="modal fade" id="indisponibleModal" tabindex="-1" aria-labelledby="indisponibleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('dossiers.indisponible.store') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="dossier_id" id="indisponibleDossierId">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Marquer comme indisponible</h5>
                    <button type="button" class="btn-close" data-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Raison</label>
                        <textarea name="raison" class="form-control" rows="3" required placeholder="Décrivez la raison..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Capture (JPEG/PNG)</label>
                        <input type="file" name="capture_file" class="form-control" accept="image/*" required>
                        <small class="text-muted">Max 5 Mo</small>
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
