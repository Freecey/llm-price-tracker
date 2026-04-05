#!/bin/bash

# Wrapper pour llm-price-tracker
# Active le virtualenv puis exécute scripts/db_sync.py

set -e

PROJECT_DIR="/var/www/clients/client1/web27/web/llm-price-tracker"
PYTHON_BIN="$PROJECT_DIR/venv/bin/python"
PYTHON_SCRIPT="$PROJECT_DIR/scripts/db_sync.py"

if [ ! -d "$PROJECT_DIR" ]; then
    echo "Erreur: répertoire projet introuvable: $PROJECT_DIR"
    exit 1
fi

if [ ! -x "$PYTHON_BIN" ]; then
    echo "Erreur: Python du venv introuvable: $PYTHON_BIN"
    exit 1
fi

if [ ! -f "$PYTHON_SCRIPT" ]; then
    echo "Erreur: script introuvable: $PYTHON_SCRIPT"
    exit 1
fi

cd "$PROJECT_DIR" || {
    echo "Erreur: impossible d'entrer dans $PROJECT_DIR"
    exit 1
}

echo "[$(date '+%Y-%m-%d %H:%M:%S')] Lancement db_sync.py"
"$PYTHON_BIN" "$PYTHON_SCRIPT"
EXIT_CODE=$?
echo "[$(date '+%Y-%m-%d %H:%M:%S')] Fin avec code: $EXIT_CODE"

exit $EXIT_CODE
