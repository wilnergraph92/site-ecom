#!/bin/bash
# Ouvre une session wp-admin sans mot de passe sur le site du client :
# novamira/create-admin-access-link, puis l'échange du jeton, en un seul geste.
# N'affiche QUE l'URL de connexion : à usage unique, valable 60 secondes —
# l'ouvrir immédiatement dans le navigateur. Jetons et nonces jamais affichés.
#
# Usage :  ACCES=<dossier> scripts/lien-admin.sh
set -euo pipefail
[ -n "${ACCES:-}" ] || { echo "ACCES=<dossier créé par connecteur.py> manquant" >&2; exit 1; }
ICI="$(cd "$(dirname "$0")" && pwd)"
"$ICI/wp.sh" novamira/create-admin-access-link '{}' > "$ACCES/lien.json"   # n'accepte AUCUN paramètre
python3 - "$ACCES/lien.json" <<'PY'
import json, subprocess, sys
raw = open(sys.argv[1]).read()
try:
    r = json.loads(raw)['data']
except Exception:
    sys.exit('Réponse inattendue : ' + raw[:200])
r = r.get('return_value') or r
out = subprocess.run(['curl', '-s', '-X', 'POST',
                      '-H', 'X-Novamira-Admin-Access-Token: ' + r['access_token'],
                      '-H', 'X-Novamira-Admin-Access-Nonce: ' + r['access_nonce'],
                      r['exchange_url']], capture_output=True, text=True).stdout
try:
    print(json.loads(out)['login_url'])
except Exception:
    sys.exit('Échange refusé : ' + out[:200])
PY
rm -f "$ACCES/lien.json"
