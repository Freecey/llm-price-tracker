@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="h4 mb-0">🚨 Alertes de Prix</h2>
        <small class="text-muted">Surveille les baisses de prix significatives</small>
    </div>
    <div class="d-flex gap-2">
        <form method="GET" action="{{ route('models.alerts') }}" class="d-flex gap-2">
            <select name="days" class="form-select form-select-sm" style="width: auto;" onchange="this.form.submit()">
                <option value="3" {{ $days == 3 ? 'selected' : '' }}>3 jours</option>
                <option value="7" {{ $days == 7 ? 'selected' : '' }}>7 jours</option>
                <option value="14" {{ $days == 14 ? 'selected' : '' }}>14 jours</option>
                <option value="30" {{ $days == 30 ? 'selected' : '' }}>30 jours</option>
            </select>
            <select name="threshold" class="form-select form-select-sm" style="width: auto;" onchange="this.form.submit()">
                <option value="5" {{ $threshold == 5 ? 'selected' : '' }}>≥ 5%</option>
                <option value="10" {{ $threshold == 10 ? 'selected' : '' }}>≥ 10%</option>
                <option value="20" {{ $threshold == 20 ? 'selected' : '' }}>≥ 20%</option>
                <option value="50" {{ $threshold == 50 ? 'selected' : '' }}>≥ 50%</option>
            </select>
        </form>
        <a href="{{ route('models.index') }}" class="btn btn-outline-secondary btn-sm">← Retour</a>
    </div>
</div>

@if($priceDrops->isEmpty())
<div class="alert alert-success">
    <h5 class="alert-heading">✅ Aucune baisse significative</h5>
    <p class="mb-0">Aucun modèle n'a vu son prix baisser de plus de <strong>{{ $threshold }}%</strong> sur les {{ $days }} derniers jours.</p>
</div>
@else
<div class="alert alert-warning">
    <h5 class="alert-heading">📉 {{ $priceDrops->count() }} baisse(s) détectée(s)!</h5>
    <p class="mb-0">Ces modèles ont vu leur prix baisser de plus de <strong>{{ $threshold }}%</strong> sur {{ $days }} jours.</p>
</div>

@foreach($priceDrops as $alert)
@php
    $row = $alert['model'];
@endphp
<div class="card shadow-sm mb-3">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h5 class="mb-1">
                    📉 {{ $row->name }}
                    @if($row->supports_tools)
                        <span class="badge bg-info ms-2" title="Supporte les tools">🛠️</span>
                    @endif
                </h5>
                <p class="text-muted mb-2">{{ $row->openrouter_id }}</p>
                <div class="d-flex gap-3">
                    <div>
                        <small class="text-muted">Provider</small>
                        <div class="fw-bold">{{ $row->provider_name }}</div>
                    </div>
                    <div>
                        <small class="text-muted">Input</small>
                        <div>
                            <span class="text-decoration-line-through text-muted">${{ number_format($row->prev_input, 4) }}</span>
                            <span class="text-success fw-bold ms-1">${{ number_format($row->current_input, 4) }}</span>
                            <span class="badge bg-success ms-1">-{{ $alert['input_drop_pct'] }}%</span>
                        </div>
                    </div>
                    <div>
                        <small class="text-muted">Output</small>
                        <div>
                            <span class="text-decoration-line-through text-muted">${{ number_format($row->prev_output, 4) }}</span>
                            <span class="text-success fw-bold ms-1">${{ number_format($row->current_output, 4) }}</span>
                            <span class="badge bg-success ms-1">-{{ $alert['output_drop_pct'] }}%</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 text-end">
                <a href="https://openrouter.ai/models/{{ $row->openrouter_id }}" target="_blank" 
                   class="btn btn-outline-primary btn-sm">
                    Voir sur OpenRouter →
                </a>
                <a href="{{ route('models.show', $row->id) }}" 
                   class="btn btn-outline-secondary btn-sm ms-2">
                    Détails
                </a>
            </div>
        </div>
    </div>
</div>
@endforeach
@endif

<div class="card shadow-sm mt-4">
    <div class="card-header">
        <h6 class="mb-0">💡 Astuce</h6>
    </div>
    <div class="card-body">
        <p class="mb-2">Tu peux automatiser les alertes avec le script Python :</p>
        <code class="d-block p-2 bg-light rounded">
            python scripts/price_alerts.py --threshold 10 --dry-run
        </code>
        <p class="text-muted mt-2 mb-0 small">
            Configure <code>DISCORD_WEBHOOK_URL</code> dans ton <code>.env</code> pour recevoir les notifications.
        </p>
    </div>
</div>
@endsection
