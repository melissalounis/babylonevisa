</main>
<footer class="footer">
  <div class="footer-container">
    <div class="footer-content">
      <div class="footer-section">
        <div class="footer-logo">
          <img src="/babylone/public/images/logo.jpg" alt="Babylone Service Logo" class="logo-img">
          <h3>Babylone Service</h3>
        </div>
        <p class="footer-description">
          Votre partenaire de confiance pour toutes vos démarches administratives, 
          vos besoins de voyage .
        </p>
        <div class="footer-social">
          <a href="https://www.facebook.com/p/Babylone-Service-100077820729045/" target="_blank" class="social-link">
            <i class="fab fa-facebook-f"></i>
          </a>
          <a href="https://www.instagram.com/babylone_service/" target="_blank" class="social-link">
            <i class="fab fa-instagram"></i>
          </a>
          <a href="https://www.whatsapp.com/?lang=fr_FR" target="_blank" class="social-link">
            <i class="fab fa-whatsapp"></i>
          </a>
          <a href="https://www.google.com/maps/place/BABYLONE+SERVICE/@36.6979625,4.0501907,17z/data=!3m1!4b1!4m6!3m5!1s0x128dc9000548b5bd:0x3da7de0f7d08a0d3!8m2!3d36.6979625!4d4.0527656!16s%2Fg%2F11vpt943d5?entry=ttu&g_ep=EgoyMDI1MDkyMS4wIKXMDSoASAFQAw%3D%3D" target="_blank" class="social-link">
            <i class="fas fa-map-marker-alt"></i>
          </a>
        </div>
      </div>

      <div class="footer-section">
        <h4>Services</h4>
        <ul class="footer-links">
          <li><a href="/babylone/publics/etudes/pays.php">Etudes</a></li>
          <li><a href="/babylone/publics/tourisme/pays.php">Tourismes et affaires</a></li>
          <li><a href="/babylone/publics/immigration/pays.php">Immigration</a></li>
          <li><a href="/babylone/publics/travail/pays.php">Travail</a></li>
        </ul>
      </div>

      <div class="footer-section">
        <h4>Liens Rapides</h4>
        <ul class="footer-links">
          <li><a href="/babylone/public/index.php">Accueil</a></li>
          <li><a href="/babylone/public/apropos.php">À propos</a></li>
          <li><a href="/babylone/public/contact.php">Contactez nous</a></li>
          <li><a href="/babylone/public/client/index.php">Espace Client</a></li>
        </ul>
      </div>

      <div class="footer-section">
        <h4>Coordonnées</h4>
        <div class="contact-info">
          <div class="contact-item">
            <i class="fas fa-phone"></i>
            <span>+213 554 31 00 47 / 026 18 63 42</span>
          </div>
          <div class="contact-item">
            <i class="fas fa-envelope"></i>
            <span>babylone.service15@gmail.com</span>
          </div>
          <div class="contact-item">
            <i class="fas fa-map-marker-alt"></i>
            <span>Deuxieme Portail de l'université Mouloud Mammerie bastos Tizi-Ouzou</span>
          </div>
          <div class="contact-item">
            <i class="fas fa-clock"></i>
            <span>Sam-Jeu: 8h30-17h | Sam: 10h-17h</span>
          </div>
        </div>
      </div>
    </div>

    <div class="footer-bottom">
      <div class="footer-divider"></div>
      <div class="footer-bottom-content">
        <p>&copy; 2025 Babylone Service. Tous droits réservés.</p>
        <div class="footer-legal">
          <a href="#">Politique de confidentialité</a>
          <a href="#">Conditions d'utilisation</a>
          <a href="#">Mentions légales</a>
        </div>
      </div>
    </div>
  </div>
</footer>

<script src="../assets/js/script.js"></script>
</body>
</html>

<style>
/* Footer Styles */
.footer {
  background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
  color: #ecf0f1;
  padding: 60px 0 20px;
  margin-top: 80px;
}

.footer-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 20px;
}

.footer-content {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 40px;
  margin-bottom: 40px;
}

.footer-section h4 {
  color: #3498db;
  margin-bottom: 20px;
  font-size: 1.2rem;
  font-weight: 600;
}

.footer-logo {
  display: flex;
  align-items: center;
  margin-bottom: 20px;
}

.logo-img {
  width: 50px;
  height: 50px;
  border-radius: 8px;
  margin-right: 15px;
  object-fit: cover;
}

.footer-logo h3 {
  color: #3498db;
  font-size: 1.5rem;
  font-weight: 700;
  margin: 0;
}

.footer-description {
  line-height: 1.6;
  margin-bottom: 25px;
  color: #bdc3c7;
}

.footer-social {
  display: flex;
  gap: 15px;
}

.social-link {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 45px;
  height: 45px;
  background: rgba(52, 152, 219, 0.1);
  border: 2px solid #3498db;
  border-radius: 50%;
  color: #3498db;
  text-decoration: none;
  transition: all 0.3s ease;
  font-size: 1.1rem;
}

.social-link:hover {
  background: #3498db;
  color: white;
  transform: translateY(-3px);
  box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
}

.footer-links {
  list-style: none;
  padding: 0;
  margin: 0;
}

.footer-links li {
  margin-bottom: 12px;
}

.footer-links a {
  color: #bdc3c7;
  text-decoration: none;
  transition: color 0.3s ease;
  display: flex;
  align-items: center;
  gap: 8px;
}

.footer-links a:hover {
  color: #3498db;
}

.footer-links a::before {
  content: "→";
  font-size: 0.9rem;
  opacity: 0;
  transition: opacity 0.3s ease;
}

.footer-links a:hover::before {
  opacity: 1;
}

.contact-info {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.contact-item {
  display: flex;
  align-items: center;
  gap: 12px;
  color: #bdc3c7;
}

.contact-item i {
  color: #3498db;
  width: 20px;
  font-size: 1.1rem;
}

.footer-bottom {
  margin-top: 40px;
}

.footer-divider {
  height: 1px;
  background: linear-gradient(90deg, transparent, rgba(52, 152, 219, 0.3), transparent);
  margin-bottom: 30px;
}

.footer-bottom-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 20px;
}

.footer-bottom-content p {
  margin: 0;
  color: #95a5a6;
}

.footer-legal {
  display: flex;
  gap: 25px;
}

.footer-legal a {
  color: #95a5a6;
  text-decoration: none;
  font-size: 0.9rem;
  transition: color 0.3s ease;
}

.footer-legal a:hover {
  color: #3498db;
}

/* Responsive Design */
@media (max-width: 768px) {
  .footer {
    padding: 40px 0 20px;
    margin-top: 60px;
  }
  
  .footer-content {
    grid-template-columns: 1fr;
    gap: 30px;
    text-align: center;
  }
  
  .footer-logo {
    justify-content: center;
  }
  
  .footer-social {
    justify-content: center;
  }
  
  .footer-bottom-content {
    flex-direction: column;
    text-align: center;
  }
  
  .footer-legal {
    flex-direction: column;
    gap: 15px;
  }
  
  .contact-item {
    justify-content: center;
  }
}

@media (max-width: 480px) {
  .footer {
    padding: 30px 0 20px;
  }
  
  .footer-container {
    padding: 0 15px;
  }
  
  .social-link {
    width: 40px;
    height: 40px;
    font-size: 1rem;
  }
  
  .footer-logo h3 {
    font-size: 1.3rem;
  }
}

/* Animation */
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.footer-section {
  animation: fadeInUp 0.6s ease-out;
}

.footer-section:nth-child(1) { animation-delay: 0.1s; }
.footer-section:nth-child(2) { animation-delay: 0.2s; }
.footer-section:nth-child(3) { animation-delay: 0.3s; }
.footer-section:nth-child(4) { animation-delay: 0.4s; }
</style>

<script>
// Animation au défilement pour le footer
document.addEventListener('DOMContentLoaded', function() {
  const footer = document.querySelector('.footer');
  
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.style.opacity = '1';
        entry.target.style.transform = 'translateY(0)';
      }
    });
  }, { threshold: 0.1 });
  
  // Observer chaque section du footer
  document.querySelectorAll('.footer-section').forEach(section => {
    section.style.opacity = '0';
    section.style.transform = 'translateY(20px)';
    section.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
    observer.observe(section);
  });
});
</script>