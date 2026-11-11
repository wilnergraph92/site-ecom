#!/bin/bash
# Téléverse un fichier local vers le site (novamira/create-upload-link + PUT).
# Le jeton de téléversement n'est jamais affiché.
#
# Usage :  ACCES=<dossier> scripts/televerser.sh <fichier-local> <chemin-sur-le-site>
# Exemple : ACCES=… scripts/televerser.sh /tmp/ma-boutique.zip wp-content/uploads/theme.zip
set -euo pipefail
[ -n "${ACCES:-}" ] || { echo "ACCES=<dossier créé par connecteur.py> manquant" >&2; exit 1; }
ICI="$(cd "$(dirname "$0")" && pwd)"
LOCAL="$1"; DISTANT="$2"
[ -f "$LOCAL" ] || { echo "Fichier absent : $LOCAL" >&2; exit 1; }
PARAMS=$(python3 -c 'import json,sys; print(json.dumps({"path": sys.argv[1], "overwrite": True}))' "$DISTANT")
"$ICI/wp.sh" novamira/create-upload-link "$PARAMS" > "$ACCES/lien.json"
python3 - "$ACCES/lien.json" "$LOCAL" <<'PY'
import json, subprocess, sys
raw = open(sys.argv[1]).read()
try:
    d = json.loads(raw)
except Exception:
    sys.exit('Réponse inattendue de create-upload-link : ' + raw[:300])
r = d.get('data', {}).get('return_value') or d.get('data', {})
out = subprocess.run(['curl', '-s', '--max-time', '600', '-X', 'PUT',
                      '-H', 'X-Novamira-Upload-Token: ' + r['upload_token'],
                      '--data-binary', '@' + sys.argv[2], r['upload_url']], capture_output=True, text=True).stdout
try:
    j = json.loads(out)
    print({k: v for k, v in j.items() if k in ('path', 'bytes_written', 'created')})
except Exception:
    sys.exit('Échec du téléversement : ' + out[:300])
PY
rm -f "$ACCES/lien.json"
