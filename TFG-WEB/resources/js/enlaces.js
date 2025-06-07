// Enlaces
const ENLACES = {
  inicio: "./index.html",
  tarifas: "./calculador.html",
  contacto: "/debug_panel.php?debug=1",
  snosotros: "./gestor-reservas.php",
  reservas: "./reservas.php",
  flota: "./flota.html",
  facebook: "enlace a redes",
  instagram: "enlace a redes",
  twitter: "debug_panel.php",
  tycondiciones: "./politicas",
  pdprivacidad: "./politicas",
  super: "./flota-super.html",
  depor: "./flota-depor.html",
  moto: "./flota-moto.html",
  todo: "./flota-todo.html",
  muscle: "./flota-muscle.html",
  pista: "./flota-pista.html",
};

function asignarEnlaces() {
  document.querySelectorAll("[data-enlaces]").forEach((elemento) => {
    const nombreEnlace = elemento.getAttribute("data-enlaces");
    if (ENLACES[nombreEnlace]) {
      elemento.href = ENLACES[nombreEnlace];
    }
  });
}

document.addEventListener("DOMContentLoaded", () => {
  asignarEnlaces();
});