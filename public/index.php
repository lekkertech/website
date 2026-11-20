<?php

// Configuration comes from the environment (see .env.example and README.md).
$config = [
    'site_key'         => getenv('RECAPTCHA_SITE_KEY') ?: '',
    'secret_key'       => getenv('RECAPTCHA_SECRET_KEY') ?: '',
    'slack_invite_url' => getenv('SLACK_INVITE_URL') ?: '',
    'app_version'      => getenv('APP_VERSION') ?: 'dev',
];

// Which build is serving, on every response. Baked into the image; see Dockerfile.
header('X-App-Version: ' . $config['app_version']);

// Behind Cloudflare the socket peer is the proxy; the visitor's address is in CF-Connecting-IP.
$clientIp = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['REMOTE_ADDR'] ?? null;

function verifyRecaptchaCurl($token, $secretKey, $userIP = null) {
    $url = 'https://www.google.com/recaptcha/api/siteverify';

    $data = [
        'secret' => $secretKey,
        'response' => $token
    ];

    if ($userIP) {
        $data['remoteip'] = $userIP;
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if (curl_error($ch)) {
        return ['success' => false, 'error' => 'cURL error: ' . curl_error($ch)];
    }

    if ($httpCode !== 200) {
        return ['success' => false, 'error' => 'HTTP error: ' . $httpCode];
    }

    return json_decode($response, true);
}

$token = $_POST['token'] ?? null;
if ($token !== null) {
    $json = verifyRecaptchaCurl($token, $config['secret_key'], $clientIp);

    if (($json['success'] ?? false) === true) {
        if ($config['slack_invite_url'] === '') {
            error_log('SLACK_INVITE_URL is not set; redirecting nowhere');
        }
        header('Location: ' . $config['slack_invite_url']);
        exit;
    }

    // Google's reply carries "error-codes"; our own failures carry "error".
    // The visitor gets a generic message; the reason goes to the error log.
    $reason = $json['error'] ?? implode(',', $json['error-codes'] ?? ['no usable response']);
    error_log('reCAPTCHA verification failed: ' . $reason);
    echo 'Unknown error.';
} else {
?><!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Lekker Tech | South African Tech Slack Community 🔥</title>
<meta name="description" content="Lekker Tech is a free Slack community for South African developers and tech workers, at home and abroad. Connect over boerewors, bytes, and banter.">

<link rel="canonical" href="https://lekkertech.org.za/" />

<script async src="https://www.googletagmanager.com/gtag/js?id=G-9K7PZHF635"></script>
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', 'G-9K7PZHF635');
</script>

<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "Organization",
    "name": "Lekker Tech",
    "url": "https://lekkertech.org.za/",
    "logo": "https://lekkertech.org.za/android-chrome-512x512.png",
    "description": "A free Slack community for South African developers and tech workers, at home and abroad.",
    "areaServed": "ZA",
    "sameAs": ["https://github.com/lekkertech", "https://bsky.app/profile/lekkertech.bsky.social", "https://x.com/LekkerTechSlack", "https://www.linkedin.com/company/lekker-tech", "https://www.facebook.com/lekkertechslack"]
}
</script>

<link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
<link rel="icon" type="image/x-icon" href="/favicon.ico">
<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
<link rel="manifest" href="/site.webmanifest">

<!-- Open Graph / Facebook -->
<meta property="og:type" content="website">
<meta property="og:url" content="https://lekkertech.org.za/">
<meta property="og:title" content="Lekker Tech | South African Tech Slack Community 🔥">
<meta property="og:description" content="Lekker Tech is a free Slack community for South African developers and tech workers, at home and abroad. Connect over boerewors, bytes, and banter.">
<meta property="og:image" content="https://lekkertech.org.za/lekkertech-preview-og.png">

<!-- Twitter -->
<meta property="twitter:card" content="summary_large_image">
<meta name="twitter:site" content="@LekkerTechSlack">
<meta property="twitter:url" content="https://lekkertech.org.za/">
<meta property="twitter:title" content="Lekker Tech | South African Tech Slack Community 🔥">
<meta property="twitter:description" content="Lekker Tech is a free Slack community for South African developers and tech workers, at home and abroad. Connect over boerewors, bytes, and banter.">
<meta property="twitter:image" content="https://lekkertech.org.za/lekkertech-preview-twitter.png">

<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Arial', sans-serif;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
        overflow-x: hidden;
        position: relative;
    }

    .parallax-bg {
        position: fixed;
        top: 0;
        left: 0;
        width: 120%;
        height: 120%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #667eea 100%);
        background-size: 200% 200%;
        animation: gradientShift 20s ease infinite;
        z-index: -1;
        transform: translate(-10%, -10%);
    }

    @keyframes gradientShift {
        0% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
        100% { background-position: 0% 50%; }
    }

    .stars {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        pointer-events: none;
        z-index: 1;
    }

    .star {
        position: absolute;
        width: 2px;
        height: 2px;
        background: white;
        border-radius: 50%;
        animation: twinkle 2s infinite;
    }

    @keyframes twinkle {
        0%, 100% { opacity: 0.3; }
        50% { opacity: 1; }
    }

    .container {
        position: relative;
        z-index: 2;
        max-width: 800px;
        margin: 0 auto;
        padding: 2rem;
        text-align: center;
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .logo {
        font-size: 4rem;
        margin-bottom: 1rem;
        animation: bounce 2s infinite, glow 3s infinite;
        text-shadow: 0 0 20px rgba(255, 255, 255, 0.6);
    }

    @keyframes glow {
        0%, 100% {
            text-shadow: 0 0 20px rgba(255, 255, 255, 0.6), 0 0 40px rgba(255, 255, 255, 0.4);
        }
        50% {
            text-shadow: 0 0 30px rgba(255, 255, 255, 0.9), 0 0 60px rgba(255, 255, 255, 0.6), 0 0 80px rgba(255, 255, 255, 0.3);
        }
    }

    @keyframes bounce {
        0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
        40% { transform: translateY(-10px); }
        60% { transform: translateY(-5px); }
    }

    h1 {
        font-size: 3.5rem;
        font-weight: bold;
        background: linear-gradient(45deg, #ff6b6b, #ffd93d, #6bcf7f, #4ecdc4);
        background-size: 400% 400%;
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        animation: rainbow 3s infinite;
        margin-bottom: 1rem;
        text-shadow: 0 0 30px rgba(255, 255, 255, 0.5);
        text-transform: uppercase;
    }

    @keyframes rainbow {
        0% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
        100% { background-position: 0% 50%; }
    }

    .subtitle {
        font-size: 1.5rem;
        font-weight: normal;
        color: #fff;
        margin-bottom: 2rem;
        text-shadow: 0 2px 4px rgba(0,0,0,0.3);
    }

    .meme-text {
        font-size: 1.1rem;
        color: #fff;
        margin-bottom: 2rem;
        background: rgba(255, 255, 255, 0.1);
        padding: 1.5rem;
        border-radius: 15px;
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        line-height: 1.6;
    }

    .faq {
        text-align: left;
        margin-top: 2rem;
    }

    .faq h2 {
        font-size: 1.2rem;
        margin-bottom: 1rem;
        text-align: center;
    }

    .faq summary {
        cursor: pointer;
        font-weight: bold;
    }

    .faq details + details {
        margin-top: 0.8rem;
    }

    .faq details p {
        margin-top: 0.4rem;
    }

    .meme-text p {
        margin-bottom: 0.8rem;
    }

    .meme-text p:last-child {
        margin-bottom: 0;
    }

    .cta-note {
        margin-top: 0.75rem;
        font-size: 0.85rem;
        color: rgba(255, 255, 255, 0.8);
    }

    .cta-button {
        display: inline-block;
        padding: 1rem 2rem;
        font-size: 1.3rem;
        font-weight: bold;
        text-decoration: none;
        color: white;
        background: linear-gradient(45deg, #ff6b6b, #4ecdc4);
        border-radius: 50px;
        transition: all 0.3s ease;
        box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        position: relative;
        overflow: hidden;
    }

    .cta-button:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 40px rgba(0,0,0,0.4);
    }

    .cta-button::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
        transition: left 0.5s;
    }

    .cta-button:hover::before {
        left: 100%;
    }

    .cta-button.loading {
        background: linear-gradient(45deg, #888, #666);
        cursor: not-allowed;
        transform: none !important;
        box-shadow: 0 10px 30px rgba(0,0,0,0.3) !important;
    }

    .cta-button.loading::before {
        display: none;
    }

    .loading-spinner {
        display: inline-block;
        width: 20px;
        height: 20px;
        border: 2px solid rgba(255,255,255,0.3);
        border-radius: 50%;
        border-top-color: #fff;
        animation: spin 1s ease-in-out infinite;
        margin-right: 10px;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    .page-loader {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        transition: opacity 0.5s ease-out;
    }

    .grecaptcha-badge {
        z-index: 3;
    }

    .page-loader.fade-out {
        opacity: 0;
        pointer-events: none;
    }

    .loader-content {
        text-align: center;
        color: white;
    }

    .loader-rocket {
        font-size: 4rem;
        margin-bottom: 1rem;
        animation: bounce 1s infinite;
    }

    .loader-text {
        font-size: 1.2rem;
        margin-bottom: 1rem;
        opacity: 0.9;
    }

    .loader-dots {
        display: inline-block;
    }

    .loader-dots span {
        display: inline-block;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background-color: white;
        margin: 0 3px;
        animation: dotPulse 1.4s infinite ease-in-out both;
    }

    .loader-dots span:nth-child(1) { animation-delay: -0.32s; }
    .loader-dots span:nth-child(2) { animation-delay: -0.16s; }
    .loader-dots span:nth-child(3) { animation-delay: 0s; }

    @keyframes dotPulse {
        0%, 80%, 100% {
            transform: scale(0.8);
            opacity: 0.5;
        }
        40% {
            transform: scale(1);
            opacity: 1;
        }
    }

    .floating-emojis {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        pointer-events: none;
        z-index: 1;
    }

    .emoji {
        position: absolute;
        font-size: 2rem;
        animation: float 6s infinite linear;
        opacity: 0.7;
    }

    .code-snippet {
        position: absolute;
        font-family: 'Courier New', monospace;
        font-size: 0.8rem;
        background: rgba(0, 0, 0, 0.6);
        color: #88cc88;
        padding: 0.4rem 0.6rem;
        border-radius: 6px;
        border: 1px solid rgba(136, 204, 136, 0.2);
        animation: float 8s linear forwards;
        opacity: 0.5;
        white-space: nowrap;
        backdrop-filter: blur(5px);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
    }

    @keyframes float {
        0% {
            transform: translateY(100vh);
            opacity: 0;
        }
        10% {
            opacity: 0.7;
        }
        90% {
            opacity: 0.7;
        }
        100% {
            transform: translateY(-100px);
            opacity: 0;
        }
    }

    .tech-stack {
        margin-top: 2rem;
        color: #fff;
        font-size: 0.9rem;
        opacity: 0.8;
    }

    .copyright {
        margin-top: 1rem;
        color: #fff;
        font-size: 0.8rem;
        opacity: 0.6;
        transition: opacity 0.3s ease;
    }

    .copyright:hover {
        opacity: 1;
    }

    .tooltip {
        position: relative;
        cursor: help;
        border-bottom: 1px dotted rgba(255, 255, 255, 0.5);
    }

    .tooltip .tooltiptext {
        visibility: hidden;
        width: 300px;
        background-color: rgba(0, 0, 0, 0.9);
        color: #fff;
        text-align: left;
        border-radius: 10px;
        padding: 15px;
        position: absolute;
        z-index: 1000;
        bottom: 125%;
        left: 50%;
        margin-left: -150px;
        opacity: 0;
        transition: opacity 0.3s;
        font-size: 0.85rem;
        line-height: 1.4;
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .tooltip .tooltiptext::after {
        content: "";
        position: absolute;
        top: 100%;
        left: 50%;
        margin-left: -5px;
        border-width: 5px;
        border-style: solid;
        border-color: rgba(0, 0, 0, 0.9) transparent transparent transparent;
    }

    .tooltip:hover .tooltiptext {
        visibility: visible;
        opacity: 1;
    }

    .hero {
        display: flex;
        flex-direction: column;
    }

    .lead {
        margin-bottom: 1rem;
    }

    .vibes {
        list-style: none;
    }

    .vibes li + li {
        margin-top: 0.8rem;
    }

    .vibes .quote,
    .vibes cite {
        font-style: italic;
    }

    .faq a {
        color: #fff;
    }

    .social-icons {
        margin-top: 1.5rem;
        margin-bottom: 0.5rem;
    }

    .social-icons a {
        display: inline-block;
        margin: 0 10px;
        color: #fff;
        opacity: 0.7;
        transition: opacity 0.3s ease, transform 0.3s ease;
    }

    .social-icons svg {
        vertical-align: middle;
    }

    .copyright .credit {
        margin-top: 0.5rem;
        font-size: 0.7rem;
        opacity: 0.6;
    }

    .copyright .credit a {
        color: rgba(255, 255, 255, 0.8);
        text-decoration: none;
    }

    .tooltip button {
        font: inherit;
        color: inherit;
        background: none;
        border: 0;
        padding: 0;
        cursor: help;
    }

    .tooltip:focus-within .tooltiptext {
        visibility: visible;
        opacity: 1;
    }

    .bottom-space {
        height: 10vh;
    }

    .social-icons a:hover {
        opacity: 1 !important;
        transform: translateY(-2px);
    }

    @media (max-width: 768px) {
        h1 {
            font-size: 2.2rem;
            margin-bottom: 0.8rem;
        }
        .subtitle {
            font-size: 1.1rem;
            margin-bottom: 1.5rem;
        }
        .logo {
            font-size: 2.5rem;
            margin-bottom: 0.8rem;
        }
        .container {
            padding: 1rem;
            justify-content: flex-start;
            padding-top: 2rem;
        }

        .meme-text {
            font-size: 0.95rem;
            padding: 1.2rem;
            margin-bottom: 1.5rem;
        }

        .meme-text .emoji {
            font-size: 1.1rem;
        }

        .cta-button {
            font-size: 1.1rem;
            padding: 0.8rem 1.5rem;
        }

        .tech-stack {
            margin-top: 1.5rem;
            font-size: 0.8rem;
        }

        .copyright {
            margin-top: 2rem;
            font-size: 0.7rem;
        }

        .tooltip .tooltiptext {
            width: 250px;
            margin-left: -125px;
            font-size: 0.8rem;
        }
    }

    @media (max-width: 576px) {
        h1 {
            font-size: 1.8rem;
            margin-bottom: 0.6rem;
        }
        .subtitle {
            font-size: 0.95rem;
            margin-bottom: 1.2rem;
        }
        .logo {
            font-size: 2rem;
            margin-bottom: 0.6rem;
        }
        .container {
            padding: 0.8rem;
            justify-content: flex-start;
            padding-top: 1.5rem;
        }

        .meme-text {
            font-size: 0.85rem;
            padding: 1rem;
            margin-bottom: 1.2rem;
        }

        .meme-text .emoji {
            font-size: 1rem;
        }

        .cta-button {
            font-size: 1rem;
            padding: 0.7rem 1.2rem;
        }

        .tech-stack {
            margin-top: 1.2rem;
            font-size: 0.7rem;
        }

        .copyright {
            margin-top: 1.5rem;
            font-size: 0.65rem;
        }

        .tooltip .tooltiptext {
            width: 200px;
            margin-left: -100px;
            font-size: 0.75rem;
        }
    }
</style>

<script defer src="https://cdnjs.cloudflare.com/ajax/libs/jquery/4.1.0/jquery.min.js"></script>

</head>

<body>

<div class="page-loader">
    <div class="loader-content">
        <div class="loader-rocket">🚀</div>
        <div class="loader-text">Loading lekker vibes</div>
        <div class="loader-dots">
            <span></span>
            <span></span>
            <span></span>
        </div>
    </div>
</div>

<div class="parallax-bg"></div>
<div class="stars"></div>
<div class="floating-emojis"></div>

<main class="container">
    <header class="hero">
        <div class="logo" aria-hidden="true">🚀</div>
        <h1>Lekker Tech</h1>
        <h2 class="subtitle"><span aria-hidden="true">💬 </span>The South African Tech Slack Where Code Meets Braai<span aria-hidden="true"> 🔥</span></h2>

        <div class="meme-text lead">
            <p>A free Slack community for South African developers and tech workers, at home and abroad. A relaxed space to connect, socialise, and support one another.</p>
        </div>

        <ul class="meme-text vibes">
            <li><span class="vibe-icon" aria-hidden="true">💻</span> <span><em class="quote">“It's not a bug, it's a feature.”</em> <cite>Every dev, ever</cite></span></li>
            <li><span class="vibe-icon" aria-hidden="true">🌍</span> <span>South African tech wizards making magic happen</span></li>
            <li><span class="vibe-icon" aria-hidden="true">☕</span> <span>Powered by coffee and good vibes</span></li>
        </ul>

        <a href="#" class="cta-button">Join the Lekker Squad <span aria-hidden="true">🎯</span></a>
        <p class="cta-note">Free to join. A quick reCAPTCHA check, then straight into Slack.</p>
    </header>

    <section class="meme-text faq" aria-labelledby="faq-heading">
        <h2 id="faq-heading">Questions</h2>
        <details>
            <summary>What is Lekker Tech?</summary>
            <p>A free Slack community for South African tech workers, wherever they are in the world, to connect, socialise, and support one another.</p>
        </details>
        <details>
            <summary>Who can join?</summary>
            <p>South African developers and tech workers, whether they live in South Africa or abroad.</p>
        </details>
        <details>
            <summary>How do I join the Slack?</summary>
            <p>Click "Join the Lekker Squad" above. After a quick reCAPTCHA check you are sent to the Slack invite.</p>
        </details>
        <details>
            <summary>Does it cost anything?</summary>
            <p>No. Joining is free.</p>
        </details>
        <details>
            <summary>Is the website open source?</summary>
            <p>Yes. The code is on <a href="https://github.com/lekkertech/website">GitHub</a>.</p>
        </details>
    </section>

    <footer>
        <p class="tech-stack">jQuery • PHP • Vim • Coffee • Biltong</p>

        <nav class="social-icons" aria-label="Lekker Tech elsewhere">
            <a href="https://github.com/lekkertech/website" target="_blank" rel="noopener noreferrer" aria-label="GitHub">
                <svg aria-hidden="true" focusable="false" width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M12 .297c-6.63 0-12 5.373-12 12 0 5.303 3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61C4.422 18.07 3.633 17.7 3.633 17.7c-1.087-.744.084-.729.084-.729 1.205.084 1.838 1.236 1.838 1.236 1.07 1.835 2.809 1.305 3.495.998.108-.776.417-1.305.76-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.465-2.38 1.235-3.22-.135-.303-.54-1.523.105-3.176 0 0 1.005-.322 3.3 1.23.96-.267 1.98-.399 3-.405 1.02.006 2.04.138 3 .405 2.28-1.552 3.285-1.23 3.285-1.23.645 1.653.24 2.873.12 3.176.765.84 1.23 1.91 1.23 3.22 0 4.61-2.805 5.625-5.475 5.92.42.36.81 1.096.81 2.22 0 1.606-.015 2.896-.015 3.286 0 .315.21.69.825.57C20.565 22.092 24 17.592 24 12.297c0-6.627-5.373-12-12-12"/></svg>
            </a>
            <a href="https://x.com/LekkerTechSlack" target="_blank" rel="noopener noreferrer" aria-label="X">
                <svg aria-hidden="true" focusable="false" width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M18.901 1.153h3.68l-8.04 9.19L24 22.846h-7.406l-5.8-7.584-6.638 7.584H.474l8.6-9.83L0 1.154h7.594l5.243 6.932ZM17.61 20.644h2.039L6.486 3.24H4.298Z"/></svg>
            </a>
            <a href="https://bsky.app/profile/lekkertech.bsky.social" target="_blank" rel="noopener noreferrer" aria-label="Bluesky">
                <svg aria-hidden="true" focusable="false" width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M12 10.8c-1.087-2.114-4.046-6.053-6.798-7.995C2.566.944 1.561 1.266.902 1.565.139 1.908 0 3.08 0 3.768c0 .69.378 5.65.624 6.479.815 2.736 3.713 3.66 6.383 3.364.136-.02.275-.039.415-.056-.138.022-.276.04-.415.056-3.912.58-7.387 2.005-2.83 7.078 5.013 5.19 6.87-1.113 7.823-4.308.953 3.195 2.05 9.271 7.733 4.308 4.267-4.308 1.172-6.498-2.74-7.078a8.741 8.741 0 0 1-.415-.056c.14.017.279.036.415.056 2.67.297 5.568-.628 6.383-3.364.246-.828.624-5.79.624-6.478 0-.69-.139-1.861-.902-2.206-.659-.298-1.664-.62-4.3 1.24C16.046 4.748 13.087 8.687 12 10.8Z"/></svg>
            </a>
            <a href="https://www.linkedin.com/company/lekker-tech" target="_blank" rel="noopener noreferrer" aria-label="LinkedIn">
                <svg aria-hidden="true" focusable="false" width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
            </a>
            <a href="https://www.facebook.com/lekkertechslack" target="_blank" rel="noopener noreferrer" aria-label="Facebook">
                <svg aria-hidden="true" focusable="false" width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M9.101 23.691v-7.98H6.627v-3.667h2.474v-1.58c0-4.085 1.848-5.978 5.858-5.978.401 0 .955.042 1.468.103a8.68 8.68 0 0 1 1.141.195v3.325a8.623 8.623 0 0 0-.653-.036 26.805 26.805 0 0 0-.733-.009c-.707 0-1.259.096-1.675.309a1.686 1.686 0 0 0-.679.622c-.258.42-.374.995-.374 1.752v1.297h3.919l-.386 2.103-.287 1.564h-3.246v8.245C19.396 23.238 24 18.179 24 12.044c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.628 3.874 10.35 9.101 11.647Z"/></svg>
            </a>
        </nav>

        <div class="copyright">
            <p>© <?php echo date('Y'); ?> Made with 🔥 by <span class="tooltip"><button type="button" aria-describedby="bafana">Bafana Nakamoto</button><span class="tooltiptext" role="tooltip" id="bafana">Bafana Nakamoto, the lesser-known cousin of Satoshi, moved to Cape Town after a brief stint mining dogecoin in Tokyo. Disillusioned by the noise of the blockchain world, he started Lekker Tech to connect devs over boerewors, bytes, and banter.</span></span></p>
            <p class="credit">🤖 Pair programmed with <a href="https://claude.ai/code" target="_blank" rel="noopener noreferrer">Claude Code</a> (it didn't judge my jQuery choice)</p>
        </div>
    </footer>
</main>

<div class="bottom-space"></div>

<script>

const RECAPTCHA_SITE_KEY = '<?php echo $config['site_key']; ?>';
let recaptchaLoaded = null;

function loadRecaptcha() {
    recaptchaLoaded = recaptchaLoaded || new Promise((resolve, reject) => {
        const script = document.createElement('script');
        script.src = 'https://www.google.com/recaptcha/api.js?render=' + RECAPTCHA_SITE_KEY;
        script.onload = () => grecaptcha.ready(resolve);
        script.onerror = reject;
        document.head.appendChild(script);
    });
    return recaptchaLoaded;
}

document.addEventListener('DOMContentLoaded', function() {
    // Track active code snippets to prevent duplicates
    let activeSnippets = new Set();

    $('.page-loader').addClass('fade-out');
    setTimeout(() => {
        $('.page-loader').remove();
    }, 500);

    // Parallax effect for background and stars
    $(window).on('scroll', function() {
        const scrollTop = $(this).scrollTop();
        $('.parallax-bg').css('transform', `translate(-10%, -10%) translateY(${scrollTop * 0.3}px)`);
        $('.stars').css('transform', `translateY(${scrollTop * 0.2}px)`);
    });

    // Create twinkling stars
    function createStars() {
        const $starsContainer = $('.stars');
        for (let i = 0; i < 100; i++) {
            const $star = $('<div>').addClass('star').css({
                left: Math.random() * 100 + '%',
                top: Math.random() * 100 + '%',
                animationDelay: Math.random() * 2 + 's'
            });
            $starsContainer.append($star);
        }
    }

    // Create floating emojis and code snippets
    function createFloatingEmoji() {
        const emojis = ['💻', '🚀', '⚡', '🔥', '🎯', '🌟', '💡', '🎨', '🔧', '📱'];
        const $emoji = $('<div>')
            .addClass('emoji')
            .text(emojis[Math.floor(Math.random() * emojis.length)])
            .css({
                left: Math.random() * 100 + '%',
                animationDuration: (Math.random() * 3 + 4) + 's'
            });

        $('.floating-emojis').append($emoji);

        setTimeout(() => {
            $emoji.remove();
        }, 7000);
    }

    function createFloatingCodeSnippet() {
        const codeSnippets = [
            'brew install rooibos',
            'npm install braai-tools',
            'git commit -m "lekker code"',
            'sudo apt-get install biltong',
            'console.log("howzit!");',
            'import { vibe } from "cape-town"',
            'const isLekker = true;',
            'composer require boerewors',
            'docker run --rm ubuntu-za',
            'ssh user@jozi-server',
            'curl durban-api.co.za/waves',
            'echo "Sharp sharp!"',
            'kubectl apply -f braai.yaml'
        ];

        // Filter out snippets that are already active
        const availableSnippets = codeSnippets.filter(snippet => !activeSnippets.has(snippet));

        // If no snippets available, skip this cycle
        if (availableSnippets.length === 0) {
            return;
        }

        const selectedSnippet = availableSnippets[Math.floor(Math.random() * availableSnippets.length)];
        activeSnippets.add(selectedSnippet);

        const animationDuration = Math.random() * 4 + 6; // 6-10 seconds
        const $snippet = $('<div>')
            .addClass('code-snippet')
            .text(selectedSnippet)
            .css({
                left: Math.random() * 70 + '%', // Narrower range so text doesn't get cut off
                animationDuration: animationDuration + 's',
                transform: `rotate(${(Math.random() - 0.5) * 10}deg)` // Random angle between -5 and +5 degrees
            });

        $('.floating-emojis').append($snippet);

        // Remove when animation completes (when it reaches the top)
        $snippet.on('animationend', function() {
            activeSnippets.delete(selectedSnippet);
            $(this).remove();
        });

        // Fallback removal in case animationend doesn't fire
        setTimeout(() => {
            activeSnippets.delete(selectedSnippet);
            $snippet.remove();
        }, (animationDuration + 1) * 1000);
    }

    // Initialize
    createStars();

    // Create floating emojis and code snippets periodically
    setInterval(createFloatingEmoji, 1000);
    setInterval(createFloatingCodeSnippet, 4000);

    // Add interactive sparkle on click and touch
    function createSparkle(x, y) {
        const sparkleEmojis = ['✨', '⭐', '💫', '🌟'];
        const randomSparkle = sparkleEmojis[Math.floor(Math.random() * sparkleEmojis.length)];

        const $sparkle = $('<div>')
            .css({
                position: 'fixed',
                left: x + 'px',
                top: y + 'px',
                pointerEvents: 'none',
                fontSize: '2rem',
                zIndex: '1000',
                animation: 'float 1s ease-out forwards'
            })
            .html(randomSparkle);

        $('body').append($sparkle);
        setTimeout(() => $sparkle.remove(), 1000);
    }

    // Handle both click and touch events
    $(document).on('click touchstart', function(e) {
        if ($(e.target).closest('a, button, summary').length) {
            return;
        }

        e.preventDefault();

        let x, y;
        if (e.type === 'touchstart') {
            const touch = e.originalEvent.touches[0];
            x = touch.clientX;
            y = touch.clientY;
        } else {
            x = e.clientX;
            y = e.clientY;
        }

        createSparkle(x, y);
    });

    $('.cta-button').one('pointerenter focusin touchstart', loadRecaptcha);

    $('.cta-button').on('click', function(e) {
        e.preventDefault();

        const $button = $(this);
        const originalText = $button.html();

        // Show loading state
        $button.addClass('loading')
            .html('<span class="loading-spinner"></span>Verifying...')
            .prop('disabled', true);

        loadRecaptcha()
            .then(() => grecaptcha.execute(RECAPTCHA_SITE_KEY, {action: 'submit'}))
            .then(function(token) {
                const form = document.createElement('form');
                form.method = 'POST';

                const tokenInput = document.createElement('input');
                tokenInput.type = 'hidden';
                tokenInput.name = 'token';
                tokenInput.value = token;
                form.appendChild(tokenInput);

                document.body.appendChild(form);
                form.submit();
            }).catch(function(error) {
                $button.removeClass('loading')
                    .html(originalText)
                    .prop('disabled', false);
            });
    });
});
</script>

</body>

</html><?php
}
