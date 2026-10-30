#!/usr/bin/env bash
set -e

echo "===================================================================="
echo "  PROJECT SAAVEDRA - INSTALADOR DE HERRAMIENTAS DE AGENTE Y AST (LINUX)"
echo "===================================================================="
echo ""

# 1. Verificar PHP
if ! command -v php &> /dev/null; then
    echo "[ERROR] PHP no está instalado o no se encuentra en el PATH."
    exit 1
fi
echo "[OK] PHP detectado: $(php -v | head -n 1)"

# 2. Verificar Python
PYTHON_BIN="python3"
if ! command -v python3 &> /dev/null; then
    if command -v python &> /dev/null; then
        PYTHON_BIN="python"
    else
        echo "[ERROR] Python no está instalado. Requiere Python 3.10+"
        exit 1
    fi
fi
echo "[OK] Python detectado: $($PYTHON_BIN --version)"

# 3. Verificar NPM / Node
if ! command -v npm &> /dev/null; then
    echo "[ERROR] NPM/Node.js no está instalado."
    exit 1
fi
echo "[OK] Node detectado: $(node -v)"

echo ""
echo "--------------------------------------------------------------------"
echo " [Paso 1/4] Instalando dependencias Python (tools/requirements.txt)..."
echo "--------------------------------------------------------------------"
$PYTHON_BIN -m pip install --quiet --upgrade pip || true
$PYTHON_BIN -m pip install --quiet -r tools/requirements.txt || \
$PYTHON_BIN -m pip install --user -r tools/requirements.txt
echo "[OK] Librerías Python instaladas con éxito."

echo ""
echo "--------------------------------------------------------------------"
echo " [Paso 2/4] Instalando AST-grep en devDependencies de NPM..."
echo "--------------------------------------------------------------------"
npm install --save-dev @ast-grep/cli
echo "[OK] AST-grep (@ast-grep/cli) instalado con éxito."

echo ""
echo "--------------------------------------------------------------------"
echo " [Paso 3/4] Introspeccionando esquema MySQL a Prisma..."
echo "--------------------------------------------------------------------"
php tools/introspect_schema.php

echo ""
echo "--------------------------------------------------------------------"
echo " [Paso 4/4] Construyendo Grafo de Dependencias, Repomap y Tags..."
echo "--------------------------------------------------------------------"
$PYTHON_BIN tools/dependency_graph.py --rebuild
$PYTHON_BIN tools/repomap.py --save
$PYTHON_BIN tools/generate_tags.py --build

chmod +x tools/*.sh tools/*.py || true

echo ""
echo "===================================================================="
echo "  INSTALACIÓN COMPLETADA EXITOSAMENTE EN LINUX!"
echo "===================================================================="
echo "Archivos generados:"
echo " - prisma/schema.prisma"
echo " - repomap.txt"
echo " - tags"
echo " - storage/code_dependency_graph.json"
echo ""

