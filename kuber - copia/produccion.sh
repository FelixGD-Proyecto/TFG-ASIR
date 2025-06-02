#!/bin/bash

echo "🚀 Desplegando entorno de producción..."

# Confirmar
echo "⚠️  ¿Desplegar en PRODUCCIÓN? (escribe 'si' para confirmar):"
read confirmacion
if [ "$confirmacion" != "si" ]; then
    echo "Cancelado"
    exit 1
fi

# Cambiar a 3 réplicas para producción
sed 's/replicas: 1/replicas: 3/' web.yaml > web-prod.yaml

# Aplicar configuración
kubectl apply -f namespace.yaml
kubectl apply -f mysql.yaml
kubectl apply -f web-prod.yaml

# No desplegar phpMyAdmin en producción por seguridad
echo "⚠️  phpMyAdmin NO se despliega en producción por seguridad"

# Esperar
echo "Esperando a que los pods estén listos..."
kubectl wait --for=condition=ready pod -l app=mysql -n mi-app --timeout=300s
kubectl wait --for=condition=ready pod -l app=web -n mi-app --timeout=300s

# Limpiar archivo temporal
rm web-prod.yaml

MINIKUBE_IP=$(minikube ip)

echo ""
echo "✅ Producción desplegada!"
echo "🌐 Web: http://$MINIKUBE_IP:30080"
echo "📊 3 réplicas ejecutándose"
echo ""
echo "Para verificar: kubectl get pods -n mi-app"