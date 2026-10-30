#!/usr/bin/env bash
set -e
DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$DIR"

CMD="$1"
shift || true

PYTHON_BIN="python3"
if ! command -v python3 &> /dev/null; then
    PYTHON_BIN="python"
fi

case "$CMD" in
    rg|grep)
        if command -v rg &> /dev/null; then
            rg "$@"
        else
            grep -rn "$@"
        fi
        ;;
    sg|ast)
        npx --no-install ast-grep "$@"
        ;;
    skeleton)
        $PYTHON_BIN tools/ast_skeleton.py "$@"
        ;;
    graph)
        $PYTHON_BIN tools/dependency_graph.py "$@"
        ;;
    repomap)
        $PYTHON_BIN tools/repomap.py "$@"
        ;;
    tags)
        $PYTHON_BIN tools/generate_tags.py "$@"
        ;;
    diff)
        $PYTHON_BIN tools/clean_diff.py "$@"
        ;;
    compress)
        $PYTHON_BIN tools/compress_context.py "$@"
        ;;
    tokens)
        $PYTHON_BIN tools/count_tokens.py "$@"
        ;;
    schema)
        php tools/introspect_schema.php "$@"
        ;;
    install|setup)
        $PYTHON_BIN -m pip install -r tools/requirements.txt
        php tools/introspect_schema.php
        $PYTHON_BIN tools/dependency_graph.py --rebuild
        $PYTHON_BIN tools/repomap.py --save
        $PYTHON_BIN tools/generate_tags.py --build
        ;;
    help|--help|-h|"")
        echo "===================================================================="
        echo "  PROJECT SAAVEDRA - CLI UNIFICADO DE AGENTE (tools/agent.sh)"
        echo "===================================================================="
        echo ""
        echo " Uso: tools/agent.sh [comando] [argumentos...]"
        echo ""
        echo " Comandos Disponibles:"
        echo "   rg [query] [ruta]        - Ripgrep ultrarrápido"
        echo "   sg [args...]             - AST-grep búsqueda estructural"
        echo "   skeleton [archivo]       - Esqueleto sintáctico Tree-sitter"
        echo "   graph [-q nombre]        - Grafo NetworkX y análisis de impacto"
        echo "   repomap [--save]         - Mapa de arquitectura del repositorio"
        echo "   tags [-s símbolo]        - Índice de símbolos Ctags"
        echo "   diff [--staged]          - Git diff limpio y atómico (-U1)"
        echo "   compress [archivo]       - Compresor de logs y prompts"
        echo "   tokens [archivo|dir]     - Medidor de presupuesto de tokens"
        echo "   schema                   - Introspección de MySQL a prisma"
        echo "   install                  - Instalar o actualizar dependencias"
        echo "===================================================================="
        ;;
    *)
        echo "[ERROR] Comando desconocido: $CMD"
        exit 1
        ;;
esac

