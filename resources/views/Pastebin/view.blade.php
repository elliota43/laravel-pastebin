<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>{{$pastebin->title}} — Paste</title>
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <link rel="stylesheet" href="{{ asset('css/theme.css') }}">
  <style>
    /* Page-specific styles */
    .meta {
      display: grid; grid-template-columns: 1fr auto; gap: 16px; padding: 18px 18px 0 18px;
    }
    .title-line { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
    .title-line h2 { margin: 0; font-size: 20px; }
    .meta-grid {
      display: grid; grid-template-columns: repeat(4, minmax(0,1fr));
      gap: 10px; margin-top: 10px; font-size: 13px; color: var(--muted);
    }
    .meta-item { border: 1px solid var(--border); border-radius: 10px; padding: 10px 12px; background: var(--bg-soft); }
    .meta-item b { display: block; color: var(--text); font-weight: 600; margin-bottom: 4px; }

    @media (max-width: 760px) {
      .meta-grid { grid-template-columns: 1fr 1fr; }
    }
  </style>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/prismjs@1/themes/prism-tomorrow.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/prismjs@1/plugins/line-numbers/prism-line-numbers.css">
  <script defer src="https://cdn.jsdelivr.net/npm/prismjs@1/components/prism-core.min.js"></script>
  <script defer src="https://cdn.jsdelivr.net/npm/prismjs@1/plugins/autoloader/prism-autoloader.min.js"></script>
  <script defer src="https://cdn.jsdelivr.net/npm/prismjs@1/plugins/line-numbers/prism-line-numbers.min.js"></script>

</head>
<body>
  <div class="container">
    <header>
      <a class="brand" href="/">
        <div class="logo"></div>
        <h1>Pastebin<span class="muted">.lite</span></h1>
      </a>
      <div class="actions">
        <a class="btn btn-ghost" href="/new">New Paste</a>
        <button class="btn btn-secondary" id="copyBtn" type="button">Copy</button>
        <a class="btn btn-secondary" id="rawBtn" href="/p/{{$pastebin->hash}}/raw" rel="nofollow">View Raw</a>
        <a class="btn btn-secondary" id="downloadBtn" href="/p/{{$pastebin->hash}}/download">Download</a>
        <a class="btn btn-primary" href="/p/{{$pastebin->hash}}/fork">Fork</a>
      </div>
    </header>

    <main class="card" aria-labelledby="paste-title">
      <!-- Meta / Title -->
      <section class="meta">
        <div class="title-line">
          <h2 id="paste-title">{{$pastebin->title || ''}}</h2>
          <span class="badge badge-primary" id="langBadge">{{$pastebin->language}}</span>
          <span class="badge" title="Visibility">{{$pastebin->visibility}}</span>
        </div>
        <div style="text-align:right; align-self:center; color: var(--muted); font-size: 13px;">
          <span>Size: <b id="size">{{$pastebin->size()}}</b></span>
        </div>

        <div class="meta-grid">
          <div class="meta-item">
            <b>Author</b>
            <span id="author">Anonymous</span>
          </div>
          <div class="meta-item">
            <b>Created</b>
            <time id="created" datetime="{{$pastebin->getCreatedAt()}}">{{$pastebin->getCreatedAt()}}</time>
          </div>
          <div class="meta-item">
            <b>Expires</b>
            <time id="expires" datetime="{{$pastebin->getExpiresAt()}}">{{$pastebin->getExpiresAt()}}</time>
          </div>
          <div class="meta-item">
            <b>Lines</b>
            <span id="lineCount"></span>
          </div>
        </div>
      </section>

      <!-- Toolbar -->
      <div class="toolbar">
        <label class="muted" style="display:flex; align-items:center; gap:8px; cursor:pointer;">
          <input type="checkbox" id="toggleLines" checked /> Show line numbers
        </label>
        <div class="spacer"></div>
        <span class="muted">Syntax: <b id="syntaxName">{{$pastebin->language}}</b></span>
      </div>

      <!-- Code -->
      <section class="code-wrap">
        <div class="code-scroll" id="codeScroll">
          <div class="code">
            <div class="gutter" id="gutter" aria-hidden="true"></div>
            <pre><code class="language-{{ $pastebin->language }}" id="codeBlock" class="language-{{$pastebin->language}}">{{{$pastebin->getContent()}}}</code></pre>
          </div>
        </div>
      </section>

      <!-- Footer -->
      <footer class="footer">
        <div>
          <span class="muted">Slug:</span> <code style="font-family:var(--mono)">{{$pastebin->hash}}</code>
        </div>
        <div style="display:flex; gap:12px;">
          <a href="/report/{{$pastebin->hash}}">Report</a>
          <a href="/embed/{{$pastebin->hash}}">Embed</a>
          <a href="/history/{{$pastebin->hash}}">History</a>
        </div>
      </footer>
    </main>
  </div>

  <script>
    // --- Populate line numbers based on content ---
    const codeBlock = document.getElementById('codeBlock');
    const gutter = document.getElementById('gutter');
    const toggleLines = document.getElementById('toggleLines');
    const copyBtn = document.getElementById('copyBtn');

    function buildGutter() {
      const text = codeBlock.textContent || '';
      const lines = text.split('\n').length;
      const frag = document.createDocumentFragment();
      for (let i = 0; i < lines; i++) {
        const d = document.createElement('div');
        frag.appendChild(d);
      }
      gutter.innerHTML = '';
      gutter.appendChild(frag);
      document.getElementById('lineCount') && (document.getElementById('lineCount').textContent = lines);
    }

    // Toggle line numbers visibility
    toggleLines.addEventListener('change', () => {
      gutter.style.display = toggleLines.checked ? 'block' : 'none';
    });

    // Copy to clipboard
    copyBtn.addEventListener('click', async () => {
      try {
        await navigator.clipboard.writeText(codeBlock.textContent);
        copyBtn.textContent = 'Copied!';
        setTimeout(() => (copyBtn.textContent = 'Copy'), 1200);
      } catch {
        copyBtn.textContent = 'Copy failed';
        copyBtn.classList.add('btn-danger');
      }
    });

    // Rebuild gutter on load; if you inject content dynamically, call this again.
    buildGutter();

    // Optional: very lightweight "syntax highlight" for a few tokens (demo only).
    // Replace with Prism.js/Highlight.js serverside or clientside for real highlighting.
    (function demoHighlight() {
      const lang = '{{$pastebin->language}}'.toLowerCase();
      if (!['js','javascript','json','html','css','python','php'].some(k => lang.includes(k))) return;
      let html = codeBlock.textContent
        .replace(/(&)/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;');

      if (lang.includes('js') || lang.includes('javascript')) {
        html = html
          .replace(/\b(const|let|var|function|return|if|else|for|while|switch|case|break|continue|class|new|try|catch|finally|throw)\b/g, '<span style="color:#93c5fd">$1</span>')
          .replace(/("[^"]*"|'[^']*')/g, '<span style="color:#fca5a5">$1</span>')
          .replace(/(\/\/.*?$)/gm, '<span style="color:#94a3b8">$1</span>');
      }
      if (lang.includes('html')) {
        html = html
          .replace(/(&lt;\/?[a-z-]+)(.*?)(\/?&gt;)/g, '<span style="color:#93c5fd">$1</span>$2<span style="color:#93c5fd">$3</span>')
          .replace(/("[^"]*"|'[^']*')/g, '<span style="color:#fca5a5">$1</span>');
      }
      if (lang.includes('css')) {
        html = html
          .replace(/([a-z-]+)\s*:/g, '<span style="color:#93c5fd">$1</span>:')
          .replace(/("[^"]*"|'[^']*')/g, '<span style="color:#fca5a5">$1</span>')
          .replace(/(\/\*[\s\S]*?\*\/)/g, '<span style="color:#94a3b8">$1</span>');
      }
      if (lang.includes('python')) {
        html = html
          .replace(/\b(def|return|if|else|elif|for|while|class|try|except|finally|import|from|as|with|lambda|yield)\b/g, '<span style="color:#93c5fd">$1</span>')
          .replace(/(#.*?$)/gm, '<span style="color:#94a3b8">$1</span>')
          .replace(/("[^"]*"|'[^']*')/g, '<span style="color:#fca5a5">$1</span>');
      }
      if (lang.includes('php')) {
        html = html
          .replace(/(&lt;\?php|\?&gt;)/g, '<span style="color:#93c5fd">$1</span>')
          .replace(/\b(function|return|if|else|elseif|for|foreach|while|class|new|try|catch|finally|throw)\b/g, '<span style="color:#93c5fd">$1</span>')
          .replace(/(\/\/.*?$|#.*?$)/gm, '<span style="color:#94a3b8">$1</span>')
          .replace(/("[^"]*"|'[^']*')/g, '<span style="color:#fca5a5">$1</span>');
      }

      codeBlock.innerHTML = html;
    })();
  </script>
</body>
</html>
