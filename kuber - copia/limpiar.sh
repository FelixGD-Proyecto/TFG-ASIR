#!/bin/bash

echo "🧹 Limpiando entorno..."

echo "¿Eliminar todo? (escribe 'si' para confirmar):"
read confirmacion
if [ "$confirmacion" != "si" ]; then
    echo "Cancelado"
    exit 1
fi

# Eliminar namespace (esto elimina todo dentro)
kubectl delete namespace mi-app --ignore-not-found=true

# Eliminar volúmenes persistentes
kubectl delete pv mysql-pv web-pv --ignore-not-found=true

echo "✅ Entorno limpiado"
echo ""
echo "Para volver a desplegar:"
echo "  Desarrollo: ./desarrollo.sh"
echo "  Producción: ./produccion.sh"