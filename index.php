<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Filipino Ulam Recipes – Ang Lutong Pilipino</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;0,900;1,400;1,700&family=IM+Fell+English:ital@0;1&family=Crimson+Text:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --cream:    #f5ead6;
            --parchment:#e8d5b0;
            --brown-lt: #c49a5a;
            --brown:    #8b5e2e;
            --brown-dk: #5c3a1e;
            --ink:      #2c1a0e;
            --red-acc:  #9b2335;
            --gold:     #c8992a;
        }

        body {
            background: radial-gradient(ellipse at center, #3d2008 0%, #1a0d04 60%, #0a0500 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            font-family: 'Crimson Text', serif;
            overflow: hidden;
            position: relative;
        }

        /* Ambient dust particles */
        .dust {
            position: fixed;
            width: 100%; height: 100%;
            top: 0; left: 0;
            pointer-events: none;
            z-index: 0;
        }
        .dust span {
            position: absolute;
            width: 2px; height: 2px;
            background: rgba(200,153,42,0.4);
            border-radius: 50%;
            animation: float linear infinite;
        }
        @keyframes float {
            0%   { transform: translateY(100vh) translateX(0); opacity: 0; }
            10%  { opacity: 1; }
            90%  { opacity: 0.5; }
            100% { transform: translateY(-10vh) translateX(30px); opacity: 0; }
        }

        /* Shelf / table surface */
        .shelf {
            position: fixed;
            bottom: 0; left: 0; right: 0;
            height: 80px;
            background: linear-gradient(to bottom, #3d1f08, #1a0a02);
            box-shadow: 0 -4px 30px rgba(0,0,0,0.8);
            z-index: 1;
        }
        .shelf::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 8px;
            background: linear-gradient(to right, #6b3a1f, #c49a5a, #8b5e2e, #c49a5a, #6b3a1f);
        }

        /* Scene container */
        .scene {
            perspective: 1400px;
            position: relative;
            z-index: 10;
            margin-bottom: 50px;
        }

        /* THE BOOK */
        .book {
            width: 380px;
            height: 520px;
            position: relative;
            cursor: pointer;
            transform-style: preserve-3d;
            transition: transform 0.3s ease;
        }
        .book:hover {
            transform: translateY(-10px) rotateY(-5deg) scale(1.01);
            filter: drop-shadow(0 24px 40px rgba(0,0,0,0.55));
        }

        /* Smooth multi-stage book open — ease-in-out with natural delay */
        .book.opening {
            animation: bookOpen 1.9s cubic-bezier(0.45, 0.05, 0.28, 1.0) forwards;
            pointer-events: none;
        }

        @keyframes bookOpen {
            /* Phase 1 — anticipation lift */
            0%   {
                transform: translateY(-10px) rotateY(-5deg) scale(1.01);
                opacity: 1;
                filter: drop-shadow(0 24px 40px rgba(0,0,0,0.55));
            }
            /* Phase 2 — begin opening */
            15%  {
                transform: translateY(-22px) rotateY(-18deg) scale(1.03);
                opacity: 1;
                filter: drop-shadow(0 32px 50px rgba(0,0,0,0.5));
            }
            /* Phase 3 — halfway, natural arc */
            45%  {
                transform: translateY(-36px) rotateY(-62deg) scale(1.07);
                opacity: 1;
                filter: drop-shadow(0 40px 60px rgba(0,0,0,0.4));
            }
            /* Phase 4 — nearly flat open */
            72%  {
                transform: translateY(-48px) rotateY(-90deg) scale(1.10);
                opacity: 0.8;
                filter: drop-shadow(0 44px 65px rgba(0,0,0,0.3));
            }
            /* Phase 5 — swung past flat, fade */
            88%  {
                transform: translateY(-58px) rotateY(-105deg) scale(1.12);
                opacity: 0.3;
                filter: drop-shadow(0 50px 70px rgba(0,0,0,0.2));
            }
            /* Phase 6 — fully gone */
            100% {
                transform: translateY(-70px) rotateY(-112deg) scale(1.14);
                opacity: 0;
                filter: drop-shadow(0 50px 70px rgba(0,0,0,0));
            }
        }

        /* Book spine */
        .book-spine {
            position: absolute;
            left: 0; top: 0;
            width: 40px; height: 100%;
            background: linear-gradient(to right, #3a1a08, #7a4520, #5c3012);
            border-radius: 4px 0 0 4px;
            box-shadow: inset -3px 0 8px rgba(0,0,0,0.4);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .book-spine .spine-text {
            writing-mode: vertical-rl;
            transform: rotate(180deg);
            color: var(--gold);
            font-family: 'Playfair Display', serif;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 3px;
            text-transform: uppercase;
            opacity: 0.9;
        }

        /* Book cover (front) */
        .book-cover {
            position: absolute;
            left: 40px; top: 0;
            width: calc(100% - 40px); height: 100%;
            background:
                url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='200' height='200'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.75' numOctaves='4' stitchTiles='stitch'/%3E%3CfeColorMatrix type='saturate' values='0'/%3E%3C/filter%3E%3Crect width='200' height='200' filter='url(%23n)' opacity='0.08'/%3E%3C/svg%3E"),
                linear-gradient(160deg, #8b4513 0%, #6b3010 25%, #7a3d18 50%, #5c2a0c 75%, #4a2008 100%);
            border-radius: 0 6px 6px 0;
            box-shadow:
                4px 0 20px rgba(0,0,0,0.6),
                inset 0 0 60px rgba(0,0,0,0.3),
                inset 3px 0 10px rgba(200,153,42,0.1);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-between;
            padding: 28px 24px 24px;
            overflow: hidden;
        }

        /* Decorative border on cover */
        .cover-border {
            position: absolute;
            inset: 12px;
            border: 2px solid rgba(200,153,42,0.35);
            border-radius: 3px;
            pointer-events: none;
        }
        .cover-border::before {
            content: '';
            position: absolute;
            inset: 6px;
            border: 1px solid rgba(200,153,42,0.18);
            border-radius: 2px;
        }

        /* Corner ornaments */
        .corner {
            position: absolute;
            width: 28px; height: 28px;
            color: var(--gold);
            opacity: 0.6;
            font-size: 22px;
            line-height: 1;
        }
        .corner.tl { top: 18px; left: 18px; }
        .corner.tr { top: 18px; right: 18px; transform: scaleX(-1); }
        .corner.bl { bottom: 18px; left: 18px; transform: scaleY(-1); }
        .corner.br { bottom: 18px; right: 18px; transform: scale(-1,-1); }

        /* Cover content */
        .cover-top { text-align: center; z-index: 2; }

        .cover-label {
            font-family: 'IM Fell English', serif;
            font-size: 10px;
            letter-spacing: 5px;
            text-transform: uppercase;
            color: var(--gold);
            opacity: 0.8;
            display: block;
            margin-bottom: 14px;
        }

        .cover-title {
            font-family: 'Playfair Display', serif;
            font-size: 32px;
            font-weight: 900;
            color: var(--cream);
            line-height: 1.15;
            text-shadow: 2px 3px 12px rgba(0,0,0,0.6);
            margin-bottom: 6px;
        }
        .cover-title span { color: var(--gold); }

        .cover-subtitle {
            font-family: 'IM Fell English', serif;
            font-style: italic;
            font-size: 14px;
            color: var(--parchment);
            opacity: 0.75;
            letter-spacing: 1px;
        }

        /* Illustration area */
        .cover-illustration {
            width: 160px; height: 160px;
            z-index: 2;
            position: relative;
        }
        .cover-illustration svg {
            width: 100%; height: 100%;
            filter: drop-shadow(0 4px 16px rgba(0,0,0,0.5));
        }

        .cover-bottom { text-align: center; z-align: 2; z-index: 2; }
        .cover-divider {
            width: 120px;
            height: 1px;
            background: linear-gradient(to right, transparent, var(--gold), transparent);
            margin: 0 auto 10px;
        }
        .cover-edition {
            font-family: 'IM Fell English', serif;
            font-size: 11px;
            color: var(--brown-lt);
            letter-spacing: 2px;
            opacity: 0.8;
        }

        /* Page layers (illusion of pages) */
        .book-pages {
            position: absolute;
            left: 42px; top: 4px;
            width: calc(100% - 46px); height: calc(100% - 8px);
            background: repeating-linear-gradient(
                90deg,
                #e8d5b0 0px, #e8d5b0 1px,
                #f0dfc0 1px, #f0dfc0 2px,
                #e8d5b0 2px, #e8d5b0 3px
            );
            border-radius: 0 4px 4px 0;
            z-index: -1;
            transform: translateX(-3px);
            box-shadow: inset -2px 0 4px rgba(0,0,0,0.2);
        }

        /* Book shadow */
        .book-shadow {
            position: absolute;
            bottom: -30px;
            left: 40px;
            width: calc(100% - 40px);
            height: 30px;
            background: radial-gradient(ellipse at center top, rgba(0,0,0,0.6) 0%, transparent 70%);
            filter: blur(6px);
        }

        /* Click hint */
        .click-hint {
            position: absolute;
            bottom: -70px;
            left: 50%;
            transform: translateX(-50%);
            text-align: center;
            animation: pulseHint 2s ease-in-out infinite;
        }
        .click-hint span {
            display: block;
            font-family: 'IM Fell English', serif;
            font-style: italic;
            font-size: 14px;
            color: var(--brown-lt);
            letter-spacing: 2px;
            opacity: 0.8;
        }
        .click-hint .arrow {
            font-size: 20px;
            color: var(--gold);
            margin-top: 4px;
            display: block;
        }
        @keyframes pulseHint {
            0%, 100% { opacity: 0.6; transform: translateX(-50%) translateY(0); }
            50%       { opacity: 1;   transform: translateX(-50%) translateY(-5px); }
        }

        /* ─── PAGE FLIP OVERLAY ─── */
        .page-flip-overlay {
            position: fixed;
            inset: 0;
            z-index: 1000;
            pointer-events: none;
            display: none;
        }
        .page-flip-overlay.active { display: block; }

        .flip-page {
            position: absolute;
            top: 0; right: 50%;
            width: 50%; height: 100%;
            background: linear-gradient(to left,
                #f5ead6 0%,
                #eedfc0 40%,
                #e8d5b0 100%);
            transform-origin: right center;
            animation: flipPage 1.2s cubic-bezier(0.68, -0.55, 0.265, 1.55) forwards;
            box-shadow: -5px 0 30px rgba(0,0,0,0.3);
        }
        .flip-page::before {
            content: '';
            position: absolute;
            inset: 0;
            background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='300' height='300'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.65' numOctaves='4' stitchTiles='stitch'/%3E%3CfeColorMatrix type='saturate' values='0'/%3E%3C/filter%3E%3Crect width='300' height='300' filter='url(%23n)' opacity='0.06'/%3E%3C/svg%3E");
            opacity: 0.5;
        }
        .flip-page::after {
            content: '';
            position: absolute;
            top: 0; right: 0;
            width: 40px; height: 100%;
            background: linear-gradient(to left, rgba(0,0,0,0.15), transparent);
        }

        .flip-page-2 {
            position: absolute;
            top: 0; left: 50%;
            width: 50%; height: 100%;
            background: linear-gradient(to right,
                #f5ead6 0%,
                #eedfc0 40%,
                #e8d5b0 100%);
            transform-origin: left center;
            animation: flipPage2 1.2s 0.15s cubic-bezier(0.68, -0.55, 0.265, 1.55) forwards;
            opacity: 0;
        }
        @keyframes flipPage {
            0%   { transform: rotateY(0deg); }
            100% { transform: rotateY(-180deg); }
        }
        @keyframes flipPage2 {
            0%   { opacity: 1; transform: rotateY(180deg); }
            100% { opacity: 1; transform: rotateY(0deg); }
        }

        /* White flash then redirect */
        .white-flash {
            position: fixed;
            inset: 0;
            background: #f5ead6;
            z-index: 2000;
            opacity: 0;
            pointer-events: none;
            animation: none;
        }
        .white-flash.flash { animation: flashIn 0.5s 1.1s forwards; }
        @keyframes flashIn {
            0%   { opacity: 0; }
            100% { opacity: 1; }
        }

        /* Header quote */
        .page-quote {
            position: fixed;
            top: 30px;
            left: 50%;
            transform: translateX(-50%);
            text-align: center;
            z-index: 5;
            white-space: nowrap;
        }
        .page-quote p {
            font-family: 'IM Fell English', serif;
            font-style: italic;
            font-size: 15px;
            color: rgba(200,153,42,0.55);
            letter-spacing: 1.5px;
        }

        /* Responsive */
        @media (max-width: 480px) {
            .book { width: 290px; height: 400px; }
            .cover-title { font-size: 24px; }
            .cover-illustration { width: 120px; height: 120px; }
        }
    </style>
</head>
<body>

<!-- Ambient dust -->
<div class="dust" id="dust"></div>

<!-- Shelf -->
<div class="shelf"></div>

<!-- Page quote -->
<div class="page-quote">
    <p>"Ang pagkain ay pagmamahal na ibinibigay sa lahat."</p>
</div>

<!-- Scene -->
<div class="scene">
    <div class="book" id="theBook" onclick="openBook()">

        <!-- Page layers behind cover -->
        <div class="book-pages"></div>

        <!-- Spine -->
        <div class="book-spine">
            <span class="spine-text">Ulam Recipes</span>
        </div>

        <!-- Front cover -->
        <div class="book-cover">
            <div class="cover-border"></div>

            <!-- Corner ornaments -->
            <div class="corner tl">✦</div>
            <div class="corner tr">✦</div>
            <div class="corner bl">✦</div>
            <div class="corner br">✦</div>

            <div class="cover-top">
                <span class="cover-label">Koleksyon ng mga Lutuin</span>
                <h1 class="cover-title">Filipino<br><span>Ulam</span><br>Recipes</h1>
                <p class="cover-subtitle">Ang Lutong Pilipino</p>
            </div>

            <!-- SVG Illustration: pot with steam -->
            <div class="cover-illustration">
                <svg viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
                    <!-- Steam wisps -->
                    <g opacity="0.7">
                        <path d="M70 60 Q65 45 70 30 Q75 15 70 5" stroke="#c8992a" stroke-width="2.5" fill="none" stroke-linecap="round" opacity="0.6">
                            <animate attributeName="d" dur="3s" repeatCount="indefinite"
                                values="M70 60 Q65 45 70 30 Q75 15 70 5;
                                        M70 60 Q75 45 70 30 Q65 15 70 5;
                                        M70 60 Q65 45 70 30 Q75 15 70 5"/>
                            <animate attributeName="opacity" dur="3s" repeatCount="indefinite" values="0.6;0.3;0.6"/>
                        </path>
                        <path d="M100 55 Q95 40 100 25 Q105 10 100 0" stroke="#c8992a" stroke-width="2.5" fill="none" stroke-linecap="round" opacity="0.7">
                            <animate attributeName="d" dur="2.5s" repeatCount="indefinite"
                                values="M100 55 Q95 40 100 25 Q105 10 100 0;
                                        M100 55 Q105 40 100 25 Q95 10 100 0;
                                        M100 55 Q95 40 100 25 Q105 10 100 0"/>
                            <animate attributeName="opacity" dur="2.5s" repeatCount="indefinite" values="0.7;0.2;0.7"/>
                        </path>
                        <path d="M130 60 Q135 45 130 30 Q125 15 130 5" stroke="#c8992a" stroke-width="2.5" fill="none" stroke-linecap="round" opacity="0.6">
                            <animate attributeName="d" dur="3.5s" repeatCount="indefinite"
                                values="M130 60 Q135 45 130 30 Q125 15 130 5;
                                        M130 60 Q125 45 130 30 Q135 15 130 5;
                                        M130 60 Q135 45 130 30 Q125 15 130 5"/>
                            <animate attributeName="opacity" dur="3.5s" repeatCount="indefinite" values="0.5;0.8;0.5"/>
                        </path>
                    </g>
                    <!-- Pot lid -->
                    <ellipse cx="100" cy="72" rx="62" ry="10" fill="#7a4520" opacity="0.9"/>
                    <rect x="80" y="60" width="40" height="14" rx="7" fill="#5c3012"/>
                    <ellipse cx="100" cy="72" rx="60" ry="8" fill="#9b5a2a"/>
                    <!-- Pot body -->
                    <path d="M42 80 Q38 150 45 175 L155 175 Q162 150 158 80 Z" fill="#6b3010"/>
                    <path d="M44 82 Q40 150 47 173 L153 173 Q160 150 156 82 Z" fill="#8b4520"/>
                    <!-- Handles -->
                    <path d="M42 100 Q25 100 25 115 Q25 130 42 130" stroke="#5c2a0c" stroke-width="8" fill="none" stroke-linecap="round"/>
                    <path d="M158 100 Q175 100 175 115 Q175 130 158 130" stroke="#5c2a0c" stroke-width="8" fill="none" stroke-linecap="round"/>
                    <!-- Pot sheen -->
                    <path d="M55 90 Q50 130 52 165" stroke="rgba(255,255,255,0.12)" stroke-width="6" fill="none" stroke-linecap="round"/>
                    <!-- Base -->
                    <rect x="40" y="175" width="120" height="10" rx="5" fill="#5c2a0c"/>
                    <!-- Decorative band -->
                    <path d="M44 120 Q100 110 156 120" stroke="#c8992a" stroke-width="1.5" fill="none" opacity="0.5"/>
                    <!-- Stars / spice dots -->
                    <circle cx="85" cy="140" r="3" fill="#c8992a" opacity="0.4"/>
                    <circle cx="100" cy="135" r="2.5" fill="#c8992a" opacity="0.3"/>
                    <circle cx="115" cy="143" r="3" fill="#c8992a" opacity="0.4"/>
                </svg>
            </div>

            <div class="cover-bottom">
                <div class="cover-divider"></div>
                <p class="cover-edition">Piling mga Lutuin · Ika-isang Edisyon</p>
            </div>
        </div>

        <!-- Book shadow -->
        <div class="book-shadow"></div>

        <!-- Click hint -->
        <div class="click-hint">
            <span>Pindutin upang buksan</span>
            <span class="arrow">↑</span>
        </div>
    </div>
</div>

<!-- Page flip overlay -->
<div class="page-flip-overlay" id="pageFlip">
    <div class="flip-page"></div>
    <div class="flip-page-2"></div>
</div>

<!-- White flash before redirect -->
<div class="white-flash" id="whiteFlash"></div>

<script>
    // Generate dust particles
    const dustEl = document.getElementById('dust');
    for (let i = 0; i < 25; i++) {
        const s = document.createElement('span');
        s.style.left = Math.random() * 100 + '%';
        s.style.animationDuration = (8 + Math.random() * 15) + 's';
        s.style.animationDelay = (Math.random() * 10) + 's';
        s.style.width = s.style.height = (1 + Math.random() * 2.5) + 'px';
        s.style.opacity = Math.random() * 0.6;
        dustEl.appendChild(s);
    }

    let clicked = false;

    function openBook() {
        if (clicked) return;
        clicked = true;

        const book = document.getElementById('theBook');
        const flipOverlay = document.getElementById('pageFlip');
        const whiteFlash = document.getElementById('whiteFlash');

        // 1. Book opening animation
        book.classList.add('opening');

        // 2. Page flip effect
        setTimeout(() => {
            flipOverlay.classList.add('active');
        }, 800);

        // 3. White flash
        setTimeout(() => {
            whiteFlash.classList.add('flash');
        }, 1100);

        // 4. Redirect
        setTimeout(() => {
            window.location.href = 'home.php';
        }, 1700);
    }
</script>
</body>
</html>