#!/bin/bash
# Appelle une "ability" Novamira en HTTP direct, sans installer le serveur MCP.
#
# Usage :  ACCES=<dossier-d-acces> scripts/wp.sh <ability> '<json des parametres>'
# Exemple : ACCES=~/.config/boutique-woocommerce/ma-boutique scripts/wp.sh novamira/execute-php '{"code":"return 1;"}'
#
# <dossier-d-acces> est créé par scripts/connecteur.py à partir du bundle .mcpb
# fourni au brief : acces.env (droits 600) y porte WP_API_URL, WP_API_USERNAME,
# WP_API_PASSWORD. Le mot de passe n'est jamais affiché ni écrit ailleurs.
# L'identifiant de session MCP est gardé dans ce même dossier, jamais dans le skill.

if [ -n "${ACCES:-}" ] && [ -f "$ACCES/acces.env" ]; then
  # shellcheck disable=SC1091
  source "$ACCES/acces.env"
fi
A="${WP_API_URL:-}"
U="${WP_API_USERNAME:-}"
P="${WP_API_PASSWORD:-}"
SID_FILE="${ACCES:-${TMPDIR:-/tmp}}/.mcp-sid"

if [ -z "$A" ] || [ -z "$U" ] || [ -z "$P" ]; then
  echo "Accès Novamira manquant : ACCES=<dossier créé par connecteur.py> (ou WP_API_URL, WP_API_USERNAME, WP_API_PASSWORD)." >&2
  exit 1
fi

# Les identifiants passent à curl par sa configuration sur l'entrée standard,
# jamais en argument : un argument se lit dans la liste des processus (ps).
curl_auth() {
  printf 'user = "%s"\n' "$(printf '%s:%s' "$U" "$P" | sed 's/\\/\\\\/g; s/"/\\"/g')" | curl -K - "$@"
}

ouvrir_session() {
  H=$(curl_auth -s -D - -o /dev/null --max-time 30 -X POST "$A" \
    -H 'Content-Type: application/json' -H 'Accept: application/json, text/event-stream' \
    -d '{"jsonrpc":"2.0","id":1,"method":"initialize","params":{
         "protocolVersion":"2024-11-05","capabilities":{},
         "clientInfo":{"name":"maquette-dabord","version":"1"}}}')
  SID=$(printf '%s' "$H" | grep -i '^mcp-session-id:' | tr -d '\r' | awk '{print $2}')
  [ -z "$SID" ] && { echo "Pas d'identifiant de session renvoyé." >&2; return 1; }
  printf '%s' "$SID" > "$SID_FILE"
  curl_auth -s -o /dev/null --max-time 20 -X POST "$A" \
    -H 'Content-Type: application/json' -H "Mcp-Session-Id: $SID" \
    -d '{"jsonrpc":"2.0","method":"notifications/initialized"}'
  echo "$SID"
}

[ -s "$SID_FILE" ] && SID=$(cat "$SID_FILE") || SID=$(ouvrir_session) || exit 1

appeler() {
  BODY=$(python3 -c "
import json,sys
print(json.dumps({'jsonrpc':'2.0','id':2,'method':'tools/call','params':{
  'name':'mcp-adapter-execute-ability',
  'arguments':{'ability_name':sys.argv[1],'parameters':json.loads(sys.argv[2])}}}))
" "$1" "$2")
  curl_auth -s --max-time 180 -X POST "$A" \
    -H 'Content-Type: application/json' -H 'Accept: application/json, text/event-stream' \
    -H "Mcp-Session-Id: $SID" -d "$BODY"
}

OUT=$(appeler "$1" "${2:-{\}}")

# une session expirée se rouvre toute seule
if printf '%s' "$OUT" | grep -q 'Missing Mcp-Session-Id\|Invalid session'; then
  SID=$(ouvrir_session) || exit 1
  OUT=$(appeler "$1" "${2:-{\}}")
fi

# la réponse peut arriver en text/event-stream : lire la ligne "data:"
printf '%s' "$OUT" | python3 -c "
import json,sys
raw = sys.stdin.read().strip()
for ligne in raw.splitlines():
    if ligne.startswith('data:'):
        raw = ligne[5:].strip()
try:
    d = json.loads(raw)
except Exception:
    print(raw[:900]); sys.exit()
if 'error' in d:
    print('ERREUR :', json.dumps(d['error'], ensure_ascii=False)[:700]); sys.exit(1)
for bloc in d.get('result', {}).get('content', []):
    t = bloc.get('text', '')
    try:
        print(json.dumps(json.loads(t), ensure_ascii=False, indent=1)[:5000])
    except Exception:
        print(t[:5000])
"
