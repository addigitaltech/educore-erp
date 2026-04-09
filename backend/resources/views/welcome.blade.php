<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduCore ERP API</title>
    <style>
        body{font-family:-apple-system,sans-serif;background:#f5f7fa;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0}
        .card{background:#fff;border-radius:12px;padding:40px;text-align:center;max-width:440px;box-shadow:0 4px 20px rgba(0,0,0,0.08);border:1px solid #e2e8f0}
        h1{font-size:22px;font-weight:800;color:#0f172a;margin:12px 0 6px}
        p{color:#64748b;font-size:13px;margin-bottom:20px}
        .badge{background:#eff6ff;color:#2563eb;padding:4px 14px;border-radius:20px;font-size:12px;font-weight:700}
        .ep{background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;padding:8px 14px;margin-top:8px;font-family:monospace;font-size:12px;color:#475569;text-align:left}
    </style>
</head>
<body>
<div class="card">
    <div style="font-size:44px">🎓</div>
    <h1>EduCore ERP</h1>
    <p>Enterprise School Management System API</p>
    <span class="badge">✅ API Running</span>
    <div class="ep">POST /api/auth/login</div>
    <div class="ep">GET  /api/dashboard</div>
    <div class="ep">GET  /up  →  health check</div>
    <p style="margin-top:16px;font-size:11px;color:#94a3b8">Laravel {{ app()->version() }} · PHP {{ PHP_VERSION }}</p>
</div>
</body>
</html>
