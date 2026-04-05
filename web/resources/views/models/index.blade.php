@extends('layouts.app')

@section('content')
{{-- Modèle du jour --}}
@if($modelOfDay)
<div class="card shadow-sm mb-4 border-primary" style="border-left: 4px solid #0d6efd;">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <h6 class="text-primary mb-1">⭐ Modèle du jour</h6>
                <h4 class="mb-2">{{ $modelOfDay->name }}</h4>
                <p class="text-muted mb-2">{{ Str::limit($modelOfDay->openrouter_id, 60) }}</p>
                <div class="d-flex gap-3">
                    <div>
                        <small class="text-muted">Provider</small>
                        <div class="fw-bold">{{ $modelOfDay->provider_name }}</div>
                    </div>
                    <div>
                        <small class="text-muted">Contexte</small>
                        <div class="fw-bold">{{ number_format($modelOfDay->context_length) }}</div>
                    </div>
                    @if($modelOfDay->priceHistory->isNotEmpty())
                        <div>
                            <small class="text-muted">Prix Input</small>
                            <div class="fw-bold text-success">${{ number_format($modelOfDay->priceHistory->last()->input_price_per_m, 4) }}</div>
                        </div>
                        <div>
                            <small class="text-muted">Prix Output</small>
                            <div class="fw-bold">${{ number_format($modelOfDay->priceHistory->last()->output_price_per_m, 4) }}</div>
                        </div>
                    @endif
                </div>
            </div>
            <a href="{{ route('models.show', $modelOfDay->id) }}" class="btn btn-primary">
                Voir détails →
            </a>
        </div>
    </div>
</div>
@endif

{{-- Kyra's Picks --}}
@if($kyraPicks && $kyraPicks->isNotEmpty())
<div class="card shadow-sm mb-4" style="border: 2px solid #6f42c1;">
    <div class="card-header bg-white">
        <h5 class="mb-0 text-primary">⌬ Les choix de Kyra</h5>
        <small class="text-muted">Score basé sur: prix (40%), contexte (30%), tools (20%), provider (10%)</small>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="50">#</th>
                        <th>Modèle</th>
                        <th>Score</th>
                        <th class="text-end">Input ($/M)</th>
                        <th class="text-end">Contexte</th>
                        <th>Tools</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($kyraPicks as $idx => $pick)
                    <tr>
                        <td class="text-center">
                            @if($idx == 0)
                                <span class="badge bg-warning text-dark">🥇</span>
                            @elseif($idx == 1)
                                <span class="badge bg-secondary">🥈</span>
                            @elseif($idx == 2)
                                <span class="badge bg-danger">🥉</span>
                            @else
                                <span class="badge bg-light text-dark">{{ $idx + 1 }}</span>
                            @endif
                        </td>
                        <td>
                            <div class="fw-bold">{{ $pick['model']->name }}</div>
                            <small class="text-muted">{{ $pick['model']->provider_name }}</small>
                        </td>
                        <td>
                            <div class="progress" style="height: 20px;">
                                <div class="progress-bar bg-success" role="progressbar" 
                                     style="width: {{ $pick['score'] }}%;"
                                     aria-valuenow="{{ $pick['score'] }}" aria-valuemin="0" aria-valuemax="100">
                                    {{ $pick['score'] }}/100
                                </div>
                            </div>
                        </td>
                        <td class="text-end">
                            <strong>${{ number_format($pick['latest_price']->input_price_per_m, 4) }}</strong>
                        </td>
                        <td class="text-end">
                            <small>{{ number_format($pick['model']->context_length) }}</small>
                        </td>
                        <td>
                            @if($pick['model']->supports_tools)
                                <span class="badge bg-success">✓</span>
                            @else
                                <span class="badge bg-secondary">×</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('models.show', $pick['model']->id) }}" 
                               class="btn btn-sm btn-outline-primary">→</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('models.index') }}" class="row g-3">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="Rechercher (nom, id, provider)..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <select name="provider" class="form-select">
                    <option value="">Tous les providers</option>
                    @foreach($providers as $p)
                        <option value="{{ $p }}" {{ request('provider') == $p ? 'selected' : '' }}>{{ $p }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="modality" class="form-select">
                    <option value="">Toutes les modalités</option>
                    @foreach($modalities as $m)
                        <option value="{{ $m }}" {{ request('modality') == $m ? 'selected' : '' }}>{{ $m }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="tools" class="form-select">
                    <option value="">Tous modèles</option>
                    <option value="1" {{ request('tools') == '1' ? 'selected' : '' }}>✓ Avec Tools</option>
                    <option value="0" {{ request('tools') == '0' ? 'selected' : '' }}>× Sans Tools</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Filtrer</button>
            </div>
            <div class="col-md-2">
                <a href="{{ route('models.index') }}" class="btn btn-secondary w-100">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h2 class="h4 mb-0">Liste des modèles</h2>
        <small class="text-muted">Cochez les modèles à comparer, puis cliquez sur "Comparer"</small>
    </div>
    <div class="d-flex gap-2 align-items-center">
        <span class="badge bg-primary">{{ $models->total() }} résultats</span>
        <form method="GET" action="{{ route('models.compare') }}" id="compareForm">
            <div id="compareInputs"></div>
            <button type="submit" class="btn btn-success" id="compareBtn" disabled>
                📊 Comparer (<span id="compareCount">0</span>)
            </button>
        </form>
    </div>
</div>

{{-- Stats rapides --}}
@if($quickStats)
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card shadow-sm card-kyra">
            <div class="card-body text-center py-3">
                <h6 class="text-muted mb-1 small">Modèles</h6>
                <h4 class="mb-0">{{ $quickStats['total_models'] }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm card-kyra">
            <div class="card-body text-center py-3">
                <h6 class="text-muted mb-1 small">Providers</h6>
                <h4 class="mb-0">{{ $quickStats['total_providers'] }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm card-kyra">
            <div class="card-body text-center py-3">
                <h6 class="text-muted mb-1 small">Prix moyen (input)</h6>
                <h5 class="mb-0 text-success">${{ number_format($quickStats['cheapest_avg'] ?? 0, 4) }}</h5>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm card-kyra">
            <div class="card-body text-center py-3">
                <h6 class="text-muted mb-1 small">Top 10 moyen</h6>
                <h5 class="mb-0 text-danger">${{ number_format($quickStats['most_expensive_avg'] ?? 0, 4) }}</h5>
            </div>
        </div>
    </div>
</div>
@endif

<div class="table-responsive">
    <table class="table table-hover align-middle">
        <thead class="table-light">
            <tr>
                <th width="40">
                    <input type="checkbox" id="selectAll" class="form-check-input">
                </th>
                <th>
                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'name', 'dir' => $sortField == 'name' && $sortDir == 'asc' ? 'desc' : 'asc']) }}" class="text-decoration-none text-dark">
                        Nom @if($sortField == 'name') {{ $sortDir == 'asc' ? '↑' : '↓' }} @endif
                    </a>
                </th>
                <th>
                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'provider_name', 'dir' => $sortField == 'provider_name' && $sortDir == 'asc' ? 'desc' : 'asc']) }}" class="text-decoration-none text-dark">
                        Provider @if($sortField == 'provider_name') {{ $sortDir == 'asc' ? '↑' : '↓' }} @endif
                    </a>
                </th>
                <th>
                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'context_length', 'dir' => $sortField == 'context_length' && $sortDir == 'asc' ? 'desc' : 'asc']) }}" class="text-decoration-none text-dark">
                        Contexte @if($sortField == 'context_length') {{ $sortDir == 'asc' ? '↑' : '↓' }} @endif
                    </a>
                </th>
                <th>
                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'input_price', 'dir' => $sortField == 'input_price' && $sortDir == 'asc' ? 'desc' : 'asc']) }}" class="text-decoration-none text-dark">
                        Prix Input ($/M) @if($sortField == 'input_price') {{ $sortDir == 'asc' ? '↑' : '↓' }} @endif
                    </a>
                </th>
                <th>
                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'output_price', 'dir' => $sortField == 'output_price' && $sortDir == 'asc' ? 'desc' : 'asc']) }}" class="text-decoration-none text-dark">
                        Prix Output ($/M) @if($sortField == 'output_price') {{ $sortDir == 'asc' ? '↑' : '↓' }} @endif
                    </a>
                </th>
                <th width="80">Tools</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($models as $model)
            <tr>
                <td>
                    <input type="checkbox" class="model-check form-check-input" value="{{ $model->id }}" data-name="{{ $model->name }}">
                </td>
                <td>
                    <div class="fw-bold">{{ $model->name }}</div>
                    <small class="text-muted">{{ Str::limit($model->openrouter_id, 30) }}</small>
                </td>
                <td><span class="badge bg-secondary text-uppercase">{{ $model->provider_name }}</span></td>
                <td>{{ number_format($model->context_length) }}</td>
                <td>
                    @if($model->priceHistory->isNotEmpty())
                        ${{ number_format($model->priceHistory->last()->input_price_per_m, 4) }}
                    @else
                        -
                    @endif
                </td>
                <td>
                    @if($model->priceHistory->isNotEmpty())
                        ${{ number_format($model->priceHistory->last()->output_price_per_m, 4) }}
                    @else
                        -
                    @endif
                </td>
                <td class="text-center">
                    @if($model->supports_tools)
                        <span class="badge bg-success" title="Supporte les tools/function calling">✓</span>
                    @else
                        <span class="badge bg-secondary" title="Ne supporte pas les tools">×</span>
                    @endif
                </td>
                <td>
                    <a href="{{ route('models.show', $model->id) }}" class="btn btn-sm btn-outline-primary">Détails</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
{{ $models->links() }}
@endsection

@section('scripts')
<script>
const checkboxes = document.querySelectorAll('.model-check');
const selectAll = document.getElementById('selectAll');
const compareBtn = document.getElementById('compareBtn');
const compareCount = document.getElementById('compareCount');
const compareIds = document.getElementById('compareIds');
const searchInput = document.querySelector('input[name="search"]');

function updateCompare() {
    const checked = document.querySelectorAll('.model-check:checked');
    const count = checked.length;
    const ids = Array.from(checked).map(cb => cb.value);
    
    compareCount.textContent = count;
    compareBtn.disabled = count < 2;
    
    // Update hidden inputs
    const container = document.getElementById('compareInputs');
    container.innerHTML = '';
    ids.forEach(id => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'ids[]';
        input.value = id;
        container.appendChild(input);
    });
}

selectAll?.addEventListener('change', (e) => {
    checkboxes.forEach(cb => cb.checked = e.target.checked);
    updateCompare();
});

checkboxes.forEach(cb => {
    cb.addEventListener('change', updateCompare);
});

// Raccourci clavier: / pour focus recherche
document.addEventListener('keydown', (e) => {
    if (e.key === '/' && document.activeElement !== searchInput) {
        e.preventDefault();
        searchInput?.focus();
    }
});

// Auto-focus au chargement si paramètre search présent
@if(request('search'))
searchInput?.focus();
@endif
</script>
@endsection
