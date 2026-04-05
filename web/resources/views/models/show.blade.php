@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">{{ $model->name }}</h4>
            </div>
            <div class="card-body">
                <p class="text-muted">{{ $model->openrouter_id }}</p>
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
                <li class="list-group-item"><strong>Provider:</strong> {{ $model->provider_name }}</li>
                <li class="list-group-item"><strong>Modality:</strong> {{ $model->modality ?: 'N/A' }}</li>
                <li class="list-group-item"><strong>Context:</strong> {{ number_format($model->context_length) }}</li>
                <li class="list-group-item"><strong>Max Tokens:</strong> {{ number_format($model->max_tokens) }}</li>
                <li class="list-group-item"><strong>Quantization:</strong> {{ $model->quantization ?: 'N/A' }}</li>
            </ul>
            
            <div class="card-footer">
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
