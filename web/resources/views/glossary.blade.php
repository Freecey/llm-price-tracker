@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="h4 mb-0">📖 Glossaire LLM</h2>
        <small class="text-muted">Comprendre les termes techniques de l'IA</small>
    </div>
    <a href="{{ route('models.index') }}" class="btn btn-outline-secondary">← Retour à la liste</a>
</div>

<div class="row g-4">
    {{-- Colonne gauche : Termes --}}
    <div class="col-md-8">
        @php
        $terms = [
            [
                'term' => 'Provider',
                'desc' => 'L\'entreprise ou l\'organisation qui a développé et héberge le modèle.',
                'ex' => 'OpenAI (GPT-4), Anthropic (Claude), Meta (Llama), Google (Gemma).'
            ],
            [
                'term' => 'Modality',
                'desc' => 'Les types de données que le modèle peut accepter en entrée et produire en sortie. Le format est généralement <code>entrée->sortie</code>.',
                'ex' => '<ul class="mb-0 mt-2 ps-3 small">'
                    . '<li><strong>text→text</strong> : Le modèle lit et écrit du texte (ex: GPT-3.5).</li>'
                    . '<li><strong>text+image→text</strong> : Le modèle "voit" les images et répond par texte (ex: GPT-4V, Llama 3.2).</li>'
                    . '<li><strong>text+image+video→text</strong> : Il analyse aussi des séquences vidéo (ex: Gemini 1.5).</li>'
                    . '<li><strong>text→speech</strong> : Il génère de l\'audio à partir de texte (ex: ElevenLabs).</li>'
                    . '</ul>'
            ],
            [
                'term' => 'Context Length',
                'desc' => 'La quantité totale d\'informations (en tokens) que le modèle peut "garder en tête" simultanément lors d\'une conversation ou d\'une analyse.',
                'ex' => 'Un contexte de 128k tokens équivaut à environ 300 pages de texte. Plus il est grand, plus le modèle peut analyser de longs documents sans "oublier" le début.'
            ],
            [
                'term' => 'Max Tokens',
                'desc' => 'Le nombre maximum de tokens que le modèle peut générer en une seule réponse (output).',
                'ex' => 'Si le max est de 4096, le modèle s\'arrêtera là, même si sa réponse n\'est pas terminée. C\'est la limite de la "réply".'
            ],
            [
                'term' => 'Quantization',
                'desc' => 'Une technique d\'optimisation qui réduit la précision des calculs du modèle (ex: de 16 bits à 8 bits) pour le rendre plus léger et plus rapide, avec une perte d\'intelligence minime.',
                'ex' => '<code>int8</code>, <code>fp4</code>. Souvent utilisé pour faire tourner de gros modèles sur des machines moins puissantes.'
            ],
            [
                'term' => 'Tools / Function Calling',
                'desc' => 'La capacité du modèle à interagir avec des outils externes (calculatrice, recherche web, base de données) pour exécuter des tâches précises au lieu de simplement générer du texte.',
                'ex' => 'Un modèle avec Tools peut chercher la météo actuelle ou exécuter du code Python pour toi.'
            ],
            [
                'term' => 'Input Price ($/M)',
                'desc' => 'Le coût pour envoyer 1 million de tokens au modèle (ce que tu lui donnes à lire/analyser).',
                'ex' => 'Si le prix est de $2.50/M et que tu envoies 10k tokens, cela te coûtera $0.025.'
            ],
            [
                'term' => 'Output Price ($/M)',
                'desc' => 'Le coût pour chaque million de tokens générés par le modèle (sa réponse).',
                'ex' => 'Souvent plus cher que l\'input car la génération demande plus de puissance de calcul.'
            ],
            [
                'term' => 'Token',
                'desc' => 'L\'unité de base de texte pour les LLM. Un token ne correspond pas exactement à un mot.',
                'ex' => 'En anglais, 1 token ≈ 0.75 mot. En français, c\'est souvent un peu plus court. "Bonjour" = 1 token, "L\'intelligence artificielle" ≈ 3-4 tokens.'
            ]
        ];
        @endphp

        @foreach($terms as $item)
        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <h4 class="h5 text-primary mb-2">{{ $item['term'] }}</h4>
                <p class="mb-2">{{ $item['desc'] }}</p>
                <div class="bg-light p-2 rounded small font-monospace">
                    <strong>Ex:</strong> {!! $item['ex'] !!}
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Colonne droite : Astuces Kyra --}}
    <div class="col-md-4">
        <div class="card shadow-sm border-primary h-100">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0">⌬ Le coin de Kyra</h6>
            </div>
            <div class="card-body">
                <p class="small text-muted">Pour bien choisir ton modèle, regarde d'abord le <strong>rapport Contexte/Prix</strong>.</p>
                <hr>
                <ul class="small mb-0">
                    <li class="mb-2">🚀 <strong>Contexte large</strong> = idéal pour résumer des livres ou analyser des codes sources entiers.</li>
                    <li class="mb-2">🛠️ <strong>Tools support</strong> = indispensable si tu veux que l'IA agisse (recherche, calcul, BDD).</li>
                    <li class="mb-2">💸 <strong>Input vs Output</strong> : si tu envoies beaucoup de texte pour une réponse courte, privilégie un faible prix d'Input.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
