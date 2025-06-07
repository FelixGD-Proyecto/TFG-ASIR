<?php
// ============================================
// DATABASE.PHP - CONFIGURACIÓN CENTRALIZADA
// ============================================

// Configuración global
$CONFIG = [
    'db' => [
        'host' => 'database',
        'dbname' => 'myapp',
        'username' => 'felix',
        'password' => '4444'
    ],
    'app' => [
        'name' => 'GDR Motion',
        'version' => '1.0',
        'debug' => true
    ],
    'vehiculos' => [
        'Deportivos' => [
            ['BMW M5 Competition 2025', 55],
            ['Nissan GT-R 2017', 50]
        ],
        'Motocicletas' => [
            ['Honda Africa Twin 2022', 25],
            ['Kawasaki Ninja H2R 2024', 65],
            ['Yamaha R1 2022', 40]
        ],
        'Muscle cars' => [
            ['Dodge Challenger 2020', 50],
            ['Ford Mustang 2025', 55],
            ['Hennessey Chevrolet Camaro ZL1 2019', 60]
        ],
        'Pista' => [
            ['Aston Martin Valhalla 2025', 90],
            ['Mercedes AMG GT Black Series 2025', 85],
            ['Porsche 911 GT2 RS 2022', 85]
        ],
        'Super Deportivos' => [
            ['Audi R8 V10 2024', 70],
            ['Chevrolet Corvette Z06 2024', 65],
            ['Ford GT 2005', 80]
        ],
        'Todoterreno' => [
            ['Ford F-150 Raptor 2024', 50],
            ['Jeep Wrangler Rubicon 2019', 40],
            ['Mercedes AMG G 63 2022', 75]
        ]
    ]
];

// Auto-setup de base de datos (simplificado)
function setup_database($config) {
    try {
        // Conectar al servidor
        $dsn = "mysql:host={$config['host']};charset=utf8mb4";
        $pdo = new PDO($dsn, $config['username'], $config['password']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Crear base de datos si no existe
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$config['dbname']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        
        // Conectar a la base de datos específica
        $dsn_db = "mysql:host={$config['host']};dbname={$config['dbname']};charset=utf8mb4";
        $pdo = new PDO($dsn_db, $config['username'], $config['password']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Crear tabla si no existe
        $stmt = $pdo->query("SHOW TABLES LIKE 'reservas'");
        if ($stmt->rowCount() == 0) {
            $sql = "CREATE TABLE `reservas` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `nombre` varchar(100) NOT NULL,
                `email` varchar(150) NOT NULL,
                `telefono` varchar(20) NOT NULL,
                `categoria` varchar(50) NOT NULL,
                `modelo` varchar(100) NOT NULL,
                `dias` int(11) NOT NULL,
                `precio_dia` decimal(10,2) NOT NULL,
                `precio_total` decimal(10,2) NOT NULL,
                `estado` varchar(20) NOT NULL DEFAULT 'Pendiente',
                `fecha_inicio` date NOT NULL,
                `hora_inicio` time NOT NULL,
                `fecha_fin` date NOT NULL,
                `hora_fin` time NOT NULL,
                `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `idx_estado` (`estado`),
                KEY `idx_categoria` (`categoria`),
                KEY `idx_created_at` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            
            $pdo->exec($sql);
        }

        return $pdo;
        
    } catch (PDOException $e) {
        throw new Exception("Error de BD: " . $e->getMessage());
    }
}

// Inicializar conexión
try {
    $pdo = setup_database($CONFIG['db']);
} catch (Exception $e) {
    if ($CONFIG['app']['debug']) {
        die("❌ Error de conexión: " . $e->getMessage());
    } else {
        die("Error de conexión a la base de datos.");
    }
}

// Convertir vehículos a formato compatible con JS
$vehiculos = [];
foreach ($CONFIG['vehiculos'] as $categoria => $modelos) {
    foreach ($modelos as $modelo_data) {
        $vehiculos[$categoria][$modelo_data[0]] = $modelo_data[1];
    }
}

// ============================================
// FUNCIONES ESENCIALES (definidas antes del panel)
// ============================================

function generar_opciones_categoria($vehiculos, $selected = '') {
    $html = '<option value="">Selecciona categoría</option>';
    foreach ($vehiculos as $categoria => $modelos) {
        $selected_attr = ($categoria === $selected) ? ' selected' : '';
        $html .= "<option value=\"{$categoria}\"{$selected_attr}>{$categoria}</option>";
    }
    return $html;
}

function procesar_reserva($pdo, $datos) {
    try {
        $precio_total = $datos['dias'] * $datos['precio_dia'];
        $estado = $datos['estado'] ?? 'Pendiente';

        $sql = "INSERT INTO reservas (nombre, email, telefono, categoria, modelo, dias, precio_dia, precio_total, estado, fecha_inicio, hora_inicio, fecha_fin, hora_fin) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            $datos['nombre'],
            $datos['email'],
            $datos['telefono'],
            $datos['categoria'],
            $datos['modelo'],
            $datos['dias'],
            $datos['precio_dia'],
            $precio_total,
            $estado,
            $datos['fecha_inicio'],
            $datos['hora_inicio'],
            $datos['fecha_fin'],
            $datos['hora_fin']
        ]);
    } catch (Exception $e) {
        throw $e;
    }
}

function obtener_estadisticas($pdo) {
    $sql = "SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN estado = 'Pendiente' THEN 1 ELSE 0 END) as pendientes,
        SUM(CASE WHEN estado = 'Confirmada' THEN 1 ELSE 0 END) as confirmadas,
        SUM(CASE WHEN estado = 'Completada' THEN 1 ELSE 0 END) as completadas,
        SUM(CASE WHEN estado = 'Cancelada' THEN 1 ELSE 0 END) as canceladas,
        SUM(precio_total) as ingresos_total
    FROM reservas";
    return $pdo->query($sql)->fetch(PDO::FETCH_ASSOC);
}

// ============================================
// PANEL DE ESTADO (cuando se accede con ?status=1)
// ============================================

if (isset($_GET['status'])) {
    try {
        $stats = obtener_estadisticas($pdo);
        $ultimas = $pdo->query("SELECT * FROM reservas ORDER BY id DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
        $tablas = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        
        echo "<!DOCTYPE html>
        <html lang='es'>
        <head>
            <meta charset='UTF-8'>
            <title>🔧 Panel de Estado - {$CONFIG['app']['name']}</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
                .panel { background: white; padding: 20px; margin: 20px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
                .status-ok { color: #28a745; }
                .status-error { color: #dc3545; }
                table { width: 100%; border-collapse: collapse; margin: 10px 0; }
                th, td { padding: 8px; text-align: left; border: 1px solid #ddd; }
                th { background-color: #f8f9fa; }
                .btn { padding: 10px 15px; background: #007bff; color: white; text-decoration: none; border-radius: 4px; margin: 5px; }
            </style>
        </head>
        <body>
            <h1>🔧 Panel de Estado - {$CONFIG['app']['name']} v{$CONFIG['app']['version']}</h1>
            
            <div class='panel'>
                <h2>📊 Estadísticas del Sistema</h2>
                <table>
                    <tr><th>Total Reservas</th><td>{$stats['total']}</td></tr>
                    <tr><th>Pendientes</th><td>{$stats['pendientes']}</td></tr>
                    <tr><th>Confirmadas</th><td>{$stats['confirmadas']}</td></tr>
                    <tr><th>Completadas</th><td>{$stats['completadas']}</td></tr>
                    <tr><th>Canceladas</th><td>{$stats['canceladas']}</td></tr>
                    <tr><th>Ingresos Total</th><td>€" . number_format($stats['ingresos_total'], 2) . "</td></tr>
                </table>
            </div>

            <div class='panel'>
                <h2>🗄️ Estado de Base de Datos</h2>
                <table>
                    <tr><th>Host</th><td>{$CONFIG['db']['host']}</td><td><span class='status-ok'>✅ OK</span></td></tr>
                    <tr><th>Base de Datos</th><td>{$CONFIG['db']['dbname']}</td><td><span class='status-ok'>✅ OK</span></td></tr>
                    <tr><th>Tabla 'reservas'</th><td>" . (in_array('reservas', $tablas) ? 'Existe' : 'No existe') . "</td><td><span class='status-ok'>✅ OK</span></td></tr>
                    <tr><th>Total Tablas</th><td>" . count($tablas) . "</td><td><span class='status-ok'>ℹ️ INFO</span></td></tr>
                </table>
            </div>";

        if (!empty($ultimas)) {
            echo "<div class='panel'>
                <h2>📋 Últimas 5 Reservas</h2>
                <table>
                    <tr><th>ID</th><th>Nombre</th><th>Modelo</th><th>Estado</th><th>Total</th><th>Fecha</th></tr>";
            foreach ($ultimas as $reserva) {
                echo "<tr>
                    <td>#{$reserva['id']}</td>
                    <td>" . htmlspecialchars($reserva['nombre']) . "</td>
                    <td>" . htmlspecialchars($reserva['modelo']) . "</td>
                    <td>{$reserva['estado']}</td>
                    <td>€{$reserva['precio_total']}</td>
                    <td>{$reserva['fecha_inicio']}</td>
                </tr>";
            }
            echo "</table></div>";
        }

        echo "<div class='panel'>
                <h2>🔗 Enlaces del Sistema</h2>
                <a href='reservas.php' class='btn'>📝 Reservas</a>
                <a href='gestor-reservas.php' class='btn'>📊 Gestor</a>
                <a href='calculador.html' class='btn'>🧮 Calculadora</a>
                <a href='index.html' class='btn'>🏠 Inicio</a>
            </div>
        </body>
        </html>";
        exit;
        
    } catch (Exception $e) {
        echo "❌ Error en panel de estado: " . $e->getMessage();
        exit;
    }
}

// Si no es panel de estado, este archivo solo provee configuración
// Las variables $pdo, $vehiculos, $CONFIG y funciones están disponibles para includes
?>