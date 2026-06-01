<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>CAReva — Predict The Future Value Of Your Car</title>
<link rel="stylesheet" href="style.css">
<style>
/* Landing Page Specific */
body { overflow-x: hidden; }

/* Hero */
.hero {
    min-height: 100vh;
    display: flex; align-items: center; justify-content: center;
    text-align: center;
    position: relative;
    padding: 8rem 2rem 4rem;
    background:
        radial-gradient(ellipse at 20% 60%, rgba(0,212,255,0.08) 0%, transparent 55%),
        radial-gradient(ellipse at 80% 20%, rgba(0,153,204,0.05) 0%, transparent 50%),
        radial-gradient(ellipse at 50% 100%, rgba(0,212,255,0.04) 0%, transparent 60%),
        #030508;
    overflow: hidden;
}
.hero::before {
    content: '';
    position: absolute; inset: 0;
    background-image:
        repeating-linear-gradient(0deg, rgba(0,212,255,0.03) 0px, transparent 1px, transparent 60px, rgba(0,212,255,0.03) 60px),
        repeating-linear-gradient(90deg, rgba(0,212,255,0.03) 0px, transparent 1px, transparent 60px, rgba(0,212,255,0.03) 60px);
    pointer-events: none;
}
.hero-content { position: relative; z-index: 2; max-width: 800px; }
.hero-tag {
    display: inline-flex; align-items: center; gap: 0.5rem;
    padding: 0.4rem 1.2rem;
    background: rgba(0,212,255,0.1);
    border: 1px solid rgba(0,212,255,0.3);
    border-radius: 20px;
    font-family: var(--font-accent); font-size: 0.85rem; font-weight: 600;
    color: var(--neon); letter-spacing: 2px; text-transform: uppercase;
    margin-bottom: 2rem;
    animation: fadeInDown 0.8s ease both;
}
.hero-tag::before { content: '◆'; font-size: 0.6rem; }
.hero-title {
    font-family: var(--font-display);
    font-size: clamp(2.5rem, 6vw, 5rem);
    font-weight: 900; line-height: 1.05;
    letter-spacing: 2px;
    margin-bottom: 1.5rem;
    animation: fadeInUp 0.8s 0.2s ease both;
}
.hero-title .line1 { display: block; color: var(--text-primary); }
.hero-title .line2 { display: block; color: var(--neon); text-shadow: var(--neon-glow); }
.hero-sub {
    font-family: var(--font-accent); font-size: 1.2rem;
    color: var(--text-secondary); line-height: 1.6;
    max-width: 600px; margin: 0 auto 2.5rem;
    animation: fadeInUp 0.8s 0.4s ease both;
}
.hero-actions {
    display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;
    animation: fadeInUp 0.8s 0.6s ease both;
}
.hero-actions .btn { padding: 0.9rem 2.2rem; font-size: 1.05rem; }

/* Floating car SVG */
.hero-car {
    position: absolute; bottom: 5%; right: 5%;
    width: 280px; opacity: 0.12;
    animation: floatCar 4s ease-in-out infinite;
    pointer-events: none;
}
@keyframes floatCar {
    0%, 100% { transform: translateY(0) rotate(-3deg); }
    50% { transform: translateY(-20px) rotate(-1deg); }
}
/* Glowing rings */
.ring {
    position: absolute; border-radius: 50%;
    border: 1px solid rgba(0,212,255,0.08);
    animation: pulse 4s ease infinite;
    pointer-events: none;
}
.ring-1 { width: 600px; height: 600px; top: 50%; left: 50%; transform: translate(-50%, -50%); animation-delay: 0s; }
.ring-2 { width: 900px; height: 900px; top: 50%; left: 50%; transform: translate(-50%, -50%); animation-delay: 1s; }
.ring-3 { width: 1200px; height: 1200px; top: 50%; left: 50%; transform: translate(-50%, -50%); animation-delay: 2s; }
@keyframes pulse {
    0%, 100% { opacity: 0.4; transform: translate(-50%, -50%) scale(1); }
    50% { opacity: 0.8; transform: translate(-50%, -50%) scale(1.02); }
}

/* Stats strip */
.stats-strip {
    background: rgba(0,212,255,0.04);
    border-top: 1px solid var(--border-glass);
    border-bottom: 1px solid var(--border-glass);
    padding: 2rem;
    display: flex; justify-content: center; gap: 4rem; flex-wrap: wrap;
}
.strip-stat { text-align: center; }
.strip-val {
    font-family: var(--font-display); font-size: 2rem; font-weight: 700;
    color: var(--neon); text-shadow: var(--neon-glow-sm);
}
.strip-lbl { color: var(--text-muted); font-size: 0.85rem; margin-top: 0.2rem; }

/* Features */
.section { padding: 5rem 2rem; max-width: 1200px; margin: 0 auto; }
.section-header { text-align: center; margin-bottom: 3.5rem; }
.section-eyebrow {
    font-family: var(--font-accent); font-size: 0.85rem; font-weight: 600;
    color: var(--neon); letter-spacing: 3px; text-transform: uppercase;
    margin-bottom: 0.8rem;
}
.section-h2 {
    font-family: var(--font-display); font-size: 2.5rem; font-weight: 700;
    color: var(--text-primary); letter-spacing: 1px;
}
.section-h2 span { color: var(--neon); text-shadow: var(--neon-glow-sm); }

.features-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.5rem; }
.feature-card {
    background: var(--bg-glass);
    border: 1px solid var(--border-glass);
    border-radius: var(--radius-lg);
    padding: 2rem;
    transition: var(--transition);
    position: relative; overflow: hidden;
}
.feature-card::before {
    content: '';
    position: absolute; top: 0; left: -100%;
    width: 100%; height: 2px;
    background: linear-gradient(90deg, transparent, var(--neon), transparent);
    transition: left 0.5s ease;
}
.feature-card:hover { border-color: var(--border-glass2); transform: translateY(-5px); box-shadow: 0 10px 40px rgba(0,212,255,0.12); }
.feature-card:hover::before { left: 100%; }
.feature-icon { font-size: 2.5rem; margin-bottom: 1rem; display: block; }
.feature-title { font-family: var(--font-accent); font-size: 1.1rem; font-weight: 700; margin-bottom: 0.5rem; color: var(--text-primary); }
.feature-desc { color: var(--text-secondary); font-size: 0.9rem; line-height: 1.7; }

/* How it works */
.steps-container {
    background: var(--bg-dark);
    padding: 5rem 2rem;
    border-top: 1px solid var(--border-glass);
    border-bottom: 1px solid var(--border-glass);
}
.steps-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 2rem; max-width: 1200px; margin: 0 auto; }
.step-item { text-align: center; padding: 1.5rem; }
.step-num {
    width: 60px; height: 60px;
    background: transparent;
    border: 2px solid var(--neon);
    border-radius: 50%;
    display: inline-flex; align-items: center; justify-content: center;
    font-family: var(--font-display); font-size: 1.3rem; font-weight: 700;
    color: var(--neon);
    text-shadow: var(--neon-glow-sm);
    box-shadow: var(--neon-glow-sm);
    margin-bottom: 1rem;
}
.step-title { font-family: var(--font-accent); font-size: 1rem; font-weight: 600; margin-bottom: 0.5rem; }
.step-desc { color: var(--text-muted); font-size: 0.88rem; }

/* CTA */
.cta-section {
    padding: 5rem 2rem;
    text-align: center;
    background: radial-gradient(ellipse at 50% 50%, rgba(0,212,255,0.06) 0%, transparent 70%);
}
.cta-title {
    font-family: var(--font-display); font-size: 2.5rem;
    color: var(--text-primary); margin-bottom: 1rem; letter-spacing: 1px;
}
.cta-title span { color: var(--neon); text-shadow: var(--neon-glow-sm); }
.cta-sub { color: var(--text-secondary); margin-bottom: 2rem; font-size: 1.05rem; }

/* About / Contact */
.about-contact { display: grid; grid-template-columns: 1fr 1fr; gap: 3rem; padding: 5rem 2rem; max-width: 1200px; margin: 0 auto; }
.about-text h3, .contact-info h3 {
    font-family: var(--font-display); font-size: 1.5rem; color: var(--neon);
    letter-spacing: 2px; margin-bottom: 1rem;
}
.about-text p { color: var(--text-secondary); line-height: 1.8; margin-bottom: 1rem; }
.contact-item { display: flex; gap: 1rem; margin-bottom: 1rem; align-items: flex-start; }
.contact-item-icon { font-size: 1.2rem; color: var(--neon); margin-top: 2px; }
.contact-item-text { color: var(--text-secondary); font-size: 0.9rem; }
.contact-item-text strong { display: block; color: var(--text-primary); font-family: var(--font-accent); }

@keyframes fadeInDown { from { opacity: 0; transform: translateY(-20px); } to { opacity: 1; transform: translateY(0); } }
@keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

@media (max-width: 768px) {
    .about-contact { grid-template-columns: 1fr; }
    .stats-strip { gap: 2rem; }
    .hero-car { width: 150px; }
}
</style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar" id="navbar">
    <a href="index.php" class="nav-logo">CAR<span>eva</span></a>
    <ul class="nav-links">
        <li><a href="#features">Features</a></li>
        <li><a href="#how-it-works">How It Works</a></li>
        <li><a href="#about">About</a></li>
        <li><a href="#contact">Contact</a></li>
    </ul>
    <div class="nav-actions">
        <a href="login.php" class="btn btn-ghost">Login</a>
        <a href="signup.php" class="btn btn-primary">Sign Up</a>
    </div>
</nav>

<!-- HERO -->
<section class="hero">
    <div class="ring ring-1"></div>
    <div class="ring ring-2"></div>
    <div class="ring ring-3"></div>
    <svg class="hero-car" viewBox="0 0 200 80" fill="none" xmlns="http://www.w3.org/2000/svg">
        <rect x="20" y="30" width="160" height="40" rx="8" fill="#00d4ff"/>
        <rect x="50" y="15" width="100" height="30" rx="6" fill="#00d4ff"/>
        <circle cx="55" cy="72" r="12" fill="#00d4ff"/>
        <circle cx="145" cy="72" r="12" fill="#00d4ff"/>
        <rect x="55" y="20" width="40" height="20" rx="3" fill="#030508" opacity="0.5"/>
        <rect x="100" y="20" width="40" height="20" rx="3" fill="#030508" opacity="0.5"/>
    </svg>
    <div class="hero-content">
        <div class="hero-tag">AI-Powered Automotive Intelligence</div>
        <h1 class="hero-title">
            <span class="line1">Predict The Future</span>
            <span class="line2">Value Of Your Car</span>
        </h1>
        <p class="hero-sub">Buy, Sell and Analyze Cars With AI-Powered Future Predictions. Know what your vehicle is worth today — and tomorrow.</p>
        <div class="hero-actions">
            <a href="signup.php" class="btn btn-primary btn-lg">🚀 Get Started Free</a>
            <a href="#features" class="btn btn-ghost btn-lg">Explore Features</a>
        </div>
    </div>
</section>

<!-- STATS STRIP -->
<div class="stats-strip">
    <div class="strip-stat"><div class="strip-val">50K+</div><div class="strip-lbl">Cars Listed</div></div>
    <div class="strip-stat"><div class="strip-val">1M+</div><div class="strip-lbl">Predictions Made</div></div>
    <div class="strip-stat"><div class="strip-val">200+</div><div class="strip-lbl">Cities Covered</div></div>
    <div class="strip-stat"><div class="strip-val">98%</div><div class="strip-lbl">Prediction Accuracy</div></div>
</div>

<!-- FEATURES -->
<section id="features">
    <div class="section">
        <div class="section-header">
            <p class="section-eyebrow">What We Offer</p>
            <h2 class="section-h2">Everything You Need For <span>Smart Car Decisions</span></h2>
        </div>
        <div class="features-grid">
            <div class="feature-card">
                <span class="feature-icon">🔮</span>
                <h3 class="feature-title">AI Future Predictor</h3>
                <p class="feature-desc">Get 5, 10 and 20-year value predictions for any car. Our algorithm factors in depreciation, fuel type, brand history and market trends.</p>
            </div>
            <div class="feature-card">
                <span class="feature-icon">🚗</span>
                <h3 class="feature-title">Buy & Sell Marketplace</h3>
                <p class="feature-desc">Browse thousands of verified listings or list your car in minutes. Transparent pricing with no hidden fees.</p>
            </div>
            <div class="feature-card">
                <span class="feature-icon">📍</span>
                <h3 class="feature-title">Nearby Cars</h3>
                <p class="feature-desc">Find cars near you using GPS. Sort by distance and discover the best deals in your neighbourhood.</p>
            </div>
            <div class="feature-card">
                <span class="feature-icon">📊</span>
                <h3 class="feature-title">Depreciation Charts</h3>
                <p class="feature-desc">Visual depreciation curves help you understand exactly how your car's value changes over time.</p>
            </div>
            <div class="feature-card">
                <span class="feature-icon">🛡️</span>
                <h3 class="feature-title">Verified Listings</h3>
                <p class="feature-desc">Every listing goes through our verification process. Number plate images required for added trust.</p>
            </div>
            <div class="feature-card">
                <span class="feature-icon">❤️</span>
                <h3 class="feature-title">Wishlist & History</h3>
                <p class="feature-desc">Save cars to your wishlist and revisit your prediction history anytime from your personal profile.</p>
            </div>
        </div>
    </div>
</section>

<!-- HOW IT WORKS -->
<section id="how-it-works" class="steps-container">
    <div style="text-align:center;margin-bottom:3rem;">
        <p class="section-eyebrow" style="font-family:var(--font-accent);font-size:0.85rem;font-weight:600;color:var(--neon);letter-spacing:3px;text-transform:uppercase;margin-bottom:0.8rem;">Simple Process</p>
        <h2 class="section-h2" style="font-family:var(--font-display);font-size:2.5rem;font-weight:700;color:var(--text-primary);">How <span style="color:var(--neon);">CAReva</span> Works</h2>
    </div>
    <div class="steps-grid">
        <div class="step-item"><div class="step-num">01</div><h4 class="step-title">Create Account</h4><p class="step-desc">Sign up free in under 2 minutes. Just your name, email and location.</p></div>
        <div class="step-item"><div class="step-num">02</div><h4 class="step-title">Enter Car Details</h4><p class="step-desc">Brand, model, year, fuel type and purchase price is all we need.</p></div>
        <div class="step-item"><div class="step-num">03</div><h4 class="step-title">Get Predictions</h4><p class="step-desc">Instant AI-powered 5, 10 and 20 year value forecasts with charts.</p></div>
        <div class="step-item"><div class="step-num">04</div><h4 class="step-title">Buy or Sell</h4><p class="step-desc">Use your insights to make smart buying or selling decisions.</p></div>
    </div>
</section>

<!-- ABOUT & CONTACT -->
<div id="about">
    <div class="about-contact">
        <div class="about-text">
            <h3>About CAReva</h3>
            <p>CAReva is India's most advanced automotive intelligence platform. Built for buyers, sellers and car enthusiasts who want more than just a listing site.</p>
            <p>Our AI prediction engine analyses thousands of data points — brand history, fuel type trends, market demand and economic factors — to give you the most accurate future value estimate available.</p>
            <p>Whether you're planning to buy your first car or want to know the right time to sell your current one, CAReva gives you the data-driven edge.</p>
        </div>
        <div id="contact" class="contact-info">
            <h3>Contact Us</h3>
            <div class="contact-item">
                <div class="contact-item-icon">📧</div>
                <div class="contact-item-text"><strong>Email</strong>hello@careva.com</div>
            </div>
            <div class="contact-item">
                <div class="contact-item-icon">📞</div>
                <div class="contact-item-text"><strong>Phone</strong>+91 98765 43210</div>
            </div>
            <div class="contact-item">
                <div class="contact-item-icon">📍</div>
                <div class="contact-item-text"><strong>Address</strong>Tech Park, Whitefield, Bangalore — 560066</div>
            </div>
            <div class="contact-item">
                <div class="contact-item-icon">⏰</div>
                <div class="contact-item-text"><strong>Support Hours</strong>Mon–Sat, 9 AM – 7 PM IST</div>
            </div>
        </div>
    </div>
</div>

<!-- CTA -->
<div class="cta-section">
    <h2 class="cta-title">Ready to Make <span>Smarter</span> Car Decisions?</h2>
    <p class="cta-sub">Join thousands of Indians who trust CAReva for buying, selling and predicting car values.</p>
    <div style="display:flex;gap:1rem;justify-content:center;flex-wrap:wrap;">
        <a href="signup.php" class="btn btn-primary btn-lg">Create Free Account</a>
        <a href="login.php" class="btn btn-ghost btn-lg">Sign In</a>
    </div>
</div>

<footer>
    <a href="index.php" style="font-family:var(--font-display);font-size:1.2rem;letter-spacing:3px;">CAReva</a>
    <p style="margin-top:0.5rem;">© 2025 CAReva. All rights reserved. Built with ❤️ for India's automotive community.</p>
</footer>

<script src="js/script.js"></script>
</body>
</html>