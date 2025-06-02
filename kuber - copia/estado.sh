#!/bin/bash

echo "📊 Estado del entorno:"
echo ""

# Verificar si minikube está corriendo
if ! minikube status &> /dev/null; then
    echo "❌ Minikube no está ejecutándose"
    exit 1
fi

echo "✅ Minikube ejecutándose"
echo ""

# Mostrar pods
echo "🔹 Pods:"
kubectl get pods -n mi-app -o wide 2>/dev/null || echo "No hay pods desplegados"
echo ""

# Mostrar servicios
echo "🔹 Servicios:"
kubectl get services -n mi-app 2>/dev/null || echo "No hay servicios desplegados"
echo ""

# URLs de acceso
MINIKUBE_IP=$(minikube ip)
echo "🌐 URLs de acceso:"
echo "   📱 Web: http://$MINIKUBE_IP:30080"
echo "   🗄️  phpMyAdmin: http://$MINIKUBE_IP:30081"
echo ""

# Comandos útiles
echo "🔧 Comandos útiles:"
echo "   Ver logs web: kubectl logs -n mi-app -l app=web"
echo "   Ver logs MySQL: kubectl logs -n mi-app -l app=mysql"
echo "   Escalar web: kubectl scale deployment web -n mi-app --replicas=2"