{{-- Command+K Spotlight Search --}}
<div class="modal fade" id="spotlightModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 12px;">
            <div class="modal-body p-0">
                <input type="text" 
                       id="spotlightInput" 
                       class="form-control form-control-lg border-0 rounded-top"
                       placeholder="Rechercher un modèle (Ctrl+K)..."
                       autocomplete="off"
                       style="border-radius: 12px 12px 0 0;">
                <div id="spotlightResults" class="list-group list-group-flush" style="max-height: 300px; overflow-y: auto;">
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('spotlightModal');
    const input = document.getElementById('spotlightInput');
    const results = document.getElementById('spotlightResults');
    const bootstrapModal = new bootstrap.Modal(modal);
    
    // Raccourci Ctrl+K ou Cmd+K
    document.addEventListener('keydown', (e) => {
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            bootstrapModal.show();
            setTimeout(() => input.focus(), 300);
        }
    });
    
    // Recherche en temps réel
    let debounceTimer;
    input.addEventListener('input', (e) => {
        clearTimeout(debounceTimer);
        const query = e.target.value.trim();
        
        if (query.length < 2) {
            results.innerHTML = '';
            return;
        }
        
        debounceTimer = setTimeout(() => {
            fetch(`/api/search?q=${encodeURIComponent(query)}`)
                .then(res => res.json())
                .then(data => {
                    results.innerHTML = '';
                    
                    if (data.length === 0) {
                        results.innerHTML = '<div class="list-group-item text-muted text-center py-3">Aucun résultat</div>';
                        return;
                    }
                    
                    data.forEach(model => {
                        const item = document.createElement('a');
                        item.href = `/model/${model.id}`;
                        item.className = 'list-group-item list-group-item-action';
                        item.innerHTML = `
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-bold">${model.name}</div>
                                    <small class="text-muted">${model.openrouter_id}</small>
                                </div>
                                <span class="badge bg-secondary">${model.provider_name}</span>
                            </div>
                        `;
                        results.appendChild(item);
                    });
                })
                .catch(err => console.error('Erreur recherche:', err));
        }, 300);
    });
    
    // Reset au fermeture
    modal.addEventListener('hidden.bs.modal', () => {
        input.value = '';
        results.innerHTML = '';
    });
});
</script>
