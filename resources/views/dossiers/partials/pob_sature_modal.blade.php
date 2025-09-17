
{{-- Modal PBO saturé --}}
<div class="modal fade" id="pboSatureModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form id="pboSatureForm" method="POST" action="{{ route('dossiers.pbo_sature') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="dossier_id" id="pboSatureDossierId">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">PBO saturé — Ajouter le rapport</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="file" name="rapport_file" class="form-control" accept=".pdf,.doc,.docx,.txt" required>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Valider</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                </div>
            </div>
        </form>
    </div>
</div>
