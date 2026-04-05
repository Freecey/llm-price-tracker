@php
    // Calcul du Kyra Score de fiabilité pour cette vue
    $historyCount = $model->priceHistory->count();
    $kyraReliability = round(min(100, ($historyCount / 5) * 100));
    $latestPrice = $model->priceHistory->sortByDesc('timestamp')->first();
@endphp

@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h4 class="mb-0">{{ $model->name }}</h4>
                <div class="text-end" title="Kyra Score de Fiabilité">
                    <small class="d-block opacity-75">Fiabilité</small>
                    <span class="badge bg-{{ $kyraReliability > 80 ? 'success' : ($kyraReliability > 40 ? 'warning' : 'danger') }} fs-6">
                        {{ $kyraReliability }}%
                    </span>
                </div>
            </div>
            <div class="card-body">
                <p class="text-muted small">{{ $model->openrouter_id }}</p>
                
                @if($model->description)
                <div class="alert alert-light border-start border-4 border-primary mb-3">
                    <p class="mb-0 small fst-italic">{{ Str::limit($model->description, 300) }}</p>
                </div>
                @endif

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded h-100">
                            <h6 class="border-bottom pb-2 mb-2">🧠 Intelligence & Données</h6>
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted small">Knowledge Cutoff:</span>
                                <span class="fw-bold small">{{ $model->knowledge_cutoff ?: 'Inconnue' }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted small">Tokenizer:</span>
                                <span class="fw-bold small">{{ $model->tokenizer ?: 'N/A' }}</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted small">Moderation:</span>
                                <span class="fw-bold small">{{ $model->is_moderated ? '⚠️ Oui' : '✅ Non' }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded h-100">
                            <h6 class="border-bottom pb-2 mb-2">📅 Cycle de vie</h6>
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted small">Ajouté le:</span>
                                <span class="fw-bold small">{{ $model->created_at_date ? \Carbon\Carbon::parse($model->created_at_date)->format('d/m/Y') : 'N/A' }}</span>
                            </div>
                            @if($model->expiration_date)
                            <div class="mt-2 p-2 bg-danger text-white rounded text-center">
                                <small>⚠️ Expiration: {{ \Carbon\Carbon::parse($model->expiration_date)->format('d/m/Y') }}</small>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <canvas id="priceChart" height="100"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-header">Spécifications</div>
            <ul class="list-group list-group-flush">
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <span class="d-block text-muted small" style="font-size: 0.75rem;">Provider & Modalité</span>
                        <span class="fw-bold">{{ $model->provider_name }}</span>
                        <span class="badge bg-secondary ms-1">{{ $model->modality ?: 'N/A' }}</span>
                    </div>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <span class="d-block text-muted small" style="font-size: 0.75rem;">Fenêtre de contexte</span>
                        <span class="fw-bold text-primary">{{ number_format($model->context_length) }}</span> tokens
                    </div>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <span class="d-block text-muted small" style="font-size: 0.75rem;">Max Output Tokens</span>
                        <span class="fw-bold">{{ number_format($model->max_tokens) }}</span>
                    </div>
                </li>
                @if($model->quantization)
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <span class="d-block text-muted small" style="font-size: 0.75rem;">Quantization</span>
                        <span class="font-monospace">{{ $model->quantization }}</span>
                    </div>
                </li>
                @endif
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <span class="d-block text-muted small" style="font-size: 0.75rem;">Support des Outils (Tools)</span>
                        @if($model->supports_tools)
                            <span class="badge bg-success">✔️ Fonction Calling</span>
                        @else
                            <span class="badge bg-secondary">Non supporté</span>
                        @endif
                    </div>
                </li>
            </ul>
            
            <div class="card-footer">
                <div class="d-grid gap-2 mb-2">
                    <button class="btn btn-warning btn-sm" onclick="addFavorite({{ $model->id }}, '{{ addslashes($model->name) }}', '{{ addslashes($model->provider_name) }}')">
                        ⭐ Ajouter aux favoris
                    </button>
                </div>
                <h6 class="mb-2">Liens externes :</h6>
                <div class="d-grid gap-2">
                    <a href="https://openrouter.ai/models/{{ $model->openrouter_id }}" target="_blank" class="btn btn-outline-primary btn-sm">
                        🌐 Voir sur OpenRouter
                    </a>
                    @if($model->links)
                        @foreach($model->links as $name => $url)
                            <a href="{{ $url }}" target="_blank" class="btn btn-outline-secondary btn-sm">
                                {{ ucfirst($name) }}
                            </a>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modèles similaires --}}
@if($similarModels->isNotEmpty())
<div class="card shadow-sm">
    <div class="card-header bg-light">
        <h6 class="mb-0">🔍 Modèles similaires</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Modèle</th>
                        <th>Provider</th>
                        <th class="text-end">Input</th>
                        <th class="text-end">Output</th>
                        <th class="text-end">Contexte</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($similarModels as $similar)
                    @php
                        $latest = $similar->priceHistory->sortByDesc('timestamp')->first();
                    @endphp
                    <tr>
                        <td>
                            <div class="fw-bold small">{{ $similar->name }}</div>
                            <small class="text-muted">{{ Str::limit($similar->openrouter_id, 25) }}</small>
                        </td>
                        <td><span class="badge bg-secondary">{{ $similar->provider_name }}</span></td>
                        <td class="text-end">
                            @if($latest)
                                <small>${{ number_format($latest->input_price_per_m, 4) }}</small>
                            @endif
                        </td>
                        <td class="text-end">
                            @if($latest)
                                <small>${{ number_format($latest->output_price_per_m, 4) }}</small>
                            @endif
                        </td>
                        <td class="text-end">
                            <small>{{ number_format($similar->context_length) }}</small>
                        </td>
                        <td>
                            <a href="{{ route('models.show', $similar->id) }}" 
                               class="btn btn-sm btn-outline-primary">
                                →
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif
@endsection

@section('scripts')
<script>
    const ctx = document.getElementById('priceChart').getContext('2d');
    const history = @json($history);
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: history.map(h => new Date(h.timestamp).toLocaleDateString()),
            datasets: [
                {
                    label: 'Input Price ($/M)',
                    data: history.map(h => h.input_price_per_m),
                    borderColor: 'rgb(75, 192, 192)',
                    tension: 0.1
                },
                {
                    label: 'Output Price ($/M)',
                    data: history.map(h => h.output_price_per_m),
                    borderColor: 'rgb(255, 99, 132)',
                    tension: 0.1
                }
            ]
        }
    });
</script>
@endsection
