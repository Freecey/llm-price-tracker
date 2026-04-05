<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kyra's LLM Tracker ⌬</title>
    <link rel="icon" type="image/png" href="/img/favicon.png">
    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body {
            background-color: #f0f2f5;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .navbar {
            background: linear-gradient(90deg, #1a1a1a 0%, #333 100%);
            border-bottom: 2px solid #0d6efd;
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }
        .table th a {
            color: #333;
            transition: color 0.2s;
        }
        .table th a:hover {
            color: #0d6efd;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="{{ route('models.index') }}">
                <img src="/img/favicon.png" width="30" height="30" class="me-2 rounded-circle" alt="Kyra">
                <span class="fw-bold">Kyra's LLM Tracker ⌬</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <div class="navbar-nav ms-auto align-items-center">
                    <a href="{{ route('models.index') }}" class="nav-link {{ request()->routeIs('models.index') ? 'active' : '' }}">Liste</a>
                    <a href="{{ route('models.compare') }}" class="nav-link {{ request()->routeIs('models.compare') ? 'active' : '' }}">Comparer</a>
                    <a href="{{ route('models.tools') }}" class="nav-link {{ request()->routeIs('models.tools') ? 'active' : '' }}">🛠️ Tools</a>
                    <a href="{{ route('models.dashboard') }}" class="nav-link {{ request()->routeIs('models.dashboard') ? 'active' : '' }}">Dashboard</a>
                    <a href="{{ route('providers.list') }}" class="nav-link {{ request()->routeIs('providers.list') ? 'active' : '' }}">🏢 Providers</a>
                    <a href="{{ route('models.trends') }}" class="nav-link {{ request()->routeIs('models.trends') ? 'active' : '' }}">Tendances</a>
                    <a href="{{ route('models.alerts') }}" class="nav-link {{ request()->routeIs('models.alerts') ? 'active' : '' }}">🚨 Alertes</a>
                    <button class="btn btn-sm btn-outline-light ms-2" data-bs-toggle="modal" data-bs-target="#exportModal">
                        📥 Export
                    </button>
                    <button class="btn btn-sm btn-outline-light ms-2" data-bs-toggle="modal" data-bs-target="#spotlightModal" title="Recherche rapide (Ctrl+K)">
                        🔍 <kbd style="font-size: 0.7em;">Ctrl+K</kbd>
                    </button>
                    <button id="slotMachineBtn" class="btn btn-sm btn-warning ms-2" title="Modèle aléatoire">
                        🎰
                    </button>
                    <a href="{{ route('glossary') }}" class="nav-link {{ request()->routeIs('glossary') ? 'active' : '' }}">📖 Glossaire</a>
                    <a href="{{ route('about') }}" class="nav-link {{ request()->routeIs('about') ? 'active' : '' }}">À propos</a>
                </div>
            </div>
        </div>
    </nav>
    <main class="container my-4">
        @yield('content')
    </main>
    
    <footer class="container mt-5 mb-3">
        <div class="card shadow-sm">
            <div class="card-body py-2">
                <div class="row text-center small text-muted">
                    <div class="col-md-4">
                        <strong>Kyra's LLM Tracker ⌬</strong>
                    </div>
                    <div class="col-md-4">
                        Données mises à jour quotidiennement via OpenRouter API
                    </div>
                    <div class="col-md-4">
                        <a href="https://github.com/Freecey/llm-price-tracker" target="_blank" class="text-decoration-none">
                            GitHub
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    
    @include('models.export-modal')
    @include('components.search-spotlight')
    @include('components.favorites-widget')
    @include('components.easter-eggs')
    @yield('scripts')
    <script>
        // Activation des tooltips Bootstrap partout
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
        const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))
    </script>
</body>
</html>
