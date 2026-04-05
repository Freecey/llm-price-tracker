@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8 text-center">
        <div class="card card-kyra p-5 mb-4">
            <img src="/img/favicon.png" width="100" class="mb-4 rounded-circle shadow" alt="Kyra">
            <h1 class="display-5 fw-bold mb-3">Kyra's LLM Tracker ⌬</h1>
            <p class="lead text-muted">
                Pas de rembourrage, juste les données. 
                <br>Ce projet est conçu pour suivre l'évolution des prix et des specs des IA sans bruit inutile.
            </p>
        </div>
    </div>
    <div class="col-md-10">
        <div class="row g-4">
            <div class="col-md-6">
                <div class="card card-kyra h-100">
                    <div class="card-body">
                        <h4 class="card-title">🎯 L'Objectif</h4>
                        <p class="card-text">
                            Avoir un historique fiable pour voir qui baisse ses prix (ou qui les augmente). 
                            Le script Python tourne une fois par jour, détecte les changements et ne stocke que le nécessaire.
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card card-kyra h-100">
                    <div class="card-body">
                        <h4 class="card-title">🛠️ La Stack</h4>
                        <ul class="list-unstyled mb-0">
                            <li>⚡ <strong>Backend:</strong> Python (Sync) + Laravel 11</li>
                            <li>🗄️ <strong>Base:</strong> MariaDB (Historique delta)</li>
                            <li>🎨 <strong>Front:</strong> Bootstrap 5 + Chart.js</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Stats funs --}}
@if($stats)
<div class="card shadow-sm">
    <div class="card-header bg-light">
        <h5 class="mb-0">📊 Stats Fun (que personne n'a demandées mais que Kyra a ajoutées)</h5>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <div class="text-center">
                    <h6 class="text-muted small">Modèle le moins cher</h6>
                    @if($stats['cheapest_model'])
                        <div class="fw-bold text-success">{{ $stats['cheapest_model']['model']->name }}</div>
                        <small>${{ number_format($stats['cheapest_model']['price'], 4) }}/M</small>
                    @endif
                </div>
            </div>
            <div class="col-md-4">
                <div class="text-center">
                    <h6 class="text-muted small">Modèle le plus cher</h6>
                    @if($stats['most_expensive_model'])
                        <div class="fw-bold text-danger">{{ $stats['most_expensive_model']['model']->name }}</div>
                        <small>${{ number_format($stats['most_expensive_model']['price'], 4) }}/M</small>
                    @endif
                </div>
            </div>
            <div class="col-md-4">
                <div class="text-center">
                    <h6 class="text-muted small">Plus gros contexte</h6>
                    @if($stats['biggest_context'])
                        <div class="fw-bold text-primary">{{ $stats['biggest_context']->name }}</div>
                        <small>{{ number_format($stats['biggest_context']->context_length) }} tokens</small>
                    @endif
                </div>
            </div>
            <div class="col-md-3">
                <div class="text-center p-2 bg-light rounded">
                    <div class="h4 mb-1">{{ $stats['total_history_entries'] }}</div>
                    <small class="text-muted">Entrées d'historique</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="text-center p-2 bg-light rounded">
                    <div class="h4 mb-1">{{ $stats['providers_with_tools'] }}</div>
                    <small class="text-muted">Providers avec tools</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="text-center p-2 bg-light rounded">
                    <div class="h4 mb-1">{{ number_format($stats['avg_context_length']) }}</div>
                    <small class="text-muted">Contexte moyen</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="text-center p-2 bg-light rounded">
                    <div class="h4 mb-1">{{ $stats['total_sync_runs'] }}</div>
                    <small class="text-muted">Jours de sync</small>
                </div>
            </div>
        </div>
        
        <hr>
        
        <div class="text-center text-muted small">
            <p class="mb-1"><strong>🥚 Easter Eggs cachés :</strong></p>
            <ul class="list-inline mb-0">
                <li class="list-inline-item">⌨️ Konami Code (↑↑↓↓←→←→BA)</li>
                <li class="list-inline-item">🎰 Slot machine (bouton navbar)</li>
                <li class="list-inline-item">⌬ Ctrl+K (recherche rapide)</li>
            </ul>
        </div>
    </div>
</div>
@endif
@endsection
