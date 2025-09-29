{{-- Modal Injoignable --}}
<div class="modal fade" id="injoignableModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form id="injoignableForm" method="POST" action="{{ route('dossiers.injoignable') }}">
            @csrf
            <input type="hidden" name="dossier_id" id="injoignableDossierId">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Injoignable — Préciser l'action prise</h5>
                    <button type="button" class="btn-close" data-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <textarea name="action_pris" class="form-control" rows="4" placeholder="Action prise..." required></textarea>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Valider</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                </div>
            </div>
        </form>
    </div>
</div>
