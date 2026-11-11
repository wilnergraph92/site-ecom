#!/usr/bin/env python3
"""
Polices de la maquette → polices du thème, hébergées dans le thème.

La maquette (phase 2) charge ses polices par un <link> Google Fonts : c'est
rapide pour itérer. Le thème, lui, les embarque (assets/fonts/*.woff2) et les
déclare dans theme.json (fontFace). Trois bénéfices :
  - elles se chargent aussi dans l'éditeur (le client compose dans la vraie typo) ;
  - elles apparaissent dans Apparence → Éditeur → Styles → Typographie ;
  - aucune requête vers Google (RGPD).

Usage :
  python3 polices.py --theme projet/theme/<slug> \\
      --url "https://fonts.googleapis.com/css2?family=Fraunces:wght@400;700&family=Inter:wght@400;500;600&display=swap" \\
      --titres "Fraunces:serif" --texte "Inter:sans-serif" \\
      --ttf projet/polices-ttf

  --url     l'URL exacte du <link> de la maquette (copier-coller)
  --titres  famille des titres, puis « : » et le repli générique
  --texte   famille du texte courant (peut être la même)
  --ttf     dossier où déposer le TTF de la police des titres (graisse la plus
            forte chargée), pour scripts/visuels.py logo — Pillow ne lit pas le woff2.

Seul le sous-ensemble « latin » est téléchargé : il couvre le français (é, œ, €…).
"""
import argparse
import json
import os
import re
import sys
import urllib.parse
import urllib.request

UA_WOFF2 = ('Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 '
            '(KHTML, like Gecko) Chrome/126.0 Safari/537.36')


def lire(url, ua=None):
    req = urllib.request.Request(url, headers={'User-Agent': ua} if ua else {})
    with urllib.request.urlopen(req, timeout=30) as r:
        return r.read()


def faces(css):
    """Blocs @font-face précédés du commentaire /* latin */ (sous-ensemble latin)."""
    out = []
    for sous, bloc in re.findall(r'/\*\s*([\w-]+)\s*\*/\s*@font-face\s*{([^}]*)}', css):
        if sous != 'latin':
            continue
        get = lambda k: (re.search(k + r'\s*:\s*([^;]+);', bloc) or [None, ''])[1].strip()
        out.append({
            'famille': get('font-family').strip("'\""),
            'style': get('font-style') or 'normal',
            'graisse': get('font-weight') or '400',
            'url': re.search(r'url\(([^)]+)\)', bloc).group(1),
        })
    return out


def slug(txt):
    return re.sub(r'[^a-z0-9]+', '-', txt.lower()).strip('-')


def main():
    ap = argparse.ArgumentParser(description=__doc__, formatter_class=argparse.RawDescriptionHelpFormatter)
    ap.add_argument('--theme', required=True)
    ap.add_argument('--url', required=True)
    ap.add_argument('--titres', required=True)
    ap.add_argument('--texte', required=True)
    ap.add_argument('--ttf')
    a = ap.parse_args()

    roles = {}
    for role, val in (('titres', a.titres), ('texte', a.texte)):
        nom, _, repli = val.partition(':')
        roles[role] = (nom.strip(), repli.strip() or 'sans-serif')

    css = lire(a.url, UA_WOFF2).decode('utf-8')
    liste = faces(css)
    if not liste:
        sys.exit('Aucune @font-face « latin » dans la réponse — vérifier l’URL.')

    dossier = os.path.join(a.theme, 'assets', 'fonts')
    os.makedirs(dossier, exist_ok=True)

    # Une police variable renvoie LE MÊME fichier pour chaque graisse : on
    # regroupe par URL et on déclare une plage de graisses (« 400 700 »).
    par_url = {}
    for f in liste:
        cle = (f['famille'], f['style'], f['url'])
        par_url.setdefault(cle, []).append(f['graisse'])
    declarations = {}
    for (famille, style, url), graisses in par_url.items():
        nums = sorted({int(x) for g in graisses for x in g.split()})
        plage = str(nums[0]) if len(nums) == 1 else '%d %d' % (nums[0], nums[-1])
        nom = '%s-%s-%s.woff2' % (slug(famille), style, plage.replace(' ', '-'))
        with open(os.path.join(dossier, nom), 'wb') as out:
            out.write(lire(url, UA_WOFF2))
        declarations.setdefault(famille, []).append({
            'fontFamily': famille, 'fontStyle': style, 'fontWeight': plage,
            'fontDisplay': 'swap', 'src': ['file:./assets/fonts/' + nom],
        })
        print('assets/fonts/' + nom)

    # theme.json : les deux rôles « titres » et « texte », rien d'autre.
    tj_path = os.path.join(a.theme, 'theme.json')
    tj = json.load(open(tj_path, encoding='utf-8'))
    familles = tj.setdefault('settings', {}).setdefault('typography', {}).setdefault('fontFamilies', [])
    deja = set()
    for role, (famille, repli) in roles.items():
        if famille not in declarations:
            sys.exit('« %s » absente de l’URL fournie (familles trouvées : %s).' % (famille, ', '.join(declarations)))
        entree = {'slug': role, 'name': 'Titres' if role == 'titres' else 'Texte',
                  'fontFamily': "'%s', %s" % (famille, repli)}
        if famille not in deja:           # même famille pour les deux rôles : une seule déclaration
            entree['fontFace'] = declarations[famille]
            deja.add(famille)
        familles[:] = [f for f in familles if f.get('slug') != role] + [entree]
    json.dump(tj, open(tj_path, 'w', encoding='utf-8'), ensure_ascii=False, indent=2)
    print('theme.json : fontFamilies « titres » et « texte » mis à jour')

    # TTF de la police des titres, pour le logo provisoire (visuels.py logo).
    if a.ttf:
        famille = roles['titres'][0]
        graisse = max(int(g.split()[-1]) for d in declarations[famille] for g in [d['fontWeight']] if d['fontStyle'] == 'normal')
        graisse = min(graisse, 800)
        url = 'https://fonts.googleapis.com/css2?family=%s:wght@%d' % (urllib.parse.quote_plus(famille), graisse)
        css_ttf = lire(url).decode('utf-8')    # sans agent moderne : Google répond en TTF
        m = re.search(r'url\(([^)]+\.ttf)\)', css_ttf)
        if not m:
            sys.exit('TTF introuvable pour %s %d.' % (famille, graisse))
        os.makedirs(a.ttf, exist_ok=True)
        chemin = os.path.join(a.ttf, 'titres.ttf')
        with open(chemin, 'wb') as out:
            out.write(lire(m.group(1)))
        print(chemin, '(%s %d, pour le logo provisoire)' % (famille, graisse))


if __name__ == '__main__':
    main()
