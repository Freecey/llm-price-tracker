@extends('layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm border-0 overflow-hidden">
            <div class="card-body p-4" style="background: linear-gradient(135deg, #0d6efd 0%, #6610f2 100%); color: white;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="mb-0"><span class="display-6 fw-bold">🎁</span> Le Coin des Gratuits</h2>
                        <p class="mb-0 mt-2 opacity-75">Les modèles qui ne vident pas ton compte en banque. Merci qui ? Merci Kyra. ⌬</p>
                    </div>
                    <div class="text-end d-none d-md-block">
                        <div class="h5 mb-0">{{ $stats['total_free'] }}</div>
                        <small>Modèles trouvés</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if($stats['fully_free'] > 0)
<div class="alert alert-success d-flex align-items-center" role="alert">
    <i class="bi bi-gift-fill me-2 fs-4"></i>
    <div>
        <strong>{{ $stats['fully_free'] }} modèles sont 100% Gratuits (Input & Output) !</strong>
        <p class="mb-0 small">Profites-en tant que c'est le cas... OpenRouter doit bien vivre d'une manière ou d'une autre.</p>
    </div>
</div>
@endif

<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('models.free') }}" class="row g-3">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="Rechercher un modèle..." 
                       value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="provider" class="form-select">
                    <option value="">Tous les providers</option>
                    @foreach($providers as $p)
                        <option value="{{ $p }}" {{ request('provider') == $p ? 'selected' : '' }}>{{ $p }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="tools" class="form-select">
                    <option value="">Avec ou sans Tools</option>
                    <option value="1" {{ request('tools') == '1' ? 'selected' : '' }}>Avec Tools 🛠️</option>
                    <option value="0" {{ request('tools') == '0' ? 'selected' : '' }}>Sans Tools</option>
                </select>
            </div>
            <div class="col-md-2 d-grid">
                <button type="submit" class="btn btn-primary">Filtrer</button>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Modèle</th>
                        <th>Provider</th>
                        <th class="text-end">Input / 1M</th>
                        <th class="text-end">Output / 1M</th>
                        <th class="text-center">Contexte</th>
                        <th class="text-center">Outils</th>
                        <th class="text-center" title="Indice de fiabilité des données (Kyra Score)">Fiabilité</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($freeModels as $item)
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    @if($item['input_price'] == 0 && $item['output_price'] == 0)
                                        <span class="badge bg-success me-2" style="font-size: 0.65rem;">100% FREE</span>
                                    @else
                                        <span class="badge bg-info text-dark me-2" style="font-size: 0.65rem;">FREE TIER</span>
                                    @endif
                                    <div>
                                        <div class="fw-bold">{{ $item['model']->name }}</div>
                                        <small class="text-muted">{{ Str::limit($item['model']->openrouter_id, 30) }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-secondary">{{ $item['model']->provider_name }}</span>
                            </td>
                            <td class="text-end font-monospace">
                                <span class="{{ $item['input_price'] == 0 ? 'text-success fw-bold' : '' }}">
                                    ${{ number_format($item['input_price'], 4) }}
                                </span>
                            </td>
                            <td class="text-end font-monospace">
                                <span class="{{ $item['output_price'] == 0 ? 'text-success fw-bold' : '' }}">
                                    ${{ number_format($item['output_price'], 4) }}
                                </span>
                            </td>
                            <td class="text-center">
                                {{ number_format($item['model']->context_length / 1000) }}k
                            </td>
                            <td class="text-center">
                                @if($item['model']->supports_tools)
                                    <span class="text-success">✔️</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="d-inline-block position-relative" style="width: 60px;" title="Kyra Score: {{ $item['stability_score'] }}%">
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar bg-{{ $item['stability_score'] > 80 ? 'success' : ($item['stability_score'] > 40 ? 'warning' : 'danger') }}" 
                                             style="width: {{ $item['stability_score'] }}%"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-end pe-4">
                                <a href="{{ route('models.show', $item['model']->id) }}" class="btn btn-sm btn-outline-primary rounded-circle" style="width: 32px; height: 32px; padding: 0; line-height: 30px;">
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

<div class="mt-5 d-flex justify-content-center">
    {{ $freeModels->links() }}
</div>

@endsection
