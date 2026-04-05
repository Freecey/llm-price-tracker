@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="h4 mb-0">🛠️Capacités Tools / Function Calling</h2>
        <small class="text-muted">Modèles LLM supportant les tools et function calling</small>
    </div>
    <a href="{{ route('models.alerts') }}" class="btn btn-outline-warning btn-sm">🚨 Alertes Prix</a>
</div>

{{-- Stats cards --}}
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card shadow-sm card-kyra border-success" style="border-left: 4px solid #198754;">
            <div class="card-body text-center">
                <h6 class="text-muted mb-1">Avec Tools</h6>
                <h2 class="mb-0 text-success">{{ $totalWithTools }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm card-kyra border-secondary" style="border-left: 4px solid #6c757d;">
            <div class="card-body text-center">
                <h6 class="text-muted mb-1">Sans Tools</h6>
                <h2 class="mb-0 text-muted">{{ $totalWithoutTools }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm card-kyra border-primary" style="border-left: 4px solid #0d6efd;">
            <div class="card-body text-center">
                <h6 class="text-muted mb-1">% avec Tools</h6>
                @php
                    $pct = round(($totalWithTools / ($totalWithTools + $totalWithoutTools)) * 100, 1);
                @endphp
                <h2 class="mb-0 text-primary">{{ $pct }}%</h2>
            </div>
        </div>
    </div>
</div>

{{-- Top providers --}}
@if($topProviders->isNotEmpty())
<div class="card shadow-sm mb-4">
    <div class="card-header bg-light">
        <h6 class="mb-0">🏆 Top Providers (modèles avec tools)</h6>
    </div>
    <div class="card-body">
        <div class="d-flex flex-wrap gap-2">
            @foreach($topProviders as $prov)
            <div class="badge bg-primary p-2">
                {{ $prov->provider_name }}: {{ $prov->cnt }} modèles
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif

{{-- Toggle With/Without Tools --}}
<div class="btn-group mb-3" role="group">
    <a href="{{ route('models.tools', ['with_tools' => 1]) }}" 
       class="btn {{ $withTools ? 'btn-success' : 'btn-outline-secondary' }}">
        ✓ Avec Tools ({{ $totalWithTools }})
    </a>
    <a href="{{ route('models.tools', ['with_tools' => 0]) }}" 
       class="btn {{ !$withTools ? 'btn-secondary' : 'btn-outline-secondary' }}">
        × Sans Tools ({{ $totalWithoutTools }})
    </a>
</div>

{{-- Filtres --}}
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('models.tools') }}" class="row g-3">
            <input type="hidden" name="with_tools" value="{{ $withTools ? 1 : 0 }}">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" 
                       placeholder="Rechercher (nom, id, provider)..." 
                       value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="provider" class="form-select">
                    <option value="">Tous les providers</option>
                    @foreach($providers as $p)
                        <option value="{{ $p }}" {{ request('provider') == $p ? 'selected' : '' }}>
                            {{ $p }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Filtrer</button>
            </div>
            <div class="col-md-2">
                <a href="{{ route('models.tools', ['with_tools' => $withTools ? 1 : 0]) }}" 
                   class="btn btn-secondary w-100">Reset</a>
            </div>
        </form>
    </div>
</div>

{{-- Liste --}}
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="h5 mb-0">
        {{ $withTools ? '✓ Modèles avec Tools' : '× Modèles sans Tools' }}
    </h3>
    <div class="d-flex gap-2 align-items-center">
        <form method="GET" action="{{ route('models.tools') }}" class="d-flex gap-2 align-items-center">
            <input type="hidden" name="with_tools" value="{{ $withTools ? 1 : 0 }}">
            @foreach(request()->except('per_page', 'with_tools') as $key => $value)
                @if(is_array($value))
                    @foreach($value as $v)
                        <input type="hidden" name="{{ $key }}[]" value="{{ $v }}">
                    @endforeach
                @else
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endif
            @endforeach
            <select name="per_page" class="form-select form-select-sm" style="width: auto;" onchange="this.form.submit()">
                <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10/page</option>
                <option value="30" {{ request('per_page') == 30 || !request('per_page') ? 'selected' : '' }}>30/page</option>
                <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50/page</option>
                <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100/page</option>
                <option value="200" {{ request('per_page') == 200 ? 'selected' : '' }}>200/page</option>
            </select>
        </form>
        <span class="badge bg-primary">{{ $models->total() }} résultats</span>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-hover align-middle">
        <thead class="table-light">
            <tr>
                <th>
                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'name', 'dir' => $sortField == 'name' && $sortDir == 'asc' ? 'desc' : 'asc']) }}" 
                       class="text-decoration-none text-dark">
                        Nom @if($sortField == 'name') {{ $sortDir == 'asc' ? '↑' : '↓' }} @endif
                    </a>
                </th>
                <th>
                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'provider_name', 'dir' => $sortField == 'provider_name' && $sortDir == 'asc' ? 'desc' : 'asc']) }}" 
                       class="text-decoration-none text-dark">
                        Provider @if($sortField == 'provider_name') {{ $sortDir == 'asc' ? '↑' : '↓' }} @endif
                    </a>
                </th>
                <th>
                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'context_length', 'dir' => $sortField == 'context_length' && $sortDir == 'asc' ? 'desc' : 'asc']) }}" 
                       class="text-decoration-none text-dark">
                        Contexte @if($sortField == 'context_length') {{ $sortDir == 'asc' ? '↑' : '↓' }} @endif
                    </a>
                </th>
                <th>
                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'input_price', 'dir' => $sortField == 'input_price' && $sortDir == 'asc' ? 'desc' : 'asc']) }}" 
                       class="text-decoration-none text-dark">
                        Input ($/M) @if($sortField == 'input_price') {{ $sortDir == 'asc' ? '↑' : '↓' }} @endif
                    </a>
                </th>
                <th>
                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'output_price', 'dir' => $sortField == 'output_price' && $sortDir == 'asc' ? 'desc' : 'asc']) }}" 
                       class="text-decoration-none text-dark">
                        Output ($/M) @if($sortField == 'output_price') {{ $sortDir == 'asc' ? '↑' : '↓' }} @endif
                    </a>
                </th>
                <th>Modalités</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($models as $model)
            <tr>
                <td>
                    <div class="fw-bold">{{ $model->name }}</div>
                    <small class="text-muted">{{ Str::limit($model->openrouter_id, 35) }}</small>
                </td>
                <td>
                    <span class="badge bg-secondary text-uppercase">{{ $model->provider_name }}</span>
                </td>
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
                <td>
                    <small class="text-muted">{{ Str::limit($model->modality, 20) }}</small>
                </td>
                <td>
                    <div class="d-flex gap-1">
                        <a href="{{ route('models.show', $model->id) }}" 
                           class="btn btn-sm btn-outline-primary">Détails</a>
                        <a href="https://openrouter.ai/models/{{ $model->openrouter_id }}" 
                           target="_blank" class="btn btn-sm btn-outline-secondary">↗</a>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
{{ $models->links() }}
@endsection
