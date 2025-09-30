{{-- Modal Injoignable --}}
<div class="modal fade" id="injoignableModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form id="injoignableForm" method="POST" action="{{ route('dossiers.injoignable') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="dossier_id" id="injoignableDossierId">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Injoignable — Préciser l'action prise</h5>
                    <button type="button" class="btn-close" data-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    {{-- Action prise --}}
                    <div class="mb-3">
                        <label for="action_pris" class="form-label">Action prise</label>
                        <textarea name="action_pris" id="action_pris" class="form-control" rows="3" placeholder="Action prise..." required></textarea>
                    </div>

                    {{-- Capture optionnelle --}}
                    <div class="mb-3">
                        <label for="capture_file" class="form-label">Capture (optionnelle)</label>
                        <input type="file" name="capture_file" id="capture_file" class="form-control" accept="image/jpeg,image/png,image/jpg">
                        <small class="text-muted">Formats acceptés : jpeg, jpg, png — Max 5 Mo</small>
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
