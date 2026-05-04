<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page Not Found</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background: linear-gradient(135deg, #e8f0fe 0%, #f0f4ff 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; font-family: 'Inter', sans-serif; }
        .error-card { text-align: center; max-width: 500px; }
        .error-code { font-size: 8rem; font-weight: 800; color: #1e293b; line-height: 1; opacity: .15; }
        .error-icon { font-size: 4rem; color: #3b82f6; margin-bottom: 1rem; }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="error-code">404</div>
        <div class="error-icon"><i class="bi bi-search"></i></div>
        <h2 class="fw-bold mb-3">Page Not Found</h2>
        <p class="text-muted mb-4">The page you're looking for doesn't exist or has been moved.</p>
        <a href="{{ url('/dashboard') }}" class="btn btn-primary"><i class="bi bi-house me-1"></i>Back to Dashboard</a>
    </div>
</body>
</html>
