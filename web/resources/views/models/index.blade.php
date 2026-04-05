@extends('layouts.app')

@section('content')
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
                <button type="submit" class="btn btn-primary w-100">Filtrer</button>
            </div>
            <div class="col-md-2">
                <a href="{{ route('models.index') }}" class="btn btn-secondary w-100">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="h4 mb-0">Liste des modèles</h2>
    <span class="badge bg-primary">{{ $models->total() }} résultats</span>
</div>

<div class="table-responsive">
    <table class="table table-hover align-middle">
        <thead class="table-light">
            <tr>
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
                <th>Prix Input ($/M)</th>
                <th>Prix Output ($/M)</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($models as $model)
            <tr>
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
