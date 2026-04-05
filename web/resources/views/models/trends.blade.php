@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="h4 mb-0">📊 Tendances & Évolutions</h2>
        <small class="text-muted">Analyse des changements de prix dans le temps</small>
    </div>
    <a href="{{ route('models.index') }}" class="btn btn-outline-secondary">← Retour</a>
</div>

{{-- Timeline des 14 derniers jours --}}
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header">
                <h6 class="mb-0">📅 Timeline des changements (14 derniers jours)</h6>
            </div>
            <div class="card-body">
                @foreach($timeline as $date => $changes)
                <div class="mb-3">
                    <div class="d-flex align-items-center mb-2">
                        <div class="h6 mb-0 me-2">
                            {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}
                        </div>
                        <span class="badge bg-secondary">{{ count($changes) }} changement(s)</span>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($changes as $change)
                        <div class="badge {{ 
                            $change->change_type === 'decrease' ? 'bg-success' : 
                            ($change->change_type === 'increase' ? 'bg-danger' : 'bg-warning text-dark')
                        }} p-2">
                            @if($change->change_type === 'decrease') ↓ @elseif($change->change_type === 'increase') ↑ @else − @endif
                            {{ Str::limit($change->name, 20) }}
                            <small>(${{ number_format($change->input_price_per_m, 4) }})</small>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

{{-- Top 20 modèles avec plus gros changements --}}
<div class="card shadow-sm mb-4">
    <div class="card-header bg-warning">
        <h6 class="mb-0">🔥 Top 20 - Plus gros changements de prix (%)</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Modèle</th>
                        <th>Provider</th>
                        <th class="text-end">Δ Input (%)</th>
                        <th class="text-end">Δ Output (%)</th>
                        <th class="text-end">Input actuel</th>
                        <th class="text-end">Historique</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($modelTrends as $trend)
                    @php
                        $inputBadges = $trend['input_change_pct'] < 0 ? 'success' : 
                                     ($trend['input_change_pct'] > 0 ? 'danger' : 'secondary');
                        $outputBadges = $trend['output_change_pct'] < 0 ? 'success' : 
                                      ($trend['output_change_pct'] > 0 ? 'danger' : 'secondary');
                    @endphp
                    <tr>
                        <td>
                            <div class="fw-bold">{{ $trend['model']->name }}</div>
                            <small class="text-muted">{{ Str::limit($trend['model']->openrouter_id, 30) }}</small>
                        </td>
                        <td><span class="badge bg-secondary">{{ $trend['model']->provider_name }}</span></td>
                        <td class="text-end">
                            <span class="badge bg-{{ $inputBadges }}">
                                {{ $trend['input_change_pct'] > 0 ? '+' : '' }}{{ $trend['input_change_pct'] }}%
                            </span>
                        </td>
                        <td class="text-end">
                            <span class="badge bg-{{ $outputBadges }}">
                                {{ $trend['output_change_pct'] > 0 ? '+' : '' }}{{ $trend['output_change_pct'] }}%
                            </span>
                        </td>
                        <td class="text-end">
                            <small>${{ number_format($trend['last_price']->input_price_per_m, 4) }}</small>
                        </td>
                        <td class="text-end">
                            <small>{{ $trend['history_count'] }} pts</small>
                        </td>
                        <td>
                            <a href="{{ route('models.show', $trend['model']->id) }}" 
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

{{-- Liste complète des changements récents --}}
<div class="card shadow-sm">
    <div class="card-header">
        <h6 class="mb-0">📝 Tous les changements (30 jours)</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
            <table class="table table-sm table-hover mb-0">
                <thead class="table-light sticky-top">
                    <tr>
                        <th>Date</th>
                        <th>Modèle</th>
                        <th>Provider</th>
                        <th>Type</th>
                        <th class="text-end">Input</th>
                        <th class="text-end">Output</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($significantChanges as $change)
                    <tr>
                        <td>
                            <small>{{ \Carbon\Carbon::parse($change->timestamp)->format('d/m H:i') }}</small>
                        </td>
                        <td>
                            <small class="fw-bold">{{ Str::limit($change->name, 25) }}</small>
                        </td>
                        <td>
                            <span class="badge bg-secondary">{{ $change->provider_name }}</span>
                        </td>
                        <td>
                            @if($change->change_type === 'increase')
                                <span class="badge bg-danger">↑ Hausse</span>
                            @elseif($change->change_type === 'decrease')
                                <span class="badge bg-success">↓ Baisse</span>
                            @else
                                <span class="badge bg-info">− Nouveau</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <small>${{ number_format($change->input_price_per_m, 4) }}</small>
                        </td>
                        <td class="text-end">
                            <small>${{ number_format($change->output_price_per_m, 4) }}</small>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
