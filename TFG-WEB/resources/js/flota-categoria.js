// Generador de páginas de flota
class FlotaGenerator {
  constructor() {
    this.init();
  }

  init() {
    this.setupAnimations();
  }

  // Animaciones de entrada
  setupAnimations() {
    const cards = document.querySelectorAll(".car-card");

    cards.forEach((card, index) => {
      card.style.opacity = "0";
      card.style.transform = "translateY(30px)";

      setTimeout(() => {
        card.style.transition = "opacity 0.6s ease, transform 0.6s ease";
        card.style.opacity = "1";
        card.style.transform = "translateY(0)";
      }, index * 200);
    });
  }
}

// Función de redirección a reservas con los botones HTML
function reservar(modelo) {
  window.location.href = `./reservas.php?modelo=${encodeURIComponent(modelo)}`;
}

// Cargar animaciones antes que la página
document.addEventListener("DOMContentLoaded", () => {
  new FlotaGenerator();
});