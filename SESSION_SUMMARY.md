# 🚀 LLM Price Tracker - Résumé de Session (05/04/2026)

Ce fichier sert de point de reprise pour les futures sessions de développement.

## ✅ Session Actuelle : "L'Ère Kyra"

### 🛠️ Nouvelles Pages & Features
- **Comparaison (`/compare`)** : Sélection multi-modèles, graphiques superposés (input/output), historique.
- **Dashboard (`/dashboard`)** : Top 10, Répartition modalités, Heatmap des changements.
- **Onglet Providers (`/providers`)** : Vue "Cartes d'identité" + Lien vers l'analyse graphique (`/providers-analysis`).
- **Page Tools (`/tools`)** : Liste dédiée avec toggle "Avec/Sans Tools" et stats par provider.
- **Tendances (`/trends`)** : Timeline, Top 20 des plus gros changements %, liste des 30 derniers jours.
- **Alertes (`/alerts`)** : UI pour les baisses de prix + Script Python pour notifications Discord.
- **Glossaire (`/glossary`)** : Explications des termes techniques (Modality, Tokens, etc.) avec le "Coin de Kyra".

### 🧠 Intelligence & Données
- **Kyra's Picks** : Algo de scoring (Prix 40%, Contexte 30%, Tools 20%, Provider 10%).
- **Champs Avancés** : Stockage de `description`, `knowledge_cutoff`, `tokenizer`, `is_moderated`, `expiration_date`.
- **Modèles Similaires** : Suggestion automatique sur les fiches détails.
- **Filtrage Robuste** : Exclusion des prix négatifs (ex: `openrouter/auto`).

### 🎨 UX & Fun
- **Pagination Flexible** : 10, 20, 50, 100, 200, 500 éléments par page.
- **Tri Avancé** : Tri possible sur les colonnes de prix (Input/Output).
- **Easter Eggs** : Konami Code (Hacker Mode), Slot Machine (🎰), Spotlight (Ctrl+K).
- **Favoris** : Système de favoris en localStorage avec widget flottant.
- **Export** : Boutons fonctionnels pour CSV et JSON.

### 🏗️ Technique
- **Base de Données** : Ajout de colonnes pour les specs avancées et les dates.
- **SEO** : Balises meta et titres optimisés pour le référencement.
- **Sync Python** : Mise à jour pour gérer les nouveaux champs et les conversions de dates.

## 📂 Fichiers Clés à Surveiller
- `web/app/Http/Controllers/ModelController.php` : Le cerveau de l'application.
- `scripts/db_sync.py` : Le moteur de données (OpenRouter → MariaDB).
- `web/resources/views/layouts/app.blade.php` : La structure globale et la navigation.
- `web/routes/web.php` : La cartographie des URLs.

## 🎯 Prochaines Étapes Possibles
1. **Authentification User** : Pour sauvegarder les favoris et les configs d'alertes en BDD.
2. **Cron Job** : Automatiser le `db_sync.py` et le `price_alerts.py` sur le serveur.
3. **PWA** : Rendre l'app installable sur mobile.
4. **API Publique** : Exposer les données pour d'autres développeurs.

---
*Maintenu par Kyra ⌬*
