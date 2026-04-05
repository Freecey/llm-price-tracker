# ⌬ Kyra's LLM Price Tracker

> _Pas de rembourrage. Juste les données._

Un tracker de prix LLM (OpenRouter) qui surveille l'évolution des coûts, des specs et des capacités outils — avec style, analytics poussés et des easter eggs cachés.

---

## 🚀 Features

### 📊 Tracking & Données
- **Sync quotidienne** via Python (`scripts/db_sync.py`) avec logique delta (change-only)
- **350+ modèles** suivis depuis OpenRouter API
- **Historique complet** des prix input/output et specs
- **Détection automatique** des nouveaux modèles, changements de prix, suppressions
- **Filtrage des prix invalides** (ex: `openrouter/auto`, `openrouter/bodybuilder`)

### 🛠️ Capacités Tools
- **Détection function calling** via `supported_parameters` de l'API
- **247 modèles** supportant les tools (70% du catalogue)
- **Filtre dédié** "Avec/Sans Tools" dans toutes les vues
- **Page Tools dédiée** (`/tools`) avec stats par provider

### 🎨 Interface Web (Laravel 11 + Bootstrap 5)
- **Liste paginée** avec tri multi-colonnes (nom, provider, contexte, prix input/output)
- **Recherche globale** (nom, ID, provider) + filtres (provider, modalité, tools)
- **Fiches détaillées** avec graphique d'évolution des prix (Chart.js)
- **Modèles similaires** suggérés automatiquement (même provider ou contexte proche)

### 📈 Analytics & Comparaisons
- **Comparaison côte-à-côte** (`/compare`) — sélection multi-modèles avec graphiques superposés
- **Dashboard analytique** (`/dashboard`) — top 10 moins chers, widgets "Top Gratuits" et "Nouveautés"
- **Analyse par Provider** (`/providers`) — stats détaillées, évolution 30 jours, top modèles
- **Modèles Gratuits** (`/free`) — section dédiée aux modèles 100% gratuits ou avec Free Tier
- **Tendances** (`/trends`) — timeline des changements, top 20 plus grosses variations %, heatmap 14 jours

### 🚨 Alertes
- **Page Alertes** (`/alerts`) — baisses de prix significatives configurables (seuil + période)
- **Script Python** (`scripts/price_alerts.py`) — notifications Discord automatisées
- **Webhook Discord** configurable via `.env`

### ⌬ Kyra's Touch
- **Kyra Score** — Indice de fiabilité intelligent basé sur la quantité d'historique et la stabilité des prix
- **Kyra's Picks** — Top 5 modèles recommandés par l'algo (mis à jour avec le nouveau score)
- **Modèle du Jour** — spotlight quotidien aléatoire (seedé par date)
- **Badges de Fiabilité** — Visualisation du score directement dans les listes et fiches modèles
- **Favoris** (localStorage) — bouton flottant ⭐ pour sauvegarder tes modèles
- **Export CSV/JSON** — téléchargement de toutes les données

### 🧠 Intelligence & Données Avancées
- **Glossaire Interactif** (`/glossary`) — explications claires des termes techniques (Modality, Tokens, Quantization...)
- **Tooltips contextuels** — survol des termes techniques pour une définition instantanée
- **Champs API avancés** — Description officielle, Knowledge Cutoff, Tokenizer, et Date d'expiration
- **Indicateur de Modération** — sais si un modèle a des filtres de sécurité (⚠️) ou s'il est libre (🔓)

### 🎮 Easter Eggs & Fun
- **Konami Code** (↑↑↓↓←→←→BA) — Hacker Mode vert fluo 🟢
- **Slot Machine** (🎰 navbar) — modèle aléatoire avec animation roulette
- **Recherche Spotlight** (Ctrl+K) — recherche rapide temps réel
- **Stats funs** (page À propos) — modèle le moins cher, plus gros contexte, etc.

---

## 🏗️ Stack Technique

| Composant | Technologie |
|-----------|-------------|
| **Sync Engine** | Python 3 + `requests` + `mysql-connector` |
| **Backend Web** | Laravel 11 (PHP 8.3) |
| **Base de données** | MariaDB (Docker, port 3307) |
| **Frontend** | Bootstrap 5 + SCSS + Chart.js |
| **Containerisation** | Docker Compose (MariaDB + phpMyAdmin:8888) |
| **Thème** | "Kyra" — couleurs custom, avatar, style sharp |

---

## 📦 Installation

### Prérequis
- Docker & Docker Compose
- PHP 8.3+ avec extensions MySQL
- Node.js & NPM (pour compilation SCSS)
- Python 3 avec `pip`

### 1. Cloner le repo
```bash
git clone https://github.com/Freecey/llm-price-tracker.git
cd llm-price-tracker
```

### 2. Configurer les variables d'environnement
```bash
cp .env.example .env
```

Éditer `.env` avec tes credentials DB et optionnellement `DISCORD_WEBHOOK_URL` pour les alertes.

### 3. Lancer l'infra Docker
```bash
docker-compose up -d
```

### 4. Installer les dépendances web
```bash
cd web
composer install
npm install
npm run build
```

### 5. Migrer la base de données
```bash
php artisan migrate
```

### 6. Lancer le sync initial
```bash
cd ..
python3 scripts/db_sync.py
```

### 7. Démarrer le serveur Laravel
```bash
cd web
php artisan serve --host=0.0.0.0 --port=8000
```

---

## 🌐 Accès

| Service | URL |
|---------|-----|
| **Application Web** | `http://localhost:8000` |
| **phpMyAdmin** | `http://localhost:8888` (root / azerty123..) |
| **MariaDB** | `localhost:3307` |

---

## 📁 Structure du Projet

```
llm-price-tracker/
├── scripts/
│   ├── db_sync.py           # Sync quotidienne OpenRouter → MariaDB
│   └── price_alerts.py      # Détection baisses de prix + notifications Discord
├── web/
│   ├── app/
│   │   ├── Http/Controllers/
│   │   │   └── ModelController.php  # Tous les endpoints
│   │   └── Models/
│   │       ├── Model.php             # Modèle Eloquent principal
│   │       └── PriceHistory.php      # Historique des prix
│   ├── resources/
│   │   ├── views/
│   │   │   ├── models/
│   │   │   │   ├── index.blade.php       # Liste principale
│   │   │   │   ├── show.blade.php        # Fiche détaillée
│   │   │   │   ├── compare.blade.php     # Comparaison
│   │   │   │   ├── dashboard.blade.php   # Dashboard analytics
│   │   │   │   ├── providers.blade.php   # Analyse providers
│   │   │   │   ├── trends.blade.php      # Tendances
│   │   │   │   ├── tools.blade.php       # Page Tools dédiée
│   │   │   │   ├── alerts.blade.php      # Alertes de prix
│   │   │   │   └── export-modal.blade.php
│   │   │   ├── components/
│   │   │   │   ├── search-spotlight.blade.php
│   │   │   │   ├── favorites-widget.blade.php
│   │   │   │   └── easter-eggs.blade.php
│   │   │   ├── layouts/
│   │   │   │   └── app.blade.php
│   │   │   └── about.blade.php
│   │   ├── scss/
│   │   │   └── app.scss              # Thème Kyra custom
│   │   └── js/
│   │       └── app.js
│   ├── routes/
│   │   └── web.php                   # Toutes les routes
│   └── package.json
├── docker-compose.yml                # MariaDB + phpMyAdmin
└── README.md
```

---

## 🔄 Workflow de Sync

```
OpenRouter API → Python script → MariaDB (delta only)
     │                                    │
     │                              Laravel Web UI
     │                                    │
     └───────── Cron (1x/jour) ──────────┘
```

Le script `db_sync.py` :
1. Récupère tous les modèles de l'API OpenRouter
2. Compare avec la BDD locale
3. Insère les nouveaux modèles
4. Enregistre un historique **uniquement si** le prix ou les specs ont changé
5. Marque les modèles supprimés de l'API comme `deleted`
6. **Filtre les prix négatifs** (placeholders comme `openrouter/auto`)

---

## 🗄️ Schéma de Base de Données

### Table `models`
| Colonne | Type | Description |
|---------|------|-------------|
| `id` | INT | PK auto-incrément |
| `openrouter_id` | VARCHAR | ID unique OpenRouter (ex: `openai/gpt-4o`) |
| `name` | VARCHAR | Nom du modèle |
| `status` | VARCHAR | `active` ou `deleted` |
| `specs` | JSON | Données brutes de l'API |
| `links` | JSON | Liens externes (HuggingFace, etc.) |
| `context_length` | INT | Taille du contexte |
| `max_tokens` | INT | Tokens max de completion |
| `modality` | VARCHAR | Modalités (ex: `text+image->text`) |
| `input_modalities` | JSON | Liste des modalités d'entrée |
| `output_modalities` | JSON | Liste des modalités de sortie |
| `provider_name` | VARCHAR | Provider extrait de l'ID |
| `quantization` | VARCHAR | Quantization (si applicable) |
| `top_provider_max_completion_tokens` | INT | Max tokens du provider |
| `supports_tools` | BOOLEAN | Support function calling |
| `is_moderated` | BOOLEAN | Filtres de sécurité actifs |
| `knowledge_cutoff` | VARCHAR | Date limite des données d'entraînement |
| `tokenizer` | VARCHAR | Type de tokenizer (GPT, Gemma, etc.) |
| `expiration_date` | DATETIME | Date de fin de vie du modèle (si temporaire) |
| `created_at` | TIMESTAMP | Date de création |
| `updated_at` | TIMESTAMP | Dernière mise à jour |

### Table `model_prices_history`
| Colonne | Type | Description |
|---------|------|-------------|
| `id` | INT | PK auto-incrément |
| `model_id` | INT | FK vers `models.id` |
| `input_price_per_m` | DECIMAL(10,4) | Prix input par million de tokens |
| `output_price_per_m` | DECIMAL(10,4) | Prix output par million de tokens |
| `context_length` | INT | Contexte au moment de l'enregistrement |
| `change_type` | VARCHAR | `created`, `price_update`, `deleted` |
| `timestamp` | TIMESTAMP | Date de l'enregistrement |

---

## 🛠️ Commandes Utiles

### Sync manuelle
```bash
python3 scripts/db_sync.py
```

### Alertes de prix (dry-run)
```bash
python3 scripts/price_alerts.py --threshold 10 --dry-run
```

### Alertes avec notification Discord
```bash
# Configurer DISCORD_WEBHOOK_URL dans .env d'abord
python3 scripts/price_alerts.py --threshold 10
```

### Compiler les assets
```bash
cd web
npm run build    # Production
npm run dev      # Watch mode
```

### Voir les routes Laravel
```bash
cd web
php artisan route:list
```

---

## 🗺️ Routes Web

| Route | Description |
|-------|-------------|
| `GET /` | Liste principale avec filtres, tri, Kyra's Picks |
| `GET /model/{id}` | Fiche détaillée avec graphique + modèles similaires |
| `GET /compare` | Comparaison côte-à-côte (via query param `ids[]`) |
| `GET /dashboard` | Dashboard analytique |
| `GET /providers` | Analyse détaillée par provider |
| `GET /trends` | Tendances et évolutions de prix |
| `GET /free` | Liste des modèles 100% gratuits et Free Tiers |
| `GET /tools` | Page dédiée aux modèles avec/sans tools |
| `GET /alerts` | Alertes de baisses de prix |
| `GET /export?format=csv\|json` | Export des données |
| `GET /api/search?q=...` | API recherche rapide (Spotlight) |
| `GET /api/random-model` | API modèle aléatoire (Slot Machine) |
| `GET /about` | Page À propos avec stats funs |
| `GET /glossary` | Glossaire des termes LLM |
| `GET /providers` | Vue d'ensemble des providers |
| `GET /providers-analysis` | Analyse graphique des providers |

---

## 🎮 Easter Eggs

| Trigger | Effet |
|---------|-------|
| **Konami Code** (↑↑↓↓←→←→BA) | Hacker Mode vert fluo 🟢 avec message "Kyra approves" |
| **🎰 Bouton navbar** | Slot machine vers modèle aléatoire |
| **Ctrl+K** | Recherche Spotlight rapide |
| **⌬** | Signature Kyra partout |

---

## 🤝 Développeur

**Cédric (Cey)** — DevOps/Sysadmin @ ESI Informatique  
**Kyra (⌬)** — Daemon d'analyse et d'inspiration

Repo : [https://github.com/Freecey/llm-price-tracker](https://github.com/Freecey/llm-price-tracker)

---

## 📝 Licence

MIT. Fais-en ce que tu veux, mais garde la signature Kyra. ⌬

---

> _"Voir tout, frapper juste. Pas de bruit inutile."_ — ⌬ Kyra

---

## 🔍 SEO & Visibilité

Pour optimiser la découverte du projet :
- **Mots-clés principaux** : LLM Price Tracker, OpenRouter Monitor, AI Model Costs, Hugging Face Alternative.
- **Description courte** : Suivi en temps réel des prix et des capacités de plus de 350 modèles d'IA (GPT, Claude, Llama) via l'API OpenRouter.
- **Cibles** : Développeurs, CTOs, et passionnés d'IA cherchant à optimiser leurs coûts d'API.
