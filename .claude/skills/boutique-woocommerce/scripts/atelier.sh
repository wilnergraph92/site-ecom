#!/bin/bash
# L'atelier : un vrai WordPress + WooCommerce en local (WordPress Playground),
# avec le thème du projet MONTÉ — chaque modification de fichier est visible au
# rechargement suivant, en quelques secondes.
#
# Usage :   scripts/atelier.sh <dossier-projet> [port]
#
#   <dossier-projet>/theme/<slug>/        le thème bloc (obligatoire)
#   <dossier-projet>/deploiement.json     réglages, catalogue, pages (voir
#                                         assets/deploiement-exemple.json)
#
# Variables facultatives :
#   WC_VERSION=11.1.2   aligner WooCommerce sur la version du site du client
#                       (porte 4 : l'atelier et la production doivent servir le même DOM)
#   WP_VERSION=6.9      idem pour WordPress
#
# La base repart de zéro à chaque lancement : réglages, catalogue et pages sont
# rejoués depuis deploiement.json par scripts/boutique.php — le même fichier que
# pour la production. Tout ce qui compte vit donc dans des fichiers : un réglage
# fait à la main dans le wp-admin de l'atelier disparaît au prochain lancement.
#
# Session administrateur : le lien de connexion sans mot de passe affiché au
# lancement (jeton dans <dossier-projet>/.atelier/jeton). Jamais de saisie de mot
# de passe. PHP dans l'atelier : scripts/atelier-php.sh (équivalent local de
# novamira/execute-php).
# Rapport du déploiement à blanc : <dossier-projet>/.atelier/rapport.json

set -euo pipefail

[ $# -ge 1 ] || { sed -n '2,26p' "$0"; exit 1; }
PROJET="$(cd "$1" && pwd)"
PORT="${2:-9400}"
SCRIPTS="$(cd "$(dirname "$0")" && pwd)"

THEME_DIR="$(find "$PROJET/theme" -mindepth 2 -maxdepth 2 -name style.css -print -quit 2>/dev/null || true)"
[ -n "$THEME_DIR" ] || { echo "Aucun thème dans $PROJET/theme/<slug>/style.css" >&2; exit 1; }
THEME_DIR="$(dirname "$THEME_DIR")"
SLUG="$(basename "$THEME_DIR")"

mkdir -p "$PROJET/.atelier"
rm -f "$PROJET/.atelier/rapport.json"
# Jeton de session : protège les deux outils locaux (exec.php, connexion.php).
JETON="$(python3 -c 'import secrets; print(secrets.token_hex(16))')"
printf '%s' "$JETON" > "$PROJET/.atelier/jeton"

if [ -f "$PROJET/deploiement.json" ]; then
  python3 "$SCRIPTS/paquet.py" "$PROJET/deploiement.json" > "$PROJET/.atelier/paquet.json"
else
  printf '{"theme":"%s"}' "$SLUG" > "$PROJET/.atelier/paquet.json"
fi

# Blueprint généré en Python : le code PHP embarqué y est échappé proprement.
python3 - "$PROJET" "$SLUG" "${WC_VERSION:-}" > "$PROJET/.atelier/blueprint.json" <<'PY'
import json, sys
projet, slug, wc = sys.argv[1], sys.argv[2], sys.argv[3]
paquet = json.load(open(projet + '/.atelier/paquet.json', encoding='utf-8'))

def extension(s, version=''):
    if version:
        return {"resource": "url", "url": "https://downloads.wordpress.org/plugin/%s.%s.zip" % (s, version)}
    return {"resource": "wordpress.org/plugins", "slug": s}

etapes = [{"step": "installPlugin", "pluginData": extension("woocommerce", wc), "options": {"activate": True}}]
for e in paquet.get("extensions", []):
    etapes.append({"step": "installPlugin", "pluginData": extension(e), "options": {"activate": True}})
etapes += [
    # Mode développement de thème : ni la liste des compositions ni theme.json ne
    # sont mis en cache — un fichier ajouté au thème monté est vu immédiatement.
    {"step": "defineWpConfigConsts", "consts": {"WP_DEVELOPMENT_MODE": "theme"}},
    {"step": "setSiteLanguage", "language": "fr_FR"},
    {"step": "activateTheme", "themeFolderName": slug},
    {"step": "setSiteOptions", "options": {"blogname": paquet.get("marque", slug), "blogdescription": ""}},
    {"step": "runPHP", "code": (
        "<?php require '/wordpress/wp-load.php';"
        "require '/atelier/scripts/boutique.php';"
        "$p = json_decode(file_get_contents('/atelier/projet/.atelier/paquet.json'), true);"
        "$r = c2woo_deployer($p, 'atelier');"
        "file_put_contents('/atelier/projet/.atelier/rapport.json', wp_json_encode($r, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));"
    )},
]
print(json.dumps({
    "$schema": "https://playground.wordpress.net/blueprint-schema.json",
    "landingPage": "/",
    "preferredVersions": {"php": "8.3", "wp": "latest"},
    "steps": etapes,
}, ensure_ascii=False, indent=1))
PY

echo "Atelier : thème « $SLUG » monté depuis $THEME_DIR"
echo "Premier lancement : 1 à 3 min (téléchargement de WordPress et WooCommerce)."
echo "Session administrateur dans le navigateur (sans mot de passe) :"
echo "  http://127.0.0.1:$PORT/atelier/connexion.php?jeton=$JETON&vers=/wp-admin/"
echo "PHP dans l'atelier :  scripts/atelier-php.sh \"$PROJET\" 'return c2woo_porte1();'"

# cwd dans /tmp : sous macOS, un cwd protégé par TCC fait échouer Node (voir pieges.md).
cd /tmp
exec npx -y @wp-playground/cli@3 server \
  --port="$PORT" \
  --php=8.3 \
  --wp="${WP_VERSION:-latest}" \
  --login \
  --mount-dir "$THEME_DIR" "/wordpress/wp-content/themes/$SLUG" \
  --mount-dir "$SCRIPTS" "/atelier/scripts" \
  --mount-dir "$PROJET" "/atelier/projet" \
  --mount-dir "$SCRIPTS/atelier-outils" "/wordpress/atelier" \
  --blueprint="$PROJET/.atelier/blueprint.json"
