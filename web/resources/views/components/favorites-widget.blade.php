{{-- Widget Favoris (localStorage) --}}
<div class="position-fixed bottom-0 end-0 m-3" style="z-index: 1050;">
    <button class="btn btn-primary rounded-circle shadow p-3" 
            id="favoritesBtn" 
            title="Modèles favoris"
            style="width: 56px; height: 56px;">
        ⭐
    </button>
</div>

<div class="modal fade" id="favoritesModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">⭐ Mes Favoris</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="favoritesList">
                <p class="text-muted text-center">Aucun favori pour le moment.<br>
                <small>Cliquez sur le bouton ⭐ à côté d'un modèle pour l'ajouter.</small></p>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const favoritesBtn = document.getElementById('favoritesBtn');
    const favoritesModal = new bootstrap.Modal(document.getElementById('favoritesModal'));
    const favoritesList = document.getElementById('favoritesList');
    
    // Charger les favoris
    let favorites = JSON.parse(localStorage.getItem('llm_favorites') || '[]');
    
    // Gestion du bouton
    favoritesBtn?.addEventListener('click', () => {
        updateFavoritesList();
        favoritesModal.show();
    });
    
    // Mettre à jour la liste
    function updateFavoritesList() {
        favorites = JSON.parse(localStorage.getItem('llm_favorites') || '[]');
        
        if (favorites.length === 0) {
            favoritesList.innerHTML = `
                <p class="text-muted text-center">Aucun favori pour le moment.<br>
                <small>Cliquez sur le bouton ⭐ à côté d'un modèle pour l'ajouter.</small></p>
            `;
            return;
        }
        
        favoritesList.innerHTML = '<div class="list-group">';
        favorites.forEach(fav => {
            favoritesList.innerHTML += `
                <div class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-bold">${fav.name}</div>
                        <small class="text-muted">${fav.provider}</small>
                    </div>
                    <div>
                        <a href="/model/${fav.id}" class="btn btn-sm btn-outline-primary me-2">→</a>
                        <button class="btn btn-sm btn-outline-danger remove-fav" data-id="${fav.id}">×</button>
                    </div>
                </div>
            `;
        });
        favoritesList.innerHTML += '</div>';
        
        // Gestion suppression
        document.querySelectorAll('.remove-fav').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const id = e.target.dataset.id;
                favorites = favorites.filter(f => f.id != id);
                localStorage.setItem('llm_favorites', JSON.stringify(favorites));
                updateFavoritesList();
            });
        });
    }
    
    // Ajouter un favoris (si bouton présent sur la page)
    window.addFavorite = function(id, name, provider) {
        if (!favorites.find(f => f.id == id)) {
            favorites.push({ id, name, provider });
            localStorage.setItem('llm_favorites', JSON.stringify(favorites));
        }
    };
    
    window.removeFavorite = function(id) {
        favorites = favorites.filter(f => f.id != id);
        localStorage.setItem('llm_favorites', JSON.stringify(favorites));
    };
});
</script>
