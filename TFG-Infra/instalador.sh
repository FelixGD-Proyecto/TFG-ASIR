#!/bin/bash

# Script de instalación de Docker y Docker Compose para Ubuntu 24

set -e  # Salir si hay algún error

echo "🐳 Instalando Docker y Docker Compose en Ubuntu 24..."
echo "=================================================="

# Verificar que se ejecuta en Ubuntu
if ! grep -q "Ubuntu" /etc/os-release; then
    echo "❌ Este script está diseñado en un pricipio para Ubuntu 24. Sistema operativo actual:"
    cat /etc/os-release | grep PRETTY_NAME
    exit 1
fi

# Mostrar información del sistema
echo "📋 Sistema actual:"
lsb_release -a
echo ""

# Actualizar el sistema
echo "🔄 Actualizando paquetes del sistema"
sudo apt update
sudo apt upgrade -y

# Instalar dependencias previas
echo "📦 Instalando dependencias necesarias"
sudo apt install -y \
    apt-transport-https \
    ca-certificates \
    curl \
    gnupg \
    lsb-release \
    software-properties-common \
    wget

# Eliminar versiones antiguas de Docker si existen
echo "🧹 Eliminando versiones antiguas de Docker..."
sudo apt remove -y docker docker-engine docker.io containerd runc 2>/dev/null || true

# Agregar clave GPG oficial de Docker
echo "🔑 Agregando clave GPG de Docker"
sudo mkdir -p /etc/apt/keyrings
curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo gpg --dearmor -o /etc/apt/keyrings/docker.gpg
sudo chmod a+r /etc/apt/keyrings/docker.gpg

# Agregar repositorio de Docker
echo "📋 Agregando repositorio de Docker"
echo \
  "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.com/linux/ubuntu \
  $(lsb_release -cs) stable" | sudo tee /etc/apt/sources.list.d/docker.list > /dev/null

# Actualizar lista de paquetes
echo "🔄 Actualizando índice de lista"
sudo apt update

# Instalar Docker Engine, CLI y containerd
echo "🐳 Instalando Docker Engine"
sudo apt install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin

# Verificar instalación de Docker
echo "✅ Verificando instalación"
sudo docker --version
sudo docker compose version

# Iniciar y habilitar Docker
echo "🚀 Iniciando servicio Docker"
sudo systemctl start docker
sudo systemctl enable docker

# Agregar usuario actual al grupo docker
echo "👤 Agregando usuario actual ($USER) al grupo docker..."
sudo usermod -aG docker $USER

# Configurar hardware Docker daemon
echo "⚙️ Configurando Docker daemon..."
sudo mkdir -p /etc/docker
sudo tee /etc/docker/daemon.json > /dev/null << 'EOF'
{
  "log-driver": "json-file",
  "log-opts": {
    "max-size": "10m",
    "max-file": "3"
  },
  "storage-driver": "overlay2"
}
EOF

# Reiniciar Docker para aplicar configuración
echo "🔄 Reiniciando Docker"
sudo systemctl restart docker

# Probar Docker con contenedor hello-world
echo "🧪 Probando Docker"
sudo docker run --rm hello-world

# Mostrar estado del servicio
echo "📊 Estado del servicio Docker:"
sudo systemctl status docker --no-pager -l

echo "======================================"
echo "     ¡INSTALACIÓN COMPLETADA!"
echo "======================================"
