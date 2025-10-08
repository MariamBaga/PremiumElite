{{-- Modal Réalisé --}}
<div class="modal fade" id="realiseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form id="realiseForm" method="POST" action="{{ route('dossiers.realise') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="dossier_id" id="realiseDossierId">

            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Dossier Réalisé</h5>
                    <button type="button" class="btn-close" data-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    {{-- Rapport intervention obligatoire --}}
                    <div class="mb-3">
                        <label for="rapport_intervention">Rapport d’intervention</label>
                        <textarea name="rapport_intervention" class="form-control" rows="4" required></textarea>
                    </div>

                    {{-- Raison de non activation obligatoire --}}
                    <div class="mb-3">
                        <label for="raison_non_activation">Raison de non activation</label>
                        <input type="text" name="raison_non_activation" class="form-control" required>
                    </div>

                    {{-- Upload du rapport (fichier obligatoire) --}}
<div class="mb-3">
    <label for="rapport_file">Rapport signé (PDF, DOC, DOCX, TXT, IMAGE)</label>
    <input
        type="file"
        name="rapport_file"
        class="form-control"
        accept=".pdf,.PDF,.doc,.DOC,.docx,.DOCX,.txt,.TXT,.jpg,.JPG,.jpeg,.JPEG,.png,.PNG,.gif,.GIF"
        required
    >
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
