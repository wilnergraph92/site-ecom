#!/bin/bash
# Exécute du PHP dans l'atelier — l'équivalent local de
#   ./wp.sh novamira/execute-php '{"code":"…"}'
# avec scripts/boutique.php déjà chargé et l'utilisateur administrateur actif.
#
# Usage :  scripts/atelier-php.sh <dossier-projet> 'return c2woo_porte1();'
#          scripts/atelier-php.sh <dossier-projet> fichier.php     (code sans « <?php »)
# Port :   ATELIER_PORT=9400 par défaut.
set -euo pipefail
PROJET="$(cd "$1" && pwd)"; CODE="$2"
[ -f "$CODE" ] && CODE="$(sed '1s/^<?php//' "$CODE")"
JETON="$(cat "$PROJET/.atelier/jeton")"
curl -s --max-time 300 -X POST -H "X-Atelier-Jeton: $JETON" -H 'Content-Type: text/plain' \
  --data-binary "$CODE" "http://127.0.0.1:${ATELIER_PORT:-9400}/atelier/exec.php"
echo
