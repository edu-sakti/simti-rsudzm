<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{ $title ?? 'Error' }}</title>
  <style>
    body {
      margin: 0;
      min-height: 100vh;
      background-color: #222e3c;
      color: #00ff55;
      font-family: "Courier New", Courier, monospace;
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 16px;
      box-sizing: border-box;
    }
    .terminal {
      font-size: clamp(14px, 2vw, 18px);
      color: #00ff55;
      white-space: pre-wrap;
      word-break: break-word;
      line-height: 1.6;
      text-align: left;
      padding: clamp(16px, 4vw, 24px) clamp(24px, 5vw, 40px);
      border-left: 4px solid #00ff55;
      box-shadow: inset 0 0 20px rgba(0,255,85,0.15);
      max-width: 960px;
      width: 100%;
      max-height: 90vh;
      overflow: auto;
      border-radius: 10px;
      box-sizing: border-box;
      background: rgba(0,0,0,0.15);
    }
    .prompt { color: #00cc44; margin-right: 8px; }
    .cursor {
      display: inline-block;
      width: 10px; height: 20px;
      background-color: #00ff55;
      animation: blink 0.8s infinite;
      vertical-align: bottom;
    }
    @keyframes blink { 0%,50%{opacity:1;} 50.01%,100%{opacity:0;} }
  </style>
</head>
<body>
  <div class="terminal" id="terminal"></div>

  <script>
    const terminal = document.getElementById("terminal");
    const lines = @json($lines ?? []);
    let index = 0, charIndex = 0;
    const typingSpeed = 35;

    function typeLine() {
      if (index < lines.length) {
        const line = lines[index];
        if (charIndex === 0)
          terminal.innerHTML += "<span class='prompt'>server@simti.rsudzm:~$</span> ";
        if (charIndex < line.length) {
          terminal.innerHTML += line.charAt(charIndex);
          charIndex++;
          setTimeout(typeLine, typingSpeed);
        } else {
          terminal.innerHTML += "\n";
          charIndex = 0;
          index++;
          setTimeout(typeLine, 300);
        }
      } else {
        terminal.innerHTML += "<span class='prompt'>server@simti.rsudzm:~$</span> <span class='cursor'></span>";
        document.addEventListener("keydown", e => {
          if (e.key === "Enter") window.location.href = "{{ url('/') }}";
        });
      }
    }

    window.addEventListener("load", () => {
      setTimeout(typeLine, 200);
    });
  </script>
</body>
</html>
