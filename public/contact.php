<?php
require_once __DIR__ . '/../config.php';
$page_title = "Contact - Babylone Service";
$sent = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validation des données
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    if (!empty($name) && !empty($email) && !empty($message) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $stmt = $pdo->prepare("INSERT INTO contact_messages(name, email, phone, subject, message) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $email, $phone, $subject, $message]);
        $sent = true;
        
        // Vous pourriez ajouter ici l'envoi d'un email de notification
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="contact-hero">
    <div class="hero-content">
        <h1><i class="fas fa-envelope"></i> Contactez-nous</h1>
        <p>Nous sommes là pour répondre à toutes vos questions</p>
    </div>
    <div class="hero-pattern"></div>
</div>

<div class="contact-container">
    <div class="contact-content">
        <div class="contact-info">
            <div class="info-card">
                <div class="info-icon">
                    <i class="fas fa-phone"></i>
                </div>
                <h3>Téléphone</h3>
                <p>026 18 63 42 </p>
                <p>Disponible du Samedi au Jeudi</p>
                <p>9h - 17h </p>
            </div>

            <div class="info-card">
                <div class="info-icon">
                    <i class="fas fa-envelope"></i>
                </div>
                <h3>Email</h3>
                <p>babylone.service15gmail.com</p>
                <p>Réponse sous 24h</p>
            </div>

            <div class="info-card">
                <div class="info-icon">
                    <i class="fas fa-map-marker-alt"></i>
                </div>
                <h3>Adresse</h3>
                <p>Deuxieme portail de l'université Mouloud Mammerie bastos </p>
                <p>Nouvelle ville, Tizi-Ouzou</p>
                <p>Algérie</p>
            </div>

            <div class="info-card">
                <div class="info-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <h3>Horaires</h3>
                <p>sam: 10h00 - 17h00</p>
                <p>Dim-jeu: 8h30 - 17h00</p>
                <p>ven: Fermé</p>
            </div>
        </div>

        <div class="contact-form-section">
            <div class="form-header">
                <h2>Envoyez-nous un message</h2>
                <p>Remplissez le formulaire ci-dessous et nous vous répondrons dans les plus brefs délais</p>
            </div>

            <?php if ($sent): ?>
            <div class="alert success">
                <i class="fas fa-check-circle"></i>
                <span>Message envoyé avec succès ! Nous vous répondrons très bientôt.</span>
            </div>
            <?php endif; ?>

            <form method="post" class="contact-form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="name">Nom complet <span class="required">*</span></label>
                        <input type="text" id="name" name="name" required placeholder="Votre nom complet">
                    </div>
                    <div class="form-group">
                        <label for="email">Adresse email <span class="required">*</span></label>
                        <input type="email" id="email" name="email" required placeholder="votre@email.com">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="phone">Numéro de téléphone</label>
                        <input type="tel" id="phone" name="phone" placeholder="Votre numéro de téléphone">
                    </div>
                    <div class="form-group">
                        <label for="subject">Sujet <span class="required">*</span></label>
                        <select id="subject" name="subject" required>
                            <option value="">-- Choisir un sujet --</option>
                            <option value="visa">Demande de visa</option>
                            <option value="etudes">Études à l'étranger</option>
                            <option value="tourisme">Tourisme</option>
                            <option value="travail">Travail à l'étranger</option>
                            <option value="autre">Autre demande</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="message">Message <span class="required">*</span></label>
                    <textarea id="message" name="message" rows="6" required placeholder="Décrivez-nous votre demande..."></textarea>
                </div>

                <button type="submit" class="submit-btn">
                    <i class="fas fa-paper-plane"></i>
                    Envoyer le message
                </button>
            </form>
        </div>
    </div>
</div>

<style>
    /* Variables */
    :root {
        --primary-blue: #0056b3;
        --secondary-blue: #0077ff;
        --accent-orange: #ff6b35;
        --light-bg: #f8fafc;
        --dark-text: #2d3748;
        --white: #ffffff;
        --light-gray: #e2e8f0;
        --border-color: #e5e7eb;
        --success-color: #10b981;
        --error-color: #ef4444;
        --transition: all 0.3s ease;
    }

    /* Hero Section */
    .contact-hero {
        background: linear-gradient(135deg, var(--primary-blue) 0%, var(--secondary-blue) 100%);
        color: var(--white);
        padding: 80px 20px;
        text-align: center;
        margin-bottom: 60px;
        position: relative;
        overflow: hidden;
    }

    .hero-content h1 {
        font-size: 2.8rem;
        margin-bottom: 15px;
        font-weight: 700;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
    }

    .hero-content h1 i {
        margin-right: 15px;
    }

    .hero-content p {
        font-size: 1.3rem;
        opacity: 0.95;
        max-width: 600px;
        margin: 0 auto;
        font-weight: 300;
    }

    .hero-pattern {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-image: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM12 86c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm28-65c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm23-11c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-6 60c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm29 22c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zM32 63c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm57-13c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-9-21c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM60 91c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM35 41c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM12 60c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2z' fill='%23ffffff' fill-opacity='0.05' fill-rule='evenodd'/%3E%3C/svg%3E");
        z-index: 1;
    }

    /* Contact Container */
    .contact-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px 80px;
    }

    .contact-content {
        display: grid;
        grid-template-columns: 1fr 1.2fr;
        gap: 50px;
        align-items: start;
    }

    /* Contact Info */
    .contact-info {
        display: grid;
        gap: 25px;
    }

    .info-card {
        background: var(--white);
        padding: 25px;
        border-radius: 12px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        text-align: center;
        transition: var(--transition);
        border: 1px solid var(--light-gray);
    }

    .info-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12);
    }

    .info-icon {
        width: 60px;
        height: 60px;
        background: linear-gradient(135deg, var(--primary-blue), var(--secondary-blue));
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 15px;
    }

    .info-icon i {
        font-size: 24px;
        color: var(--white);
    }

    .info-card h3 {
        font-size: 1.2rem;
        margin-bottom: 10px;
        color: var(--dark-text);
        font-weight: 600;
    }

    .info-card p {
        color: #64748b;
        margin-bottom: 5px;
        font-size: 0.95rem;
    }

    /* Contact Form Section */
    .contact-form-section {
        background: var(--white);
        padding: 40px;
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    }

    .form-header {
        text-align: center;
        margin-bottom: 30px;
    }

    .form-header h2 {
        font-size: 1.8rem;
        color: var(--dark-text);
        margin-bottom: 10px;
        font-weight: 700;
    }

    .form-header p {
        color: #64748b;
        font-size: 1rem;
    }

    /* Alert */
    .alert {
        padding: 15px 20px;
        border-radius: 10px;
        margin-bottom: 25px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .alert.success {
        background: #ecfdf5;
        border: 1px solid #d1fae5;
        color: #065f46;
    }

    .alert i {
        font-size: 1.2rem;
    }

    /* Form Styles */
    .contact-form {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    .form-group {
        display: flex;
        flex-direction: column;
    }

    .form-group label {
        font-weight: 600;
        color: var(--dark-text);
        margin-bottom: 8px;
        font-size: 0.95rem;
    }

    .required {
        color: var(--error-color);
    }

    .form-group input,
    .form-group select,
    .form-group textarea {
        padding: 14px 16px;
        border: 2px solid var(--border-color);
        border-radius: 10px;
        font-size: 1rem;
        transition: var(--transition);
        font-family: inherit;
    }

    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: var(--primary-blue);
        box-shadow: 0 0 0 3px rgba(0, 86, 179, 0.1);
    }

    .form-group textarea {
        resize: vertical;
        min-height: 120px;
    }

    .submit-btn {
        background: linear-gradient(135deg, var(--primary-blue), var(--secondary-blue));
        color: var(--white);
        border: none;
        padding: 16px 32px;
        border-radius: 50px;
        font-size: 1.1rem;
        font-weight: 600;
        cursor: pointer;
        transition: var(--transition);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        margin-top: 10px;
    }

    .submit-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 86, 179, 0.3);
    }

    /* Responsive Design */
    @media (max-width: 968px) {
        .contact-content {
            grid-template-columns: 1fr;
            gap: 40px;
        }
        
        .contact-info {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 768px) {
        .contact-hero {
            padding: 60px 20px;
        }
        
        .hero-content h1 {
            font-size: 2.2rem;
        }
        
        .hero-content p {
            font-size: 1.1rem;
        }
        
        .form-row {
            grid-template-columns: 1fr;
        }
        
        .contact-info {
            grid-template-columns: 1fr;
        }
        
        .contact-form-section {
            padding: 30px 25px;
        }
    }

    @media (max-width: 480px) {
        .contact-hero {
            padding: 50px 15px;
        }
        
        .hero-content h1 {
            font-size: 1.8rem;
        }
        
        .contact-container {
            padding: 0 15px 60px;
        }
        
        .contact-form-section {
            padding: 25px 20px;
        }
        
        .info-card {
            padding: 20px;
        }
    }
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>