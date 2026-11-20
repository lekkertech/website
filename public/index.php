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

<link rel="preload" href="/fonts/baloo2-latin.woff2" as="font" type="font/woff2" crossorigin>
<link rel="preload" href="/fonts/figtree-latin.woff2" as="font" type="font/woff2" crossorigin>

<style>
    /* Self-hosted Google Fonts, Latin subset (OFL; licences in /fonts). */
    @font-face {
        font-family: 'Baloo 2';
        font-style: normal;
        font-weight: 400 800;
        font-display: swap;
        src: url(/fonts/baloo2-latin.woff2) format('woff2');
        unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+0304, U+0308, U+0329, U+2000-206F, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
    }

    @font-face {
        font-family: 'Figtree';
        font-style: normal;
        font-weight: 300 900;
        font-display: swap;
        src: url(/fonts/figtree-latin.woff2) format('woff2');
        unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+0304, U+0308, U+0329, U+2000-206F, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
    }

    @font-face {
        font-family: 'Figtree';
        font-style: italic;
        font-weight: 500;
        font-display: swap;
        src: url(/fonts/figtree-italic-latin.woff2) format('woff2');
        unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+0304, U+0308, U+0329, U+2000-206F, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
    }

    @font-face {
        font-family: 'JetBrains Mono';
        font-style: normal;
        font-weight: 500;
        font-display: swap;
        src: url(/fonts/jetbrainsmono-latin.woff2) format('woff2');
        unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+0304, U+0308, U+0329, U+2000-206F, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
    }

    /*
     * Lekker Refresh. One identity in two moods: light mode is today's
     * purple sky brightened to daytime, dark mode is the same sky at night.
     * Every colour is a token; dark mode only redefines tokens.
     */
    :root {
        color-scheme: light;

        --font-display: "Baloo 2", "Trebuchet MS", "Arial Rounded MT Bold", system-ui, sans-serif;
        --font-body: "Figtree", system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
        --font-mono: "JetBrains Mono", ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;

        --sky-1: #97a3ff;
        --sky-2: #a690f6;
        --sky-3: #b88de8;
        --sky-glow: rgba(255, 255, 255, 0.42);
        --blob-a: rgba(112, 132, 250, 0.55);
        --blob-b: rgba(160, 110, 230, 0.5);
        --ink: #1f1147;
        --ink-soft: #2e1f63;
        --ink-faint: #2f2065;
        --card: rgba(255, 255, 255, 0.5);
        --card-strong: rgba(255, 255, 255, 0.68);
        --card-line: rgba(255, 255, 255, 0.75);
        --hair: rgba(46, 31, 99, 0.2);
        --title-a: #e8365d;
        --title-b: #f07800;
        --title-c: #1f9e5c;
        --title-d: #0f8fb0;
        --title-e: #7a4dff;
        --title-glow: rgba(255, 255, 255, 0.6);
        --cta-a: #ff6b6b;
        --cta-b: #ffb547;
        --cta-ink: #2a0d1f;
        --cta-shadow: rgba(214, 60, 90, 0.35);
        --focus: #1f1147;
        --star: 255, 255, 255;
        --star-alpha: 0.9;
        --star-shape: sparkle;
        --snippet-bg: rgba(255, 255, 255, 0.5);
        --snippet-ink: #2f5d3a;
        --snippet-line: rgba(255, 255, 255, 0.8);
        --float-alpha: 0.55;
        --tip-bg: #1f1147;
        --tip-ink: #fff;
    }

    @media (prefers-color-scheme: dark) {
        :root {
            color-scheme: dark;

            --sky-1: #232055;
            --sky-2: #2c1c4f;
            --sky-3: #1b1538;
            --sky-glow: rgba(140, 120, 255, 0.2);
            --blob-a: rgba(90, 100, 230, 0.32);
            --blob-b: rgba(150, 70, 170, 0.3);
            --ink: #f4efff;
            --ink-soft: #d8cff3;
            --ink-faint: #b9aedd;
            --card: rgba(255, 255, 255, 0.06);
            --card-strong: rgba(255, 255, 255, 0.09);
            --card-line: rgba(255, 255, 255, 0.12);
            --hair: rgba(255, 255, 255, 0.1);
            --title-a: #ff8a8f;
            --title-b: #ffd36b;
            --title-c: #7fdc9e;
            --title-d: #6fd3d0;
            --title-e: #b59bff;
            --title-glow: rgba(255, 190, 160, 0.28);
            --cta-a: #ff7a7a;
            --cta-b: #ffbf5e;
            --cta-shadow: rgba(255, 110, 110, 0.25);
            --focus: #ffd36b;
            --star: 236, 230, 255;
            --star-alpha: 0.85;
            --star-shape: dot;
            --snippet-bg: rgba(10, 6, 30, 0.55);
            --snippet-ink: #9fdcaa;
            --snippet-line: rgba(159, 220, 170, 0.18);
            --tip-bg: #0f0b24;
            --tip-ink: #f4efff;
        }
    }

    *,
    *::before,
    *::after {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }

    html {
        -webkit-text-size-adjust: 100%;
        overflow-x: clip;
    }

    body {
        position: relative;
        min-height: 100vh;
        overflow-x: clip;
        font-family: var(--font-body);
        font-size: 1.0625rem;
        line-height: 1.6;
        color: var(--ink);
        background: linear-gradient(160deg, var(--sky-1) 0%, var(--sky-2) 40%, var(--sky-3) 75%, var(--sky-2) 100%);
        background-color: var(--sky-2);
        -webkit-font-smoothing: antialiased;
    }

    /* Sky: two colour glows drifting slowly behind everything. */
    .sky {
        position: absolute;
        inset: 0;
        z-index: 0;
        overflow: hidden;
        pointer-events: none;
    }

    .sky::before,
    .sky::after {
        content: "";
        position: absolute;
        width: 90vmax;
        height: 90vmax;
        border-radius: 50%;
        will-change: transform, opacity;
    }

    .sky::before {
        left: -30vmax;
        top: -30vmax;
        background: radial-gradient(closest-side, var(--blob-a), transparent);
        animation: driftA 38s ease-in-out infinite alternate;
    }

    .sky::after {
        right: -35vmax;
        bottom: -45vmax;
        background: radial-gradient(closest-side, var(--blob-b), transparent);
        animation: driftB 46s ease-in-out infinite alternate;
    }

    @keyframes driftA {
        0% { transform: translate(0, 0); opacity: 0.7; }
        100% { transform: translate(28vmax, 18vmax); opacity: 1; }
    }

    @keyframes driftB {
        0% { transform: translate(0, 0); opacity: 1; }
        100% { transform: translate(-26vmax, -22vmax); opacity: 0.6; }
    }

    .stars {
        position: fixed;
        inset: 0;
        z-index: 1;
        width: 100%;
        height: 100%;
        pointer-events: none;
    }

    .floating-emojis {
        position: fixed;
        inset: 0;
        z-index: 1;
        overflow: hidden;
        pointer-events: none;
    }

    .emoji,
    .code-snippet {
        position: absolute;
        bottom: -80px;
        will-change: transform, opacity;
        animation: rise var(--dur, 18s) linear forwards;
    }

    .emoji {
        font-size: 1.75rem;
    }

    .code-snippet {
        padding: 0.35rem 0.6rem;
        font-family: var(--font-mono);
        font-size: 0.78rem;
        white-space: nowrap;
        color: var(--snippet-ink);
        background: var(--snippet-bg);
        border: 1px solid var(--snippet-line);
        border-radius: 8px;
        -webkit-backdrop-filter: blur(6px);
        backdrop-filter: blur(6px);
    }

    @keyframes rise {
        0% { transform: translateY(0) rotate(var(--rot, 0deg)); opacity: 0; }
        12% { opacity: var(--float-alpha); }
        85% { opacity: var(--float-alpha); }
        100% { transform: translateY(calc(-100vh - 160px)) rotate(calc(var(--rot, 0deg) * -1)); opacity: 0; }
    }

    .sparkle {
        position: fixed;
        z-index: 20;
        font-size: 1.6rem;
        pointer-events: none;
        animation: pop 0.9s ease-out forwards;
    }

    @keyframes pop {
        0% { transform: translate(-50%, -50%) scale(0.4); opacity: 1; }
        100% { transform: translate(-50%, -160%) scale(1.1); opacity: 0; }
    }

    /* Layout */
    .wrap {
        position: relative;
        z-index: 2;
        max-width: 56rem;
        margin: 0 auto;
        padding: clamp(2.5rem, 9vh, 6rem) 1.25rem 3rem;
        text-align: center;
    }

    .glow {
        position: absolute;
        left: 50%;
        top: 0;
        z-index: -1;
        width: min(70rem, 100vw);
        height: 36rem;
        transform: translateX(-50%);
        background: radial-gradient(50% 50% at 50% 40%, var(--sky-glow), transparent 75%);
        pointer-events: none;
    }

    .hero {
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .logo {
        display: inline-block;
        font-size: 4.25rem;
        line-height: 1;
        filter: drop-shadow(0 0 18px var(--title-glow));
        animation: bob 5s ease-in-out infinite;
    }

    @keyframes bob {
        0%, 100% { transform: translateY(0) rotate(-4deg); }
        50% { transform: translateY(-10px) rotate(2deg); }
    }

    h1 {
        margin: 0.6rem 0 0.5rem;
        font-family: var(--font-display);
        font-weight: 800;
        font-size: clamp(3.4rem, 10vw, 7rem);
        line-height: 0.95;
        letter-spacing: 0.01em;
        text-transform: uppercase;
        color: var(--title-a);
        background: linear-gradient(90deg, var(--title-a), var(--title-b), var(--title-c), var(--title-d), var(--title-e), var(--title-a));
        background-size: 200% 100%;
        -webkit-background-clip: text;
        background-clip: text;
        -webkit-text-fill-color: transparent;
        filter: drop-shadow(0 0 18px var(--title-glow));
        animation: shimmer 18s linear infinite;
    }

    @keyframes shimmer {
        to { background-position: -200% 0; }
    }

    .subtitle {
        max-width: 30ch;
        margin: 0 auto 1.1rem;
        font-family: var(--font-display);
        font-weight: 700;
        font-size: clamp(1.2rem, 2.6vw, 1.65rem);
        line-height: 1.25;
        color: var(--ink);
        text-wrap: balance;
    }

    .lead {
        max-width: 38rem;
        margin: 0 auto 1.75rem;
        font-size: clamp(1.05rem, 1.6vw, 1.2rem);
        line-height: 1.6;
        color: var(--ink-soft);
        text-wrap: pretty;
    }

    .vibes {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        width: 100%;
        max-width: 54rem;
        margin: 0 auto 1.75rem;
        list-style: none;
        text-align: left;
        border-top: 1px solid var(--hair);
        border-bottom: 1px solid var(--hair);
    }

    .vibes li {
        display: flex;
        gap: 0.7rem;
        align-items: flex-start;
        padding: 0.85rem 1rem;
        font-size: 0.95rem;
        line-height: 1.45;
        color: var(--ink-soft);
        text-wrap: pretty;
    }

    .vibes li + li {
        border-left: 1px solid var(--hair);
    }

    .vibe-icon {
        flex: none;
        font-size: 1.3rem;
        line-height: 1.1;
    }

    .vibes li > span:last-child {
        flex: 1;
        min-width: 0;
    }

    .vibes .quote {
        font-style: italic;
        font-weight: 500;
        color: var(--ink);
    }

    .vibes cite {
        display: block;
        margin-top: 0.2rem;
        font-style: normal;
        font-size: 0.8rem;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: var(--ink-faint);
    }

    .cta-button {
        position: relative;
        isolation: isolate;
        overflow: hidden;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.6rem;
        min-height: 68px;
        padding: 0 2.75rem;
        border-radius: 999px;
        font-family: var(--font-display);
        font-weight: 800;
        font-size: 1.55rem;
        letter-spacing: 0.01em;
        color: var(--cta-ink);
        text-decoration: none;
        background: linear-gradient(100deg, var(--cta-a), var(--cta-b));
        box-shadow: 0 14px 34px -10px var(--cta-shadow), inset 0 1px 0 rgba(255, 255, 255, 0.5), inset 0 -3px 0 rgba(0, 0, 0, 0.08);
        transition: transform 0.25s cubic-bezier(0.3, 1.6, 0.5, 1), box-shadow 0.25s ease;
    }

    .cta-button::before {
        content: "";
        position: absolute;
        inset: 0;
        z-index: -1;
        background: linear-gradient(100deg, transparent 30%, rgba(255, 255, 255, 0.55) 50%, transparent 70%);
        transform: translateX(-120%);
        animation: sheen 6s ease-in-out 1.5s infinite;
    }

    @keyframes sheen {
        0%, 70% { transform: translateX(-120%); }
        100% { transform: translateX(120%); }
    }

    .cta-button:hover {
        transform: translateY(-3px) scale(1.02);
        box-shadow: 0 20px 40px -12px var(--cta-shadow), inset 0 1px 0 rgba(255, 255, 255, 0.5);
    }

    .cta-button:active {
        transform: translateY(0) scale(0.99);
    }

    .cta-button.loading {
        filter: grayscale(0.6);
        cursor: progress;
        transform: none;
    }

    .cta-button.loading::before {
        display: none;
    }

    .loading-spinner {
        display: inline-block;
        width: 20px;
        height: 20px;
        border: 2px solid currentColor;
        border-top-color: transparent;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    .cta-note {
        margin-top: 0.85rem;
        font-size: 0.9rem;
        color: var(--ink-faint);
    }

    :focus-visible {
        outline: 3px solid var(--focus);
        outline-offset: 3px;
        border-radius: 6px;
    }

    .cta-button:focus-visible {
        outline-offset: 5px;
        border-radius: 999px;
    }

    /* Questions */
    .faq {
        max-width: 40rem;
        margin: clamp(4rem, 10vh, 6rem) auto 0;
        text-align: left;
    }

    .faq h2 {
        margin-bottom: 1rem;
        font-family: var(--font-display);
        font-weight: 800;
        font-size: 1.9rem;
        line-height: 1.1;
        text-align: center;
    }

    .faq-list {
        padding: 0.35rem 0;
        background: var(--card);
        border: 1px solid var(--card-line);
        border-radius: 20px;
        -webkit-backdrop-filter: blur(12px);
        backdrop-filter: blur(12px);
    }

    .faq details + details {
        border-top: 1px solid var(--hair);
    }

    .faq summary {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        min-height: 56px;
        padding: 0.75rem 1.35rem;
        list-style: none;
        cursor: pointer;
        font-weight: 700;
        font-size: 1.05rem;
        color: var(--ink);
        border-radius: 14px;
    }

    .faq summary::-webkit-details-marker {
        display: none;
    }

    .faq summary::after {
        content: "";
        flex: none;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        border: 1px solid var(--card-line);
        background: var(--card-strong) no-repeat center;
        background-image: linear-gradient(currentColor, currentColor), linear-gradient(currentColor, currentColor);
        background-size: 12px 2px, 2px 12px;
        transition: transform 0.3s ease;
    }

    .faq details[open] summary::after {
        transform: rotate(45deg);
    }

    .faq summary:hover {
        background: var(--card);
    }

    .faq details p {
        max-width: 34rem;
        padding: 0 1.35rem 1.1rem;
        color: var(--ink-soft);
    }

    .faq a {
        font-weight: 600;
        color: var(--ink);
        text-decoration-thickness: 2px;
        text-underline-offset: 3px;
    }

    /* Footer */
    footer {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 1.1rem;
        margin-top: 3rem;
    }

    .tech-stack {
        font-family: var(--font-mono);
        font-size: 0.82rem;
        letter-spacing: 0.02em;
        color: var(--ink-soft);
    }

    .social-icons {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 0.6rem;
    }

    .social-icons a {
        display: grid;
        place-items: center;
        width: 48px;
        height: 48px;
        border-radius: 50%;
        color: var(--ink);
        background: var(--card);
        border: 1px solid var(--card-line);
        transition: transform 0.2s ease, background 0.2s ease;
    }

    .social-icons svg {
        width: 22px;
        height: 22px;
    }

    .social-icons a:hover {
        transform: translateY(-3px);
        background: var(--card-strong);
    }

    .social-icons a:focus-visible {
        border-radius: 50%;
    }

    .copyright {
        position: relative;
        font-size: 0.88rem;
        color: var(--ink-soft);
        text-align: center;
    }

    .copyright .credit {
        margin-top: 0.35rem;
        font-size: 0.8rem;
        color: var(--ink-faint);
    }

    .copyright a {
        display: inline-block;
        padding: 0.75rem 0.1rem;
        margin: -0.75rem 0;
        font-weight: 600;
        color: inherit;
    }

    /* The tooltip is anchored to the footer block, so it stays on screen at phone width. */
    .tooltip button {
        padding: 0.7rem 0.15rem;
        margin: -0.7rem 0;
        font: inherit;
        font-weight: 600;
        color: var(--ink);
        background: none;
        border: 0;
        cursor: help;
        text-decoration: underline dotted 1.5px;
        text-underline-offset: 4px;
    }

    .tooltiptext {
        position: absolute;
        bottom: calc(100% + 12px);
        left: 50%;
        z-index: 10;
        width: min(320px, calc(100vw - 32px));
        padding: 0.9rem 1rem;
        border-radius: 14px;
        font-size: 0.85rem;
        line-height: 1.5;
        text-align: left;
        color: var(--tip-ink);
        background: var(--tip-bg);
        box-shadow: 0 18px 40px -12px rgba(0, 0, 0, 0.45);
        opacity: 0;
        visibility: hidden;
        transform: translate(-50%, 4px);
        transition: opacity 0.2s ease, transform 0.2s ease, visibility 0.2s;
    }

    .tooltip:hover .tooltiptext,
    .tooltip:focus-within .tooltiptext {
        opacity: 1;
        visibility: visible;
        transform: translate(-50%, 0);
    }

    .grecaptcha-badge {
        z-index: 3;
    }

    /*
     * Page loader. Fades as soon as the scripts are ready; the delayed
     * animation hides it anyway if they never arrive.
     */
    .page-loader {
        position: fixed;
        inset: 0;
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(160deg, var(--sky-1) 0%, var(--sky-2) 50%, var(--sky-3) 100%);
        transition: opacity 0.3s ease-out;
        animation: loader-out 0.3s ease-out 3s forwards;
    }

    .page-loader.fade-out {
        opacity: 0;
        pointer-events: none;
    }

    @keyframes loader-out {
        to { opacity: 0; visibility: hidden; }
    }

    .loader-content {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.9rem;
        color: var(--ink);
    }

    .loader-rocket {
        font-size: 4rem;
        line-height: 1;
        filter: drop-shadow(0 0 18px var(--title-glow));
        animation: bob 1.2s ease-in-out infinite;
    }

    .loader-text {
        font-family: var(--font-display);
        font-weight: 700;
        font-size: 1.25rem;
    }

    .loader-dots {
        display: flex;
        gap: 6px;
    }

    .loader-dots span {
        width: 9px;
        height: 9px;
        border-radius: 50%;
        background: linear-gradient(100deg, var(--cta-a), var(--cta-b));
        animation: dotPulse 1.2s ease-in-out infinite both;
    }

    .loader-dots span:nth-child(1) { animation-delay: -0.32s; }
    .loader-dots span:nth-child(2) { animation-delay: -0.16s; }

    @keyframes dotPulse {
        0%, 80%, 100% { transform: scale(0.7); opacity: 0.5; }
        40% { transform: scale(1); opacity: 1; }
    }

    @media (max-width: 720px) {
        body {
            font-size: 1rem;
        }

        .wrap {
            padding: 1.5rem 1rem 2.5rem;
        }

        .logo {
            font-size: 3rem;
        }

        h1 {
            margin: 0.35rem 0 0.45rem;
        }

        .subtitle {
            max-width: none;
            margin-bottom: 0.8rem;
            font-size: 1.18rem;
        }

        .lead {
            margin-bottom: 1.25rem;
        }

        .vibes {
            grid-template-columns: 1fr;
            margin-bottom: 1.25rem;
        }

        .vibes li {
            padding: 0.55rem 0.25rem;
            font-size: 0.92rem;
        }

        .vibes li + li {
            border-left: 0;
            border-top: 1px solid var(--hair);
        }

        .cta-button {
            width: 100%;
            min-height: 60px;
            padding: 0 1.25rem;
            font-size: 1.25rem;
        }

        .faq {
            margin-top: 3rem;
        }

        .faq summary {
            padding: 0.7rem 1rem;
        }

        .faq details p {
            padding: 0 1rem 1rem;
        }

        .emoji {
            font-size: 1.4rem;
        }

        .floating-emojis {
            --float-alpha: 0.25;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        *,
        *::before,
        *::after {
            animation: none !important;
            transition: none !important;
        }

        .floating-emojis {
            display: none;
        }

        .page-loader {
            display: none;
        }
    }
</style>

<script defer src="https://cdnjs.cloudflare.com/ajax/libs/jquery/4.1.0/jquery.min.js"></script>

</head>

<body>

<div class="page-loader" aria-hidden="true">
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

<div class="sky" aria-hidden="true"></div>
<canvas id="stars" class="stars" aria-hidden="true"></canvas>
<div class="floating-emojis" aria-hidden="true"></div>

<main class="wrap">
    <div class="glow" aria-hidden="true"></div>
    <header class="hero">
        <div class="logo" aria-hidden="true">🚀</div>
        <h1>Lekker Tech</h1>
        <h2 class="subtitle"><span aria-hidden="true">💬 </span>The South African Tech Slack Where Code Meets Braai<span aria-hidden="true"> 🔥</span></h2>

        <p class="lead">A free Slack community for South African developers and tech workers, at home and abroad. A relaxed space to connect, socialise, and support one another.</p>

        <ul class="vibes">
            <li><span class="vibe-icon" aria-hidden="true">💻</span> <span><em class="quote">“It's not a bug, it's a feature.”</em> <cite>Every dev, ever</cite></span></li>
            <li><span class="vibe-icon" aria-hidden="true">🌍</span> <span>South African tech wizards making magic happen</span></li>
            <li><span class="vibe-icon" aria-hidden="true">☕</span> <span>Powered by coffee and good vibes</span></li>
        </ul>

        <a href="#" class="cta-button">Join the Lekker Squad <span aria-hidden="true">🎯</span></a>
        <p class="cta-note">Free to join. A quick reCAPTCHA check, then straight into Slack.</p>
    </header>

    <section class="faq" aria-labelledby="faq-heading">
        <h2 id="faq-heading">Questions</h2>
        <div class="faq-list">
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
        </div>
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


<script>
// Starfield: three depth layers on a canvas, drifting with scroll at different
// speeds. Colours come from the CSS tokens, so the stars dim or brighten with
// the colour scheme. Plain JS so it starts before jQuery arrives.
(function() {
    const canvas = document.getElementById('stars');
    const ctx = canvas.getContext('2d');
    const reduceMotion = matchMedia('(prefers-reduced-motion: reduce)');
    const darkScheme = matchMedia('(prefers-color-scheme: dark)');
    const start = performance.now();
    let width = 0, height = 0, field = 0, layers = [], colour = {}, frame = 0;

    function readColours() {
        const css = getComputedStyle(document.documentElement);
        colour.rgb = css.getPropertyValue('--star').trim() || '255, 255, 255';
        colour.alpha = parseFloat(css.getPropertyValue('--star-alpha')) || 0.8;
        colour.sparkle = css.getPropertyValue('--star-shape').trim() === 'sparkle';
    }

    // Size the canvas to the viewport. Stars are only scattered again when the
    // width changes: phones resize the height whenever the URL bar shows or
    // hides, and the sky should not jump when that happens.
    function resize() {
        const dpr = Math.min(window.devicePixelRatio || 1, 2);
        const widthChanged = innerWidth !== width;
        width = innerWidth;
        height = innerHeight;
        canvas.width = width * dpr;
        canvas.height = height * dpr;
        ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
        if (widthChanged) scatter();
    }

    function scatter() {
        // The field is as tall as the screen, so a taller viewport never shows a gap.
        field = Math.max(height, screen.height || 0);
        const density = width * field / 10000;
        // Far, mid and near: count, radius range, scroll speed, twinkle depth.
        const specs = [
            { count: density * 1.1, radius: [0.5, 1], parallax: 0.04, twinkle: 0.25, alpha: 0.55 },
            { count: density * 0.45, radius: [0.9, 1.6], parallax: 0.12, twinkle: 0.5, alpha: 0.8 },
            { count: density * 0.12, radius: [1.6, 2.6], parallax: 0.24, twinkle: 0.8, alpha: 1, near: true },
        ];
        layers = specs.map(spec => {
            const stars = [];
            for (let i = 0; i < spec.count; i++) {
                stars.push({
                    x: Math.random() * width,
                    y: Math.random() * field,
                    r: spec.radius[0] + Math.random() * (spec.radius[1] - spec.radius[0]),
                    phase: Math.random() * Math.PI * 2,
                    speed: 0.4 + Math.random() * 1.1,
                });
            }
            return { ...spec, stars };
        });
    }

    // A four-point sparkle, used for near stars in light mode.
    function sparkle(x, y, r) {
        const l = r * 3.2;
        ctx.beginPath();
        ctx.moveTo(x, y - l);
        ctx.quadraticCurveTo(x, y, x + l, y);
        ctx.quadraticCurveTo(x, y, x, y + l);
        ctx.quadraticCurveTo(x, y, x - l, y);
        ctx.quadraticCurveTo(x, y, x, y - l);
        ctx.fill();
    }

    function draw(now) {
        const t = (now - start) / 1000;
        ctx.clearRect(0, 0, width, height);
        ctx.fillStyle = `rgb(${colour.rgb})`;
        for (const layer of layers) {
            const offset = scrollY * layer.parallax;
            for (const star of layer.stars) {
                const y = ((star.y - offset) % field + field) % field;
                if (y > height + 8) continue;
                const twinkle = reduceMotion.matches ? 1 : 1 - layer.twinkle / 2 + layer.twinkle / 2 * Math.sin(t * star.speed + star.phase);
                const alpha = colour.alpha * twinkle * layer.alpha;
                ctx.globalAlpha = alpha;
                if (layer.near && colour.sparkle) {
                    sparkle(star.x, y, star.r);
                    continue;
                }
                ctx.beginPath();
                ctx.arc(star.x, y, star.r, 0, Math.PI * 2);
                ctx.fill();
                if (layer.near) {
                    ctx.globalAlpha = alpha * 0.12;
                    ctx.beginPath();
                    ctx.arc(star.x, y, star.r * 2.4, 0, Math.PI * 2);
                    ctx.fill();
                }
            }
        }
        ctx.globalAlpha = 1;
    }

    function loop(now) {
        draw(now);
        frame = requestAnimationFrame(loop);
    }

    // Reduced motion: no twinkle, redraw only when scrolling.
    function drawStill() {
        cancelAnimationFrame(frame);
        frame = requestAnimationFrame(() => draw(start));
    }

    function run() {
        cancelAnimationFrame(frame);
        if (document.hidden) return;
        if (reduceMotion.matches) drawStill();
        else frame = requestAnimationFrame(loop);
    }

    readColours();
    resize();
    run();

    addEventListener('resize', () => { resize(); if (reduceMotion.matches) drawStill(); });
    addEventListener('scroll', () => { if (reduceMotion.matches) drawStill(); }, { passive: true });
    darkScheme.addEventListener('change', () => { readColours(); if (reduceMotion.matches) drawStill(); });
    reduceMotion.addEventListener('change', run);
    document.addEventListener('visibilitychange', run);
})();
</script>

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
    $('.page-loader').addClass('fade-out');
    setTimeout(() => {
        $('.page-loader').remove();
    }, 300);

    // Floating emojis and code snippets: an asteroid field drifting up behind
    // the page. They sit under the content layer, so text and cards always
    // stay on top. None on reduced motion, none while the tab is hidden, and
    // no snippets on phones.
    const reduceMotion = matchMedia('(prefers-reduced-motion: reduce)');
    const $floaters = $('.floating-emojis');
    const emojis = ['💻', '🚀', '⚡', '🔥', '🎯', '🌟', '💡', '🎨', '🔧', '📱'];
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
    const activeSnippets = new Set();
    let floatTimers = [];

    const isNarrow = () => innerWidth < 720;
    const pick = list => list[Math.floor(Math.random() * list.length)];
    // A random x (px) that keeps an element of `width` on screen.
    const randomX = width => 8 + Math.random() * Math.max(0, innerWidth - width - 16);

    // Rise over `duration` seconds; a negative delay starts it part-way up.
    function launch($el, duration, progress) {
        $el.css({
            '--dur': duration + 's',
            '--rot': ((Math.random() - 0.5) * 10).toFixed(1) + 'deg',
            animationDelay: progress ? (-duration * progress) + 's' : ''
        });
    }

    function spawnEmoji(progress) {
        const $emoji = $('<div class="emoji">').text(pick(emojis)).css('left', randomX(36) + 'px');
        launch($emoji, 18 + Math.random() * 8, progress);
        $emoji.one('animationend', () => $emoji.remove());
        $floaters.append($emoji);
    }

    // Rendered width of each snippet, measured once.
    const snippetWidths = new Map();
    function snippetWidth(snippet) {
        if (!snippetWidths.has(snippet)) {
            const $probe = $('<div class="code-snippet">').text(snippet).css({ visibility: 'hidden', animation: 'none' });
            $floaters.append($probe);
            snippetWidths.set(snippet, $probe.outerWidth());
            $probe.remove();
        }
        return snippetWidths.get(snippet);
    }

    function spawnSnippet(progress) {
        const available = codeSnippets.filter(snippet => !activeSnippets.has(snippet));
        if (!available.length) return;

        const snippet = pick(available);
        const $snippet = $('<div class="code-snippet">').text(snippet).css('left', randomX(snippetWidth(snippet)) + 'px');
        activeSnippets.add(snippet);
        $floaters.append($snippet);
        launch($snippet, 22 + Math.random() * 8, progress);
        $snippet.one('animationend', () => {
            activeSnippets.delete(snippet);
            $snippet.remove();
        });
    }

    function stopFloaters() {
        floatTimers.forEach(clearInterval);
        floatTimers = [];
    }

    function startFloaters() {
        stopFloaters();
        if (reduceMotion.matches || document.hidden) return;

        const narrow = isNarrow();
        // Start a few mid-flight so the sky is never empty on arrival.
        if (!$floaters.children().length) {
            for (let i = 0; i < (narrow ? 2 : 4); i++) spawnEmoji(0.15 + Math.random() * 0.7);
            if (!narrow) for (let i = 0; i < 2; i++) spawnSnippet(0.2 + Math.random() * 0.6);
        }
        floatTimers.push(setInterval(() => {
            if ($floaters.children().length < (isNarrow() ? 3 : 8)) spawnEmoji();
        }, narrow ? 6000 : 3500));
        if (!narrow) floatTimers.push(setInterval(() => spawnSnippet(), 8000));
    }

    startFloaters();
    reduceMotion.addEventListener('change', startFloaters);
    document.addEventListener('visibilitychange', startFloaters);

    // A sparkle wherever the page is tapped or clicked, away from links and buttons.
    const sparkles = ['✨', '⭐', '💫', '🌟'];
    $(document).on('pointerdown', function(e) {
        if (reduceMotion.matches || $(e.target).closest('a, button, summary').length) {
            return;
        }
        const $sparkle = $('<div class="sparkle" aria-hidden="true">')
            .text(pick(sparkles))
            .css({ left: e.clientX + 'px', top: e.clientY + 'px' });
        $sparkle.one('animationend', () => $sparkle.remove());
        $('body').append($sparkle);
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
