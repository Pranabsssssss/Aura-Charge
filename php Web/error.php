<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page Not Found</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0f0f23 0%, #1a1a2e 50%, #16213e 100%);
            min-height: 100%;
            color: #ffffff;
            overflow-x: hidden;
        }
        
        /* Loading Screen */
        .loading-screen {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #0f0f23 0%, #1a1a2e 50%, #16213e 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            transition: opacity 0.5s ease-out;
        }
        
        .loading-screen.fade-out {
            opacity: 0;
            pointer-events: none;
        }
        
        .loader {
            width: 60px;
            height: 60px;
            border: 4px solid rgba(255, 255, 255, 0.3);
            border-top: 4px solid #ffffff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
                ::-webkit-scrollbar {
            width: 10px;
            background: #101820;
        }

        ::-webkit-scrollbar-thumb {
            background: linear-gradient(90deg, #00cfff 30%, #015eab 70%);
            border-radius: 8px;
            box-shadow: 0 0 10px #00cfff, 0 0 5px #0197f6;
            border: 2px solid #16232f;
            transition: background 0.3s;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(90deg, #0ff 20%, #017aff 80%);
            box-shadow: 0 0 14px #0ff;
        }

        ::-webkit-scrollbar-track {
            background: #0d1622;
            border-radius: 10px;
            box-shadow: inset 0 0 5px #015eab;
        }
        /* Main Content */
        .container {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 2rem;
            position: relative;
            opacity: 0;
            transform: translateY(30px);
            animation: fadeInUp 0.8s ease-out 1.5s forwards;
        }
        
        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Animated Background Elements */
        .bg-shapes {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            overflow: hidden;
        }
        
        .shape {
            position: absolute;
            opacity: 0.1;
            animation: float 6s ease-in-out infinite;
        }
        
        .shape:nth-child(1) {
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(0, 191, 255, 0.1), transparent);
            border-radius: 50%;
            top: 10%;
            left: 10%;
            animation-delay: 0s;
        }
        
        .shape:nth-child(2) {
            width: 200px;
            height: 200px;
            background: radial-gradient(circle, rgba(30, 144, 255, 0.1), transparent);
            border-radius: 50%;
            top: 60%;
            right: 15%;
            animation-delay: 2s;
        }
        
        .shape:nth-child(3) {
            width: 150px;
            height: 150px;
            background: radial-gradient(circle, rgba(0, 255, 255, 0.1), transparent);
            border-radius: 50%;
            bottom: 20%;
            left: 20%;
            animation-delay: 4s;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }
        
        /* Error Content */
        .error-container {
            text-align: center;
            max-width: 600px;
        }
        
        .error-code {
            font-size: 8rem;
            font-weight: 700;
            margin-bottom: 1rem;
            text-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            animation: bounce 2s ease-in-out infinite;
        }
        
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-10px); }
            60% { transform: translateY(-5px); }
        }
        
        .error-title {
            font-size: 2.5rem;
            font-weight: 600;
            margin-bottom: 1rem;
            opacity: 0;
            animation: slideIn 0.6s ease-out 2s forwards;
        }
        
        .error-message {
            font-size: 1.1rem;
            margin-bottom: 1rem;
            opacity: 0.9;
            line-height: 1.6;
            opacity: 0;
            animation: slideIn 0.6s ease-out 2.3s forwards;
        }
        
        .error-description {
            font-size: 1rem;
            margin-bottom: 3rem;
            opacity: 0.8;
            line-height: 1.6;
            opacity: 0;
            animation: slideIn 0.6s ease-out 2.6s forwards;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        /* Buttons */
        .action-buttons {
            display: flex;
            gap: 1.5rem;
            justify-content: center;
            flex-wrap: wrap;
            opacity: 0;
            animation: slideIn 0.6s ease-out 2.9s forwards;
        }
        
        .btn {
            padding: 1rem 2rem;
            border: none;
            border-radius: 50px;
            font-size: 1rem;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }
        
        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }
        
        .btn:hover::before {
            left: 100%;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #00bfff, #1e90ff);
            color: white;
            border: 2px solid rgba(0, 191, 255, 0.3);
            box-shadow: 0 4px 15px rgba(0, 191, 255, 0.3);
        }
        
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 191, 255, 0.4);
            background: linear-gradient(135deg, #00ffff, #00bfff);
        }
        
        .btn-secondary {
            background: rgba(0, 191, 255, 0.1);
            color: white;
            border: 2px solid rgba(0, 191, 255, 0.3);
            backdrop-filter: blur(10px);
        }
        
        .btn-secondary:hover {
            transform: translateY(-3px);
            background: rgba(0, 191, 255, 0.2);
            border-color: rgba(0, 191, 255, 0.5);
            box-shadow: 0 10px 25px rgba(0, 191, 255, 0.2);
        }
        
        /* Floating particles */
        .particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 1;
        }
        
        .particle {
            position: absolute;
            width: 4px;
            height: 4px;
            background: #00bfff;
            border-radius: 50%;
            box-shadow: 0 0 10px #00bfff, 0 0 20px #00bfff, 0 0 30px #00bfff;
            animation: particle-float 8s infinite linear;
        }
        
        .particle:nth-child(1) { left: 10%; animation-delay: 0s; }
        .particle:nth-child(2) { left: 20%; animation-delay: 1s; }
        .particle:nth-child(3) { left: 30%; animation-delay: 2s; }
        .particle:nth-child(4) { left: 40%; animation-delay: 3s; }
        .particle:nth-child(5) { left: 50%; animation-delay: 4s; }
        .particle:nth-child(6) { left: 60%; animation-delay: 5s; }
        .particle:nth-child(7) { left: 70%; animation-delay: 6s; }
        .particle:nth-child(8) { left: 80%; animation-delay: 7s; }
        
        @keyframes particle-float {
            0% { transform: translateY(100vh) scale(0); opacity: 0; }
            10% { opacity: 0.6; }
            90% { opacity: 0.6; }
            100% { transform: translateY(-100px) scale(1); opacity: 0; }
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .error-code {
                font-size: 5rem;
            }
            
            .error-title {
                font-size: 2rem;
            }
            
            .error-message {
                font-size: 1rem;
            }
            
            .action-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .btn {
                width: 100%;
                max-width: 280px;
                justify-content: center;
            }
            
            .container {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Loading Screen -->
    <div class="loading-screen" id="loadingScreen">
        <div class="loader"></div>
    </div>
    
    <!-- Grid Pattern -->
    <div style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-image: linear-gradient(rgba(0,191,255,0.1) 1px, transparent 1px), linear-gradient(90deg, rgba(0,191,255,0.1) 1px, transparent 1px); background-size: 50px 50px; z-index: -1; opacity: 0.3;"></div>
    
    <!-- Background Shapes -->
    <div class="bg-shapes">
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
    </div>
    
    <!-- Floating Particles -->
    <div class="particles">
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
    </div>
    
    <div class="container">
        <div class="error-container">
            <div class="error-code">404</div>
            
            <h1 class="error-title">Oops! Page Not Found</h1>
            
            <p class="error-message">We couldn't find what you're looking for</p>
            
            <p class="error-description">
                The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.
            </p>
            
            <div class="action-buttons">
                <button onclick="history.back()" class="btn btn-secondary">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Go Back
                </button>
                
                <a href="/" class="btn btn-primary">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                    Home Page
                </a>
            </div>
        </div>
    </div>
    
    <script>
        // Loading screen
        window.addEventListener('load', function() {
            setTimeout(function() {
                const loadingScreen = document.getElementById('loadingScreen');
                loadingScreen.classList.add('fade-out');
                
                setTimeout(function() {
                    loadingScreen.style.display = 'none';
                }, 500);
            }, 1000);
        });
        
        // Interactive particle effects
        document.addEventListener('mousemove', function(e) {
            const particles = document.querySelectorAll('.particle');
            const mouseX = e.clientX / window.innerWidth;
            const mouseY = e.clientY / window.innerHeight;
            
            particles.forEach((particle, index) => {
                const speed = (index + 1) * 0.3;
                const x = mouseX * speed * 10;
                const y = mouseY * speed * 10;
                
                particle.style.transform = `translate(${x}px, ${y}px)`;
            });
        });
        
        // Add click ripple effect to buttons
        document.querySelectorAll('.btn').forEach(button => {
            button.addEventListener('click', function(e) {
                const ripple = document.createElement('span');
                const rect = this.getBoundingClientRect();
                const size = Math.max(rect.width, rect.height);
                const x = e.clientX - rect.left - size / 2;
                const y = e.clientY - rect.top - size / 2;
                
                ripple.style.width = ripple.style.height = size + 'px';
                ripple.style.left = x + 'px';
                ripple.style.top = y + 'px';
                ripple.style.position = 'absolute';
                ripple.style.borderRadius = '50%';
                ripple.style.background = 'rgba(255, 255, 255, 0.3)';
                ripple.style.transform = 'scale(0)';
                ripple.style.animation = 'ripple 0.6s linear';
                ripple.style.pointerEvents = 'none';
                
                this.appendChild(ripple);
                
                setTimeout(() => {
                    ripple.remove();
                }, 600);
            });
        });
        
        // Add ripple animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes ripple {
                to {
                    transform: scale(2);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>
