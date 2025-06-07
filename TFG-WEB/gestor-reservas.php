<?php
require_once 'database.php';

$mensaje = '';
$error = '';

// Procesar acciones
if ($_POST) {
    try {
        if (isset($_POST['action']) && isset($_POST['reserva_id'])) {
            $reserva_id = (int)$_POST['reserva_id'];
            $action = $_POST['action'];
            
            switch ($action) {
                case 'cambiar_estado':
                    $nuevo_estado = $_POST['nuevo_estado'];
                    $sql = "UPDATE reservas SET estado = ? WHERE id = ?";
                    $stmt = $pdo->prepare($sql);
                    if ($stmt->execute([$nuevo_estado, $reserva_id])) {
                        $mensaje = "✅ Estado de reserva #$reserva_id cambiado a: $nuevo_estado";
                    }
                    break;
                    
                case 'eliminar':
                    $sql = "DELETE FROM reservas WHERE id = ?";
                    $stmt = $pdo->prepare($sql);
                    if ($stmt->execute([$reserva_id])) {
                        $mensaje = "✅ Reserva #$reserva_id eliminada correctamente";
                    }
                    break;
            }
        }
    } catch (Exception $e) {
        $error = "❌ Error: " . $e->getMessage();
    }
}

// Filtros y paginación
$filtro_estado = $_GET['estado'] ?? '';
$filtro_categoria = $_GET['categoria'] ?? '';
$busqueda = $_GET['busqueda'] ?? '';
$pagina = max(1, (int)($_GET['pagina'] ?? 1));
$por_pagina = 10;
$offset = ($pagina - 1) * $por_pagina;

// Construir consulta con filtros
$where_conditions = [];
$params = [];

if ($filtro_estado) {
    $where_conditions[] = "estado = ?";
    $params[] = $filtro_estado;
}

if ($filtro_categoria) {
    $where_conditions[] = "categoria = ?";
    $params[] = $filtro_categoria;
}

if ($busqueda) {
    $where_conditions[] = "(nombre LIKE ? OR email LIKE ? OR modelo LIKE ?)";
    $busqueda_param = "%$busqueda%";
    $params[] = $busqueda_param;
    $params[] = $busqueda_param;
    $params[] = $busqueda_param;
}

$where_clause = $where_conditions ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Contar total y obtener reservas
$count_sql = "SELECT COUNT(*) FROM reservas $where_clause";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($params);
$total_registros = $count_stmt->fetchColumn();
$total_paginas = ceil($total_registros / $por_pagina);

$sql = "SELECT * FROM reservas $where_clause ORDER BY created_at DESC LIMIT $por_pagina OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener estadísticas
$stats = obtener_estadisticas($pdo);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="./resources/css/general.css" />
    <link rel="stylesheet" href="./resources/css/gestor-reservas.css" />
    <title>Gestor de Reservas - <?php echo $CONFIG['app']['name']; ?></title>
</head>

<body>
    <!-- CABECERA -->
    <header>
        <div class="header-top">
            <div class="logo-container">
                <h1 class="logo">GESTOR DE RESERVAS</h1>
            </div>
            <nav class="nav-alto">
                <ul>
                    <li><a href="index.html">INICIO</a></li>
                    <li><a href="reservas.php">NUEVA RESERVA</a></li>
                    <li><a href="calculador.html">CALCULADORA</a></li>
                    <li><a href="database.php?status=1">ESTADO</a></li>
                </ul>
            </nav>
        </div>
        <div class="nlogo">
            <img src="./resources/img/header/logo-cabecera.jpg" alt="" />
        </div>
    </header>

    <main>
        <!-- ESTADÍSTICAS -->
        <section class="stats-section">
            <h2>📊 Panel de Control</h2>
            <div class="stats-grid">
                <div class="stat-card total">
                    <span class="stat-number"><?php echo $stats['total']; ?></span>
                    <span class="stat-label">Total Reservas</span>
                </div>
                <div class="stat-card pendiente">
                    <span class="stat-number"><?php echo $stats['pendientes']; ?></span>
                    <span class="stat-label">Pendientes</span>
                </div>
                <div class="stat-card confirmada">
                    <span class="stat-number"><?php echo $stats['confirmadas']; ?></span>
                    <span class="stat-label">Confirmadas</span>
                </div>
                <div class="stat-card completada">
                    <span class="stat-number"><?php echo $stats['completadas']; ?></span>
                    <span class="stat-label">Completadas</span>
                </div>
                <div class="stat-card cancelada">
                    <span class="stat-number"><?php echo $stats['canceladas']; ?></span>
                    <span class="stat-label">Canceladas</span>
                </div>
                <div class="stat-card ingresos">
                    <span class="stat-number">€<?php echo number_format($stats['ingresos_total'], 2); ?></span>
                    <span class="stat-label">Ingresos Total</span>
                </div>
            </div>
        </section>

        <!-- MENSAJES -->
        <?php if($mensaje): ?>
            <div class="mensaje exito"><?php echo $mensaje; ?></div>
        <?php endif; ?>
        
        <?php if($error): ?>
            <div class="mensaje error"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- FILTROS -->
        <section class="filtros-section">
            <h3>🔍 Filtros y Búsqueda</h3>
            <form method="GET" class="filtros-form">
                <div class="filtros-grid">
                    <div class="filtro-grupo">
                        <label>Estado:</label>
                        <select name="estado">
                            <option value="">Todos</option>
                            <option value="Pendiente" <?php echo $filtro_estado === 'Pendiente' ? 'selected' : ''; ?>>Pendiente</option>
                            <option value="Confirmada" <?php echo $filtro_estado === 'Confirmada' ? 'selected' : ''; ?>>Confirmada</option>
                            <option value="Completada" <?php echo $filtro_estado === 'Completada' ? 'selected' : ''; ?>>Completada</option>
                            <option value="Cancelada" <?php echo $filtro_estado === 'Cancelada' ? 'selected' : ''; ?>>Cancelada</option>
                        </select>
                    </div>
                    
                    <div class="filtro-grupo">
                        <label>Categoría:</label>
                        <select name="categoria">
                            <option value="">Todas</option>
                            <?php foreach(array_keys($vehiculos) as $categoria): ?>
                                <option value="<?php echo $categoria; ?>" <?php echo $filtro_categoria === $categoria ? 'selected' : ''; ?>>
                                    <?php echo $categoria; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filtro-grupo">
                        <label>Buscar:</label>
                        <input type="text" name="busqueda" placeholder="Nombre, email o modelo..." value="<?php echo htmlspecialchars($busqueda); ?>">
                    </div>
                    
                    <div class="filtro-grupo">
                        <button type="submit" class="btn-filtrar">🔍 Filtrar</button>
                        <a href="?" class="btn-limpiar">🗑️ Limpiar</a>
                    </div>
                </div>
            </form>
        </section>

        <!-- LISTA DE RESERVAS -->
        <section class="reservas-section">
            <div class="section-header">
                <h3>📋 Reservas (<?php echo $total_registros; ?> total)</h3>
                <div class="paginacion-info">
                    Página <?php echo $pagina; ?> de <?php echo $total_paginas; ?> 
                    (<?php echo min($offset + 1, $total_registros); ?>-<?php echo min($offset + $por_pagina, $total_registros); ?> de <?php echo $total_registros; ?>)
                </div>
            </div>

            <?php if (empty($reservas)): ?>
                <div class="no-reservas">
                    <p>📝 No se encontraron reservas con los filtros aplicados.</p>
                    <a href="?" class="btn">Ver todas las reservas</a>
                </div>
            <?php else: ?>
                <div class="reservas-grid">
                    <?php foreach ($reservas as $reserva): ?>
                        <div class="reserva-card estado-<?php echo strtolower($reserva['estado']); ?>">
                            <div class="reserva-header">
                                <span class="reserva-numero">Nº <?php echo str_pad($reserva['id'], 6, '0', STR_PAD_LEFT); ?></span>
                                <span class="reserva-estado estado-<?php echo strtolower($reserva['estado']); ?>">
                                    <?php echo strtoupper($reserva['estado']); ?>
                                </span>
                            </div>
                            
                            <div class="reserva-info">
                                <div class="info-row">
                                    <div class="info-item">
                                        <label>Categoría</label>
                                        <span><?php echo htmlspecialchars($reserva['categoria']); ?></span>
                                    </div>
                                    <div class="info-item">
                                        <label>Modelo</label>
                                        <span><?php echo htmlspecialchars($reserva['modelo']); ?></span>
                                    </div>
                                </div>
                                
                                <div class="info-row">
                                    <div class="info-item">
                                        <label>Horas</label>
                                        <span><?php echo $reserva['dias']; ?>h</span>
                                    </div>
                                    <div class="info-item">
                                        <label>Precio/hora</label>
                                        <span>€<?php echo number_format($reserva['precio_dia'], 2); ?></span>
                                    </div>
                                </div>
                                
                                <div class="info-row">
                                    <div class="info-item">
                                        <label>Inicio</label>
                                        <span><?php echo date('d/m/Y', strtotime($reserva['fecha_inicio'])); ?><br>
                                              <?php echo substr($reserva['hora_inicio'], 0, 5); ?></span>
                                    </div>
                                    <div class="info-item">
                                        <label>Fin</label>
                                        <span><?php echo date('d/m/Y', strtotime($reserva['fecha_fin'])); ?><br>
                                              <?php echo substr($reserva['hora_fin'], 0, 5); ?></span>
                                    </div>
                                </div>
                                
                                <div class="info-row">
                                    <div class="info-item">
                                        <label>Nombre</label>
                                        <span><?php echo htmlspecialchars($reserva['nombre']); ?></span>
                                    </div>
                                    <div class="info-item">
                                        <label>Teléfono</label>
                                        <span><?php echo htmlspecialchars($reserva['telefono']); ?></span>
                                    </div>
                                </div>
                                
                                <div class="info-row">
                                    <div class="info-item full-width">
                                        <label>Email</label>
                                        <span><?php echo htmlspecialchars($reserva['email']); ?></span>
                                    </div>
                                </div>
                                
                                <div class="total-precio">
                                    <strong>TOTAL: €<?php echo number_format($reserva['precio_total'], 2); ?></strong>
                                </div>
                            </div>
                            
                            <div class="reserva-acciones">
                                <form method="POST" class="form-cambiar-estado" style="display: inline;">
                                    <input type="hidden" name="reserva_id" value="<?php echo $reserva['id']; ?>">
                                    <input type="hidden" name="action" value="cambiar_estado">
                                    <select name="nuevo_estado" onchange="this.form.submit()">
                                        <option value="">Cambiar a...</option>
                                        <?php if ($reserva['estado'] !== 'Pendiente'): ?>
                                            <option value="Pendiente">Pendiente</option>
                                        <?php endif; ?>
                                        <?php if ($reserva['estado'] !== 'Confirmada'): ?>
                                            <option value="Confirmada">Confirmada</option>
                                        <?php endif; ?>
                                        <?php if ($reserva['estado'] !== 'Completada'): ?>
                                            <option value="Completada">Completada</option>
                                        <?php endif; ?>
                                        <?php if ($reserva['estado'] !== 'Cancelada'): ?>
                                            <option value="Cancelada">Cancelada</option>
                                        <?php endif; ?>
                                    </select>
                                </form>
                                
                                <button class="btn-eliminar" onclick="confirmarEliminacion(<?php echo $reserva['id']; ?>)">
                                    🗑️ Eliminar
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <!-- PAGINACIÓN -->
        <?php if ($total_paginas > 1): ?>
        <section class="paginacion-section">
            <div class="paginacion">
                <?php if ($pagina > 1): ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['pagina' => 1])); ?>" class="btn-paginacion">⟪ Primera</a>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['pagina' => $pagina - 1])); ?>" class="btn-paginacion">‹ Anterior</a>
                <?php endif; ?>
                
                <?php
                $inicio = max(1, $pagina - 2);
                $fin = min($total_paginas, $pagina + 2);
                
                for ($i = $inicio; $i <= $fin; $i++):
                ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['pagina' => $i])); ?>" 
                       class="btn-paginacion <?php echo $i === $pagina ? 'activa' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
                
                <?php if ($pagina < $total_paginas): ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['pagina' => $pagina + 1])); ?>" class="btn-paginacion">Siguiente ›</a>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['pagina' => $total_paginas])); ?>" class="btn-paginacion">Última ⟫</a>
                <?php endif; ?>
            </div>
        </section>
        <?php endif; ?>
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
    <script src="./resources/js/enlaces.js"></script>
    
    <!-- Form de eliminación oculto -->
    <form id="form-eliminar" method="POST" style="display: none;">
        <input type="hidden" name="reserva_id" id="eliminar-id">
        <input type="hidden" name="action" value="eliminar">
    </form>

    <script>
        function confirmarEliminacion(id) {
            if (confirm('¿Estás seguro de que quieres eliminar la reserva #' + id + '?\n\nEsta acción no se puede deshacer.')) {
                document.getElementById('eliminar-id').value = id;
                document.getElementById('form-eliminar').submit();
            }
        }

        // Auto-submit en cambio de estado
        document.querySelectorAll('.form-cambiar-estado select').forEach(select => {
            select.addEventListener('change', function() {
                if (this.value && confirm('¿Cambiar el estado de la reserva a: ' + this.value + '?')) {
                    this.form.submit();
                } else {
                    this.value = '';
                }
            });
        });

        // Mostrar mensajes temporales
        document.addEventListener('DOMContentLoaded', function() {
            const mensajes = document.querySelectorAll('.mensaje');
            mensajes.forEach(mensaje => {
                setTimeout(() => {
                    mensaje.style.opacity = '0';
                    setTimeout(() => mensaje.remove(), 300);
                }, 3000);
            });
        });
    </script>
</body>
</html>