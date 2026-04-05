@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="h4 mb-0">🏢 Analyse par Provider</h2>
        <small class="text-muted">Comparaison détaillée des providers LLM</small>
    </div>
    <a href="{{ route('models.index') }}" class="btn btn-outline-secondary">← Retour</a>
</div>

{{-- Cards résumé --}}
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card shadow-sm card-kyra">
            <div class="card-body text-center">
                <h6 class="text-muted mb-2">Total Providers</h6>
                <h2 class="mb-0">{{ count($providerDetails) }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm card-kyra">
            <div class="card-body text-center">
                <h6 class="text-muted mb-2">Provider le moins cher (moy.)</h6>
                <h4 class="mb-0 text-success">{{ $providerDetails->keys()->first() }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm card-kyra">
            <div class="card-body text-center">
                <h6 class="text-muted mb-2">Modèle le moins cher</h6>
                @php
                    $overallCheapest = $providerDetails->map(fn($p) => $p['cheapest_model'])
                        ->sortBy(fn($m) => $m->priceHistory->last()->input_price_per_m)
                        ->first();
                @endphp
                <h5 class="mb-0 text-primary">{{ $overallCheapest->name }}</h5>
                <small class="text-muted">${{ number_format($overallCheapest->priceHistory->last()->input_price_per_m, 4) }}</small>
            </div>
        </div>
    </div>
</div>

{{-- Liste des providers --}}
@foreach($providerDetails as $providerName => $stats)
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">
                    <span class="badge bg-primary me-2">{{ $stats['count'] }}</span>
                    {{ ucfirst($providerName) }}
                </h5>
            </div>
            <div class="text-end">
                <small class="text-muted d-block">Prix moyen Input</small>
                <strong>${{ number_format($stats['avg_input'], 4) }}</strong>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            {{-- Stats --}}
            <div class="col-md-3">
                @php
                    $toolsInfo = $toolsStats[$providerName] ?? ['with_tools' => 0, 'total' => 0, 'pct' => 0];
                @endphp
                <div class="mb-3">
                    <small class="text-muted">Tools support</small>
                    <div class="fw-bold">
                        {{ $toolsInfo['with_tools'] }}/{{ $toolsInfo['total'] }} 
                        <small class="text-muted">({{ $toolsInfo['pct'] }}%)</small>
                    </div>
                </div>
                <div class="mb-3">
                    <small class="text-muted">Prix moyen Output</small>
                    <div class="h5 mb-0">${{ number_format($stats['avg_output'], 4) }}</div>
                </div>
                <div class="mb-3">
                    <small class="text-muted">Range Input</small>
                    <div class="small">
                        ${{ number_format($stats['min_input'], 4) }} - 
                        ${{ number_format($stats['max_input'], 4) }}
                    </div>
                </div>
                <div class="mb-3">
                    <small class="text-muted">Modèle le moins cher</small>
                    @if($stats['cheapest_model'])
                        <div class="fw-bold text-success">
                            {{ Str::limit($stats['cheapest_model']->name, 25) }}
                        </div>
                        <small>
                            ${{ number_format($stats['cheapest_model']->priceHistory->last()->input_price_per_m, 4) }}
                        </small>
                    @endif
                </div>
            </div>
            
            {{-- Top 5 modèles --}}
            <div class="col-md-9">
                <h6 class="mb-2">Top 5 modèles (les plus chers en premier)</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Modèle</th>
                                <th class="text-end">Input ($/M)</th>
                                <th class="text-end">Output ($/M)</th>
                                <th class="text-end">Contexte</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($stats['models'] as $model)
                            @php
                                $latest = $model->priceHistory->sortByDesc('timestamp')->first();
                            @endphp
                            <tr>
                                <td>
                                    <div class="fw-bold small">{{ $model->name }}</div>
                                    <small class="text-muted">{{ Str::limit($model->openrouter_id, 30) }}</small>
                                </td>
                                <td class="text-end">
                                    @if($latest)
                                        <strong>${{ number_format($latest->input_price_per_m, 4) }}</strong>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if($latest)
                                        ${{ number_format($latest->output_price_per_m, 4) }}
                                    @endif
                                </td>
                                <td class="text-end">
                                    <small>{{ number_format($model->context_length) }}</small>
                                </td>
                                <td>
                                    <a href="{{ route('models.show', $model->id) }}" 
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
    </div>
</div>
@endforeach

{{-- Graphique d'évolution temporelle --}}
<div class="card shadow-sm">
    <div class="card-header">
        <h6 class="mb-0">📈 Évolution des prix moyens (30 derniers jours)</h6>
    </div>
    <div class="card-body">
        <canvas id="providerTrendsChart" height="100"></canvas>
    </div>
</div>
@endsection

@section('scripts')
<script>
const providerTrends = @json($providerTrends);
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

// Préparer les datasets
const allDates = new Set();
Object.values(providerTrends).forEach(trend => {
    trend.forEach(t => allDates.add(t.date));
});
const sortedDates = Array.from(allDates).sort();

const datasets = Object.keys(providerTrends).flatMap((provider, idx) => {
    const trend = providerTrends[provider];
    const color = colors[idx % colors.length];
    
    return [
        {
            label: `${provider} (Input)`,
            data: sortedDates.map(date => {
                const point = trend.find(t => t.date === date);
                return point ? parseFloat(point.avg_input) : null;
            }),
            borderColor: color,
            borderWidth: 2,
            tension: 0.3,
            fill: false,
            pointRadius: 2
        },
        {
            label: `${provider} (Output)`,
            data: sortedDates.map(date => {
                const point = trend.find(t => t.date === date);
                return point ? parseFloat(point.avg_output) : null;
            }),
            borderColor: color,
            borderWidth: 2,
            borderDash: [5, 5],
            tension: 0.3,
            fill: false,
            pointRadius: 2
        }
    ];
});

new Chart(document.getElementById('providerTrendsChart'), {
    type: 'line',
    data: {
        labels: sortedDates.map(d => new Date(d).toLocaleDateString('fr-BE')),
        datasets: datasets
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
                    padding: 8,
                    font: { size: 11 }
                }
            },
            tooltip: {
                callbacks: {
                    label: ctx => ctx.dataset.label + ': $' + ctx.parsed.y.toFixed(4)
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: v => '$' + v.toFixed(4)
                }
            }
        }
    }
});
</script>
@endsection
