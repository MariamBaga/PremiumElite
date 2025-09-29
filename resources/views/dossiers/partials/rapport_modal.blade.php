<div class="modal fade" id="rapportModal" tabindex="-1" aria-labelledby="rapportModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="rapportForm" method="POST" action="{{ route('dossiers.rapport') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="dossier_id" id="rapportDossierId">

            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Rapport de satisfaction et intervention</h5>
                    <button type="button" class="btn-close" data-dismiss="modal"></button>
                </div>
                <div class="modal-body">

                    <!-- Fichier rapport -->
                    <div class="mb-3">
                        <label for="rapport_file" class="form-label">Fichier rapport signé (PDF/DOC)</label>
                        <input type="file" name="rapport_file" id="rapport_file" class="form-control" accept=".pdf,.doc,.docx,.txt" required>
                        @error('rapport_file')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Rapport d'intervention -->
                    <div class="mb-3">
                        <label for="rapport_intervention" class="form-label">Rapport d'intervention</label>
                        <textarea name="rapport_intervention" id="rapport_intervention" class="form-control" rows="4" required></textarea>
                        @error('rapport_intervention')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Numéro de port utilisé -->
                    <div class="mb-3">
                        <label for="port" class="form-label">Numéro de port utilisé</label>
                        <input type="text" name="port" id="port" class="form-control" required>
                        @error('port')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Linéaire de câble tiré -->
                  <!-- Linéaire de câble tiré -->
<div class="mb-3">
    <label for="lineaire_m" class="form-label">Linéaire de câble tiré (mètres)</label>
    <input type="number" name="lineaire_m" id="lineaire_m" class="form-control" min="0" required>
    @error('lineaire_m')
        <div class="text-danger small mt-1">{{ $message }}</div>
    @enderror
</div>


                    <!-- Type de câble utilisé -->
                    <div class="mb-3">
                        <label for="type_cable" class="form-label">Type de câble utilisé</label>
                        <input type="text" name="type_cable" id="type_cable" class="form-control" required>
                        @error('type_cable')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
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
