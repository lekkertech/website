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
        font-size: 1.2rem;
        color: #fff;
        margin-bottom: 2rem;
        background: rgba(255, 255, 255, 0.1);
        padding: 1rem;
        border-radius: 15px;
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
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
        h1 { font-size: 2.5rem; }
        .subtitle { font-size: 1.2rem; }
        .logo { font-size: 3rem; }
        .container { padding: 1rem; }

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

<div class="stars"></div>
<div class="floating-emojis"></div>

<div class="container">
    <div class="logo">🚀</div>
    <h1>LEKKER TECH</h1>
    <div class="subtitle">Where Code Meets Braai 🔥</div>

    <div class="meme-text">
        <p>💻 "It's not a bug, it's a feature" - Every dev ever</p>
        <p>🌍 South African tech wizards making magic happen</p>
        <p>☕ Powered by coffee and good vibes</p>
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

    // Add some interactive sparkle on click
    $(document).on('click', function(e) {
        const $sparkle = $('<div>')
            .css({
                position: 'fixed',
                left: e.clientX + 'px',
                top: e.clientY + 'px',
                pointerEvents: 'none',
                fontSize: '2rem',
                zIndex: '1000',
                animation: 'float 1s ease-out forwards'
            })
            .html('✨');

        $('body').append($sparkle);

        setTimeout(() => $sparkle.remove(), 1000);
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
