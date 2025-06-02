#!/bin/bash

echo "🚀 Desplegando entorno de desarrollo..."

# Iniciar minikube si no está corriendo
if ! minikube status &> /dev/null; then
    echo "Iniciando minikube..."
    minikube start
fi

# Crear directorios necesarios
mkdir -p /home/web/proyecto-k8s/mysql-data
mkdir -p /home/web/proyecto-k8s/sql  
mkdir -p /home/web/proyecto-k8s/web

# Copiar archivos si es la primera vez
if [ ! -f "/home/web/proyecto-k8s/web/index.html" ]; then
    echo "Copiando archivos web..."
    cp -r /home/web/proyecto/web/* /home/web/proyecto-k8s/web/
fi

if [ -d "/home/web/proyecto/volumenes/sql" ]; then
    cp -r /home/web/proyecto/volumenes/sql/* /home/web/proyecto-k8s/sql/ 2>/dev/null || true
fi

# Aplicar manifiestos
kubectl apply -f namespace.yaml
kubectl apply -f mysql.yaml
kubectl apply -f web.yaml
kubectl apply -f phpmyadmin.yaml

# Esperar a que esté listo
echo "Esperando a que los pods estén listos..."
kubectl wait --for=condition=ready pod -l app=mysql -n mi-app --timeout=300s
kubectl wait --for=condition=ready pod -l app=web -n mi-app --timeout=300s
kubectl wait --for=condition=ready pod -l app=phpmyadmin -n mi-app --timeout=300s

# Obtener IP de minikube
MINIKUBE_IP=$(minikube ip)

echo ""
echo "✅ ¡Listo!"
echo "🌐 Accede a tu aplicación en:"
echo "   📱 Web: http://$MINIKUBE_IP:30080"
echo "   🗄️  phpMyAdmin: http://$MINIKUBE_IP:30081"
echo ""
echo "📊 Para ver el estado: kubectl get pods -n mi-app"