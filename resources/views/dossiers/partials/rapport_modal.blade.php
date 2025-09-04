<div class="modal fade" id="rapportModal" tabindex="-1" aria-labelledby="rapportModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <form id="rapportForm" method="POST" action="{{ route('dossiers.rapport') }}"
                    enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="dossier_id" id="rapportDossierId">

                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Rapport de satisfaction et intervention</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="rapport_file" class="form-label">Fichier rapport signé (PDF)</label>
                                <input type="file" name="rapport_file" id="rapport_file" class="form-control"
                                    accept=".pdf,.doc,.docx,.txt" required>
                                @error('rapport_file')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="rapport_intervention" class="form-label">Rapport d'intervention</label>
                                <textarea name="rapport_intervention" id="rapport_intervention" class="form-control" rows="4" required></textarea>
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
