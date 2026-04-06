<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>StyleHub — Where Fashion Meets Identity</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400&family=DM+Sans:wght@300;400;500&family=Bebas+Neue&display=swap" rel="stylesheet">

    <style>
        :root {
            --cream: #F5F0E8;
            --ink: #1A1410;
            --caramel: #C8883A;
            --rust: #B04E2A;
            --sage: #7A8C6E;
            --dust: #E8E0D4;
            --muted: #8A7E72;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html { scroll-behavior: smooth; }

        body {
            background-color: var(--cream);
            color: var(--ink);
            font-family: 'DM Sans', sans-serif;
            font-weight: 300;
            min-height: 100vh;
            overflow-x: hidden;
        }

        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)' opacity='0.04'/%3E%3C/svg%3E");
            pointer-events: none;
            z-index: 999;
            opacity: 0.4;
        }

        /* NAV */
        nav {
            position: fixed;
            top: 0; left: 0; right: 0;
            z-index: 100;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 24px 48px;
        }

        .nav-logo {
            font-family: 'Bebas Neue', sans-serif;
            font-size: 1.6rem;
            letter-spacing: 0.12em;
            color: var(--ink);
            text-decoration: none;
        }

        .nav-links {
            display: flex;
            gap: 32px;
            align-items: center;
            list-style: none;
        }

        .nav-links a {
            font-size: 0.78rem;
            font-weight: 400;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--ink);
            text-decoration: none;
            opacity: 0.7;
            transition: opacity 0.2s;
        }

        .nav-links a:hover { opacity: 1; }

        .nav-links .btn-primary {
            background: var(--ink);
            color: var(--cream);
            padding: 10px 24px;
            border-radius: 2px;
            opacity: 1;
            transition: background 0.2s;
        }

        .nav-links .btn-primary:hover { background: var(--rust); }

        /* HERO */
        .hero {
            min-height: 100vh;
            display: grid;
            grid-template-columns: 1fr 1fr;
            position: relative;
        }

        .hero-left {
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            padding: 120px 48px 80px;
            position: relative;
            z-index: 2;
        }

        .hero-eyebrow {
            font-size: 0.72rem;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: var(--caramel);
            margin-bottom: 28px;
            opacity: 0;
            animation: fadeUp 0.8s ease forwards 0.2s;
        }

        .hero-title {
            font-family: 'Cormorant Garamond', serif;
            font-size: clamp(4.5rem, 8vw, 8rem);
            font-weight: 300;
            line-height: 0.92;
            letter-spacing: -0.02em;
            margin-bottom: 40px;
            opacity: 0;
            animation: fadeUp 0.9s ease forwards 0.4s;
        }

        .hero-title em {
            font-style: italic;
            color: var(--rust);
        }

        .hero-subtitle {
            font-size: 0.95rem;
            line-height: 1.75;
            color: var(--muted);
            max-width: 360px;
            margin-bottom: 56px;
            opacity: 0;
            animation: fadeUp 0.9s ease forwards 0.6s;
        }

        .hero-cta {
            display: flex;
            gap: 16px;
            align-items: center;
            opacity: 0;
            animation: fadeUp 0.9s ease forwards 0.8s;
        }

        .cta-main {
            background: var(--ink);
            color: var(--cream);
            padding: 16px 40px;
            font-size: 0.78rem;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            text-decoration: none;
            border-radius: 2px;
            transition: background 0.25s, transform 0.2s;
        }

        .cta-main:hover {
            background: var(--rust);
            transform: translateY(-2px);
        }

        .cta-ghost {
            color: var(--ink);
            font-size: 0.78rem;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            opacity: 0.6;
            transition: opacity 0.2s;
        }

        .cta-ghost::after {
            content: '→';
            transition: transform 0.2s;
        }

        .cta-ghost:hover { opacity: 1; }
        .cta-ghost:hover::after { transform: translateX(4px); }

        /* Hero Right */
        .hero-right {
            position: relative;
            overflow: hidden;
        }

        .hero-right::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, #D4A96A 0%, #C8883A 30%, #B04E2A 60%, #6B3020 100%);
            z-index: 0;
        }

        .hero-right::after {
            content: '';
            position: absolute;
            inset: 0;
            background-image:
                repeating-linear-gradient(45deg, transparent, transparent 40px, rgba(255,255,255,0.04) 40px, rgba(255,255,255,0.04) 41px),
                repeating-linear-gradient(-45deg, transparent, transparent 40px, rgba(255,255,255,0.04) 40px, rgba(255,255,255,0.04) 41px);
            z-index: 1;
        }

        .hero-right-content {
            position: relative;
            z-index: 2;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 80px 48px;
        }

        .hero-number {
            font-family: 'Bebas Neue', sans-serif;
            font-size: clamp(10rem, 18vw, 20rem);
            color: rgba(255,255,255,0.07);
            line-height: 1;
            position: absolute;
            bottom: -20px;
            right: -10px;
            letter-spacing: -0.05em;
            user-select: none;
        }

        .hero-card {
            background: rgba(245, 240, 232, 0.12);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 4px;
            padding: 40px;
            max-width: 320px;
            opacity: 0;
            animation: fadeUp 1s ease forwards 1s;
        }

        .hero-card-tag {
            font-size: 0.68rem;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: rgba(255,255,255,0.6);
            margin-bottom: 20px;
        }

        .hero-card-title {
            font-family: 'Cormorant Garamond', serif;
            font-size: 2rem;
            font-weight: 300;
            color: var(--cream);
            line-height: 1.2;
            margin-bottom: 16px;
        }

        .hero-card-title em { font-style: italic; }

        .hero-card-text {
            font-size: 0.82rem;
            color: rgba(255,255,255,0.65);
            line-height: 1.7;
        }

        .vertical-text {
            position: absolute;
            right: 48px;
            top: 50%;
            font-size: 0.65rem;
            letter-spacing: 0.3em;
            text-transform: uppercase;
            color: rgba(255,255,255,0.3);
            white-space: nowrap;
            writing-mode: vertical-rl;
        }

        /* MARQUEE */
        .marquee-strip {
            background: var(--ink);
            color: var(--cream);
            padding: 14px 0;
            overflow: hidden;
            white-space: nowrap;
        }

        .marquee-inner {
            display: inline-flex;
            animation: marquee 30s linear infinite;
        }

        .marquee-item {
            font-family: 'Bebas Neue', sans-serif;
            font-size: 0.9rem;
            letter-spacing: 0.15em;
            padding: 0 40px;
            opacity: 0.7;
        }

        .marquee-dot {
            color: var(--caramel);
            opacity: 1 !important;
        }

        /* FEATURES */
        .features {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2px;
            background: var(--dust);
        }

        .feature-cell {
            background: var(--cream);
            padding: 64px 40px;
            transition: background 0.3s;
            cursor: default;
        }

        .feature-cell:hover { background: var(--ink); }
        .feature-cell:hover .feature-title,
        .feature-cell:hover .feature-text { color: var(--cream); }
        .feature-cell:hover .feature-num { color: var(--caramel); }

        .feature-num {
            font-family: 'Bebas Neue', sans-serif;
            font-size: 3.5rem;
            color: var(--dust);
            line-height: 1;
            margin-bottom: 24px;
            transition: color 0.3s;
        }

        .feature-title {
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.6rem;
            font-weight: 400;
            margin-bottom: 16px;
            transition: color 0.3s;
        }

        .feature-text {
            font-size: 0.85rem;
            line-height: 1.8;
            color: var(--muted);
            transition: color 0.3s;
        }

        /* FOOTER */
        footer {
            background: var(--ink);
            color: rgba(245,240,232,0.4);
            padding: 40px 48px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        footer a {
            color: rgba(245,240,232,0.4);
            text-decoration: none;
            font-size: 0.78rem;
            letter-spacing: 0.05em;
            transition: color 0.2s;
        }

        footer a:hover { color: var(--caramel); }

        .footer-logo {
            font-family: 'Bebas Neue', sans-serif;
            font-size: 1.4rem;
            letter-spacing: 0.12em;
            color: var(--cream);
        }

        /* ANIMATIONS */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(30px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        @keyframes marquee {
            from { transform: translateX(0); }
            to   { transform: translateX(-50%); }
        }

        /* RESPONSIVE */
        @media (max-width: 768px) {
            nav { padding: 20px 24px; }
            .nav-links { display: none; }

            .hero { grid-template-columns: 1fr; }
            .hero-left { padding: 100px 24px 60px; order: 2; }
            .hero-right { min-height: 50vh; order: 1; }

            .features { grid-template-columns: 1fr; }

            footer {
                flex-direction: column;
                gap: 16px;
                text-align: center;
                padding: 32px 24px;
            }
        }
    </style>
</head>
<body>

    <!-- NAV -->
    <nav>
        <a href="/" class="nav-logo">StyleHub</a>
        <ul class="nav-links">
            <li><a href="#">Collections</a></li>
            <li><a href="#">Lookbook</a></li>
            <li><a href="#">About</a></li>
            @if (Route::has('login'))
                @auth
                    <li><a href="{{ url('/dashboard') }}" class="btn-primary">Dashboard</a></li>
                @else
                    <li><a href="{{ route('login') }}">Sign In</a></li>
                    @if (Route::has('register'))
                        <li><a href="{{ route('register') }}" class="btn-primary">Get Started</a></li>
                    @endif
                @endauth
            @endif
        </ul>
    </nav>

    <!-- HERO -->
    <section class="hero">
        <div class="hero-left">
            <span class="hero-eyebrow">SS 2025 Collection — Now Live</span>
            <h1 class="hero-title">
                Your<br>
                <em>Style,</em><br>
                Redefined.
            </h1>
            <p class="hero-subtitle">
                Curate your wardrobe with intention. StyleHub connects you with independent designers, seasonal edits, and a community that believes fashion is personal expression.
            </p>
            <div class="hero-cta">
                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="cta-main">Start Exploring</a>
                @endif
                <a href="#features" class="cta-ghost">Learn more</a>
            </div>
        </div>

        <div class="hero-right">
            <div class="hero-right-content">
                <div class="hero-card">
                    <p class="hero-card-tag">Editor's Pick — March 2025</p>
                    <h2 class="hero-card-title">The Art of <em>Effortless</em> Dressing</h2>
                    <p class="hero-card-text">From minimalist staples to bold statements — discover pieces that tell your story without saying a word.</p>
                </div>
                <span class="hero-number">25</span>
                <span class="vertical-text">StyleHub · Fashion Platform · Est. 2025</span>
            </div>
        </div>
    </section>

    <!-- MARQUEE -->
    <div class="marquee-strip">
        <div class="marquee-inner">
            @foreach (range(1, 2) as $i)
                <span class="marquee-item">New Arrivals</span>
                <span class="marquee-item marquee-dot">✦</span>
                <span class="marquee-item">Independent Designers</span>
                <span class="marquee-item marquee-dot">✦</span>
                <span class="marquee-item">Sustainable Fashion</span>
                <span class="marquee-item marquee-dot">✦</span>
                <span class="marquee-item">Curated Edits</span>
                <span class="marquee-item marquee-dot">✦</span>
                <span class="marquee-item">Personal Style</span>
                <span class="marquee-item marquee-dot">✦</span>
                <span class="marquee-item">Free Shipping</span>
                <span class="marquee-item marquee-dot">✦</span>
            @endforeach
        </div>
    </div>

    <!-- FEATURES -->
    <section class="features" id="features">
        <div class="feature-cell">
            <div class="feature-num">01</div>
            <h3 class="feature-title">Curated Collections</h3>
            <p class="feature-text">Every piece is handpicked by our editorial team. No noise, just fashion that resonates with your personal aesthetic.</p>
        </div>
        <div class="feature-cell">
            <div class="feature-num">02</div>
            <h3 class="feature-title">Independent Voices</h3>
            <p class="feature-text">Support emerging designers from around the globe. Unique pieces you won't find anywhere else — fashion with a story.</p>
        </div>
        <div class="feature-cell">
            <div class="feature-num">03</div>
            <h3 class="feature-title">Style, Sustainably</h3>
            <p class="feature-text">We partner with brands who care about the planet. Thoughtful consumption, beautiful results — style without compromise.</p>
        </div>
    </section>

    <!-- FOOTER -->
    <footer>
        <span class="footer-logo">StyleHub</span>
        <span style="font-size: 0.78rem;">© 2025 StyleHub. All rights reserved.</span>
        <div style="display: flex; gap: 24px;">
            <a href="#">Privacy</a>
            <a href="#">Terms</a>
            <a href="#">Contact</a>
        </div>
    </footer>

</body>
</html>