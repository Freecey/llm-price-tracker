# LLM Price Tracker - Cahier des Charges & Documentation Laravel

## 1. Vue d'ensemble
Développement d'une interface web légère (Laravel + Bootstrap) permettant de visualiser l'historique des prix et des spécifications des modèles LLM disponibles sur OpenRouter.

## 2. Fonctionnalités Principales
- **Liste des modèles :** Tableau avec pagination, tri (prix, contexte, provider) et recherche.
- **Détails d'un modèle :**
    - Graphique d'évolution des prix (Input/Output) sur la durée (Chart.js).
    - Affichage des spécifications techniques (Modality, Context, Tokens max).
    - Historique des changements (Ajout, suppression, mise à jour).
- **Statistiques globales :**
    - Nombre de modèles suivis (actifs/supprimés).
    - Modèles les plus récents.
    - Évolution moyenne des prix par provider.

## 3. Stack Technique
- **Framework :** Laravel 11 (version légère, pas d'API lourde, juste du Blade).
- **Frontend :** Bootstrap 5 + Chart.js.
- **Base de données :** MariaDB (tables `models` et `model_prices_history`).
- **Serveur :** Nginx/Apache (via Docker ou ISPConfig existant).

## 4. Structure de la Base de Données (Rappel)
- `models` : Registry des modèles avec specs extraites et JSON brut.
- `model_prices_history` : Historique des changements (timestamps, prix, contexte).

## 5. Pages à Développer
1.  **Home (`/`) :** Vue d'ensemble avec les stats et la liste des modèles.
2.  **Model Detail (`/model/{id}`) :** Page dédiée avec le graphique et les détails techniques.
3.  **Providers (`/providers`) :** Liste des providers (OpenAI, Anthropic, etc.) avec résumé de leur offre.

## 6. Étapes de Développement
1.  Initialisation du projet Laravel dans `projects/llm-price-tracker/web`.
2.  Configuration de la connexion à la base de données (via le port 3307).
3.  Création des Modèles Eloquent (`Model.php`, `PriceHistory.php`).
4.  Développement du Controller principal et des Routes.
5.  Création des vues Blade avec intégration Bootstrap.
6.  Intégration de Chart.js pour les courbes de prix.

## 7. Déploiement
- Le projet Laravel sera servi via un vhost Nginx ou un conteneur Docker dédié.
- Les mises à jour de la base sont gérées par le script Python cron (indépendant de Laravel).
