{{-- Modal Zone dépourvue --}}
<div class="modal fade" id="zoneDepourvueModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form id="zoneDepourvueForm" method="POST" action="{{ route('dossiers.zone_depourvue') }}">
            @csrf
            <input type="hidden" name="dossier_id" id="zoneDepourvueDossierId">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Zone dépourvue — Rapport de constat</h5>
                    <button type="button" class="btn-close" data-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <label for="rapport_intervention" class="form-label">
                        Saisir le rapport de constat
                    </label>
                    <textarea name="rapport_intervention"
                              id="rapport_intervention"
                              class="form-control"
                              rows="5"
                              placeholder="Décrivez le constat (détails, mesures, etc.)"
                              required></textarea>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Valider</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                </div>
            </div>
        </form>
    </div>
</div>
