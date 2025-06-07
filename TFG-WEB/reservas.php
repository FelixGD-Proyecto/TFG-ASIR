<?php
require_once 'database.php';

$mensaje = '';
$error = '';

// Procesar formulario
if ($_POST) {
    $datos_formulario = [
        'nombre' => $_POST['nombre'],
        'email' => $_POST['email'],
        'telefono' => $_POST['telefono'],
        'categoria' => $_POST['categoria'],
        'modelo' => $_POST['modelo'],
        'dias' => (int)$_POST['dias'],
        'precio_dia' => (float)$_POST['precio_dia'],
        'fecha_inicio' => $_POST['fecha_inicio'],
        'hora_inicio' => $_POST['hora_inicio'],
        'fecha_fin' => $_POST['fecha_fin'],
        'hora_fin' => $_POST['hora_fin']
    ];
    
    try {
        if (procesar_reserva($pdo, $datos_formulario)) {
            $mensaje = "✅ Reserva guardada correctamente";
        }
    } catch (Exception $e) {
        $error = "❌ Error: " . $e->getMessage();
        error_log("Error en reserva: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="./resources/css/general.css" />
    <link rel="stylesheet" href="./resources/css/reservas.css" />
    <link rel="stylesheet" href="./resources/css/campos-fecha-hora.css" />
    <title><?php echo $CONFIG['app']['name']; ?> - Reservas</title>
</head>

<body>
    <!-- CABECERA -->
    <header>
        <div class="header-top">
            <div class="logo-container">
                <h1 class="logo">ALQUILER DE VEHICULOS</h1>
            </div>
            <nav class="nav-alto">
                <ul>
                    <li><a href="index.html">INICIO</a></li>
                    <li><a href="calculador.html">TARIFAS</a></li>
                    <li><a href="database.php?status=1">ESTADO</a></li>
                    <li><a href="gestor-reservas.php">GESTOR</a></li>
                </ul>
            </nav>
        </div>
        <div class="nlogo">
            <img src="./resources/img/header/logo-cabecera.jpg" alt="" />
        </div>
        <div class="header-bottom">
            <nav class="nav-bajo">
                <ul>
                    <li><a href="reservas.php" class="btn-reservas">RESERVAS</a></li>
                    <li><a href="flota.html" class="btn-flota">FLOTA</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <div class="formulario-reservas">
            <h1>🚗 Reservar Vehículo</h1>
            
            <?php if($mensaje): ?>
                <div class="mensaje exito"><?php echo $mensaje; ?></div>
            <?php endif; ?>
            
            <?php if($error): ?>
                <div class="mensaje error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" onsubmit="return validarFormulario()">
                <div class="form-group">
                    <label>Nombre:</label>
                    <input type="text" name="nombre" id="nombre" required>
                </div>
                
                <div class="form-group">
                    <label>Email:</label>
                    <input type="email" name="email" id="email" required>
                </div>
                
                <div class="form-group">
                    <label>Teléfono:</label>
                    <input type="tel" name="telefono" id="telefono" required>
                </div>
                
                <div class="form-group">
                    <label>Categoría:</label>
                    <select name="categoria" id="categoria" required>
                        <?php echo generar_opciones_categoria($vehiculos); ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Modelo:</label>
                    <select name="modelo" id="modelo" required>
                        <option value="">Selecciona modelo</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Días:</label>
                    <input type="number" name="dias" id="dias" min="1" required>
                </div>
                
                <div class="form-group">
                    <label>Precio por día:</label>
                    <input type="number" name="precio_dia" id="precio_dia" step="0.01" readonly>
                </div>
                
                <div class="form-group">
                    <label>Fecha inicio:</label>
                    <input type="date" name="fecha_inicio" id="fecha_inicio" required>
                </div>
                
                <div class="form-group">
                    <label>Hora inicio:</label>
                    <input type="time" name="hora_inicio" id="hora_inicio" required>
                </div>
                
                <div class="form-group">
                    <label>Fecha fin:</label>
                    <input type="date" name="fecha_fin" id="fecha_fin" required>
                </div>
                
                <div class="form-group">
                    <label>Hora fin:</label>
                    <input type="time" name="hora_fin" id="hora_fin" required>
                </div>
                
                <div class="precio" id="total" style="display:none;">
                    <strong>TOTAL: €<span id="precio_total">0</span></strong>
                </div>
                
                <button type="submit">RESERVAR</button>
            </form>
        </div>
    </main>

    <!-- PIE -->
    <footer>
        <section class="zona-redes">
            <ul>
                <li class="redes"><a href="#">Facebook</a></li>
                <li class="redes"><a href="#">Instagram</a></li>
                <li class="redes"><a href="#">X/Twitter</a></li>
            </ul>
        </section>
        <section class="zona-contacto">
            <ul>
                <li><a href="#">📞 Teléfono: +34 900 000 000</a></li>
                <li><a href="#">📍 Dirección: Calle Ejemplo, 123, Madrid</a></li>
                <li><a href="#">📧 Email: info@gdrmotion.com</a></li>
            </ul>
        </section>
        <div class="zona-links">
            <ul>
                <li><a href="index.html">Inicio</a></li>
                <li><a href="#">Términos y Condiciones</a></li>
                <li><a href="#">Política de Privacidad</a></li>
                <li><a href="database.php?status=1">Estado del Sistema</a></li>
            </ul>
        </div>
        <div class="zona-copy">
            <p class="copy">Copyright © 2024 <?php echo $CONFIG['app']['name']; ?></p>
        </div>
    </footer>

    <!-- SCRIPTS -->
    <script>
        const vehiculosData = <?php echo json_encode($vehiculos); ?>;
    </script>
    <script src="./resources/js/calculo.js"></script>
    <script src="./resources/js/enlaces.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            window.sistemaCalculo = new SistemaCalculoUnificado(true);
        });
    </script>
</body>
</html>