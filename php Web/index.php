<?php
session_start();

if (isset($_SESSION['user']) && !empty($_SESSION['user'])) {
    header('Location: /dash');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aura Charge - A New Dimension of Charging</title>
    <link rel="icon" href="https://auraof.pranab.tech/logo.png" type="image/png">
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Orbitron:wght@400;700;900&display=swap"
        rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/gh/studio-freight/lenis@1.0.19/bundled/lenis.min.js"></script>
    <style>
        body {
            box-sizing: border-box;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html,
        body {
            height: 100%;
            scroll-behavior: smooth;
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

        scrollbar-color: #00cfff #0d1622;
        scrollbar-width: thin;


        .glow-blue {
            box-shadow: 0 0 20px rgba(0, 191, 255, 0.3);
        }

        .glow-blue-intense {
            box-shadow: 0 0 30px rgba(0, 191, 255, 0.6), 0 0 60px rgba(0, 191, 255, 0.3);
        }

        .text-glow {
            text-shadow: 0 0 20px rgba(0, 191, 255, 0.5);
        }

        .gradient-text {
            background: linear-gradient(135deg, #00bfff, #1e90ff, #00ffff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero-bg {
            background: radial-gradient(ellipse at center, rgba(0, 191, 255, 0.1) 0%, rgba(0, 0, 0, 0.8) 70%),
                linear-gradient(135deg, rgba(0, 0, 0, 0.9) 0%, rgba(0, 20, 40, 0.9) 100%);
        }

        .floating {
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-20px);
            }
        }

        .pulse-glow {
            animation: pulseGlow 2s ease-in-out infinite alternate;
        }

        @keyframes pulseGlow {
            from {
                box-shadow: 0 0 20px rgba(0, 191, 255, 0.3);
            }

            to {
                box-shadow: 0 0 40px rgba(0, 191, 255, 0.6), 0 0 80px rgba(0, 191, 255, 0.2);
            }
        }

        .charging-station {
            background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
            border: 1px solid rgba(0, 191, 255, 0.2);
        }


        .main-header {
            position: fixed;
            top: 1rem;
            left: 50%;
            transform: translateX(-50%) translateY(0);
            z-index: 50;
            background: rgba(0, 0, 0, 0.9);
            backdrop-filter: blur(16px);
            border: 2px solid rgba(107, 114, 128, 0.7);
            border-radius: 1rem;
            width: calc(100% - 2rem);
            max-width: 70%;
            transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            opacity: 1;
        }

        .main-header:hover {
            border-color: rgba(0, 191, 255, 0.5);
            background: rgba(0, 0, 0, 0.95);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25), 0 0 30px rgba(0, 191, 255, 0.2);
        }

        .main-header.header-hidden {
            transform: translateX(-50%) translateY(-120%);
            opacity: 0;
        }

        .main-header.header-visible {
            transform: translateX(-50%) translateY(0);
            opacity: 1;
        }


        .main-header.header-loading {
            animation: headerEntrance 0.8s cubic-bezier(0.25, 0.46, 0.45, 0.94) forwards;
        }

        @keyframes headerEntrance {
            0% {
                transform: translateX(-50%) translateY(-120%);
                opacity: 0;
                scale: 0.9;
            }

            50% {
                transform: translateX(-50%) translateY(-10px);
                opacity: 0.8;
                scale: 1.02;
            }

            100% {
                transform: translateX(-50%) translateY(0);
                opacity: 1;
                scale: 1;
            }
        }


        .main-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(0, 191, 255, 0.1), transparent);
            transition: left 0.8s ease;
            border-radius: 1rem;
        }

        .main-header:hover::before {
            left: 100%;
        }


        .main-header::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            border-radius: 1rem;
            box-shadow: 0 0 20px rgba(0, 191, 255, 0.1);
            animation: headerPulse 3s ease-in-out 1s infinite;
            pointer-events: none;
        }

        @keyframes headerPulse {

            0%,
            100% {
                box-shadow: 0 0 20px rgba(0, 191, 255, 0.1);
            }

            50% {
                box-shadow: 0 0 30px rgba(0, 191, 255, 0.2), 0 0 60px rgba(0, 191, 255, 0.1);
            }
        }


        .logo-circle {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid rgba(0, 191, 255, 0.3);
            transition: all 0.3s ease;
        }

        .logo-circle:hover {
            border-color: rgba(0, 191, 255, 0.8);
            box-shadow: 0 0 20px rgba(0, 191, 255, 0.4);
        }

        .logo-container {
            position: relative;
            z-index: 10;
        }

        .logo-container:hover .logo-circle {
            animation: logoRotate 0.6s ease-in-out;
        }

        @keyframes logoRotate {
            0% {
                transform: rotate(0deg) scale(1);
            }

            50% {
                transform: rotate(180deg) scale(1.1);
            }

            100% {
                transform: rotate(360deg) scale(1);
            }
        }

        .brand-text {
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .logo-container:hover .brand-text {
            transform: scale(1.05);
            text-shadow: 0 0 25px rgba(0, 191, 255, 0.8), 0 0 50px rgba(0, 191, 255, 0.4);
        }


        .signin-button {
            position: relative;
            overflow: hidden;
            transform-style: preserve-3d;
            z-index: 10;
            transition: all 0.4s ease;
        }

        .signin-button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(0, 191, 255, 0.2), transparent);
            transition: left 0.5s;
            border-radius: 9999px;
        }

        .signin-button:hover::before {
            left: 100%;
        }

        .signin-button::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: radial-gradient(circle, rgba(0, 191, 255, 0.3) 0%, transparent 70%);
            transition: all 0.4s ease;
            transform: translate(-50%, -50%);
            border-radius: 50%;
        }

        .signin-button:hover::after {
            width: 200px;
            height: 200px;
        }

        .signin-button:hover {
            transform: translateY(-2px) scale(1.02);
            box-shadow: 0 8px 25px rgba(0, 191, 255, 0.3), 0 0 30px rgba(0, 191, 255, 0.2);
            background: rgba(0, 191, 255, 0.15);
            border-color: rgba(0, 191, 255, 0.8);
            color: rgb(165, 243, 252);
        }


        .fade-in-up {
            opacity: 0;
            transform: translateY(30px);
            animation: fadeInUp 0.8s ease-out forwards;
        }

        .fade-in-left {
            opacity: 0;
            transform: translateX(-30px);
            animation: fadeInLeft 0.8s ease-out forwards;
        }

        .fade-in-right {
            opacity: 0;
            transform: translateX(30px);
            animation: fadeInRight 0.8s ease-out forwards;
        }

        .scale-in {
            opacity: 0;
            transform: scale(0.8);
            animation: scaleIn 0.6s ease-out forwards;
        }

        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInLeft {
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes fadeInRight {
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes scaleIn {
            to {
                opacity: 1;
                transform: scale(1);
            }
        }


        .delay-100 {
            animation-delay: 0.1s;
        }

        .delay-200 {
            animation-delay: 0.2s;
        }

        .delay-300 {
            animation-delay: 0.3s;
        }

        .delay-400 {
            animation-delay: 0.4s;
        }

        .delay-500 {
            animation-delay: 0.5s;
        }

        .delay-600 {
            animation-delay: 0.6s;
        }

        .delay-700 {
            animation-delay: 0.7s;
        }

        .delay-800 {
            animation-delay: 0.8s;
        }


        .feature-card {
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
        }

        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(0, 191, 255, 0.1), transparent);
            transition: left 0.6s;
        }

        .feature-card:hover::before {
            left: 100%;
        }

        .feature-card:hover {
            transform: translateY(-10px) scale(1.02);
            border-color: rgba(0, 191, 255, 0.5);
            box-shadow: 0 20px 40px rgba(0, 191, 255, 0.2), 0 0 30px rgba(0, 191, 255, 0.3);
        }

        .feature-icon {
            transition: all 0.3s ease;
        }

        .feature-card:hover .feature-icon {
            transform: scale(1.1) rotate(5deg);
            box-shadow: 0 0 20px rgba(0, 191, 255, 0.4);
        }

        /* Charging Station Enhanced Animation */
        .charging-station-enhanced {
            transition: all 0.4s ease;
            position: relative;
        }

        .charging-station-enhanced:hover {
            transform: scale(1.05);
            box-shadow: 0 20px 40px rgba(0, 191, 255, 0.3);
        }

        .charging-pulse {
            animation: chargingPulse 1.5s ease-in-out infinite;
        }

        @keyframes chargingPulse {

            0%,
            100% {
                box-shadow: 0 0 20px rgba(0, 191, 255, 0.6);
                transform: scale(1);
            }

            50% {
                box-shadow: 0 0 40px rgba(0, 191, 255, 0.9), 0 0 60px rgba(0, 191, 255, 0.4);
                transform: scale(1.1);
            }
        }


        .hover-glow:hover {
            text-shadow: 0 0 30px rgba(0, 191, 255, 0.8);
            transform: scale(1.02);
            transition: all 0.3s ease;
        }

        .particle {
            position: absolute;
            background: rgba(0, 191, 255, 0.6);
            border-radius: 50%;
            pointer-events: none;
            animation: particleFloat 8s linear infinite;
        }

        @keyframes particleFloat {
            0% {
                transform: translateY(100vh) rotate(0deg);
                opacity: 0;
            }

            10% {
                opacity: 1;
            }

            90% {
                opacity: 1;
            }

            100% {
                transform: translateY(-100px) rotate(360deg);
                opacity: 0;
            }
        }


        .loading-spinner {
            width: 40px;
            height: 40px;
            border: 3px solid rgba(0, 191, 255, 0.3);
            border-top: 3px solid #00bfff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }


        .page-loader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: #000;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            transition: opacity 0.5s ease;
        }

        .page-loader.fade-out {
            opacity: 0;
            pointer-events: none;
        }


        .wireless-wave {
            animation: wirelessWave 2s ease-out infinite;
        }

        @keyframes wirelessWave {
            0% {
                transform: translate(-50%, -50%) scale(0);
                opacity: 1;
            }

            100% {
                transform: translate(-50%, -50%) scale(1);
                opacity: 0;
            }
        }


        .car-charging {
            filter: drop-shadow(0 0 10px rgba(0, 191, 255, 0.3));
        }

        .charging-port {
            animation: chargingPortGlow 1.5s ease-in-out infinite alternate;
        }

        @keyframes chargingPortGlow {
            from {
                fill: rgba(0, 191, 255, 0.6);
                filter: drop-shadow(0 0 5px rgba(0, 191, 255, 0.4));
            }

            to {
                fill: rgba(0, 191, 255, 1);
                filter: drop-shadow(0 0 15px rgba(0, 191, 255, 0.8));
            }
        }

        .battery-fill {
            animation: batteryFill 3s ease-in-out infinite;
        }

        @keyframes batteryFill {

            0%,
            100% {
                width: 26px;
            }

            50% {
                width: 20px;
            }
        }

        .charging-bar {
            animation: chargingBarPulse 1.2s ease-in-out infinite;
        }

        @keyframes chargingBarPulse {

            0%,
            100% {
                opacity: 0.3;
                transform: scaleY(0.5);
            }

            50% {
                opacity: 1;
                transform: scaleY(1);
            }
        }

        .cta-button {
            background: linear-gradient(135deg, rgba(0, 191, 255, 0.1) 0%, rgba(30, 144, 255, 0.1) 100%);
            border: 1px solid rgba(0, 191, 255, 0.3);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .cta-button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(0, 191, 255, 0.3), transparent);
            transition: left 0.6s;
        }

        .cta-button:hover::before {
            left: 100%;
        }

        .cta-button:hover {
            background: linear-gradient(135deg, rgba(0, 191, 255, 0.2) 0%, rgba(30, 144, 255, 0.2) 100%);
            border-color: rgba(0, 191, 255, 0.6);
            box-shadow: 0 0 30px rgba(0, 191, 255, 0.4);
            transform: translateY(-2px);
        }
    </style>
</head>

<body class="bg-black text-white font-inter">

    <div id="pageLoader" class="page-loader">
        <div class="text-center">
            <div class="loading-spinner mx-auto mb-4"></div>
            <div class="text-cyan-400 font-orbitron text-lg">Loading AuraStation...</div>
        </div>
    </div>
    <header class="main-header">
        <nav class="flex items-center justify-between px-6 py-4">

            <div class="flex items-center space-x-3">
                <img src="https://auraof.pranab.tech/logo.png" alt="Aura Charge Logo" class="logo-circle">
                <div>
                    <h1 class="text-xl font-orbitron font-bold gradient-text">Aura Charge</h1>
                </div>
            </div>
            <a href="/signin"
                class="signin-button px-5 sm:px-6 py-2 rounded-full border-2 border-cyan-400/40 text-cyan-400 transition-all duration-400 fade-in-right delay-300">
                <span class="relative z-10">Sign In</span>
            </a>
        </nav>
    </header>


    <main class="min-h-full">
        <section class="hero-bg min-h-full flex items-center justify-center relative overflow-hidden">

            <div class="absolute inset-0 overflow-hidden">
                <div class="absolute top-1/4 left-1/4 w-2 h-2 bg-cyan-400 rounded-full pulse-glow"></div>
                <div class="absolute top-3/4 right-1/4 w-1 h-1 bg-blue-400 rounded-full pulse-glow"
                    style="animation-delay: 1s;"></div>
                <div class="absolute top-1/2 left-3/4 w-1.5 h-1.5 bg-cyan-300 rounded-full pulse-glow"
                    style="animation-delay: 2s;"></div>


                <div id="particles-container"></div>
            </div>

            <div class="container mx-auto px-6 py-20 text-center relative z-10">
                <div class="max-w-4xl mx-auto">

                    <div class="floating mb-12 scale-in delay-400 relative z-30 mt-8">
                        <div class="relative w-80 h-40 mx-auto">

                            <div
                                class="absolute bottom-0 left-1/2 transform -translate-x-1/2 w-16 h-8 bg-gradient-to-t from-gray-700 to-gray-600 rounded-lg border border-cyan-400/30">

                                <div class="absolute -top-2 left-1/2 transform -translate-x-1/2">
                                    <div class="w-4 h-4 bg-cyan-400 rounded-full charging-pulse"></div>

                                    <div
                                        class="wireless-wave absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-8 h-8 border-2 border-cyan-400/50 rounded-full">
                                    </div>
                                    <div class="wireless-wave absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-12 h-12 border-2 border-cyan-400/40 rounded-full"
                                        style="animation-delay: 0.3s;"></div>
                                    <div class="wireless-wave absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-16 h-16 border-2 border-cyan-400/30 rounded-full"
                                        style="animation-delay: 0.6s;"></div>
                                    <div class="wireless-wave absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-20 h-20 border-2 border-cyan-400/20 rounded-full"
                                        style="animation-delay: 0.9s;"></div>
                                </div>

                                <div class="absolute top-1 right-1 w-1.5 h-1.5 bg-green-400 rounded-full pulse-glow">
                                </div>
                            </div>


                            <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2">
                                <svg width="120" height="60" viewBox="0 0 120 60" class="car-charging">

                                    <rect x="10" y="25" width="80" height="20" rx="8" fill="url(#carGradient)"
                                        stroke="rgba(0, 191, 255, 0.3)" stroke-width="1" />

                                    <rect x="25" y="15" width="50" height="15" rx="6" fill="url(#roofGradient)"
                                        stroke="rgba(0, 191, 255, 0.2)" stroke-width="1" />

                                    <rect x="30" y="18" width="15" height="8" rx="2" fill="rgba(0, 191, 255, 0.2)" />
                                    <rect x="55" y="18" width="15" height="8" rx="2" fill="rgba(0, 191, 255, 0.2)" />

                                    <circle cx="25" cy="50" r="8" fill="#333" stroke="rgba(0, 191, 255, 0.4)"
                                        stroke-width="2" />
                                    <circle cx="75" cy="50" r="8" fill="#333" stroke="rgba(0, 191, 255, 0.4)"
                                        stroke-width="2" />
                                    <circle cx="25" cy="50" r="4" fill="rgba(0, 191, 255, 0.6)" />
                                    <circle cx="75" cy="50" r="4" fill="rgba(0, 191, 255, 0.6)" />

                                    <circle cx="95" cy="35" r="3" fill="rgba(255, 255, 255, 0.8)" />

                                    <rect x="45" y="40" width="10" height="4" rx="2" fill="rgba(0, 191, 255, 0.8)"
                                        class="charging-port" />

                                    <rect x="35" y="30" width="30" height="6" rx="3" fill="rgba(0, 0, 0, 0.3)"
                                        stroke="rgba(0, 191, 255, 0.3)" stroke-width="1" />
                                    <rect x="37" y="32" width="26" height="2" rx="1" fill="url(#batteryGradient)"
                                        class="battery-fill" />


                                    <defs>
                                        <linearGradient id="carGradient" x1="0%" y1="0%" x2="0%" y2="100%">
                                            <stop offset="0%" style="stop-color:#4a5568;stop-opacity:1" />
                                            <stop offset="100%" style="stop-color:#2d3748;stop-opacity:1" />
                                        </linearGradient>
                                        <linearGradient id="roofGradient" x1="0%" y1="0%" x2="0%" y2="100%">
                                            <stop offset="0%" style="stop-color:#2d3748;stop-opacity:1" />
                                            <stop offset="100%" style="stop-color:#1a202c;stop-opacity:1" />
                                        </linearGradient>
                                        <linearGradient id="batteryGradient" x1="0%" y1="0%" x2="100%" y2="0%">
                                            <stop offset="0%" style="stop-color:#00bfff;stop-opacity:1" />
                                            <stop offset="50%" style="stop-color:#1e90ff;stop-opacity:1" />
                                            <stop offset="100%" style="stop-color:#00ffff;stop-opacity:1" />
                                        </linearGradient>
                                    </defs>
                                </svg>

                                <div class="absolute -top-8 left-1/2 transform -translate-x-1/2 text-center">
                                    <div class="text-xs text-cyan-400 font-semibold mb-1">CHARGING</div>
                                    <div class="flex space-x-1">
                                        <div class="w-1 h-3 bg-cyan-400 rounded charging-bar"
                                            style="animation-delay: 0s;"></div>
                                        <div class="w-1 h-3 bg-cyan-400/60 rounded charging-bar"
                                            style="animation-delay: 0.2s;"></div>
                                        <div class="w-1 h-3 bg-cyan-400/40 rounded charging-bar"
                                            style="animation-delay: 0.4s;"></div>
                                        <div class="w-1 h-3 bg-cyan-400/20 rounded charging-bar"
                                            style="animation-delay: 0.6s;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                    <h1 class="text-5xl md:text-7xl font-orbitron font-black mb-6 leading-tight fade-in-up delay-500">
                        <span class="gradient-text text-glow hover-glow">Aura Charge</span>
                        <br>
                        <span class="text-white hover-glow">A New Dimension of Charging</span>
                    </h1>

                    <p class="text-xl md:text-2xl text-gray-300 mb-8 font-light fade-in-up delay-600 max-w-4xl mx-auto">
                        Introducing Aura Charge, an innovative wireless charging solution designed by
                        <span class="text-cyan-400 font-medium hover-glow">Shivam Roy</span>,
                        <span class="text-blue-400 font-medium hover-glow">Manit Shukla</span>, and
                        <span class="text-purple-400 font-medium hover-glow">Manoj Sharma</span> to redefine EV
                        charging.
                    </p>

                    <p class="text-lg text-gray-400 mb-12 font-light fade-in-up delay-700 max-w-3xl mx-auto">
                        <span class="text-cyan-400 font-medium hover-glow">Safe.</span>
                        <span class="text-blue-400 font-medium hover-glow">Efficient.</span>
                        <span class="text-cyan-300 font-medium hover-glow">Wireless.</span>
                    </p>


                    <a href="/signin"
                        class="cta-button inline-block px-12 py-4 rounded-full text-lg font-semibold text-cyan-400 hover:text-white transition-all duration-300 relative fade-in-up delay-700">
                        Get Started
                        <svg class="inline-block ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                        </svg>
                    </a>
                </div>


                <div class="grid md:grid-cols-3 gap-8 mt-20 max-w-6xl mx-auto">
                    <div
                        class="feature-card bg-gray-900/50 backdrop-blur-sm border border-gray-800 rounded-xl p-6 fade-in-up delay-800">
                        <div
                            class="feature-icon w-12 h-12 bg-gradient-to-br from-cyan-400 to-blue-500 rounded-lg mb-4 flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z">
                                </path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold mb-2 text-cyan-400 hover-glow">Safe & Efficient Charging</h3>
                        <p class="text-gray-400">Advanced safety protocols ensure secure wireless power transfer with
                            optimal efficiency rates.</p>
                    </div>

                    <div
                        class="feature-card bg-gray-900/50 backdrop-blur-sm border border-gray-800 rounded-xl p-6 fade-in-up delay-900">
                        <div
                            class="feature-icon w-12 h-12 bg-gradient-to-br from-blue-400 to-purple-500 rounded-lg mb-4 flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0">
                                </path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold mb-2 text-blue-400 hover-glow">Wireless Technology</h3>
                        <p class="text-gray-400">Based on mutual induction principles, eliminating cables for seamless
                            charging experience.</p>
                    </div>

                    <div
                        class="feature-card bg-gray-900/50 backdrop-blur-sm border border-gray-800 rounded-xl p-6 fade-in-up delay-1000">
                        <div
                            class="feature-icon w-12 h-12 bg-gradient-to-br from-green-400 to-cyan-500 rounded-lg mb-4 flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                </path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold mb-2 text-green-400 hover-glow">Universal Compatibility</h3>
                        <p class="text-gray-400">Seamlessly integrates with any type of EV, making charging accessible
                            for all vehicles.</p>
                    </div>
                </div>
            </div>
        </section>


        <section class="py-20 bg-gradient-to-b from-black to-gray-900 relative overflow-hidden">
            <div class="container mx-auto px-6">
                <div class="text-center mb-16 fade-in-up">
                    <h2 class="text-4xl md:text-5xl font-orbitron font-bold mb-6 gradient-text">
                        Compatible with Any EV
                    </h2>
                    <p class="text-xl text-gray-300 max-w-3xl mx-auto">
                        Aura Charge's innovative wireless technology adapts to any electric vehicle,
                        from compact cars to heavy-duty trucks, ensuring universal accessibility.
                    </p>
                </div>

                <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8 max-w-6xl mx-auto">
                    <div
                        class="feature-card bg-gray-800/50 backdrop-blur-sm border border-gray-700 rounded-xl p-6 text-center fade-in-up delay-200">
                        <div
                            class="w-16 h-16 bg-gradient-to-br from-cyan-400 to-blue-500 rounded-full mx-auto mb-4 flex items-center justify-center">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 6h3l2 7H9l-1-7h5z"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-cyan-400 mb-2">Passenger Cars</h3>
                        <p class="text-gray-400 text-sm">Sedans, hatchbacks, and compact EVs</p>
                    </div>

                    <div
                        class="feature-card bg-gray-800/50 backdrop-blur-sm border border-gray-700 rounded-xl p-6 text-center fade-in-up delay-400">
                        <div
                            class="w-16 h-16 bg-gradient-to-br from-blue-400 to-purple-500 rounded-full mx-auto mb-4 flex items-center justify-center">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2">
                                </path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-blue-400 mb-2">SUVs & Crossovers</h3>
                        <p class="text-gray-400 text-sm">Large family vehicles and luxury EVs</p>
                    </div>

                    <div
                        class="feature-card bg-gray-800/50 backdrop-blur-sm border border-gray-700 rounded-xl p-6 text-center fade-in-up delay-600">
                        <div
                            class="w-16 h-16 bg-gradient-to-br from-green-400 to-cyan-500 rounded-full mx-auto mb-4 flex items-center justify-center">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-green-400 mb-2">Commercial Vehicles</h3>
                        <p class="text-gray-400 text-sm">Delivery vans and electric trucks</p>
                    </div>

                    <div
                        class="feature-card bg-gray-800/50 backdrop-blur-sm border border-gray-700 rounded-xl p-6 text-center fade-in-up delay-800">
                        <div
                            class="w-16 h-16 bg-gradient-to-br from-purple-400 to-pink-500 rounded-full mx-auto mb-4 flex items-center justify-center">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-purple-400 mb-2">E-Bikes & Scooters</h3>
                        <p class="text-gray-400 text-sm">Two-wheelers and micro-mobility</p>
                    </div>
                </div>
            </div>
        </section>
    </main>
    <footer class="relative bg-black border-t border-cyan-400/20 overflow-hidden">

        <div class="absolute inset-0 bg-gradient-to-t from-black via-gray-900/50 to-transparent"></div>
        <div class="absolute inset-0 opacity-30">
            <div class="absolute top-0 left-1/4 w-96 h-96 bg-cyan-400/10 rounded-full blur-3xl animate-pulse"></div>
            <div
                class="absolute bottom-0 right-1/4 w-80 h-80 bg-blue-500/10 rounded-full blur-3xl animate-pulse delay-1000">
            </div>
        </div>


        <div class="absolute inset-0 opacity-20"
            style="background-image: linear-gradient(rgba(0,191,255,0.1) 1px, transparent 1px), linear-gradient(90deg, rgba(0,191,255,0.1) 1px, transparent 1px); background-size: 50px 50px;">
        </div>

        <div class="relative container mx-auto px-6 py-16">

            <div class="flex justify-center mb-12">

                <div class="text-center max-w-2xl">
                    <div class="flex items-center justify-center space-x-4 mb-6">
                        <div class="relative">
                            <img src="https://auraof.pranab.tech/logo.png" alt="Aura Charge"
                                class="w-16 h-16 rounded-full border-2 border-cyan-400/50 shadow-lg"
                                onerror="this.src=''; this.alt='Logo failed to load'; this.style.display='none';">
                            <div class="absolute inset-0 rounded-full bg-cyan-400/20 animate-ping"></div>
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold gradient-text"
                                style="font-family: 'Orbitron', monospace; background: linear-gradient(135deg, #00bfff, #1e90ff, #00ffff); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">
                                Aura Charge</h3>
                            <p class="text-gray-400 text-sm">Powering the Future</p>
                        </div>
                    </div>
                    <p class="text-gray-300 mb-6 leading-relaxed">
                        Experience the next generation of electric vehicle charging with our advanced network of smart
                        charging stations. Clean energy, intelligent technology, seamless experience.
                    </p>

                </div>
            </div>


            <div class="border-t border-gray-800 pt-8">
                <div class="flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
                    <div class="flex flex-col md:flex-row items-center space-y-2 md:space-y-0 md:space-x-6">
                        <div class="flex flex-col items-center md:items-start space-y-1">
                            <a href="https://github.com/pranabsssssss" target="_blank" rel="noopener noreferrer"
                                class="text-cyan-400 hover:text-cyan-300 transition-colors font-medium">
                                <p class="text-gray-400 text-sm">
                                    © 2025 Aura Charge. All rights reserved.
                                </p>
                                <p class="text-gray-500 text-xs">
                                    Website Developed by

                                    Pranab Saini
                            </a>
                            </p>
                        </div>
                    </div>


                    <button onclick="scrollToTop()"
                        class="group relative w-12 h-12 bg-gradient-to-br from-cyan-400/20 to-blue-500/20 border border-cyan-400/30 rounded-xl flex items-center justify-center hover:border-cyan-400/60 transition-all duration-300 hover:scale-110">
                        <svg class="w-5 h-5 text-cyan-400 group-hover:text-white transition-colors" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
                        </svg>
                        <div
                            class="absolute inset-0 rounded-xl bg-gradient-to-br from-cyan-400/0 to-blue-500/0 group-hover:from-cyan-400/20 group-hover:to-blue-500/20 transition-all duration-300">
                        </div>
                    </button>
                </div>
            </div>
        </div>


        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute top-1/4 left-1/4 w-2 h-2 bg-cyan-400/30 rounded-full animate-bounce delay-0"></div>
            <div class="absolute top-1/3 right-1/3 w-1 h-1 bg-blue-400/40 rounded-full animate-bounce delay-500"></div>
            <div class="absolute bottom-1/4 left-1/3 w-1.5 h-1.5 bg-cyan-300/20 rounded-full animate-bounce delay-1000">
            </div>
            <div class="absolute bottom-1/3 right-1/4 w-1 h-1 bg-blue-300/30 rounded-full animate-bounce delay-1500">
            </div>
        </div>

        <script>
            function scrollToTop() {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            }
        </script>
    </footer>


    <script>

        const lenis = new Lenis({
            duration: 1.2,
            easing: (t) => Math.min(1, 1.001 - Math.pow(2, -10 * t)),
            direction: 'vertical',
            gestureDirection: 'vertical',
            smooth: true,
            mouseMultiplier: 1,
            smoothTouch: false,
            touchMultiplier: 2,
            infinite: false,
        });

        function raf(time) {
            lenis.raf(time);
            requestAnimationFrame(raf);
        }
        requestAnimationFrame(raf);


        let lastScrollTop = 0;
        let isHeaderVisible = true;
        let scrollTimeout;
        const header = document.getElementById('mainHeader');

        function handleHeaderScroll() {
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            const scrollThreshold = 100;

            if (scrollTop > lastScrollTop && scrollTop > scrollThreshold) {

                if (isHeaderVisible) {
                    header.classList.remove('header-visible');
                    header.classList.add('header-hidden');
                    isHeaderVisible = false;
                }
            } else {

                if (!isHeaderVisible || scrollTop <= scrollThreshold) {
                    header.classList.remove('header-hidden');
                    header.classList.add('header-visible');
                    isHeaderVisible = true;
                }
            }

            lastScrollTop = scrollTop <= 0 ? 0 : scrollTop;
        }


        window.addEventListener('scroll', function () {
            clearTimeout(scrollTimeout);
            scrollTimeout = setTimeout(handleHeaderScroll, 10);
        });


        window.addEventListener('load', function () {
            setTimeout(() => {
                const loader = document.getElementById('pageLoader');
                loader.classList.add('fade-out');
                setTimeout(() => {
                    loader.style.display = 'none';
                }, 500);
            }, 1000);
        });


        function createParticle() {
            const particle = document.createElement('div');
            particle.className = 'particle';

            const size = Math.random() * 4 + 1;
            particle.style.width = size + 'px';
            particle.style.height = size + 'px';
            particle.style.left = Math.random() * 100 + '%';
            particle.style.animationDuration = (Math.random() * 3 + 5) + 's';
            particle.style.animationDelay = Math.random() * 2 + 's';

            document.getElementById('particles-container').appendChild(particle);

            setTimeout(() => {
                particle.remove();
            }, 8000);
        }


        setInterval(createParticle, 300);


        document.querySelectorAll('.feature-card').forEach(card => {
            card.addEventListener('mouseenter', function () {
                this.style.transform = 'translateY(-10px) scale(1.02)';
            });

            card.addEventListener('mouseleave', function () {
                this.style.transform = 'translateY(0) scale(1)';
            });
        });


        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });


        document.addEventListener('mousemove', function (e) {
            const glowElements = document.querySelectorAll('.charging-pulse');
            glowElements.forEach(element => {
                const rect = element.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;

                if (x >= 0 && x <= rect.width && y >= 0 && y <= rect.height) {
                    element.style.boxShadow = `0 0 50px rgba(0, 191, 255, 0.8), 0 0 100px rgba(0, 191, 255, 0.4)`;
                }
            });
        });


        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver(function (entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.animationPlayState = 'running';
                }
            });
        }, observerOptions);


        document.querySelectorAll('.fade-in-up, .fade-in-left, .fade-in-right, .scale-in').forEach(el => {
            observer.observe(el);
        });
    </script>
    <script>

        window.addEventListener('load', () => {
            const user = localStorage.getItem('auraChargeUser');
            const authToken = localStorage.getItem('auraChargeAuthToken');
            if (user && authToken) {
                const userObj = JSON.parse(user);
                const loginTime = new Date(userObj.loginTime);
                const now = new Date();
                const hoursDiff = (now - loginTime) / (1000 * 60 * 60);
                if (hoursDiff < 24) {
                    window.location.href = '/dash';
                } else {
                    localStorage.removeItem('auraChargeUser');
                    localStorage.removeItem('auraChargeAuthToken');
                }
            }


            setTimeout(() => {
                const loader = document.getElementById('pageLoader');
                loader.classList.add('fade-out');
                setTimeout(() => {
                    loader.style.display = 'none';
                }, 500);
            }, 1000);
        });


    </script>
</body>

</html>