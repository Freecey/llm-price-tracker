# LLM Price Tracker - Projet Résumé

## État actuel (05/04/2026)
Projet complet de suivi des prix LLM (OpenRouter) en production locale.

## Stack Technique
*   **Sync:** Python (`scripts/db_sync.py`) via Cron (1x/jour). Logique delta (change-only).
*   **DB:** MariaDB (Docker, port 3307). Tables `models` (specs extraites + JSON brut) et `model_prices_history`.
*   **Web:** Laravel 11 + Bootstrap 5 (compilé via NPM/Vite/SCSS).
*   **Style:** Thème "Kyra" (avatar, couleurs custom, page À propos).

## Fonctionnalités
*   Liste paginée avec tri (nom, provider, contexte), recherche et filtres.
*   Page détail avec graphique d'évolution des prix (Chart.js) et liens externes.
*   Base de données optimisée pour l'historique sans redondance.

## Fichiers Clés
*   `scripts/db_sync.py`: Le moteur de données.
*   `web/`: L'interface Laravel.
*   `docker-compose.yml`: L'infrastructure (MariaDB + phpMyAdmin:8888).

## Accès
*   **Web:** `http://<ip>:8000` (via `php artisan serve`).
*   **DB:** phpMyAdmin sur le port 8888 (root/azerty123..).
*   **Repo:** https://github.com/Freecey/llm-price-tracker
