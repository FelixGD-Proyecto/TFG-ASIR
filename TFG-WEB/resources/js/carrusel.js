// Carrusel
class CarruselOptimizado {
  constructor() {
    this.currentSlide = 0;
    this.slides = document.querySelectorAll(".carousel-slide");
    this.totalSlides = this.slides.length;
    this.track = document.getElementById("carouselTrack");
    this.indicatorsContainer = document.getElementById("indicators");
    this.progressBar = document.getElementById("progress");

    this.autoSlideInterval = null;
    this.progressInterval = null;

    // Configuración intervalos
    this.slideTime = 7000;
    this.progressUpdateInterval = 100;
    this.swipeThreshold = 50;

    this.init();
  }

  init() {
    if (this.totalSlides === 0) {
      console.error("No se encontraron slides en el carrusel");
      return;
    }

    this.createIndicators();
    this.updateCarousel();
    this.setupEventListeners();
    this.startAutoSlide();
  }

  createIndicators() {
    this.indicatorsContainer.innerHTML = "";

    for (let i = 0; i < this.totalSlides; i++) {
      const indicator = document.createElement("div");
      indicator.className = `indicator${i === 0 ? " active" : ""}`;
      indicator.addEventListener("click", () => this.goToSlide(i));
      this.indicatorsContainer.appendChild(indicator);
    }
  }

  updateCarousel() {
    const translateX = -this.currentSlide * 100;
    this.track.style.transform = `translateX(${translateX}%)`;

    // Actualizar indicadores y diapositivas(slides)
    document.querySelectorAll(".indicator").forEach((indicator, index) => {
      indicator.classList.toggle("active", index === this.currentSlide);
    });

    this.slides.forEach((slide, index) => {
      slide.classList.toggle("active", index === this.currentSlide);
    });
  }

  goToSlide(slideIndex) {
    this.currentSlide = slideIndex;
    this.updateCarousel();
    this.resetAutoSlide();
  }

  nextSlide() {
    this.currentSlide = (this.currentSlide + 1) % this.totalSlides;
    this.updateCarousel();
    this.resetAutoSlide();
  }

  previousSlide() {
    this.currentSlide =
      (this.currentSlide - 1 + this.totalSlides) % this.totalSlides;
    this.updateCarousel();
    this.resetAutoSlide();
  }

  updateProgress() {
    let progress = 0;

    this.progressInterval = setInterval(() => {
      progress += 100 / (this.slideTime / this.progressUpdateInterval);
      this.progressBar.style.width = progress + "%";

      if (progress >= 100) {
        clearInterval(this.progressInterval);
        this.progressBar.style.width = "0%";
      }
    }, this.progressUpdateInterval);
  }

  startAutoSlide() {
    this.updateProgress();
    this.autoSlideInterval = setInterval(() => {
      this.nextSlide();
    }, this.slideTime);
  }

  resetAutoSlide() {
    clearInterval(this.autoSlideInterval);
    clearInterval(this.progressInterval);
    this.progressBar.style.width = "0%";
    this.startAutoSlide();
  }

  setupEventListeners() {
    const container = document.querySelector(".carousel-container");

    // Pausar al pasar el ratón por encima
    container.addEventListener("mouseenter", () => {
      clearInterval(this.autoSlideInterval);
      clearInterval(this.progressInterval);
    });

    container.addEventListener("mouseleave", () => {
      this.resetAutoSlide();
    });

    // Gestos táctiles
    this.setupTouchGestures(container);

    // Controles de teclado
    this.setupKeyboardControls();

    // Observador de visibilidad
    this.setupVisibilityObserver(container);

    // Redimensionar ventana
    window.addEventListener("resize", () => this.updateCarousel());
  }

  setupTouchGestures(container) {
    let startX = 0;
    let endX = 0;

    container.addEventListener("touchstart", (e) => {
      startX = e.touches[0].clientX;
    });

    container.addEventListener("touchend", (e) => {
      endX = e.changedTouches[0].clientX;
      const difference = startX - endX;

      if (Math.abs(difference) > this.swipeThreshold) {
        if (difference > 0) {
          this.nextSlide();
        } else {
          this.previousSlide();
        }
      }
    });
  }

  setupKeyboardControls() {
    document.addEventListener("keydown", (e) => {
      const actions = {
        ArrowLeft: () => this.previousSlide(),
        ArrowRight: () => this.nextSlide(),
        " ": (e) => {
          e.preventDefault();
          this.autoSlideInterval
            ? this.pauseAutoSlide()
            : this.resetAutoSlide();
        },
        Home: () => this.goToSlide(0),
        End: () => this.goToSlide(this.totalSlides - 1),
      };

      if (actions[e.key]) {
        actions[e.key](e);
      }
    });
  }

  pauseAutoSlide() {
    clearInterval(this.autoSlideInterval);
    clearInterval(this.progressInterval);
    this.autoSlideInterval = null;
  }

  setupVisibilityObserver(container) {
    if ("IntersectionObserver" in window) {
      const observer = new IntersectionObserver(
        (entries) => {
          entries.forEach((entry) => {
            if (entry.isIntersecting) {
              if (!this.autoSlideInterval) {
                this.startAutoSlide();
              }
            } else {
              this.pauseAutoSlide();
            }
          });
        },
        { threshold: 0.5 }
      );

      observer.observe(container);
    }
  }
}

// Funciones globales para los botones del HTML
function nextSlide() {
  if (window.carrusel) window.carrusel.nextSlide();
}

function previousSlide() {
  if (window.carrusel) window.carrusel.previousSlide();
}

// Inicializar carrusel al cargar la página
document.addEventListener("DOMContentLoaded", () => {
  window.carrusel = new CarruselOptimizado();
});
