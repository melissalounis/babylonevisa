<?php
require_once __DIR__ . '/../config.php';
$page_title = "Accueil - Babylone Service";
include __DIR__ . '/../includes/header.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil - Babylone Service</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* ===== VARIABLES CSS ===== */
        :root {
            /* Couleurs principales Babylone Service */
            --primary-blue: #1a5f7a;
            --secondary-blue: #3d8bb6;
            --accent-gold: #d4af37;
            --accent-dark: #2c3e50;
            --light-beige: #f5f5dc;
            --white: #ffffff;
            --light-gray: #f8f9fa;
            --text-dark: #2c3e50;
            --text-light: #6c757d;
            
            /* Dégradés */
            --gradient-primary: linear-gradient(135deg, var(--primary-blue) 0%, var(--secondary-blue) 100%);
            --gradient-accent: linear-gradient(135deg, var(--accent-gold) 0%, #f4d03f 100%);
            --gradient-light: linear-gradient(135deg, var(--light-beige) 0%, #f8f9fa 100%);
            
            /* Effets */
            --transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            --box-shadow: 0 10px 30px rgba(26, 95, 122, 0.1);
            --box-shadow-hover: 0 20px 50px rgba(26, 95, 122, 0.2);
            --border-radius: 16px;
            --border-radius-lg: 24px;
            --border-radius-xl: 32px;
        }

        /* ===== RESET & BASE STYLES ===== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: var(--text-dark);
            background-color: var(--white);
            overflow-x: hidden;
        }

        /* ===== ANIMATIONS ===== */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        @keyframes float {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-10px);
            }
        }

        @keyframes rotate3D {
            0% {
                transform: perspective(1000px) rotateY(0deg) rotateX(5deg);
            }
            100% {
                transform: perspective(1000px) rotateY(360deg) rotateX(5deg);
            }
        }

        /* ===== HERO SECTION MODIFIÉE ===== */
        .hero-section {
            position: relative;
            min-height: 95vh;
            display: flex;
            align-items: center;
            overflow: hidden;
            margin-top: -80px;
            padding-top: 80px;
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--secondary-blue) 100%);
        }

        .hero-background {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
        }

        .background-image {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            opacity: 0.4;
        }

        .hero-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(
                135deg,
                rgba(26, 95, 122, 0.6) 0%,
                rgba(212, 175, 55, 0.3) 50%,
                rgba(44, 62, 80, 0.5) 100%
            );
            z-index: 2;
        }

        .hero-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            align-items: center;
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 40px;
            width: 100%;
            position: relative;
            z-index: 10;
        }

        .hero-content {
            padding: 70px 50px;
            position: relative;
            z-index: 10;
            display: flex;
            flex-direction: column;
            height: 100%;
            justify-content: center;
        }

        .hero-content__title {
            font-size: clamp(2.2rem, 5vw, 3.2rem);
            font-weight: 700;
            line-height: 1.2;
            margin-bottom: 30px;
            color: var(--white);
            animation: slideInLeft 1s ease-out;
            text-shadow: 2px 2px 8px rgba(0,0,0,0.8);
        }

        .hero-content__description {
            font-size: clamp(1.1rem, 2.2vw, 1.4rem);
            line-height: 1.6;
            margin-bottom: 40px;
            color: var(--white);
            animation: slideInLeft 1s ease-out 0.2s both;
            text-shadow: 1px 1px 4px rgba(0,0,0,0.6);
            opacity: 0.95;
        }

        .hero-content__buttons {
            display: flex;
            gap: 20px;
            animation: slideInLeft 1s ease-out 0.4s both;
            justify-content: flex-start;
            align-items: center;
            flex-wrap: nowrap;
            width: 100%;
            margin-top: 0;
            padding-top: 25px;
        }

        .hero-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            padding: 18px 36px;
            border-radius: var(--border-radius);
            text-decoration: none;
            font-weight: 600;
            font-size: 1rem;
            transition: var(--transition);
            border: 2px solid transparent;
            cursor: pointer;
            min-width: 220px;
            position: relative;
            overflow: hidden;
            flex-shrink: 0;
        }

        .hero-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: left 0.6s;
        }

        .hero-btn:hover::before {
            left: 100%;
        }

        .hero-btn--primary {
            background: var(--gradient-primary);
            color: var(--white);
            box-shadow: var(--box-shadow);
        }

        .hero-btn--primary:hover {
            transform: translateY(-3px);
            box-shadow: var(--box-shadow-hover);
            background: var(--secondary-blue);
        }

        .hero-btn--secondary {
            background: transparent;
            color: var(--white);
            border: 2px solid rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
        }

        .hero-btn--secondary:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-3px);
            border-color: rgba(255, 255, 255, 0.9);
        }

        .hero-visual {
            flex: 1;
            position: relative;
            height: 550px;
            display: flex;
            align-items: center;
            justify-content: center;
            perspective: 1000px;
        }

        .hero-visual__container {
            position: relative;
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .hero-visual__3d-container {
            position: relative;
            width: 450px;
            height: 450px;
            transform-style: preserve-3d;
            animation: rotate3D 20s infinite linear;
        }

        .hero-visual__3d-element {
            position: absolute;
            width: 100%;
            height: 100%;
            border-radius: var(--border-radius-lg);
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border: 3px solid var(--accent-gold);
            box-shadow: 
                0 25px 50px rgba(0, 0, 0, 0.4),
                inset 0 0 80px rgba(212, 175, 55, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            transition: var(--transition);
            transform: rotateY(0deg) translateZ(0);
        }

        .hero-visual__image {
            width: 90%;
            height: 90%;
            object-fit: contain;
            border-radius: var(--border-radius);
            filter: drop-shadow(0 10px 20px rgba(0,0,0,0.3));
        }

        .floating-badge {
            position: absolute;
            top: 35px;
            right: 35px;
            background: var(--gradient-accent);
            padding: 16px 28px;
            border-radius: 50px;
            color: var(--accent-dark);
            font-weight: 700;
            font-size: 1rem;
            box-shadow: var(--box-shadow-hover);
            animation: float 3s ease-in-out infinite;
            z-index: 20;
            backdrop-filter: blur(10px);
            border: 2px solid rgba(212, 175, 55, 0.4);
        }

        .hero-scroll-indicator {
            position: absolute;
            bottom: 40px;
            left: 50%;
            transform: translateX(-50%);
            color: var(--white);
            text-decoration: none;
            z-index: 10;
            animation: fadeInUp 1s ease-out 0.8s both;
            text-align: center;
        }

        .hero-scroll-indicator__text {
            font-size: 1rem;
            margin-bottom: 12px;
            font-weight: 600;
            text-shadow: 1px 1px 3px rgba(0,0,0,0.5);
            opacity: 0.9;
        }

        .hero-scroll-indicator__icon {
            animation: float 2s ease-in-out infinite;
            font-size: 1.5rem;
            color: var(--accent-gold);
            filter: drop-shadow(1px 1px 2px rgba(0,0,0,0.5));
        }

        /* ===== SECTION ÉTUDES ===== */
        .etudes-section {
            padding: 100px 0;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            position: relative;
            overflow: hidden;
        }

        .etudes-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(circle at 20% 80%, rgba(26, 95, 122, 0.05) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(212, 175, 55, 0.05) 0%, transparent 50%);
            z-index: 1;
        }

        .etudes-title {
            text-align: center;
            font-size: clamp(2rem, 4vw, 3rem);
            font-weight: 800;
            margin-bottom: 70px;
            color: var(--accent-dark);
            position: relative;
            z-index: 2;
            width: 100%;
        }

        .etudes-title::before {
            content: '';
            position: absolute;
            bottom: -15px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: var(--gradient-accent);
            border-radius: 2px;
        }

        .etudes-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 40px;
            max-width: 1300px;
            margin: 0 auto;
            padding: 0 30px;
            position: relative;
            z-index: 2;
        }

        .etude-card {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.9) 0%, rgba(255, 255, 255, 0.7) 100%);
            padding: 40px 30px;
            border-radius: var(--border-radius-xl);
            text-align: center;
            box-shadow: 
                0 15px 35px rgba(26, 95, 122, 0.08),
                0 5px 15px rgba(0, 0, 0, 0.06);
            transition: var(--transition);
            border: 1px solid rgba(255, 255, 255, 0.8);
            position: relative;
            overflow: hidden;
            backdrop-filter: blur(10px);
        }

        .etude-card:hover {
            transform: translateY(-12px) scale(1.02);
            box-shadow: 
                0 25px 50px rgba(26, 95, 122, 0.15),
                0 15px 30px rgba(0, 0, 0, 0.1);
        }

        .etude-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 25px;
            background: var(--gradient-primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            z-index: 3;
            box-shadow: 0 10px 25px rgba(26, 95, 122, 0.2);
            transition: var(--transition);
        }

        .etude-card:hover .etude-icon {
            transform: scale(1.1) rotate(5deg);
            background: var(--gradient-accent);
        }

        .etude-icon i {
            font-size: 2rem;
            color: var(--white);
            transition: var(--transition);
        }

        .etude-card:hover .etude-icon i {
            transform: scale(1.1);
            color: var(--accent-dark);
        }

        .etude-card h3 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 20px;
            color: var(--accent-dark);
            position: relative;
            z-index: 3;
        }

        .etude-card p {
            color: var(--text-light);
            line-height: 1.7;
            font-size: 1rem;
            margin-bottom: 25px;
            position: relative;
            z-index: 3;
        }

        .etude-btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 12px 24px;
            background: var(--gradient-primary);
            color: var(--white);
            text-decoration: none;
            border-radius: var(--border-radius);
            font-weight: 600;
            font-size: 0.9rem;
            transition: var(--transition);
            border: none;
            cursor: pointer;
        }

        .etude-btn:hover {
            transform: translateY(-3px);
            box-shadow: var(--box-shadow-hover);
            background: var(--secondary-blue);
        }

        /* ===== SECTION PAIEMENT ===== */
        .payment-section {
            padding: 80px 0;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            position: relative;
            overflow: hidden;
        }

        .payment-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(circle at 20% 80%, rgba(26, 95, 122, 0.05) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(212, 175, 55, 0.05) 0%, transparent 50%);
            z-index: 1;
        }

        .payment-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 30px;
            position: relative;
            z-index: 2;
        }

        .payment-title {
            text-align: center;
            font-size: clamp(1.8rem, 3.5vw, 2.5rem);
            font-weight: 700;
            margin-bottom: 50px;
            color: var(--accent-dark);
            position: relative;
        }

        .payment-title::after {
            content: '';
            position: absolute;
            bottom: -12px;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 3px;
            background: var(--gradient-accent);
            border-radius: 2px;
        }

        .payment-steps {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-bottom: 50px;
        }

        .payment-step {
            background: var(--white);
            padding: 30px 25px;
            border-radius: var(--border-radius-lg);
            text-align: center;
            box-shadow: var(--box-shadow);
            transition: var(--transition);
            border-left: 4px solid var(--primary-blue);
        }

        .payment-step:hover {
            transform: translateY(-5px);
            box-shadow: var(--box-shadow-hover);
        }

        .step-icon {
            width: 60px;
            height: 60px;
            margin: 0 auto 20px;
            background: var(--gradient-primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-size: 1.5rem;
        }

        .payment-step h3 {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 15px;
            color: var(--accent-dark);
        }

        .payment-step p {
            color: var(--text-light);
            line-height: 1.6;
            font-size: 0.9rem;
        }

        .payment-cta {
            text-align: center;
            background: var(--white);
            padding: 40px;
            border-radius: var(--border-radius-lg);
            box-shadow: var(--box-shadow);
            max-width: 600px;
            margin: 0 auto;
        }

        .payment-cta h3 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 15px;
            color: var(--accent-dark);
        }

        .payment-cta p {
            color: var(--text-light);
            margin-bottom: 25px;
            line-height: 1.6;
        }

        .payment-btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 15px 30px;
            background: var(--gradient-accent);
            color: var(--accent-dark);
            text-decoration: none;
            border-radius: var(--border-radius);
            font-weight: 700;
            font-size: 1rem;
            transition: var(--transition);
            border: none;
            cursor: pointer;
            box-shadow: var(--box-shadow);
        }

        .payment-btn:hover {
            transform: translateY(-3px);
            box-shadow: var(--box-shadow-hover);
        }

        /* ===== SECTIONS EXISTANTES ===== */
        .slogan-section {
            padding: 100px 0;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            position: relative;
            overflow: hidden;
        }

        .slogan-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(circle at 20% 80%, rgba(26, 95, 122, 0.05) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(212, 175, 55, 0.05) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, rgba(61, 139, 182, 0.03) 0%, transparent 50%);
            z-index: 1;
        }

        .slogan-title {
            text-align: center;
            font-size: clamp(2rem, 4vw, 3rem);
            font-weight: 800;
            margin-bottom: 70px;
            color: var(--accent-dark);
            position: relative;
            z-index: 2;
            width: 100%;
        }

        .slogan-title::before {
            content: '';
            position: absolute;
            bottom: -15px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: var(--gradient-accent);
            border-radius: 2px;
        }

        .slogan-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 40px;
            max-width: 1300px;
            margin: 0 auto;
            padding: 0 30px;
            position: relative;
            z-index: 2;
        }

        .slogan-box {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.9) 0%, rgba(255, 255, 255, 0.7) 100%);
            padding: 45px 30px;
            border-radius: var(--border-radius-xl);
            text-align: center;
            box-shadow: 
                0 15px 35px rgba(26, 95, 122, 0.08),
                0 5px 15px rgba(0, 0, 0, 0.06);
            transition: var(--transition);
            border: 1px solid rgba(255, 255, 255, 0.8);
            position: relative;
            overflow: hidden;
            backdrop-filter: blur(10px);
        }

        .slogan-box:hover {
            transform: translateY(-12px) scale(1.02);
            box-shadow: 
                0 25px 50px rgba(26, 95, 122, 0.15),
                0 15px 30px rgba(0, 0, 0, 0.1);
        }

        .slogan-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 25px;
            background: var(--gradient-primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            z-index: 3;
            box-shadow: 0 10px 25px rgba(26, 95, 122, 0.2);
            transition: var(--transition);
        }

        .slogan-box:hover .slogan-icon {
            transform: scale(1.1) rotate(5deg);
            background: var(--gradient-accent);
        }

        .slogan-icon i {
            font-size: 2rem;
            color: var(--white);
            transition: var(--transition);
        }

        .slogan-box:hover .slogan-icon i {
            transform: scale(1.1);
            color: var(--accent-dark);
        }

        .slogan-box h3 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 20px;
            color: var(--accent-dark);
            position: relative;
            z-index: 3;
        }

        .slogan-box p {
            color: var(--text-light);
            line-height: 1.7;
            font-size: 1rem;
            position: relative;
            z-index: 3;
        }

        .stats-section {
            padding: 100px 0;
            background: linear-gradient(135deg, var(--primary-blue) 0%, #2c6b8a 100%);
            position: relative;
            overflow: hidden;
        }

        .stats-title {
            text-align: center;
            font-size: clamp(2rem, 4vw, 3rem);
            font-weight: 800;
            margin-bottom: 70px;
            color: var(--white);
            position: relative;
            z-index: 2;
            width: 100%;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 35px;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 30px;
            position: relative;
            z-index: 2;
        }

        .stat-box {
            text-align: center;
            color: var(--white);
            padding: 40px 25px;
            background: linear-gradient(135deg, 
                rgba(255, 255, 255, 0.12) 0%, 
                rgba(255, 255, 255, 0.08) 100%);
            border-radius: var(--border-radius-xl);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.15);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .stat-box:hover {
            transform: translateY(-10px) scale(1.03);
            background: linear-gradient(135deg, 
                rgba(255, 255, 255, 0.18) 0%, 
                rgba(255, 255, 255, 0.12) 100%);
            box-shadow: 
                0 20px 40px rgba(0, 0, 0, 0.2),
                0 0 0 1px rgba(255, 255, 255, 0.1);
        }

        .stat-number {
            display: block;
            font-size: clamp(2.5rem, 5vw, 3.5rem);
            font-weight: 800;
            margin-bottom: 12px;
            color: var(--accent-gold);
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
            position: relative;
            z-index: 2;
            transition: var(--transition);
        }

        .stat-box:hover .stat-number {
            transform: scale(1.1);
            color: #f4d03f;
        }

        .stat-label {
            font-size: 1.1rem;
            font-weight: 600;
            opacity: 0.9;
            text-align: center;
            position: relative;
            z-index: 2;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.3);
        }

        /* ===== RESPONSIVE DESIGN ===== */
        @media (max-width: 1200px) {
            .hero-container {
                gap: 50px;
            }
            
            .hero-visual__3d-container {
                width: 420px;
                height: 420px;
            }
        }

        @media (max-width: 992px) {
            .hero-container {
                grid-template-columns: 1fr;
                text-align: center;
                gap: 40px;
            }

            .hero-content {
                padding: 60px 40px;
            }

            .hero-visual {
                height: 450px;
                margin-top: 20px;
            }

            .hero-visual__3d-container {
                width: 380px;
                height: 380px;
            }

            .hero-content__buttons {
                justify-content: center;
            }
            
            .floating-badge {
                position: relative;
                top: auto;
                right: auto;
                margin: 25px auto;
                display: inline-block;
            }

            .payment-steps {
                grid-template-columns: 1fr;
                max-width: 500px;
                margin-left: auto;
                margin-right: auto;
            }

            .etudes-section,
            .slogan-section,
            .stats-section {
                padding: 80px 0;
            }
            
            .etudes-container,
            .slogan-container {
                grid-template-columns: 1fr;
                max-width: 600px;
                gap: 25px;
            }
            
            .stats-container {
                grid-template-columns: repeat(2, 1fr);
                gap: 25px;
            }
        }

        @media (max-width: 768px) {
            .hero-section {
                min-height: 90vh;
                margin-top: -60px;
                padding-top: 60px;
            }

            .hero-content__buttons {
                flex-direction: row;
                gap: 18px;
                flex-wrap: wrap;
                justify-content: center;
            }
            
            .hero-btn {
                min-width: 200px;
                padding: 16px 32px;
                font-size: 0.95rem;
            }

            .hero-visual__3d-container {
                width: 340px;
                height: 340px;
            }

            .payment-section,
            .etudes-section {
                padding: 60px 0;
            }
            
            .payment-cta {
                padding: 30px 20px;
            }

            .slogan-section,
            .stats-section {
                padding: 60px 0;
            }
            
            .etudes-title,
            .slogan-title,
            .stats-title {
                font-size: 2rem;
                margin-bottom: 50px;
            }
        }

        @media (max-width: 576px) {
            .hero-content {
                padding: 40px 25px;
            }

            .hero-content__title {
                font-size: 1.8rem;
            }

            .hero-content__description {
                font-size: 1.05rem;
            }

            .hero-visual {
                height: 320px;
            }

            .hero-visual__3d-container {
                width: 280px;
                height: 280px;
            }

            .hero-content__buttons {
                flex-direction: column;
                gap: 15px;
            }
            
            .hero-btn {
                width: 100%;
                max-width: 280px;
            }

            .payment-container,
            .etudes-container {
                padding: 0 20px;
            }
            
            .payment-step,
            .etude-card {
                padding: 25px 20px;
            }

            .slogan-container {
                padding: 0 15px;
                gap: 15px;
            }
            
            .slogan-box {
                padding: 25px 15px;
            }
            
            .stats-container {
                grid-template-columns: 1fr;
                max-width: 400px;
                gap: 20px;
            }
        }
    </style>
</head>
<body>
    <!-- Hero Section modifiée -->
    <section class="hero-section" role="banner" aria-label="Section principale">
        <!-- Image de fond sans effet noir et blanc -->
        <div class="hero-background" aria-hidden="true">
            <img src="images/hero.png" alt="Professionnels travaillant à l'étranger" class="background-image">
        </div>

        <!-- Overlay coloré pour contraste -->
        <div class="hero-overlay"></div>

        <div class="hero-container">
            <!-- Partie Contenu -->
            <div class="hero-content">
                <h1 class="hero-content__title">
                    Babylone service votre partenaire de confiance pour étudier, voyager et travailler à l'étranger.
                </h1>
                
                <p class="hero-content__description">
                    Accompagner les étudiants, professionnels et voyageurs dans toutes leurs démarches administratives de visa, admission et mobilité internationale.
                </p>
                
                <div class="hero-content__buttons">
                    <a href="#count" class="hero-btn hero-btn--primary">
                        <i class="fas fa-map-marker-alt"></i>
                        Choisir votre destination
                    </a>
                    <a href="../publics/services.php" class="hero-btn hero-btn--secondary">
                        <i class="fas fa-rocket"></i>
                        Découvrir nos services
                    </a>
                </div>
            </div>

            <!-- Partie Visuelle avec logo 3D tournant -->
            <div class="hero-visual">
                <div class="hero-visual__container">
                    <div class="hero-visual__3d-container">
                        <div class="hero-visual__3d-element">
                            <img src="images/logo.png" alt="Babylone Service - Services internationaux" class="hero-visual__image">
                        </div>
                    </div>
                    
                    <div class="floating-badge">
                        <i class="fas fa-graduation-cap"></i>
                        +1000 étudiants accompagnés
                    </div>
                </div>
            </div>
        </div>

        <a href="#count" class="hero-scroll-indicator">
            <div class="hero-scroll-indicator__text">Explorer nos destinations</div>
            <div class="hero-scroll-indicator__icon">
                <i class="fas fa-chevron-down"></i>
            </div>
        </a>
    </section>
<!-- Section Réalisations (Slider) -->
<?php include __DIR__ . '/realisations.php'; ?>
    <!-- Section Destinations -->
    <?php include __DIR__ . '/destinations.php'; ?>

    <!-- Section Paiement -->
    <section class="payment-section" id="paiement">
        <div class="payment-container">
            <h2 class="payment-title">Finalisez Votre Demande</h2>
            
            <div class="payment-steps">
                <div class="payment-step">
                    <div class="step-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h3>Demande Acceptée</h3>
                    <p>Votre demande a été pré-approuvée par notre équipe. Vous êtes maintenant éligible pour passer à l'étape de paiement.</p>
                </div>
                
                <div class="payment-step">
                    <div class="step-icon">
                        <i class="fas fa-credit-card"></i>
                    </div>
                    <h3>Paiement Sécurisé</h3>
                    <p>Effectuez votre paiement en toute sécurité via notre plateforme certifiée. Multiple moyens de paiement acceptés.</p>
                </div>
                
                <div class="payment-step">
                    <div class="step-icon">
                        <i class="fas fa-cogs"></i>
                    </div>
                    <h3>Traitement Garanti</h3>
                    <p>Une fois le paiement confirmé, votre dossier entre immédiatement en traitement par notre équipe spécialisée.</p>
                </div>
            </div>
            
            <div class="payment-cta">
                <h3>Prêt à finaliser votre projet ?</h3>
                <p>Votre demande a été acceptée. Procédez au paiement pour démarrer officiellement le traitement de votre dossier.</p>
                <a href="paiement.php" class="payment-btn">
                    <i class="fas fa-lock"></i>
                    Procéder au Paiement Sécurisé
                </a>
            </div>
        </div>
    </section>

    <!-- Section Études -->
    <?php include __DIR__ . '/etudes.php'; ?>

    <!-- Section Immigration Canada (CSS dans le fichier include) -->
    <?php include __DIR__ . '/immigration-canada-section.php'; ?>

    <!-- Section Statistiques -->
    <section class="stats-section">
        <h2 class="stats-title">Babylone Service en chiffres</h2>
        <div class="stats-container">
            <div class="stat-box">
                <span class="stat-number" data-count="+1000">0</span>
                <span class="stat-label">Clients satisfaits</span>
            </div>
            <div class="stat-box">
                <span class="stat-number" data-count="20">0</span>
                <span class="stat-label">Pays disponibles</span>
            </div>
            <div class="stat-box">
                <span class="stat-number" data-count="+85">0</span>
                <span class="stat-label">% de réussite</span>
            </div>
            <div class="stat-box">
                <span class="stat-number" data-count="+5">0</span>
                <span class="stat-label">Années d'expérience</span>
            </div>
        </div>
    </section>

    <script>
        // Animation des statistiques
        document.addEventListener('DOMContentLoaded', function() {
            function animateStats() {
                const statNumbers = document.querySelectorAll('.stat-number[data-count]');
                
                statNumbers.forEach(stat => {
                    const target = parseInt(stat.getAttribute('data-count'));
                    const duration = 2000;
                    const step = target / (duration / 16);
                    let current = 0;
                    
                    const timer = setInterval(() => {
                        current += step;
                        if (current >= target) {
                            current = target;
                            clearInterval(timer);
                        }
                        stat.textContent = Math.floor(current) + (stat.getAttribute('data-count') === '85' ? '%' : '');
                    }, 16);
                });
            }
            
            const statsSection = document.querySelector('.stats-section');
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        animateStats();
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.5 });
            
            if (statsSection) {
                observer.observe(statsSection);
            }
        });
    </script>

</body>
</html>
<?php include __DIR__ . '/../includes/footer.php'; ?>