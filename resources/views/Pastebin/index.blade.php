<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Create Paste — PasteIt</title>
  <link rel="stylesheet" href="{{ asset('css/theme.css') }}">
  <style>
    /* Page-specific styles */
    .controls{
      display:flex; gap: 8px; flex-wrap: wrap; align-items: center; justify-content: space-between;
      margin-top: 8px;
    }
    .controls .left, .controls .right { display:flex; gap: 8px; align-items:center; flex-wrap: wrap; }
    
    textarea{
      min-height: 280px;
    }
  </style>
</head>
<body>
  <div class="container">
    <header>
      <div class="brand">
        <div class="logo">P</div>
        <span>PasteIt</span>
      </div>
      <nav class="row">
        <a class="badge" href="/">Home</a>
        <a class="badge" href="/explore">Explore</a>
        <a class="badge" href="/login">Sign in</a>
      </nav>
    </header>

    <section class="card">
      <div class="card-header">
        <h1>Create a new paste</h1>
        <span class="hint">Quick tip: Press <kbd>Tab</kbd> inside the editor for a real tab character.</span>
      </div>

      <form class="card-body grid grid-2" method="POST" action="{{ route('handleNewPastebin') }}" method="POST" novalidate>
        <!-- CSRF: <input type="hidden" name="_token" value="..."> -->
        @csrf
        <!-- Left: main editor -->
        <div class="stack">
          <div class="stack">
            <label for="title">Title (optional)</label>
            <input id="title" name="title" type="text" maxlength="140" placeholder="e.g., nginx.conf tweak notes" />
            <div class="hint" id="title-hint">Up to 140 characters.</div>
          </div>

          <div class="row" style="gap:12px">
            <div class="stack" style="flex:1 1 220px;">
              <label for="language">Syntax highlighting</label>
              <select id="language" name="language">
                <option value="text">Plain Text</option>
                <option value="markdown">Markdown</option>
                <option value="json">JSON</option>
                <option value="javascript">JavaScript</option>
                <option value="python">Python</option>
                <option value="php">PHP</option>
                <option value="html">HTML</option>
                <option value="css">CSS</option>
                <option value="sql">SQL</option>
                <option value="bash">Bash</option>
              </select>
            </div>

            <div class="stack" style="flex:1 1 220px;">
              <label for="expires">Expiration</label>
              <select id="expires" name="expires">
                <option value="never">Never</option>
                <option value="10min">10 minutes</option>
                <option value="1hr">1 hour</option>
                <option value="1day">1 day</option>
                <option value="1week">1 week</option>
                <option value="1month">1 month</option>
              </select>
            </div>
          </div>

          <div class="stack">
            <label for="content">Paste content</label>
            <textarea id="content" name="content" placeholder="Paste or type your code/text here…" required></textarea>
            <div class="controls">
              <div class="left">
                <span class="badge"><span id="charCount">0</span> chars</span>
                <span class="badge"><span id="lineCount">1</span> lines</span>
                <label class="switch">
                  <input id="wrapToggle" type="checkbox" checked />
                  <span>Word wrap</span>
                </label>
              </div>
              <div class="right">
                <button type="button" class="btn btn-secondary" id="pasteClipboard">Paste from clipboard</button>
                <button type="button" class="btn btn-ghost" id="clear">Clear</button>
              </div>
            </div>
          </div>
        </div>

        <!-- Right: options -->
        <aside class="stack">
          <fieldset>
            <legend>Visibility</legend>
            <div class="radio-row" role="radiogroup" aria-labelledby="vis-label">
              <span id="vis-label" class="sr-only">Visibility options</span>
              <label class="radio">
                <input type="radio" name="visibility" value="public" checked />
                Public
              </label>
              <label class="radio">
                <input type="radio" name="visibility" value="unlisted" />
                Unlisted
              </label>
              <label class="radio">
                <input type="radio" name="visibility" value="private" />
                Private
              </label>
            </div>
            <div class="stack" style="margin-top:10px">
              <label for="password">Password (optional; enabled for Private)</label>
              <input id="password" name="password" type="password" placeholder="Set a view password"
                     inputmode="latin" disabled />
              <span class="hint">If set, viewers must enter the password to open.</span>
            </div>
            <div class="row" style="margin-top:10px">
              <label class="switch">
                <input type="checkbox" id="burn" name="burn" />
                <span>Burn after first read</span>
              </label>
            </div>
          </fieldset>

          <fieldset>
            <legend>Options</legend>
            <div class="stack">
              <label class="switch">
                <input type="checkbox" id="tabToSpaces" checked />
                <span>Insert spaces for Tab (2)</span>
              </label>
              <label class="switch">
                <input type="checkbox" id="confirmLeave" checked />
                <span>Warn if leaving with unsaved changes</span>
              </label>
              <div class="hint">You can customize defaults in your account settings.</div>
            </div>
          </fieldset>

          <div class="stack">
            <button type="submit" class="btn btn-primary">Create Paste</button>
            <button type="reset" class="btn btn-ghost">Reset</button>
            <div class="hint">By creating a paste, you agree to our terms of use.</div>
          </div>
        </aside>

        <div class="footer" style="grid-column: 1 / -1;">
          <div class="hint">Pro tip: Use <code>Shift+Enter</code> to add a blank line without submitting in some browsers.</div>
          <div class="row">
            <span class="badge">Autosave: off</span>
            <span class="badge">Drafts: local only</span>
          </div>
        </div>
      </form>
    </section>
  </div>

  <script>
    // Elements
    const content = document.getElementById('content');
    const charCount = document.getElementById('charCount');
    const lineCount = document.getElementById('lineCount');
    const wrapToggle = document.getElementById('wrapToggle');
    const pasteClipboard = document.getElementById('pasteClipboard');
    const clearBtn = document.getElementById('clear');
    const visibilityRadios = document.querySelectorAll('input[name="visibility"]');
    const passwordInput = document.getElementById('password');
    const tabToSpaces = document.getElementById('tabToSpaces');
    const confirmLeave = document.getElementById('confirmLeave');

    // Update counters
    function updateCounts(){
      const val = content.value;
      charCount.textContent = val.length.toString();
      // Count lines: split on \n, ensure at least 1
      lineCount.textContent = (val.length ? val.split(/\n/).length : 1).toString();
    }

    // Autosize textarea (simple approach)
    function autosize(){
      content.style.height = 'auto';
      // Add a tiny offset to avoid scrollbars flicker
      content.style.height = (content.scrollHeight + 2) + 'px';
    }

    // Toggle wrapping
    wrapToggle.addEventListener('change', () => {
      content.style.whiteSpace = wrapToggle.checked ? 'pre-wrap' : 'pre';
      // Force reflow for autosize
      autosize();
    });

    // Clipboard paste
    pasteClipboard.addEventListener('click', async () => {
      try {
        const text = await navigator.clipboard.readText();
        // Append at cursor if selection supported, else replace
        const start = content.selectionStart ?? content.value.length;
        const end = content.selectionEnd ?? content.value.length;
        const before = content.value.slice(0, start);
        const after = content.value.slice(end);
        content.value = before + text + after;
        content.focus();
        // move cursor to end of pasted
        const caret = before.length + text.length;
        content.setSelectionRange(caret, caret);
        updateCounts();
        autosize();
      } catch (e) {
        alert('Could not access clipboard. Paste manually (Cmd/Ctrl+V).');
      }
    });

    // Clear editor
    clearBtn.addEventListener('click', () => {
      content.value = '';
      updateCounts();
      autosize();
    });

    // Enable/disable password when visibility is private
    function syncPassword(){
      const vis = document.querySelector('input[name="visibility"]:checked')?.value;
      const enable = vis === 'private';
      passwordInput.disabled = !enable;
      if (!enable) passwordInput.value = '';
    }
    visibilityRadios.forEach(r => r.addEventListener('change', syncPassword));
    syncPassword();

    // Handle Tab => spaces (2) inside textarea
    content.addEventListener('keydown', (e) => {
      if (e.key === 'Tab') {
        if (!tabToSpaces.checked) return; // allow default tab behavior if off
        e.preventDefault();
        const start = content.selectionStart;
        const end = content.selectionEnd;
        const spaces = '  ';
        content.value = content.value.substring(0, start) + spaces + content.value.substring(end);
        // move caret
        content.selectionStart = content.selectionEnd = start + spaces.length;
        updateCounts();
        autosize();
      }
    });

    // Confirm before leaving with unsaved changes
    let dirty = false;
    content.addEventListener('input', () => { dirty = true; updateCounts(); autosize(); });
    document.querySelector('form').addEventListener('submit', () => { dirty = false; });
    window.addEventListener('beforeunload', (e) => {
      if (confirmLeave.checked && dirty) {
        e.preventDefault();
        e.returnValue = '';
      }
    });

    // Initial paint
    updateCounts();
    autosize();

    // Prevent Enter submitting when focused inside textarea (native behavior is fine).
    // We do, however, avoid accidental submit if someone presses Enter on buttons without type specified.
    // Buttons already have explicit type.
  </script>
</body>
</html>
