<!DOCTYPE html>
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

        @media (max-width: 768px) {
            h1 { font-size: 2.5rem; }
            .subtitle { font-size: 1.2rem; }
            .logo { font-size: 3rem; }
            .container { padding: 1rem; }
        }
    </style>
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
	<a href="https://join.slack.com/t/lekkertech/shared_invite/zt-37qcvholc-4iHOIliLkw1aL1ha8oDsRw" class="cta-button">
            Join the Lekker Squad! 🎯
        </a>
        
        <div class="tech-stack">
            <p>jQuery • PHP • Vim • Coffee • Biltong</p>
        </div>
    </div>

    <script>
        // Create twinkling stars
        function createStars() {
            const starsContainer = document.querySelector('.stars');
            for (let i = 0; i < 100; i++) {
                const star = document.createElement('div');
                star.className = 'star';
                star.style.left = Math.random() * 100 + '%';
                star.style.top = Math.random() * 100 + '%';
                star.style.animationDelay = Math.random() * 2 + 's';
                starsContainer.appendChild(star);
            }
        }

        // Create floating emojis
        function createFloatingEmoji() {
            const emojis = ['💻', '🚀', '⚡', '🔥', '🎯', '🌟', '💡', '🎨', '🔧', '📱'];
            const emoji = document.createElement('div');
            emoji.className = 'emoji';
            emoji.textContent = emojis[Math.floor(Math.random() * emojis.length)];
            emoji.style.left = Math.random() * 100 + '%';
            emoji.style.animationDuration = (Math.random() * 3 + 4) + 's';
            
            document.querySelector('.floating-emojis').appendChild(emoji);
            
            setTimeout(() => {
                emoji.remove();
            }, 7000);
        }

        // Initialize
        createStars();
        
        // Create floating emojis periodically
        setInterval(createFloatingEmoji, 800);
        
        // Add some interactive sparkle on click
        document.addEventListener('click', function(e) {
            const sparkle = document.createElement('div');
            sparkle.style.position = 'fixed';
            sparkle.style.left = e.clientX + 'px';
            sparkle.style.top = e.clientY + 'px';
            sparkle.style.pointerEvents = 'none';
            sparkle.style.fontSize = '2rem';
            sparkle.innerHTML = '✨';
            sparkle.style.zIndex = '1000';
            sparkle.style.animation = 'float 1s ease-out forwards';
            document.body.appendChild(sparkle);
            
            setTimeout(() => sparkle.remove(), 1000);
        });
    </script>
</body>
</html>
