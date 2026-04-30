<!DOCTYPE html>
<html lang="en">
<head>
   <title>AI Assistant</title>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <meta name="csrf-token" content="{{ csrf_token() }}">
   <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500&display=swap" rel="stylesheet">

   <style>
      *, *::before, *::after {
         box-sizing: border-box;
         margin: 0;
         padding: 0;
      }

      :root {
         --bg:       #08080f;
         --surface:  #10101c;
         --surface2: #16162a;
         --surface3: #1d1d33;
         --border:   rgba(255,255,255,0.07);
         --border2:  rgba(255,255,255,0.13);
         --accent:   #7c6aff;
         --accent2:  #ff6ab0;
         --accent3:  #6af0ff;
         --text:     #eeeeff;
         --text2:    #9997bb;
         --muted:    #4a4868;
         --green:    #4ade80;
         --error:    #ff6b6b;
      }

      html, body {
         height: 100%;
         font-family: 'DM Sans', sans-serif;
         background: var(--bg);
         overflow: hidden;
      }

      body {
         display: flex;
         justify-content: center;
         align-items: center;
         min-height: 100vh;
         position: relative;
      }

      /* ─── Ambient background orbs ─── */
      .orb {
         position: fixed;
         border-radius: 50%;
         filter: blur(90px);
         pointer-events: none;
         z-index: 0;
         animation: orbFloat ease-in-out infinite alternate;
      }

      .orb-1 {
         width: 550px; height: 550px;
         background: radial-gradient(circle, rgba(124,106,255,0.4), transparent 65%);
         top: -15%; left: -12%;
         animation-duration: 14s;
      }

      .orb-2 {
         width: 420px; height: 420px;
         background: radial-gradient(circle, rgba(255,106,176,0.3), transparent 65%);
         bottom: -10%; right: -8%;
         animation-duration: 11s;
         animation-delay: -5s;
      }

      .orb-3 {
         width: 280px; height: 280px;
         background: radial-gradient(circle, rgba(106,240,255,0.25), transparent 65%);
         top: 45%; right: 15%;
         animation-duration: 17s;
         animation-delay: -9s;
      }

      @keyframes orbFloat {
         from { transform: translate(0, 0) scale(1); }
         to   { transform: translate(25px, 18px) scale(1.06); }
      }

      /* ─── Chat window ─── */
      .chat-container {
         position: relative;
         z-index: 1;
         width: 460px;
         height: 680px;
         background: var(--surface);
         border-radius: 24px;
         border: 1px solid var(--border);
         display: flex;
         flex-direction: column;
         box-shadow:
            0 50px 120px rgba(0,0,0,0.75),
            0 0 0 1px rgba(124,106,255,0.08),
            inset 0 1px 0 rgba(255,255,255,0.06);
         animation: windowIn 0.6s cubic-bezier(0.16,1,0.3,1) both;
         overflow: hidden;
      }

      @keyframes windowIn {
         from { opacity: 0; transform: translateY(40px) scale(0.95); }
         to   { opacity: 1; transform: translateY(0) scale(1); }
      }

      /* ─── Header ─── */
      .chat-header {
         padding: 16px 20px;
         background: linear-gradient(135deg, rgba(124,106,255,0.18), rgba(106,240,255,0.06));
         border-bottom: 1px solid var(--border);
         display: flex;
         align-items: center;
         gap: 13px;
         flex-shrink: 0;
         position: relative;
         overflow: hidden;
      }

      .chat-header::after {
         content: '';
         position: absolute;
         inset: 0;
         background: linear-gradient(90deg, transparent, rgba(255,255,255,0.025), transparent);
         animation: shimmer 5s ease infinite;
         pointer-events: none;
      }

      @keyframes shimmer {
         0%   { transform: translateX(-100%); }
         100% { transform: translateX(200%); }
      }

      .header-icon {
         width: 40px; height: 40px;
         border-radius: 12px;
         background: linear-gradient(135deg, var(--accent), var(--accent2));
         display: flex; align-items: center; justify-content: center;
         font-size: 18px;
         box-shadow: 0 4px 16px rgba(124,106,255,0.5);
         flex-shrink: 0;
         animation: iconGlow 3s ease-in-out infinite;
         position: relative;
         z-index: 1;
      }

      @keyframes iconGlow {
         0%, 100% { box-shadow: 0 4px 16px rgba(124,106,255,0.5); }
         50%       { box-shadow: 0 4px 30px rgba(124,106,255,0.9); }
      }

      .header-text { flex: 1; position: relative; z-index: 1; }

      .header-title {
         font-family: 'Syne', sans-serif;
         font-weight: 700;
         font-size: 15.5px;
         color: var(--text);
         letter-spacing: -0.3px;
      }

      .header-sub {
         font-size: 11.5px;
         color: var(--green);
         margin-top: 2px;
         display: flex;
         align-items: center;
         gap: 5px;
      }

      .status-dot {
         width: 6px; height: 6px;
         border-radius: 50%;
         background: var(--green);
         box-shadow: 0 0 8px var(--green);
         animation: blink 2s ease-in-out infinite;
      }

      @keyframes blink {
         0%, 100% { opacity: 1; transform: scale(1); }
         50%       { opacity: 0.35; transform: scale(0.7); }
      }

      .header-badge {
         background: rgba(124,106,255,0.15);
         border: 1px solid rgba(124,106,255,0.3);
         color: var(--accent);
         font-size: 10px;
         font-weight: 600;
         padding: 3px 10px;
         border-radius: 99px;
         letter-spacing: 0.8px;
         text-transform: uppercase;
         position: relative;
         z-index: 1;
      }

      /* ─── Messages area ─── */
      .chat-box {
         flex: 1;
         padding: 18px 16px;
         overflow-y: auto;
         display: flex;
         flex-direction: column;
         gap: 14px;
         scroll-behavior: smooth;
      }

      .chat-box::-webkit-scrollbar { width: 3px; }
      .chat-box::-webkit-scrollbar-track { background: transparent; }
      .chat-box::-webkit-scrollbar-thumb {
         background: rgba(255,255,255,0.1);
         border-radius: 99px;
      }

      /* ─── Empty state ─── */
      .empty-state {
         flex: 1;
         display: flex;
         flex-direction: column;
         align-items: center;
         justify-content: center;
         gap: 14px;
         text-align: center;
         padding: 30px;
         animation: fadeIn 0.5s ease;
      }

      .empty-glyph {
         font-size: 44px;
         animation: floatGlyph 3.5s ease-in-out infinite;
         filter: drop-shadow(0 0 20px rgba(124,106,255,0.6));
      }

      @keyframes floatGlyph {
         0%, 100% { transform: translateY(0) rotate(-3deg); }
         50%       { transform: translateY(-10px) rotate(3deg); }
      }

      .empty-title {
         font-family: 'Syne', sans-serif;
         font-size: 17px;
         font-weight: 700;
         color: var(--text);
         letter-spacing: -0.3px;
      }

      .empty-desc {
         font-size: 13px;
         color: var(--text2);
         line-height: 1.6;
         max-width: 240px;
      }

      .empty-chips {
         display: flex;
         flex-wrap: wrap;
         gap: 7px;
         justify-content: center;
         margin-top: 6px;
      }

      .chip {
         background: var(--surface2);
         border: 1px solid var(--border2);
         color: var(--text2);
         font-size: 11.5px;
         padding: 6px 13px;
         border-radius: 99px;
         cursor: pointer;
         transition: all 0.2s;
      }

      .chip:hover {
         background: rgba(124,106,255,0.15);
         border-color: rgba(124,106,255,0.4);
         color: var(--text);
         transform: translateY(-2px);
         box-shadow: 0 4px 12px rgba(124,106,255,0.2);
      }

      /* ─── Message rows ─── */
      .msg-row {
         display: flex;
         gap: 10px;
         align-items: flex-end;
         animation: msgSlideIn 0.32s cubic-bezier(0.16,1,0.3,1) both;
      }

      .msg-row.user { flex-direction: row-reverse; }

      @keyframes msgSlideIn {
         from { opacity: 0; transform: translateY(12px); }
         to   { opacity: 1; transform: translateY(0); }
      }

      .msg-avatar {
         width: 30px; height: 30px;
         border-radius: 10px;
         display: flex; align-items: center; justify-content: center;
         font-size: 14px;
         flex-shrink: 0;
      }

      .msg-avatar.ai-av {
         background: linear-gradient(135deg, #1c1c34, #252545);
         border: 1px solid rgba(124,106,255,0.35);
         box-shadow: 0 2px 10px rgba(124,106,255,0.2);
      }

      .msg-avatar.user-av {
         background: linear-gradient(135deg, var(--accent), var(--accent2));
         box-shadow: 0 2px 10px rgba(124,106,255,0.4);
      }

      .msg-bubble {
         max-width: 76%;
         padding: 11px 15px;
         border-radius: 16px;
         font-size: 13.5px;
         line-height: 1.65;
         color: var(--text);
         word-break: break-word;
         white-space: pre-wrap;
      }

      .msg-bubble.user {
         background: linear-gradient(135deg, rgba(124,106,255,0.25), rgba(255,106,176,0.12));
         border: 1px solid rgba(124,106,255,0.3);
         border-bottom-right-radius: 4px;
      }

      .msg-bubble.ai {
         background: var(--surface2);
         border: 1px solid var(--border);
         border-bottom-left-radius: 4px;
      }

      .msg-bubble.error {
         background: rgba(255,107,107,0.08);
         border: 1px solid rgba(255,107,107,0.28);
         color: #ffb3b3;
         border-bottom-left-radius: 4px;
      }

      /* ─── Typing indicator ─── */
      .typing-row {
         display: flex;
         gap: 10px;
         align-items: flex-end;
         animation: msgSlideIn 0.32s cubic-bezier(0.16,1,0.3,1) both;
      }

      .typing-bubble {
         background: var(--surface2);
         border: 1px solid var(--border);
         border-radius: 16px;
         border-bottom-left-radius: 4px;
         padding: 14px 18px;
         display: flex;
         align-items: center;
         gap: 6px;
      }

      .t-dot {
         width: 7px; height: 7px;
         border-radius: 50%;
         animation: typingBounce 1.3s ease-in-out infinite;
      }

      .t-dot:nth-child(1) { background: var(--accent);  animation-delay: 0s; }
      .t-dot:nth-child(2) { background: var(--accent2); animation-delay: 0.18s; }
      .t-dot:nth-child(3) { background: var(--accent3); animation-delay: 0.36s; }

      @keyframes typingBounce {
         0%, 60%, 100% { transform: translateY(0); opacity: 0.3; }
         30%            { transform: translateY(-8px); opacity: 1; }
      }

      /* ─── Input area ─── */
      .input-area {
         padding: 12px 14px 16px;
         border-top: 1px solid var(--border);
         background: rgba(0,0,0,0.2);
         flex-shrink: 0;
      }

      .input-wrap {
         display: flex;
         align-items: center;
         gap: 10px;
         background: var(--surface2);
         border: 1px solid var(--border2);
         border-radius: 15px;
         padding: 8px 8px 8px 16px;
         transition: border-color 0.25s, box-shadow 0.25s;
      }

      .input-wrap:focus-within {
         border-color: rgba(124,106,255,0.55);
         box-shadow: 0 0 0 3px rgba(124,106,255,0.1), 0 0 20px rgba(124,106,255,0.07);
      }

      .input-wrap input {
         flex: 1;
         background: transparent;
         border: none;
         outline: none;
         color: var(--text);
         font-family: 'DM Sans', sans-serif;
         font-size: 13.5px;
         padding: 5px 0;
         caret-color: var(--accent);
      }

      .input-wrap input::placeholder { color: var(--muted); }
      .input-wrap input:disabled { opacity: 0.5; cursor: not-allowed; }

      .send-btn {
         width: 38px; height: 38px;
         border-radius: 11px;
         background: linear-gradient(135deg, var(--accent), #9b8bff);
         border: none;
         cursor: pointer;
         display: flex; align-items: center; justify-content: center;
         font-size: 15px;
         color: #fff;
         flex-shrink: 0;
         box-shadow: 0 4px 14px rgba(124,106,255,0.45);
         transition: transform 0.15s, box-shadow 0.15s, opacity 0.15s;
         position: relative;
         overflow: hidden;
      }

      .send-btn:hover {
         transform: scale(1.08);
         box-shadow: 0 6px 22px rgba(124,106,255,0.65);
      }

      .send-btn:active { transform: scale(0.93); }

      .send-btn:disabled {
         opacity: 0.4;
         cursor: not-allowed;
         transform: none;
         box-shadow: none;
      }

      /* Spinner inside send button while loading */
      .send-btn .btn-icon { display: flex; align-items: center; justify-content: center; }

      .send-btn .spinner {
         display: none;
         width: 16px; height: 16px;
         border: 2px solid rgba(255,255,255,0.3);
         border-top-color: #fff;
         border-radius: 50%;
         animation: spin 0.7s linear infinite;
      }

      .send-btn.loading .btn-icon { display: none; }
      .send-btn.loading .spinner { display: block; }

      @keyframes spin {
         to { transform: rotate(360deg); }
      }

      .input-hint {
         text-align: center;
         font-size: 10.5px;
         color: var(--muted);
         margin-top: 9px;
         letter-spacing: 0.15px;
      }

      .input-hint kbd {
         background: var(--surface3);
         border: 1px solid var(--border2);
         padding: 1px 6px;
         border-radius: 5px;
         font-size: 10px;
         font-family: inherit;
      }

      @keyframes fadeIn {
         from { opacity: 0; }
         to   { opacity: 1; }
      }

      /* ─── Dev watermark ─── */
      .dev-tag {
         position: fixed;
         top: 18px;
         left: 20px;
         z-index: 100;
         display: flex;
         align-items: center;
         gap: 8px;
         animation: fadeIn 0.8s ease 0.3s both;
      }

      .dev-dot {
         width: 7px; height: 7px;
         border-radius: 50%;
         background: linear-gradient(135deg, var(--accent), var(--accent2));
         box-shadow: 0 0 8px rgba(124,106,255,0.8);
         animation: devPulse 2.5s ease-in-out infinite;
         flex-shrink: 0;
      }

      @keyframes devPulse {
         0%, 100% { box-shadow: 0 0 6px rgba(124,106,255,0.7); transform: scale(1); }
         50%       { box-shadow: 0 0 14px rgba(255,106,176,0.9); transform: scale(1.2); }
      }

      .dev-label {
         font-family: 'Syne', sans-serif;
         font-size: 12px;
         font-weight: 600;
         letter-spacing: 0.2px;
      }

      .dev-label span:first-child {
         color: var(--text2);
         font-weight: 400;
      }

      .dev-label span:last-child {
         background: linear-gradient(135deg, var(--accent), var(--accent2));
         -webkit-background-clip: text;
         -webkit-text-fill-color: transparent;
         background-clip: text;
         font-weight: 700;
      }

      /* ─── Responsive ─── */
      @media (max-width: 520px) {
         .chat-container {
            width: 100vw;
            height: 100dvh;
            border-radius: 0;
            border: none;
         }

         body {
            align-items: flex-start;
            overflow: hidden;
         }

         .orb-1 { width: 300px; height: 300px; }
         .orb-2 { width: 250px; height: 250px; }
         .orb-3 { display: none; }

         .dev-tag {
            top: 12px;
            left: 14px;
         }

         .empty-chips {
            gap: 6px;
         }

         .chip {
            font-size: 11px;
            padding: 5px 11px;
         }

         .msg-bubble {
            font-size: 13px;
         }
      }

      @media (max-width: 360px) {
         .header-title { font-size: 14px; }
         .header-badge { display: none; }
         .empty-title  { font-size: 15px; }
      }
   </style>
</head>
<body>

   <!-- Dev watermark -->
   <div class="dev-tag">
      <div class="dev-dot"></div>
      <div class="dev-label">
         <span>Developed by </span><span>Dev</span>
      </div>
   </div>

   <!-- Ambient orbs -->
   <div class="orb orb-1"></div>
   <div class="orb orb-2"></div>
   <div class="orb orb-3"></div>

   <!-- Chat window -->
   <div class="chat-container">

      <!-- Header -->
      <div class="chat-header">
         <div class="header-icon">✦</div>
         <div class="header-text">
            <div class="header-title">AI Assistant</div>
            <div class="header-sub">
               <span class="status-dot"></span> Online &amp; Ready
            </div>
         </div>
         <div class="header-badge">Groq</div>
      </div>

      <!-- Messages -->
      <div id="chat-box" class="chat-box">
         <div class="empty-state" id="empty-state">
            <div class="empty-glyph">✦</div>
            <div class="empty-title">What's on your mind?</div>
            <div class="empty-desc">Ask me anything — I'll do my best to help you out.</div>
            <div class="empty-chips">
               <div class="chip" onclick="fillPrompt('Explain quantum computing simply')">⚛ Quantum computing</div>
               <div class="chip" onclick="fillPrompt('Write me a short poem')">✍ Write a poem</div>
               <div class="chip" onclick="fillPrompt('Tell me a fun fact')">💡 Fun fact</div>
               <div class="chip" onclick="fillPrompt('How does the internet work?')">🌐 How internet works</div>
            </div>
         </div>
      </div>

      <!-- Input -->
      <div class="input-area">
         <div class="input-wrap">
            <input type="text" id="message" placeholder="Ask anything…" autocomplete="off">
            <button class="send-btn" id="send-btn" onclick="sendMessage()" title="Send">
               <span class="btn-icon">➤</span>
               <span class="spinner"></span>
            </button>
         </div>
         <div class="input-hint">Press <kbd>Enter</kbd> to send</div>
      </div>

   </div>

   <script>
      const chatBox = document.getElementById('chat-box');
      const input   = document.getElementById('message');
      const sendBtn = document.getElementById('send-btn');

      // Enter to send
      input.addEventListener('keydown', e => {
         if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
         }
      });

      // Fill input from suggestion chip
      function fillPrompt(text) {
         input.value = text;
         input.focus();
      }

      // Remove empty state once chat starts
      function clearEmpty() {
         const es = document.getElementById('empty-state');
         if (es) es.remove();
      }

      // Append a chat bubble
      function appendMessage(content, type) {
         clearEmpty();

         const row = document.createElement('div');
         row.className = 'msg-row ' + type;

         const avatar = document.createElement('div');
         avatar.className = 'msg-avatar ' + (type === 'user' ? 'user-av' : 'ai-av');
         avatar.textContent = type === 'user' ? '👤' : '✦';

         const bubble = document.createElement('div');
         bubble.className = 'msg-bubble ' + type;
         bubble.innerHTML = content;

         row.appendChild(avatar);
         row.appendChild(bubble);
         chatBox.appendChild(row);
         chatBox.scrollTop = chatBox.scrollHeight;
      }

      // Append an error bubble
      function appendError(content) {
         clearEmpty();

         const row = document.createElement('div');
         row.className = 'msg-row ai';

         const avatar = document.createElement('div');
         avatar.className = 'msg-avatar ai-av';
         avatar.textContent = '✦';

         const bubble = document.createElement('div');
         bubble.className = 'msg-bubble error';
         bubble.innerHTML = '⚠️ ' + content;

         row.appendChild(avatar);
         row.appendChild(bubble);
         chatBox.appendChild(row);
         chatBox.scrollTop = chatBox.scrollHeight;
      }

      // Show animated typing indicator
      function showTyping() {
         clearEmpty();

         const row = document.createElement('div');
         row.className = 'typing-row';
         row.id = 'typing-indicator';

         const avatar = document.createElement('div');
         avatar.className = 'msg-avatar ai-av';
         avatar.textContent = '✦';

         const bubble = document.createElement('div');
         bubble.className = 'typing-bubble';
         bubble.innerHTML = '<div class="t-dot"></div><div class="t-dot"></div><div class="t-dot"></div>';

         row.appendChild(avatar);
         row.appendChild(bubble);
         chatBox.appendChild(row);
         chatBox.scrollTop = chatBox.scrollHeight;
      }

      // Hide typing indicator
      function hideTyping() {
         const t = document.getElementById('typing-indicator');
         if (t) t.remove();
      }

      // Lock / unlock UI during fetch
      function setLoading(on) {
         input.disabled = on;
         sendBtn.disabled = on;
         sendBtn.classList.toggle('loading', on);
      }

      // Send message to Laravel backend
      async function sendMessage() {
         const msg = input.value.trim();
         if (!msg) return;

         appendMessage(msg, 'user');
         input.value = '';
         setLoading(true);
         showTyping();

         try {
            const res = await fetch('/chat', {
               method: 'POST',
               headers: {
                  'Content-Type': 'application/json',
                  'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
               },
               body: JSON.stringify({ message: msg })
            });

            const data = await res.json();
            hideTyping();

            if (data.error) {
               appendError(data.error);
            } else {
               appendMessage(data.reply, 'ai');
            }

         } catch (err) {
            hideTyping();
            appendError('Server error. Please try again.');
         } finally {
            setLoading(false);
            input.focus();
         }
      }
   </script>

</body>
</html>
