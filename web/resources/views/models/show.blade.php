@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">{{ $model->name }}</h4>
            </div>
            <div class="card-body">
                <p class="text-muted small">{{ $model->openrouter_id }}</p>
                
                @if($model->description)
                <div class="alert alert-light border-start border-4 border-primary mb-3">
                    <p class="mb-0 small fst-italic">{{ Str::limit($model->description, 300) }}</p>
                </div>
                @endif

                <ul class="list-group list-group-flush mb-3">
                    <li class="list-group-item"><strong>Knowledge Cutoff:</strong> {{ $model->knowledge_cutoff ?: 'Inconnue' }}</li>
                    <li class="list-group-item"><strong>Tokenizer:</strong> {{ $model->tokenizer ?: 'N/A' }}</li>
                    <li class="list-group-item"><strong>Moderation:</strong> {{ $model->is_moderated ? '⚠️ Active' : '✅ Aucune' }}</li>
                    <li class="list-group-item">
                        <strong>Date de création:</strong> 
                        {{ $model->created_at_date ? \Carbon\Carbon::parse($model->created_at_date)->format('d/m/Y') : 'N/A' }}
                    </li>
                    @if($model->expiration_date)
                    <li class="list-group-item bg-danger text-white">
                        <strong>⚠️ Expiration:</strong> 
                        {{ \Carbon\Carbon::parse($model->expiration_date)->format('d/m/Y') }}
                        <small>(Modèle temporaire)</small>
                    </li>
                    @endif
                </ul>

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
                <li class="list-group-item">
                    <strong data-bs-toggle="tooltip" title="L'entreprise qui a développé le modèle">Provider:</strong> 
                    {{ $model->provider_name }}
                </li>
                <li class="list-group-item">
                    <strong data-bs-toggle="tooltip" title="Types de données gérés (entrée->sortie)">Modality:</strong> 
                    {{ $model->modality ?: 'N/A' }}
                </li>
                <li class="list-group-item">
                    <strong data-bs-toggle="tooltip" title="Mémoire à court terme du modèle (en tokens)">Context:</strong> 
                    {{ number_format($model->context_length) }}
                </li>
                <li class="list-group-item">
                    <strong data-bs-toggle="tooltip" title="Taille maximale d'une réponse unique">Max Tokens:</strong> 
                    {{ number_format($model->max_tokens) }}
                </li>
                <li class="list-group-item">
                    <strong data-bs-toggle="tooltip" title="Optimisation de la précision pour la vitesse">Quantization:</strong> 
                    {{ $model->quantization ?: 'N/A' }}
                </li>
                <li class="list-group-item">
                    <strong data-bs-toggle="tooltip" title="Capacité à utiliser des outils externes (calcul, web, etc.)">Tools:</strong> 
                    @if($model->supports_tools)
                        <span class="badge bg-success">✓ Supporté</span>
                    @else
                        <span class="badge bg-secondary">× Non supporté</span>
                    @endif
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
