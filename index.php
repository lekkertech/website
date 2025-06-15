<?php

$config = json_decode(file_get_contents('../lekker.config.json'), true);

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
        curl_close($ch);
        return ['success' => false, 'error' => 'cURL error: ' . curl_error($ch)];
    }

    curl_close($ch);

    if ($httpCode !== 200) {
        return ['success' => false, 'error' => 'HTTP error: ' . $httpCode];
    }

    return json_decode($response, true);
}

if (isset($_GET['token']) && !empty($_GET['token'])) {
    $json = verifyRecaptchaCurl($_GET['token'], $config['secret_key'], $_SERVER['REMOTE_ADDR']);

    if ($json['success'] === true) {
        // redirect
        header('Location: ' . $config['slack_invite_url']);
        exit;
    } else {
        echo 'Unknown error.';
    }
} else {
?><!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Lekker Tech - Where Code Meets Braai 🔥</title>
<meta name="description" content="Join South African tech wizards making magic happen. Connect with devs over boerewors, bytes, and banter in our lekker community!">

<!-- Open Graph / Facebook -->
<meta property="og:type" content="website">
<meta property="og:url" content="https://lekkertech.com/">
<meta property="og:title" content="Lekker Tech - Where Code Meets Braai 🔥">
<meta property="og:description" content="Join South African tech wizards making magic happen. Connect with devs over boerewors, bytes, and banter in our lekker community!">
<!-- <meta property="og:image" content="https://lekkertech.com/og-image.jpg"> -->

<!-- Twitter -->
<meta property="twitter:card" content="summary_large_image">
<meta property="twitter:url" content="https://lekkertech.com/">
<meta property="twitter:title" content="Lekker Tech - Where Code Meets Braai 🔥">
<meta property="twitter:description" content="Join South African tech wizards making magic happen. Connect with devs over boerewors, bytes, and banter in our lekker community!">
<!-- <meta property="twitter:image" content="https://lekkertech.com/og-image.jpg"> -->

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
        animation: bounce 2s infinite;
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
    }

    @keyframes rainbow {
        0% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
        100% { background-position: 0% 50%; }
    }

    .subtitle {
        font-size: 1.5rem;
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

    .meme-text p {
        margin-bottom: 0.8rem;
    }

    .meme-text p:last-child {
        margin-bottom: 0;
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

    @keyframes float {
        0% {
            transform: translateY(100vh) rotate(0deg);
            opacity: 0;
        }
        10% {
            opacity: 0.7;
        }
        90% {
            opacity: 0.7;
        }
        100% {
            transform: translateY(-100px) rotate(360deg);
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
        margin-top: 3rem;
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
</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://www.google.com/recaptcha/api.js?render=<?php echo $config['site_key']; ?>"></script>

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

<div class="container">
    <div class="logo">🚀</div>
    <h1>LEKKER TECH</h1>
    <div class="subtitle">Where Code Meets Braai 🔥</div>

    <div class="meme-text">
        <p>💻&nbsp;&nbsp;<em>"It's not a bug, it's a feature" - Every dev ever</em></p>
        <p>🌍&nbsp;&nbsp;South African tech wizards making magic happen</p>
        <p>☕&nbsp;&nbsp;Powered by coffee and good vibes</p>
    </div>

<!--<a href="#" class="cta-button" onclick="alert('Slack invite link goes here! 🎉')">-->
    <a href="#" class="cta-button">
        Join the Lekker Squad! 🎯
    </a>


    <div class="tech-stack">
        <p>jQuery • PHP • Vim • Coffee • Biltong</p>
    </div>

    <div class="copyright">
        <p>© <?php echo date('Y'); ?> Made with 🔥 by <span class="tooltip">Bafana Nakamoto
            <span class="tooltiptext">Bafana Nakamoto, the lesser-known cousin of Satoshi, moved to Cape Town after a brief stint mining dogecoin in Tokyo. Disillusioned by the noise of the blockchain world, he started Lekker Tech to connect devs over boerewors, bytes, and banter.</span>
        </span></p>
        <p style="margin-top: 0.5rem; font-size: 0.7rem; opacity: 0.6;">
            🤖 Pair programmed with <a href="https://claude.ai/code" style="color: rgba(255,255,255,0.8); text-decoration: none;" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.8'">Claude Code</a> (it didn't judge my jQuery choice)
        </p>
    </div>
</div>

<script>

$(document).ready(function() {
    // Hide page loader once everything is ready
    $(window).on('load', function() {
        $('.page-loader').addClass('fade-out');
        setTimeout(() => {
            $('.page-loader').remove();
        }, 500);
    });

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

    // Create floating emojis
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

    // Initialize
    createStars();

    // Create floating emojis periodically
    setInterval(createFloatingEmoji, 800);

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

    $('.cta-button').on('click', function(e) {
        e.preventDefault();

        const $button = $(this);
        const originalText = $button.html();

        // Show loading state
        $button.addClass('loading')
            .html('<span class="loading-spinner"></span>Verifying...')
            .prop('disabled', true);

        grecaptcha.ready(function() {
            grecaptcha.execute('<?php echo $config['site_key']; ?>', {action: 'submit'}).then(function(token) {
                window.location.href = 'index.php?token=' + token;
            }).catch(function(error) {
                // Reset button on error
                $button.removeClass('loading')
                    .html(originalText)
                    .prop('disabled', false);
            });
        });
    });
});
</script>

</body>

</html><?php
}
