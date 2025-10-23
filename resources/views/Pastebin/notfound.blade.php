<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Paste Not Found — Pastebin.lite</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" href="{{ asset('css/theme.css') }}">
  <style>
    /* Page-specific styles */
    .error-card {
      text-align: center;
      max-width: 580px;
      margin: 0 auto;
      padding: var(--spacing-xl);
    }
    .error-emoji {
      font-size: 56px;
      margin-bottom: var(--spacing-lg);
    }
    .error-title {
      font-size: 2rem;
      margin: 0 0 var(--spacing-md);
      color: var(--danger);
    }
    .error-message {
      color: var(--text-soft);
      font-size: 1rem;
      line-height: 1.6;
      margin-bottom: var(--spacing-xl);
    }
    .error-actions {
      display: flex;
      justify-content: center;
      gap: var(--spacing-md);
      flex-wrap: wrap;
    }
  </style>
</head>
<body>
  <div class="container">
    <header>
      <a class="brand" href="/">
        <div class="logo">P</div>
        <span>Pastebin<span class="muted">.lite</span></span>
      </a>
      <nav class="row">
        <a class="badge" href="/">Home</a>
        <a class="badge" href="/explore">Explore</a>
        <a class="badge" href="/login">Sign in</a>
      </nav>
    </header>

    <main class="card">
      <div class="error-card">
        <div class="error-emoji">🚫</div>
        <h2 class="error-title">Paste Not Found</h2>
        <p class="error-message">
          The paste you're trying to access either doesn't exist, has expired, or has been deleted by its creator.<br><br>
          Double-check the URL or create a new paste instead.
        </p>
        <div class="error-actions">
          <a class="btn btn-primary" href="{{ route('new') }}">Create New Paste</a>
        </div>
      </div>
    </main>
  </div>
</body>
</html>
