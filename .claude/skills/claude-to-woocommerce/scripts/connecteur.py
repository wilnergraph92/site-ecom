#!/usr/bin/env python3
"""
Range le connecteur Novamira (.mcpb) fourni AU BRIEF — sans jamais s'en servir
avant la phase 5 — puis l'efface à la clôture.

  python3 connecteur.py <fichier.mcpb> <projet>     phase 0 : ranger
  python3 connecteur.py --oublier <projet>          clôture : effacer

Ranger crée ~/.config/claude-to-woocommerce/<projet>/ (droits 700) :
  bundle/        le contenu du .mcpb (manifest compris, droits 600)
  acces.env      WP_API_URL, WP_API_USERNAME, WP_API_PASSWORD (droits 600)
  origine.txt    le chemin du .mcpb reçu (rappelé à la clôture)

N'affiche que l'adresse du site et l'identifiant — jamais le mot de passe
applicatif. Aucun appel réseau : le site n'est pas contacté ici. Le dossier est
hors du projet : il ne part ni dans un zip, ni dans un dépôt, ni chez le client.

Ensuite, en phase 5 seulement :
  ACCES=~/.config/claude-to-woocommerce/<projet> scripts/wp.sh <ability> '{…}'

Oublier (clôture, APRÈS c2woo_revoquer_acces()) supprime ce dossier entier
— identifiants, bundle, identifiant de session MCP — et le fichier de session
de secours de wp.sh. Il ne supprime pas le .mcpb reçu (fichier de l'utilisateur) :
il en rappelle le chemin.
"""
import os
import shlex
import shutil
import sys
import json
import zipfile

RACINE = os.path.expanduser('~/.config/claude-to-woocommerce')


def dossier_du(projet):
    if not projet or projet in ('.', '..') or os.sep in projet or (os.altsep and os.altsep in projet):
        sys.exit('Nom de projet invalide : ' + repr(projet))
    return os.path.join(RACINE, projet)


def ranger(mcpb, projet):
    if not zipfile.is_zipfile(mcpb):
        sys.exit('Ce fichier n\'est pas un bundle .mcpb (archive zip) : ' + mcpb)
    dossier = dossier_du(projet)
    for d in (RACINE, dossier):
        os.makedirs(d, exist_ok=True)
        os.chmod(d, 0o700)
    with zipfile.ZipFile(mcpb) as z:
        z.extractall(os.path.join(dossier, 'bundle'))
    manifeste = os.path.join(dossier, 'bundle', 'manifest.json')
    if not os.path.exists(manifeste):
        sys.exit('manifest.json absent du bundle : est-ce bien le connecteur Novamira ?')
    os.chmod(manifeste, 0o600)
    m = json.load(open(manifeste, encoding='utf-8'))
    env = ((m.get('server') or {}).get('mcp_config') or {}).get('env') or {}
    url = env.get('WP_API_URL', '')
    user = env.get('WP_API_USERNAME', '')
    mdp = env.get('WP_API_PASSWORD', '')
    if not (url and user and mdp):
        sys.exit('Le manifest ne contient pas WP_API_URL / WP_API_USERNAME / WP_API_PASSWORD. Clés : ' + ', '.join(env))
    acces = os.path.join(dossier, 'acces.env')
    # Créé directement en 600 ; valeurs entre apostrophes shell : ni $, ni `, ni \ interprétés au source.
    fd = os.open(acces, os.O_WRONLY | os.O_CREAT | os.O_TRUNC, 0o600)
    with os.fdopen(fd, 'w', encoding='utf-8') as f:
        f.write('export WP_API_URL=%s\nexport WP_API_USERNAME=%s\nexport WP_API_PASSWORD=%s\n'
                % (shlex.quote(url), shlex.quote(user), shlex.quote(mdp)))
    os.chmod(acces, 0o600)
    with open(os.path.join(dossier, 'origine.txt'), 'w', encoding='utf-8') as f:
        f.write(os.path.abspath(mcpb) + '\n')
    site = url.split('/wp-json')[0]
    print('Connecteur rangé :', dossier)
    print('Site :', site, '· utilisateur :', user, '· mot de passe applicatif : masqué (%d car.)' % len(mdp))
    print('Il ne sert qu\'en phase 5 : ACCES=%s' % dossier)


def oublier(projet):
    dossier = dossier_du(projet)
    origine = ''
    try:
        origine = open(os.path.join(dossier, 'origine.txt'), encoding='utf-8').read().strip()
    except OSError:
        pass
    efface = []
    if os.path.isdir(dossier):
        shutil.rmtree(dossier)
        efface.append(dossier)
    # fichier de session de secours de wp.sh (quand ACCES n'était pas défini)
    secours = os.path.join(os.environ.get('TMPDIR') or '/tmp', '.mcp-sid')
    if os.path.isfile(secours):
        os.remove(secours)
        efface.append(secours)
    try:
        if os.path.isdir(RACINE) and not os.listdir(RACINE):
            os.rmdir(RACINE)
    except OSError:
        pass
    print('Identifiants locaux effacés :', ', '.join(efface) if efface else 'rien à effacer (déjà fait)')
    if origine and os.path.exists(origine):
        print('Le .mcpb reçu au brief existe encore :', origine,
              '— son mot de passe est révoqué s\'il est passé par c2woo_revoquer_acces() ; le signaler au client.')


if __name__ == '__main__':
    if len(sys.argv) == 3 and sys.argv[1] == '--oublier':
        oublier(sys.argv[2])
    elif len(sys.argv) == 3 and not sys.argv[1].startswith('-'):
        ranger(sys.argv[1], sys.argv[2])
    else:
        sys.exit(__doc__)
