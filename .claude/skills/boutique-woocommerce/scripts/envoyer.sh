#!/bin/bash
# Exécute du PHP sur le site du client AVEC scripts/boutique.php chargé :
# le fichier part dans le code de l'appel (Novamira n'écrit pas de .php hors de
# son bac à sable), première ligne « <?php » retirée.
#
# Usage :  ACCES=<dossier> scripts/envoyer.sh 'return c2woo_porte7();'
#          ACCES=<dossier> scripts/envoyer.sh fichier.php     (code sans « <?php »)
set -euo pipefail
[ -n "${ACCES:-}" ] || { echo "ACCES=<dossier créé par connecteur.py> manquant" >&2; exit 1; }
ICI="$(cd "$(dirname "$0")" && pwd)"
CODE="$1"; [ -f "$CODE" ] && CODE="$(sed '1s/^<?php//' "$CODE")"
python3 - "$ICI/boutique.php" "$CODE" > "$ACCES/appel.json" <<'PY'
import json, sys
code = open(sys.argv[1], encoding='utf-8').read().split('\n', 1)[1]
print(json.dumps({"code": code + "\n" + sys.argv[2]}))
PY
"$ICI/wp.sh" novamira/execute-php "$(cat "$ACCES/appel.json")"
rm -f "$ACCES/appel.json"
