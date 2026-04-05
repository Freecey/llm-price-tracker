<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kyra's LLM Tracker ⌬</title>
    <link rel="icon" type="image/png" href="/img/favicon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background-color: #f0f2f5;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .navbar {
            background: linear-gradient(90deg, #1a1a1a 0%, #333 100%);
            border-bottom: 2px solid #0d6efd;
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }
        .table th a {
            color: #333;
            transition: color 0.2s;
        }
        .table th a:hover {
            color: #0d6efd;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="{{ route('models.index') }}">
                <img src="/img/favicon.png" width="30" height="30" class="me-2 rounded-circle" alt="Kyra">
                <span class="fw-bold">Kyra's LLM Tracker ⌬</span>
            </a>
        </div>
    </nav>
    <main class="container my-4">
        @yield('content')
    </main>
    @yield('scripts')
</body>
</html>
