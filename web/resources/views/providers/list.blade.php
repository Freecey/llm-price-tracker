@extends('layouts.app')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h2 class="h4 mb-0">🏢 Les Providers LLM</h2>
        <small class="text-muted">Carte d'identité des acteurs du marché</small>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('models.providers') }}" class="btn btn-outline-info btn-sm">
            📊 Analyse détaillée (Graphiques)
        </a>
        <a href="{{ route('models.index') }}" class="btn btn-outline-secondary btn-sm">← Retour</a>
    </div>
</div>

<div class="row g-4">
    @foreach($providers as $prov)
    <div class="col-md-6 col-lg-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-capitalize">{{ $prov->provider_name }}</h5>
                <span class="badge bg-primary">{{ $prov->count }} modèles</span>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <small class="text-muted d-block">Contexte Moyen</small>
                    <strong class="h5">{{ number_format($prov->avg_context, 0) }} tokens</strong>
                </div>

                <div class="d-grid">
                    <a href="{{ route('models.index', ['provider' => $prov->provider_name]) }}" class="btn btn-sm btn-outline-primary">
                        Voir les modèles {{ ucfirst($prov->provider_name) }} →
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endsection
