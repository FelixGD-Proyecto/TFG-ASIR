#!/bin/bash

# Script para crear la estructura de directorios a utilizar para el proyecto

echo "🚀 Creando estructura de directorios 🚀"

# Directorio base
# Aviso: modificar esta variable en caso de querer utilizar otro, pero hay que tener en cuenta que el archivo de docker-compose utilizara esta de forma estatica
BASE_DIR="/home/web/APP-WEB"

# Crear directorio base si no existe
if [ ! -d "$BASE_DIR" ]; then
    echo "📁 Creando directorio base: $BASE_DIR"
    sudo mkdir -p "$BASE_DIR"
fi

# Crear estructura de directorios
echo "📂 Creando directorios necesarios"

# Directorios para volúmenes de MariaDB
sudo mkdir -p "$BASE_DIR/volumenes/mysql-data"
sudo mkdir -p "$BASE_DIR/volumenes/sql"

# Directório para aplicación web
sudo mkdir -p "$BASE_DIR/web"

echo "✅ Estructura de directorios creada:"
echo "├── $BASE_DIR/"
echo "│   ├── volumenes/"
echo "│   │   ├── mysql-data/     (datos de MariaDB)"
echo "│   │   └── sql/             (scripts SQL de inicialización)"
echo "│   └── web/                 (código de la aplicación web)"

# Establecer permisos
echo "🔐 Configurando permisos"

# Permisos para MySQL (usuario mysql tiene UID 999 en el contenedor)
sudo chown -R 999:999 "$BASE_DIR/volumenes/mysql-data"
sudo chmod 755 "$BASE_DIR/volumenes/mysql-data"

# Permisos para directorio SQL (scripts de inicialización)
sudo chown -R $USER:$USER "$BASE_DIR/volumenes/sql"
sudo chmod 755 "$BASE_DIR/volumenes/sql"

# Permisos para aplicación web (www-data tiene UID 82 en alpine)
sudo chown -R 82:82 "$BASE_DIR/web"
sudo chmod 755 "$BASE_DIR/web"


echo ""
echo "¡Estructura creada correctamente!"
echo ""
echo "📋 Próximos pasos:"
echo "1. Ejecuta: docker-compose up -d"
echo "2. Para acceder a la aplicación en: http://localhost:8080"
echo "3. Para acceder a  phpMyAdmin en: http://localhost:8081"
echo "   Usuario: felix | Contraseña: 4444"
echo ""
echo "📁 Directorios creados:"
echo "   • $BASE_DIR/web/ - Coloca aquí tu código PHP"
echo "   • $BASE_DIR/volumenes/sql/ - Scripts SQL de inicialización"
echo "   • $BASE_DIR/volumenes/mysql-data/ - Datos persistentes de MySQL"
echo ""
