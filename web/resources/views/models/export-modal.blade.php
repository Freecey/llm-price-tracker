{{-- Modal d'export --}}
<div class="modal fade" id="exportModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">📥 Exporter les données</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted">Choisissez le format d'export :</p>
                <div class="d-grid gap-2">
                    <a href="#" id="exportCSV" class="btn btn-outline-primary">
                        📄 Export CSV (tous les modèles)
                    </a>
                    <a href="#" id="exportJSON" class="btn btn-outline-secondary">
                        💾 Export JSON (tous les modèles)
                    </a>
                </div>
                <hr>
                <small class="text-muted">
                    L'export inclut : nom, provider, contexte, prix input/output, date de dernière mise à jour.
                </small>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const exportCSV = document.getElementById('exportCSV');
    const exportJSON = document.getElementById('exportJSON');
    
    if (exportCSV) {
        exportCSV.href = '/export?format=csv';
    }
    
    if (exportJSON) {
        exportJSON.href = '/export?format=json';
    }
});
</script>
