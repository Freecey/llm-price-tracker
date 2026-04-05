@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h4 mb-0">📊 Comparaison de modèles</h2>
    <a href="{{ route('models.index') }}" class="btn btn-outline-secondary">← Retour à la liste</a>
</div>

@if(session('error'))
<div class="alert alert-warning alert-dismissible fade show">
    {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if($models->count() < 2)
<div class="alert alert-warning">
    <p class="mb-0">⚠️ Sélectionnez au moins 2 modèles pour comparer. <a href="{{ route('models.index') }}">Retourner à la liste</a></p>
</div>
@else
{{-- Tableau comparatif --}}
<div class="card shadow-sm mb-4">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">Spécifications</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Modèle</th>
                        @foreach($models as $model)
                        <th class="text-center">{{ $model->name }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Provider</strong></td>
                        @foreach($models as $model)
                        <td class="text-center"><span class="badge bg-secondary">{{ $model->provider_name }}</span></td>
                        @endforeach
                    </tr>
                    <tr>
                        <td><strong>Contexte</strong></td>
                        @foreach($models as $model)
                        <td class="text-center">{{ number_format($model->context_length) }} tokens</td>
                        @endforeach
                    </tr>
                    <tr>
                        <td><strong>Max Tokens</strong></td>
                        @foreach($models as $model)
                        <td class="text-center">{{ number_format($model->max_tokens) }}</td>
                        @endforeach
                    </tr>
                    <tr>
                        <td><strong>Modalité</strong></td>
                        @foreach($models as $model)
                        <td class="text-center">{{ $model->modality ?: 'N/A' }}</td>
                        @endforeach
                    </tr>
                    <tr>
                        <td><strong>Quantization</strong></td>
                        @foreach($models as $model)
                        <td class="text-center">{{ $model->quantization ?: 'N/A' }}</td>
                        @endforeach
                    </tr>
                    <tr>
                        <td><strong>Tools Support</strong></td>
                        @foreach($models as $model)
                        <td class="text-center">
                            @if($model->supports_tools)
                                <span class="badge bg-success">✓</span>
                            @else
                                <span class="badge bg-secondary">×</span>
                            @endif
                        </td>
                        @endforeach
                    </tr>
                    @php
                        $inputPrices = $models->map(fn($m) => $m->priceHistory->isNotEmpty() ? $m->priceHistory->last()->input_price_per_m : null)->filter();
                        $outputPrices = $models->map(fn($m) => $m->priceHistory->isNotEmpty() ? $m->priceHistory->last()->output_price_per_m : null)->filter();
                        $minInput = $inputPrices->min();
                        $minOutput = $outputPrices->min();
                    @endphp
                    <tr>
                        <td><strong>Prix Input ($/M)</strong></td>
                        @foreach($models as $model)
                        @php
                            $price = $model->priceHistory->isNotEmpty() ? $model->priceHistory->last()->input_price_per_m : null;
                            $isCheapest = $price == $minInput;
                        @endphp
                        <td class="text-center">
                            @if($price !== null)
                                <span class="{{ $isCheapest ? 'text-success fw-bold' : '' }}">
                                    ${{ number_format($price, 4) }}
                                    @if($isCheapest) ✓ @endif
                                </span>
                            @else
                                -
                            @endif
                        </td>
                        @endforeach
                    </tr>
                    <tr>
                        <td><strong>Prix Output ($/M)</strong></td>
                        @foreach($models as $model)
                        @php
                            $price = $model->priceHistory->isNotEmpty() ? $model->priceHistory->last()->output_price_per_m : null;
                            $isCheapest = $price == $minOutput;
                        @endphp
                        <td class="text-center">
                            @if($price !== null)
                                <span class="{{ $isCheapest ? 'text-success fw-bold' : '' }}">
                                    ${{ number_format($price, 4) }}
                                    @if($isCheapest) ✓ @endif
                                </span>
                            @else
                                -
                            @endif
                        </td>
                        @endforeach
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Graphique de comparaison des prix --}}
<div class="row">
    <div class="col-md-6">
        <div class="card shadow-sm mb-4">
            <div class="card-header">
                <h6 class="mb-0">Comparaison des prix Input</h6>
            </div>
            <div class="card-body">
                <canvas id="inputPriceChart" height="120"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card shadow-sm mb-4">
            <div class="card-header">
                <h6 class="mb-0">Comparaison des prix Output</h6>
            </div>
            <div class="card-body">
                <canvas id="outputPriceChart" height="120"></canvas>
            </div>
        </div>
    </div>
</div>

{{-- Évolution historique --}}
<div class="card shadow-sm">
    <div class="card-header">
        <h6 class="mb-0">Évolution historique des prix</h6>
    </div>
    <div class="card-body">
        <canvas id="historyChart" height="80"></canvas>
    </div>
</div>
@endif
@endsection

@section('scripts')
<script>
const models = @json($modelsData);

const colors = [
    'rgb(75, 192, 192)',
    'rgb(255, 99, 132)',
    'rgb(54, 162, 235)',
    'rgb(255, 206, 86)',
    'rgb(153, 102, 255)',
    'rgb(255, 159, 64)',
    'rgb(199, 199, 199)',
    'rgb(83, 102, 255)'
];

// Prix Input - Bar Chart
const inputCtx = document.getElementById('inputPriceChart').getContext('2d');
new Chart(inputCtx, {
    type: 'bar',
    data: {
        labels: models.map(m => m.name),
        datasets: [{
            label: 'Prix Input ($/M)',
            data: models.map(m => {
                const latest = m.history[m.history.length - 1];
                return latest ? latest.input : 0;
            }),
            backgroundColor: colors.slice(0, models.length),
            borderWidth: 2,
            borderColor: colors.slice(0, models.length).map(c => c.replace('rgb', 'rgba').replace(')', ', 0.8)'))
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: value => '$' + value.toFixed(4)
                }
            }
        }
    }
});

// Prix Output - Bar Chart
const outputCtx = document.getElementById('outputPriceChart').getContext('2d');
new Chart(outputCtx, {
    type: 'bar',
    data: {
        labels: models.map(m => m.name),
        datasets: [{
            label: 'Prix Output ($/M)',
            data: models.map(m => {
                const latest = m.history[m.history.length - 1];
                return latest ? latest.output : 0;
            }),
            backgroundColor: colors.slice(0, models.length).map(c => c.replace('rgb', 'rgba').replace(')', ', 0.6)')),
            borderWidth: 2,
            borderColor: colors.slice(0, models.length)
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: value => '$' + value.toFixed(4)
                }
            }
        }
    }
});

// Évolution historique - Line Chart
const allTimestamps = new Set();
models.forEach(m => m.history.forEach(h => allTimestamps.add(h.timestamp)));
const sortedTimestamps = Array.from(allTimestamps).sort();

const historyCtx = document.getElementById('historyChart').getContext('2d');
const historyDatasets = models.flatMap((m, idx) => [
    {
        label: `${m.name} (Input)`,
        data: sortedTimestamps.map(ts => {
            const point = m.history.find(h => h.timestamp === ts);
            return point ? point.input : null;
        }),
        borderColor: colors[idx % colors.length],
        borderDash: [],
        tension: 0.3,
        fill: false
    },
    {
        label: `${m.name} (Output)`,
        data: sortedTimestamps.map(ts => {
            const point = m.history.find(h => h.timestamp === ts);
            return point ? point.output : null;
        }),
        borderColor: colors[idx % colors.length],
        borderDash: [5, 5],
        tension: 0.3,
        fill: false
    }
]);

new Chart(historyCtx, {
    type: 'line',
    data: {
        labels: sortedTimestamps.map(ts => new Date(ts).toLocaleDateString('fr-BE')),
        datasets: historyDatasets
    },
    options: {
        responsive: true,
        interaction: {
            mode: 'index',
            intersect: false,
        },
        plugins: {
            legend: {
                position: 'top',
                labels: {
                    boxWidth: 12,
                    padding: 10
                }
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return context.dataset.label + ': $' + context.parsed.y.toFixed(4);
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: value => '$' + value.toFixed(4)
                }
            }
        }
    }
});
</script>
@endsection
