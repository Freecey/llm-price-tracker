<?php

namespace App\Http\Controllers;

use App\Models\Model;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class ModelController extends Controller
{
    public function index(Request $request) {
        \Illuminate\Pagination\Paginator::useBootstrapFive();
        
        // Modèle du jour (aléatoire, basé sur la date)
        $todaySeed = crc32(date('Y-m-d'));
        $totalModels = Model::count();
        $modelOfDayId = ($todaySeed % $totalModels) + 1;
        $modelOfDay = Model::with('priceHistory')->find($modelOfDayId);
        
        // Kyra's Picks — scoring intelligent
        $kyraPicks = Model::with('priceHistory')
            ->get()
            ->map(function($model) {
                $latest = $model->priceHistory->sortByDesc('timestamp')->first();
                if (!$latest || $latest->input_price_per_m <= 0) return null;
                
                $pricePerContext = $model->context_length > 0 
                    ? $latest->input_price_per_m / ($model->context_length / 1000) 
                    : PHP_FLOAT_MAX;
                
                // Score: bas prix (40%), contexte élevé (30%), tools support (20%), provider réputé (10%)
                $priceScore = max(0, 100 - ($latest->input_price_per_m * 1000)); // 0-100
                $contextScore = min(100, ($model->context_length / 200000) * 100); // 0-100
                $toolsScore = $model->supports_tools ? 100 : 0;
                $providerScore = in_array($model->provider_name, ['openai', 'anthropic', 'google', 'qwen', 'meta-llama']) ? 100 : 50;
                
                $totalScore = ($priceScore * 0.4) + ($contextScore * 0.3) + ($toolsScore * 0.2) + ($providerScore * 0.1);
                
                return [
                    'model' => $model,
                    'score' => round($totalScore, 1),
                    'price_per_k_context' => round($pricePerContext, 4),
                    'latest_price' => $latest,
                ];
            })
            ->filter()
            ->sortByDesc('score')
            ->take(5)
            ->values();
        
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
        
        // Filtre Tools
        if ($request->filled('tools')) {
            $query->where('supports_tools', $request->tools === '1' ? 1 : 0);
        }

        // Tri
        $sortField = $request->get('sort', 'name');
        $sortDir = $request->get('dir', 'asc');
        $allowedSorts = ['name', 'provider_name', 'context_length', 'created_at', 'input_price', 'output_price'];
        
        if (in_array($sortField, $allowedSorts)) {
            if ($sortField === 'input_price' || $sortField === 'output_price') {
                // Tri par prix (nécessite une jointure avec la dernière entrée d'historique)
                $priceField = $sortField === 'input_price' ? 'input_price_per_m' : 'output_price_per_m';
                $query->leftJoinSub(
                    \DB::table('model_prices_history')
                        ->select('model_id', $priceField)
                        ->whereIn('id', function($sub) use ($priceField) {
                            $sub->selectRaw('MAX(id)')
                                ->from('model_prices_history')
                                ->groupBy('model_id');
                        }),
                    'latest_prices',
                    'models.id',
                    '=',
                    'latest_prices.model_id'
                )->orderBy("latest_prices.{$priceField}", $sortDir === 'desc' ? 'desc' : 'asc');
            } else {
                $query->orderBy($sortField, $sortDir);
            }
        }

        $perPage = $request->get('per_page', 20);
        $allowedPerPages = [10, 20, 50, 100, 200, 500];
        if (!in_array($perPage, $allowedPerPages)) {
            $perPage = 20;
        }
        
        $models = $query->paginate($perPage)->withQueryString();
        
        // Récupérer les listes pour les filtres
        $providers = Model::select('provider_name')->distinct()->orderBy('provider_name')->pluck('provider_name');
        $modalities = Model::select('modality')->distinct()->orderBy('modality')->pluck('modality');

        // Stats rapides (exclure les prix négatifs/nuls)
        $allModels = Model::with('priceHistory')->get();
        $validPrices = $allModels
            ->map(fn($m) => $m->priceHistory->last()?->input_price_per_m)
            ->filter(fn($p) => $p !== null && $p > 0);
        
        $quickStats = [
            'total_models' => Model::count(),
            'total_providers' => Model::distinct('provider_name')->count(),
            'cheapest_avg' => $validPrices->isNotEmpty() ? $validPrices->avg() : 0,
            'most_expensive_avg' => $validPrices->sortDesc()->take(10)->avg() ?? 0,
        ];

        return view('models.index', compact('models', 'providers', 'modalities', 'sortField', 'sortDir', 'modelOfDay', 'quickStats', 'kyraPicks'));
    }

    public function show($id) {
        $model = Model::findOrFail($id);
        $history = $model->priceHistory()->orderBy('timestamp')->get();
        
        // Modèles similaires (même provider ou contexte similaire)
        $similarModels = Model::with('priceHistory')
            ->where('id', '!=', $model->id)
            ->where(function($q) use ($model) {
                $q->where('provider_name', $model->provider_name)
                  ->orWhereBetween('context_length', [
                      $model->context_length * 0.5,
                      $model->context_length * 1.5
                  ]);
            })
            ->limit(5)
            ->get();
        
        return view('models.show', compact('model', 'history', 'similarModels'));
    }

    public function compare(Request $request) {
        \Illuminate\Pagination\Paginator::useBootstrapFive();
        
        // Supporter ids[] en array ou ids=comma,separated
        $ids = $request->input('ids', []);
        if (is_string($ids)) {
            $ids = array_filter(explode(',', $ids));
        }
        
        if (empty($ids)) {
            return redirect()->route('models.index')->with('error', 'Sélectionnez au moins 2 modèles à comparer.');
        }

        $models = Model::with('priceHistory')->whereIn('id', $ids)->get();
        
        if ($models->isEmpty()) {
            return redirect()->route('models.index')->with('error', 'Aucun modèle trouvé.');
        }

        // Préparer les données pour Chart.js (éviter les closures dans Blade)
        $modelsData = $models->map(function($m) {
            return [
                'id' => $m->id,
                'name' => $m->name,
                'provider' => $m->provider_name,
                'history' => $m->priceHistory->map(function($h) {
                    return [
                        'timestamp' => $h->timestamp,
                        'input' => (float)$h->input_price_per_m,
                        'output' => (float)$h->output_price_per_m,
                    ];
                })->toArray()
            ];
        })->toArray();

        return view('models.compare', compact('models', 'modelsData'));
    }

    public function dashboard() {
        // Top 10 modèles les moins chers (par prix input)
        $cheapestModels = Model::with('priceHistory')
            ->has('priceHistory')
            ->get()
            ->map(function($model) {
                $latest = $model->priceHistory->sortByDesc('timestamp')->first();
                return [
                    'model' => $model,
                    'input_price' => $latest->input_price_per_m,
                    'output_price' => $latest->output_price_per_m,
                ];
            })
            ->sortBy('input_price')
            ->take(10)
            ->values();

        // Évolution moyenne par provider
        $providerStats = Model::with('priceHistory')
            ->has('priceHistory')
            ->get()
            ->groupBy('provider_name')
            ->map(function($group) {
                $latestPrices = $group->map(function($model) {
                    return $model->priceHistory->sortByDesc('timestamp')->first();
                })->filter();
                
                return [
                    'count' => $group->count(),
                    'avg_input' => $latestPrices->avg('input_price_per_m') ?? 0,
                    'avg_output' => $latestPrices->avg('output_price_per_m') ?? 0,
                ];
            })->sortByDesc('avg_input');

        // Répartition des modalités
        $modalityCounts = Model::selectRaw('modality, COUNT(*) as count')
            ->groupBy('modality')
            ->orderByDesc('count')
            ->get();

        // Heatmap des changements récents (7 derniers jours)
        $recentChanges = \DB::table('model_prices_history')
            ->join('models', 'model_prices_history.model_id', '=', 'models.id')
            ->select(
                'models.name',
                'models.provider_name',
                'model_prices_history.input_price_per_m',
                'model_prices_history.output_price_per_m',
                'model_prices_history.change_type',
                'model_prices_history.timestamp'
            )
            ->where('model_prices_history.timestamp', '>=', now()->subDays(7))
            ->orderByDesc('model_prices_history.timestamp')
            ->limit(30)
            ->get();

        return view('models.dashboard', compact(
            'cheapestModels',
            'providerStats',
            'modalityCounts',
            'recentChanges'
        ));
    }

    public function providers() {
        // Stats par provider avec détails
        $providerDetails = Model::with('priceHistory')
            ->has('priceHistory')
            ->get()
            ->groupBy('provider_name')
            ->map(function($group) {
                $latestPrices = $group->map(function($model) {
                    return $model->priceHistory->sortByDesc('timestamp')->first();
                })->filter();
                
                $cheapestModel = $group->sortBy(function($model) {
                    $latest = $model->priceHistory->sortByDesc('timestamp')->first();
                    return $latest ? $latest->input_price_per_m : PHP_FLOAT_MAX;
                })->first();
                
                return [
                    'count' => $group->count(),
                    'avg_input' => $latestPrices->avg('input_price_per_m') ?? 0,
                    'avg_output' => $latestPrices->avg('output_price_per_m') ?? 0,
                    'min_input' => $latestPrices->min('input_price_per_m') ?? 0,
                    'max_input' => $latestPrices->max('input_price_per_m') ?? 0,
                    'cheapest_model' => $cheapestModel,
                    'models' => $group->sortByDesc(function($m) {
                        $latest = $m->priceHistory->sortByDesc('timestamp')->first();
                        return $latest ? $latest->input_price_per_m : 0;
                    })->take(5),
                ];
            })->sortBy('avg_input');

        // Évolution temporelle par provider (7 derniers jours)
        $providerTrends = \DB::table('model_prices_history')
            ->join('models', 'model_prices_history.model_id', '=', 'models.id')
            ->select(
                'models.provider_name',
                \DB::raw('DATE(model_prices_history.timestamp) as date'),
                \DB::raw('AVG(model_prices_history.input_price_per_m) as avg_input'),
                \DB::raw('AVG(model_prices_history.output_price_per_m) as avg_output'),
                \DB::raw('COUNT(DISTINCT model_prices_history.model_id) as model_count')
            )
            ->where('model_prices_history.timestamp', '>=', now()->subDays(30))
            ->groupBy('models.provider_name', \DB::raw('DATE(model_prices_history.timestamp)'))
            ->orderBy('date')
            ->get()
            ->groupBy('provider_name');
        
        // Stats tools par provider
        $toolsStats = Model::selectRaw('provider_name, SUM(supports_tools) as with_tools, COUNT(*) as total')
            ->groupBy('provider_name')
            ->get()
            ->mapWithKeys(fn($row) => [$row->provider_name => [
                'with_tools' => $row->with_tools,
                'total' => $row->total,
                'pct' => round(($row->with_tools / $row->total) * 100, 1)
            ]]);

        return view('models.providers', compact('providerDetails', 'providerTrends', 'toolsStats'));
    }

    public function trends() {
        // Changements de prix significatifs (30 derniers jours)
        $significantChanges = \DB::table('model_prices_history')
            ->join('models', 'model_prices_history.model_id', '=', 'models.id')
            ->select(
                'models.name',
                'models.provider_name',
                'models.openrouter_id',
                'model_prices_history.input_price_per_m',
                'model_prices_history.output_price_per_m',
                'model_prices_history.change_type',
                'model_prices_history.timestamp'
            )
            ->where('model_prices_history.timestamp', '>=', now()->subDays(30))
            ->orderByDesc('model_prices_history.timestamp')
            ->get();

        // Tendances par modèle (top 20 avec le plus d'historique)
        $modelTrends = Model::with(['priceHistory' => function($q) {
            $q->orderBy('timestamp');
        }])
            ->has('priceHistory', '>=', 3) // Au moins 3 enregistrements
            ->get()
            ->map(function($model) {
                $history = $model->priceHistory;
                if ($history->count() < 2) return null;
                
                $first = $history->first();
                $last = $history->last();
                
                $inputChange = $first->input_price_per_m > 0 
                    ? (($last->input_price_per_m - $first->input_price_per_m) / $first->input_price_per_m) * 100 
                    : 0;
                
                $outputChange = $first->output_price_per_m > 0 
                    ? (($last->output_price_per_m - $first->output_price_per_m) / $first->output_price_per_m) * 100 
                    : 0;
                
                return [
                    'model' => $model,
                    'input_change_pct' => round($inputChange, 2),
                    'output_change_pct' => round($outputChange, 2),
                    'first_price' => $first,
                    'last_price' => $last,
                    'history_count' => $history->count(),
                ];
            })
            ->filter()
            ->sortByDesc(function($item) {
                return abs($item['input_change_pct']);
            })
            ->take(20)
            ->values();

        // Timeline des changements
        $timeline = $significantChanges
            ->groupBy(fn($c) => \Carbon\Carbon::parse($c->timestamp)->format('Y-m-d'))
            ->sortKeysDesc()
            ->take(14);

        return view('models.trends', compact(
            'significantChanges',
            'modelTrends',
            'timeline'
        ));
    }

    public function export(Request $request) {
        $format = $request->get('format', 'csv');
        
        $models = Model::with('priceHistory')->get()->map(function($model) {
            $latest = $model->priceHistory->sortByDesc('timestamp')->first();
            return [
                'id' => $model->id,
                'name' => $model->name,
                'openrouter_id' => $model->openrouter_id,
                'provider' => $model->provider_name,
                'context_length' => $model->context_length,
                'max_tokens' => $model->max_tokens,
                'modality' => $model->modality,
                'supports_tools' => $model->supports_tools ? 'Yes' : 'No',
                'input_price' => $latest ? $latest->input_price_per_m : null,
                'output_price' => $latest ? $latest->output_price_per_m : null,
                'last_updated' => $latest ? $latest->timestamp->toIso8601String() : null,
            ];
        });

        if ($format === 'json') {
            return response()->json($models, 200, [
                'Content-Type' => 'application/json',
                'Content-Disposition' => 'attachment; filename="llm-prices-' . date('Y-m-d') . '.json"',
            ]);
        }

        // CSV export
        $csv = fopen('php://temp', 'r+');
        fputcsv($csv, array_keys($models->first() ?? []));
        foreach ($models as $model) {
            fputcsv($csv, $model);
        }
        rewind($csv);
        $csvContent = stream_get_contents($csv);
        fclose($csv);

        return response($csvContent, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="llm-prices-' . date('Y-m-d') . '.csv"',
        ]);
    }

    public function apiSearch(Request $request) {
        $query = $request->get('q', '');
        
        if (strlen($query) < 2) {
            return response()->json([]);
        }
        
        $models = Model::where(function($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('openrouter_id', 'like', "%{$query}%")
                  ->orWhere('provider_name', 'like', "%{$query}%");
            })
            ->limit(10)
            ->get(['id', 'name', 'openrouter_id', 'provider_name']);
        
        return response()->json($models);
    }

    public function alerts(Request $request) {
        $threshold = $request->get('threshold', 10);
        $days = $request->get('days', 7);
        
        // Comparer les prix actuels avec ceux d'il y a X jours
        $priceDrops = \DB::table('model_prices_history as curr')
            ->join('models', 'curr.model_id', '=', 'models.id')
            ->joinSub(
                \DB::table('model_prices_history')
                    ->select('model_id', 'input_price_per_m', 'output_price_per_m', 'timestamp')
                    ->whereRaw('timestamp BETWEEN NOW() - INTERVAL ? DAY AND NOW() - INTERVAL ? DAY', [$days + 1, $days - 1]),
                'prev',
                'curr.model_id',
                '=',
                'prev.model_id'
            )
            ->select(
                'models.id',
                'models.name',
                'models.openrouter_id',
                'models.provider_name',
                'models.supports_tools',
                'curr.input_price_per_m as current_input',
                'curr.output_price_per_m as current_output',
                'prev.input_price_per_m as prev_input',
                'prev.output_price_per_m as prev_output',
                'curr.timestamp as current_ts',
                'prev.timestamp as prev_ts'
            )
            ->where('curr.timestamp', '>=', now()->subHours(24))
            ->where('curr.input_price_per_m', '>', 0)
            ->where('prev.input_price_per_m', '>', 0)
            ->get()
            ->map(function($row) use ($threshold) {
                $inputDrop = $row->prev_input > 0 
                    ? (($row->prev_input - $row->current_input) / $row->prev_input) * 100 
                    : 0;
                $outputDrop = $row->prev_output > 0 
                    ? (($row->prev_output - $row->current_output) / $row->prev_output) * 100 
                    : 0;
                
                return [
                    'model' => $row,
                    'input_drop_pct' => round($inputDrop, 2),
                    'output_drop_pct' => round($outputDrop, 2),
                    'max_drop' => max($inputDrop, $outputDrop),
                ];
            })
            ->filter(fn($item) => $item['max_drop'] >= $threshold)
            ->sortByDesc('max_drop')
            ->values();
        
        return view('models.alerts', compact('priceDrops', 'threshold', 'days'));
    }

    public function tools(Request $request) {
        \Illuminate\Pagination\Paginator::useBootstrapFive();
        
        $withTools = $request->get('with_tools', '1') === '1';
        
        $query = Model::with('priceHistory')
            ->where('supports_tools', $withTools ? 1 : 0);
        
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
        
        // Tri
        $sortField = $request->get('sort', 'name');
        $sortDir = $request->get('dir', 'asc');
        $allowedSorts = ['name', 'provider_name', 'context_length', 'input_price', 'output_price'];
        
        if (in_array($sortField, $allowedSorts)) {
            if ($sortField === 'input_price' || $sortField === 'output_price') {
                $priceField = $sortField === 'input_price' ? 'input_price_per_m' : 'output_price_per_m';
                $query->leftJoinSub(
                    \DB::table('model_prices_history')
                        ->select('model_id', $priceField)
                        ->whereIn('id', function($sub) {
                            $sub->selectRaw('MAX(id)')
                                ->from('model_prices_history')
                                ->groupBy('model_id');
                        }),
                    'latest_prices',
                    'models.id',
                    '=',
                    'latest_prices.model_id'
                )->orderBy("latest_prices.{$priceField}", $sortDir === 'desc' ? 'desc' : 'asc');
            } else {
                $query->orderBy($sortField, $sortDir);
            }
        }
        
        $perPage = $request->get('per_page', 30);
        $allowedPerPages = [10, 30, 50, 100, 200];
        if (!in_array($perPage, $allowedPerPages)) {
            $perPage = 30;
        }
        
        $models = $query->paginate($perPage)->withQueryString();
        
        $providers = Model::where('supports_tools', $withTools ? 1 : 0)
            ->distinct('provider_name')
            ->orderBy('provider_name')
            ->pluck('provider_name');
        
        // Stats
        $totalWithTools = Model::where('supports_tools', 1)->count();
        $totalWithoutTools = Model::where('supports_tools', 0)->count();
        $topProviders = Model::selectRaw('provider_name, COUNT(*) as cnt')
            ->where('supports_tools', 1)
            ->groupBy('provider_name')
            ->orderByDesc('cnt')
            ->limit(5)
            ->get();
        
        return view('models.tools', compact(
            'models', 'providers', 'sortField', 'sortDir',
            'withTools', 'totalWithTools', 'totalWithoutTools', 'topProviders'
        ));
    }

    public function randomModel() {
        $model = Model::inRandomOrder()->first();
        return response()->json($model ? ['id' => $model->id] : null);
    }

    public function about(Request $request) {
        // Stats funs
        $stats = [
            'total_models' => Model::count(),
            'total_providers' => Model::distinct('provider_name')->count(),
            'total_history_entries' => \DB::table('model_prices_history')->count(),
            'cheapest_model' => Model::with('priceHistory')
                ->get()
                ->map(function($m) {
                    return [
                        'model' => $m,
                        'price' => $m->priceHistory->last()?->input_price_per_m ?? PHP_FLOAT_MAX
                    ];
                })
                ->filter(function($m) {
                    return $m['price'] > 0;
                })
                ->sortBy('price')
                ->first(),
            'most_expensive_model' => Model::with('priceHistory')
                ->get()
                ->map(function($m) {
                    return [
                        'model' => $m,
                        'price' => $m->priceHistory->last()?->input_price_per_m ?? 0
                    ];
                })
                ->sortByDesc('price')
                ->first(),
            'biggest_context' => Model::orderByDesc('context_length')->first(),
            'providers_with_tools' => Model::where('supports_tools', 1)
                ->distinct('provider_name')
                ->count(),
            'avg_context_length' => round(Model::avg('context_length') ?? 0),
            'total_sync_runs' => \DB::table('model_prices_history')
                ->selectRaw('COUNT(DISTINCT DATE(timestamp)) as days')
                ->value('days'),
        ];
        
        return view('about', compact('stats'));
    }
}
