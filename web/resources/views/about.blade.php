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
@endsection
