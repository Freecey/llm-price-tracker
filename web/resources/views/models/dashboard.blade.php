@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h4 mb-0">📈 Dashboard Analytique</h2>
    <a href="{{ route('models.index') }}" class="btn btn-outline-secondary">← Retour à la liste</a>
</div>

{{-- Top cards --}}
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card shadow-sm card-kyra">
            <div class="card-body text-center">
                <h6 class="text-muted mb-2">Modèles suivis</h6>
                <h2 class="mb-0">{{ $modalityCounts->sum('count') }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm card-kyra">
            <div class="card-body text-center">
                <h6 class="text-muted mb-2">Providers</h6>
                <h2 class="mb-0">{{ $providerStats->count() }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm card-kyra">
            <div class="card-body text-center">
                <h6 class="text-muted mb-2">Changements (7j)</h6>
                <h2 class="mb-0">{{ $recentChanges->count() }}</h2>
            </div>
        </div>
    </div>
</div>

{{-- Top 10 modèles les moins chers + Répartition modalités --}}
<div class="row mb-4">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-success text-white">
                <h6 class="mb-0">🏆 Top 10 Modèles les Moins Chers (Prix Input)</h6>
            </div>
            <div class="card-body">
                <canvas id="cheapestChart" height="100"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-header">
                <h6 class="mb-0">Répartition des Modalités</h6>
            </div>
            <div class="card-body">
                <canvas id="modalityChart" height="180"></canvas>
            </div>
        </div>
    </div>
</div>

{{-- Providers stats + Heatmap --}}
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0">📊 Prix Moyens par Provider</h6>
            </div>
            <div class="card-body">
                <canvas id="providerChart" height="150"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header">
                <h6 class="mb-0">🔥 Changements Récents (7 derniers jours)</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height: 350px; overflow-y: auto;">
                    <table class="table table-sm mb-0">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th>Modèle</th>
                                <th>Provider</th>
                                <th>Type</th>
                                <th>Input</th>
                                <th>Output</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentChanges as $change)
                            <tr>
                                <td><small>{{ $change->name }}</small></td>
                                <td><span class="badge bg-secondary">{{ $change->provider_name }}</span></td>
                                <td>
                                    @if($change->change_type === 'increase')
                                        <span class="badge bg-danger">↑ Hausse</span>
                                    @elseif($change->change_type === 'decrease')
                                        <span class="badge bg-success">↓ Baisse</span>
                                    @else
                                        <span class="badge bg-warning">− Nouveau</span>
                                    @endif
                                </td>
                                <td><small>${{ number_format($change->input_price_per_m, 4) }}</small></td>
                                <td><small>${{ number_format($change->output_price_per_m, 4) }}</small></td>
                                <td><small>{{ \Carbon\Carbon::parse($change->timestamp)->format('d/m H:i') }}</small></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
const cheapestModels = @json($cheapestModels);
const providerStats = @json($providerStats);
const modalityCounts = @json($modalityCounts);

// Couleurs
const colors = [
    'rgba(54, 162, 235, 0.7)',
    'rgba(75, 192, 192, 0.7)',
    'rgba(255, 206, 86, 0.7)',
    'rgba(255, 99, 132, 0.7)',
    'rgba(153, 102, 255, 0.7)',
    'rgba(255, 159, 64, 0.7)',
    'rgba(199, 199, 199, 0.7)',
    'rgba(83, 102, 255, 0.7)',
    'rgba(255, 99, 255, 0.7)',
    'rgba(99, 255, 132, 0.7)'
];

// Top 10 - Bar Chart horizontal
const cheapestCtx = document.getElementById('cheapestChart').getContext('2d');
new Chart(cheapestCtx, {
    type: 'bar',
    data: {
        labels: cheapestModels.map(m => m.model.name),
        datasets: [
            {
                label: 'Input ($/M)',
                data: cheapestModels.map(m => m.input_price),
                backgroundColor: 'rgba(75, 192, 192, 0.7)',
                borderColor: 'rgb(75, 192, 192)',
                borderWidth: 1
            },
            {
                label: 'Output ($/M)',
                data: cheapestModels.map(m => m.output_price),
                backgroundColor: 'rgba(255, 99, 132, 0.7)',
                borderColor: 'rgb(255, 99, 132)',
                borderWidth: 1
            }
        ]
    },
    options: {
        indexAxis: 'y',
        responsive: true,
        plugins: {
            legend: {
                position: 'top'
            }
        },
        scales: {
            x: {
                beginAtZero: true,
                ticks: {
                    callback: value => '$' + value.toFixed(4)
                }
            }
        }
    }
});

// Modalités - Pie/Doughnut Chart
const modalityCtx = document.getElementById('modalityChart').getContext('2d');
new Chart(modalityCtx, {
    type: 'doughnut',
    data: {
        labels: modalityCounts.map(m => m.modality || 'Non spécifié'),
        datasets: [{
            data: modalityCounts.map(m => m.count),
            backgroundColor: colors.slice(0, modalityCounts.length),
            borderWidth: 2,
            borderColor: '#fff'
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    boxWidth: 12,
                    padding: 8,
                    font: {
                        size: 11
                    }
                }
            }
        }
    }
});

// Providers - Grouped Bar Chart
const providerCtx = document.getElementById('providerChart').getContext('2d');
const providerNames = Object.keys(providerStats);
new Chart(providerCtx, {
    type: 'bar',
    data: {
        labels: providerNames,
        datasets: [
            {
                label: 'Prix Input moyen ($/M)',
                data: providerNames.map(name => providerStats[name].avg_input),
                backgroundColor: 'rgba(54, 162, 235, 0.7)',
                borderColor: 'rgb(54, 162, 235)',
                borderWidth: 1
            },
            {
                label: 'Prix Output moyen ($/M)',
                data: providerNames.map(name => providerStats[name].avg_output),
                backgroundColor: 'rgba(255, 99, 132, 0.7)',
                borderColor: 'rgb(255, 99, 132)',
                borderWidth: 1
            }
        ]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'top'
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
