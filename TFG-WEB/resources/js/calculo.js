// Sistema de cálculo
class SistemaCalculoUnificado {
  constructor(isReservaMode = false) {
    this.isReservaMode = isReservaMode;
    this.elementos = this.obtenerElementos();

    // Datos 
    this.vehiculos = {
      Deportivos: [
        { nombre: "BMW M5 Competition 2025", precio: 55 },
        { nombre: "Nissan GT-R 2017", precio: 50 },
      ],
      Motocicletas: [
        { nombre: "Honda Africa Twin 2022", precio: 25 },
        { nombre: "Kawasaki Ninja H2R 2024", precio: 65 },
        { nombre: "Yamaha R1 2022", precio: 40 },
      ],
      "Muscle cars": [
        { nombre: "Dodge Challenger 2020", precio: 50 },
        { nombre: "Ford Mustang 2025", precio: 55 },
        { nombre: "Hennessey Chevrolet Camaro ZL1 2019", precio: 60 },
      ],
      Pista: [
        { nombre: "Aston Martin Valhalla 2025", precio: 90 },
        { nombre: "Mercedes AMG GT Black Series 2025", precio: 85 },
        { nombre: "Porsche 911 GT2 RS 2022", precio: 85 },
      ],
      "Super Deportivos": [
        { nombre: "Audi R8 V10 2024", precio: 70 },
        { nombre: "Chevrolet Corvette Z06 2024", precio: 65 },
        { nombre: "Ford GT 2005", precio: 80 },
      ],
      Todoterreno: [
        { nombre: "Ford F-150 Raptor 2024", precio: 50 },
        { nombre: "Jeep Wrangler Rubicon 2019", precio: 40 },
        { nombre: "Mercedes AMG G 63 2022", precio: 75 },
      ],
    };

    this.init();
  }

  obtenerElementos() {
    return {
      categoria: document.querySelector("#categoria"),
      modelo: document.querySelector("#modelo"),
      precio: document.querySelector("#precio_dia, #precio-hora"),
      total: document.querySelector("#total, #precio-total"),
      fechaInicio: document.querySelector("#fecha_inicio, #fecha-inicio"),
      fechaFin: document.querySelector("#fecha_fin, #fecha-fin"),
      horaInicio: document.querySelector("#hora_inicio, #hora-inicio"),
      horaFin: document.querySelector("#hora_fin, #hora-fin"),
      horas: document.querySelector("#dias, #horas"),
      precioTotal: document.getElementById("precio_total"),
      calcularBtn: document.querySelector(".calculate-btn"),
    };
  }

  init() {
    this.setupEventListeners();
    this.cargarCategorias();
  }

  setupEventListeners() {
    const {
      categoria,
      modelo,
      fechaInicio,
      fechaFin,
      horaInicio,
      horaFin,
      horas,
      calcularBtn,
    } = this.elementos;

    categoria?.addEventListener("change", () => this.actualizarModelos());
    modelo?.addEventListener("change", () => this.actualizarPrecio());

    // Calcular horas automáticamente
    fechaInicio?.addEventListener("change", () => this.calcularHoras());
    fechaFin?.addEventListener("change", () => this.calcularHoras());
    horaInicio?.addEventListener("change", () => this.calcularHoras());
    horaFin?.addEventListener("change", () => this.calcularHoras());

    horas?.addEventListener("input", () => this.calcularTotal());

    // Solo para calculadora
    if (!this.isReservaMode && calcularBtn) {
      calcularBtn.addEventListener("click", () => this.calcular());
    }
  }

  cargarCategorias() {
    if (!this.elementos.categoria) return;

    this.elementos.categoria.innerHTML =
      '<option value="">Selecciona categoría</option>';
    Object.keys(this.vehiculos).forEach((categoria) => {
      const option = document.createElement("option");
      option.value = categoria;
      option.textContent = categoria;
      this.elementos.categoria.appendChild(option);
    });
  }

  actualizarModelos() {
    const categoria = this.elementos.categoria.value;
    this.elementos.modelo.innerHTML =
      '<option value="">Selecciona modelo</option>';

    if (categoria && this.vehiculos[categoria]) {
      this.vehiculos[categoria].forEach((vehiculo) => {
        const option = document.createElement("option");
        option.value = vehiculo.nombre;
        option.textContent = vehiculo.nombre;
        this.elementos.modelo.appendChild(option);
      });
    }

    this.elementos.precio.value = "";
    this.ocultarTotal();
  }

  actualizarPrecio() {
    const categoria = this.elementos.categoria.value;
    const modelo = this.elementos.modelo.value;

    if (categoria && modelo) {
      const vehiculo = this.vehiculos[categoria].find(
        (v) => v.nombre === modelo
      );
      this.elementos.precio.value = vehiculo ? vehiculo.precio : 0;
      this.calcularTotal();
    } else {
      this.elementos.precio.value = "";
      this.ocultarTotal();
    }
  }

  calcularHoras() {
    const fechaInicio = this.elementos.fechaInicio?.value;
    const horaInicio = this.elementos.horaInicio?.value;
    const fechaFin = this.elementos.fechaFin?.value;
    const horaFin = this.elementos.horaFin?.value;

    if (!fechaInicio || !horaInicio || !fechaFin || !horaFin) {
      this.elementos.horas.value = "";
      this.ocultarTotal();
      return;
    }

    const inicio = new Date(`${fechaInicio}T${horaInicio}`);
    const fin = new Date(`${fechaFin}T${horaFin}`);

    if (fin <= inicio) {
      this.elementos.horas.value = "";
      this.ocultarTotal();
      alert("La fecha y hora de fin debe ser posterior a la de inicio");
      return;
    }

    const diferencia = fin - inicio;
    const horas = Math.ceil(diferencia / (1000 * 60 * 60));

    this.elementos.horas.value = horas;
    this.calcularTotal();
  }

  calcularTotal() {
    const horas = parseFloat(this.elementos.horas?.value) || 0;
    const precio = parseFloat(this.elementos.precio?.value) || 0;

    if (horas > 0 && precio > 0) {
      const total = (horas * precio).toFixed(2);
      this.mostrarTotal(total);
    } else {
      this.ocultarTotal();
    }
  }

  mostrarTotal(total) {
    if (this.elementos.precioTotal) {
      this.elementos.precioTotal.textContent = total;
    }

    if (this.elementos.total) {
      this.elementos.total.style.display = "block";

      // Para calculadora (input)
      if (this.elementos.total.tagName === "INPUT") {
        this.elementos.total.value = `${total} €`;
      }
    }
  }

  ocultarTotal() {
    if (this.elementos.total) {
      if (this.elementos.total.tagName === "INPUT") {
        this.elementos.total.value = "";
      } else {
        this.elementos.total.style.display = "none";
      }
    }
  }

  // Solo para calculadora
  calcular() {
    const { categoria, modelo, horas, precio } = this.elementos;

    if (!categoria.value || !modelo.value || !horas.value || !precio.value) {
      alert("Por favor, completa todos los campos");
      return;
    }

    if (parseFloat(horas.value) <= 0) {
      alert("Las horas deben ser mayor a 0");
      return;
    }

    this.calcularTotal();

    if (this.elementos.total.value) {
      alert(
        `Cálculo completado:\n${modelo.value}\n${horas.value} horas a ${precio.value}€/hora\nTotal: ${this.elementos.total.value}`
      );
    }
  }

  // Función para reservar desde botones HTML
  reservar(modelo) {
    const categoriaEncontrada = Object.keys(this.vehiculos).find((cat) =>
      this.vehiculos[cat].some((v) => v.nombre === modelo)
    );

    if (categoriaEncontrada) {
      this.elementos.categoria.value = categoriaEncontrada;
      this.actualizarModelos();

      setTimeout(() => {
        this.elementos.modelo.value = modelo;
        this.actualizarPrecio();
      }, 100);
    }
  }

  // Validación básica (solo para reservas)
  validarFormulario() {
    if (!this.isReservaMode) return true;

    // Validar campos básicos
    const campos = [
      "nombre",
      "email",
      "telefono",
      "categoria",
      "modelo",
      "fecha_inicio",
      "hora_inicio",
      "fecha_fin",
      "hora_fin",
    ];

    for (let campo of campos) {
      const elemento = document.getElementById(campo);
      if (!elemento || !elemento.value.trim()) {
        alert(`Por favor, completa el campo: ${campo}`);
        elemento?.focus();
        return false;
      }
    }

    // Validar email básico
    const email = document.getElementById("email");
    if (email && !email.value.includes("@")) {
      alert("Por favor, ingresa un email válido");
      email.focus();
      return false;
    }

    // Validar horas
    const horas = parseInt(this.elementos.horas.value);
    if (!horas || horas <= 0) {
      alert("Debes seleccionar fechas y horas válidas");
      return false;
    }

    return true;
  }
}

// Funciones globales simples
function reservar(modelo) {
  if (window.sistemaCalculo) {
    window.sistemaCalculo.reservar(modelo);
  }
}

function validarFormulario() {
  return window.sistemaCalculo
    ? window.sistemaCalculo.validarFormulario()
    : false;
}
