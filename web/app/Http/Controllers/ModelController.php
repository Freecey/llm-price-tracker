<?php

namespace App\Http\Controllers;

use App\Models\Model;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class ModelController extends Controller
{
    public function index(Request $request) {
        \Illuminate\Pagination\Paginator::useBootstrapFive();
        
        $query = Model::with('priceHistory');

        // Recherche
        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function($q) use ($term) {
                $q->where('name', 'like', "%$term%")
                  ->orWhere('openrouter_id', 'like', "%$term%")
                  ->orWhere('provider_name', 'like', "%$term%");
            });
        }

        // Filtre Provider
        if ($request->filled('provider')) {
            $query->where('provider_name', $request->provider);
        }

        // Filtre Modality
        if ($request->filled('modality')) {
            $query->where('modality', 'like', "%{$request->modality}%");
        }

        // Tri
        $sortField = $request->get('sort', 'name');
        $sortDir = $request->get('dir', 'asc');
        $allowedSorts = ['name', 'provider_name', 'context_length', 'created_at'];
        
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDir);
        }

        $models = $query->paginate(20)->withQueryString();
        
        // Récupérer les listes pour les filtres
        $providers = Model::select('provider_name')->distinct()->orderBy('provider_name')->pluck('provider_name');
        $modalities = Model::select('modality')->distinct()->orderBy('modality')->pluck('modality');

        return view('models.index', compact('models', 'providers', 'modalities', 'sortField', 'sortDir'));
    }

    public function show($id) {
        $model = Model::findOrFail($id);
        $history = $model->priceHistory()->orderBy('timestamp')->get();
        return view('models.show', compact('model', 'history'));
    }
}
