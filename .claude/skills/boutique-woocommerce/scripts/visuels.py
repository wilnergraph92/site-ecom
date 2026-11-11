#!/usr/bin/env python3
"""
Visuels générés par la méthode — quatre commandes, aucune dépendance à installer
à la main.

  vignette       screenshot.png du thème (1200 × 900) : fond noir, nom de la
                 marque en gras, « Ecommerce » en maigre dessous, aligné à droite.
  logo           logo provisoire : le nom de la marque en PNG transparent, dans
                 la police des titres. Le client le remplace en un clic
                 (bloc Logo du site → Remplacer).
  favicon        icône du site (512 × 512, le minimum de WordPress) : la
                 première lettre de la marque, dans la police des titres,
                 centrée sur un fond uni. Couleurs = la paire accent /
                 sur-accent de la palette — déjà contrastée par construction :
                 charte claire à accent sombre → fond sombre, lettre claire ;
                 accent clair → l'inverse. Le client la remplace dans
                 Réglages → Général → Icône du site.
  placeholders   déclinaisons du placeholder par format, à partir du carré
                 fourni (assets/placeholders/placeholder-1x1.png).

Exemples :
  python3 visuels.py vignette --marque "Maison Lumen" --sortie theme/screenshot.png
  python3 visuels.py logo --marque "Maison Lumen" --police projet/polices-ttf/titres.ttf \
          --couleur "#0A1124" --sortie theme/assets/img/logo.png
  python3 visuels.py favicon --marque "Maison Lumen" --police projet/polices-ttf/titres.ttf \
          --fond "#111111" --lettre "#FFFFFF" --sortie theme/assets/img/favicon.png
  python3 visuels.py placeholders --source ../assets/placeholders/placeholder-1x1.png \
          --dossier theme/assets/img

Pillow est requis. S'il manque, le script crée un environnement isolé dans le
dossier temporaire et l'y installe (une seule fois, ~5 s, réseau nécessaire) :
le Python de macOS n'embarque pas Pillow, et `pip install` y est souvent refusé.
"""
import os
import subprocess
import sys
import tempfile

ICI = os.path.dirname(os.path.abspath(__file__))
POLICES_VIGNETTE = os.path.join(ICI, '..', 'assets', 'vignette')


def assurer_pillow():
    try:
        import PIL  # noqa: F401
        return
    except ImportError:
        pass
    venv = os.path.join(tempfile.gettempdir(), 'c2woo-venv')
    py = os.path.join(venv, 'bin', 'python')
    if os.environ.get('C2WOO_VENV') == '1':
        sys.exit('Pillow introuvable même dans ' + venv + ' — installer Pillow à la main.')
    if not os.path.exists(py):
        print('Pillow absent : création de ' + venv + ' …', file=sys.stderr)
        subprocess.check_call([sys.executable, '-m', 'venv', venv])
    subprocess.check_call([py, '-m', 'pip', 'install', '-q', '--disable-pip-version-check', 'pillow'])
    os.environ['C2WOO_VENV'] = '1'
    os.execv(py, [py] + sys.argv)


assurer_pillow()
from PIL import Image, ImageDraw, ImageFont  # noqa: E402


def encre(police, texte):
    """Boîte d'encre réelle du texte (x0, y0, x1, y1) pour une origine en (0, 0)."""
    return ImageDraw.Draw(Image.new('L', (1, 1))).textbbox((0, 0), texte, font=police, anchor='ls')


def hex_rgb(h):
    h = h.lstrip('#')
    if len(h) == 3:
        h = ''.join(c * 2 for c in h)
    return tuple(int(h[i:i + 2], 16) for i in (0, 2, 4))


# ---------------------------------------------------------------- vignette
# Géométrie relevée sur la vignette de référence (1200 × 900) :
# « Nom de la marque » en Inter Display Bold, encre de 738 px de large, centrée ;
# « Ecommerce » en Inter Display Light, aligné sur le bord droit de la première ligne,
# 13 px d'écart d'encre entre les deux lignes, bloc centré verticalement.
LARGEUR, HAUTEUR = 1200, 900
TAILLE_MARQUE = 89          # px — Inter Display Bold : 738 px d'encre, comme la référence
RAPPORT_SOUS_TITRE = 0.55   # « Ecommerce » en Inter Display Light 49 px pour une marque à 89 px
MARGE_MIN = 110             # la marque ne s'approche jamais plus près du bord


def vignette(marque, sortie, sous_titre='Ecommerce', fond='#000000', texte='#FFFFFF'):
    gras = os.path.join(POLICES_VIGNETTE, 'InterDisplay-Bold.ttf')
    maigre = os.path.join(POLICES_VIGNETTE, 'InterDisplay-Light.ttf')

    taille = TAILLE_MARQUE
    while True:
        p1 = ImageFont.truetype(gras, taille)
        b1 = encre(p1, marque)
        if (b1[2] - b1[0]) <= LARGEUR - 2 * MARGE_MIN or taille <= 36:
            break
        taille -= 2
    p2 = ImageFont.truetype(maigre, max(18, round(taille * RAPPORT_SOUS_TITRE)))
    b2 = encre(p2, sous_titre)

    l1, l2 = b1[2] - b1[0], b2[2] - b2[0]
    bloc = max(l1, l2)
    droite = (LARGEUR + bloc) / 2          # bord droit commun aux deux lignes
    ecart = round(taille * 0.146)          # 13 px à la taille de référence

    # hauteur d'encre du bloc : de l'encre haute de la ligne 1 à l'encre basse de la ligne 2
    h1 = b1[3] - b1[1]
    h2 = b2[3] - b2[1]
    haut = (HAUTEUR - (h1 + ecart + h2)) / 2 + 6   # la référence est 6 px sous le centre

    img = Image.new('RGB', (LARGEUR, HAUTEUR), hex_rgb(fond))
    d = ImageDraw.Draw(img)
    base1 = haut - b1[1]
    d.text((droite - b1[2], base1), marque, font=p1, fill=hex_rgb(texte), anchor='ls')
    base2 = haut + h1 + ecart - b2[1]
    d.text((droite - b2[2], base2), sous_titre, font=p2, fill=hex_rgb(texte), anchor='ls')
    os.makedirs(os.path.dirname(os.path.abspath(sortie)), exist_ok=True)
    img.save(sortie, optimize=True)
    print(sortie, '· marque', taille, 'px ·', l1, 'px de large')


# ---------------------------------------------------------------- logo
def logo(marque, police, sortie, couleur='#000000', taille=96, interlettre=0.0, marge=6):
    """Nom de la marque en PNG transparent, rogné à l'encre.
    taille : corps de rendu en px (rendu à 3× pour les écrans denses, donc
    96 donne un logo net jusqu'à ~32 px de corps affiché)."""
    p = ImageFont.truetype(police, taille)
    if interlettre:
        # interlettrage en em, appliqué caractère par caractère
        avance = [p.getlength(c) + interlettre * taille for c in marque]
        largeur = int(sum(avance) + taille)
    else:
        largeur = int(p.getlength(marque) + taille)
    hauteur = int(taille * 2)
    img = Image.new('RGBA', (largeur, hauteur), (0, 0, 0, 0))
    d = ImageDraw.Draw(img)
    x, base = taille // 2, int(taille * 1.4)
    rgb = hex_rgb(couleur)
    if interlettre:
        for c, a in zip(marque, avance):
            d.text((x, base), c, font=p, fill=rgb + (255,), anchor='ls')
            x += a
    else:
        d.text((x, base), marque, font=p, fill=rgb + (255,), anchor='ls')
    boite = img.getbbox()
    if boite:
        img = img.crop((max(0, boite[0] - marge), max(0, boite[1] - marge),
                        min(largeur, boite[2] + marge), min(hauteur, boite[3] + marge)))
    os.makedirs(os.path.dirname(os.path.abspath(sortie)), exist_ok=True)
    img.save(sortie, optimize=True)
    print(sortie, '·', img.size[0], '×', img.size[1], 'px')


# ---------------------------------------------------------------- favicon
def favicon(marque, police, sortie, fond='#111111', lettre='#FFFFFF', cote=512, arrondi=0.0, part=0.62):
    """La première lettre de la marque, centrée À L'ENCRE (pas à la boîte de la
    police : un « Q » ou un « J » descendent sous la ligne de base).
    arrondi : rayon des angles en fraction du côté (0 = carré, 0.5 = rond).
    part    : hauteur d'encre visée de la lettre, en fraction du côté."""
    initiale = next((c for c in marque if c.isalnum()), 'A').upper()
    img = Image.new('RGBA', (cote, cote), (0, 0, 0, 0))
    d = ImageDraw.Draw(img)
    r = int(cote * max(0.0, min(arrondi, 0.5)))
    d.rounded_rectangle((0, 0, cote - 1, cote - 1), radius=r, fill=hex_rgb(fond) + (255,))
    # corps choisi pour que la hauteur d'encre de la lettre vaille `part` du côté
    taille = cote
    for _ in range(40):
        p = ImageFont.truetype(police, taille)
        b = encre(p, initiale)
        h = b[3] - b[1]
        if h <= cote * part:
            break
        taille = int(taille * (cote * part) / max(h, 1))
    p = ImageFont.truetype(police, taille)
    b = encre(p, initiale)
    x = (cote - (b[2] - b[0])) / 2 - b[0]
    y = (cote - (b[3] - b[1])) / 2 - b[1]
    d.text((x, y), initiale, font=p, fill=hex_rgb(lettre) + (255,), anchor='ls')
    os.makedirs(os.path.dirname(os.path.abspath(sortie)), exist_ok=True)
    img.save(sortie, optimize=True)
    print(sortie, '·', initiale, '·', cote, '×', cote, 'px · fond', fond, '· lettre', lettre)


# ---------------------------------------------------------------- placeholders
# Le carré fourni : fond plat #F4F4F4, icône centrée qui occupe 33,1 % du côté.
# Les autres formats gardent la même icône à 33,1 % du petit côté.
FORMATS = {
    '1x1': (1280, 1280), '4x5': (1200, 1500), '3x4': (1200, 1600), '2x3': (1200, 1800),
    '4x3': (1600, 1200), '3x2': (1800, 1200), '16x9': (1920, 1080), '21x9': (2520, 1080),
}


def placeholders(source, dossier):
    src = Image.open(source).convert('RGB')
    fond = src.getpixel((0, 0))
    icone = src.crop(src.convert('L').point(lambda v: 255 if abs(v - fond[0]) > 6 else 0).getbbox())
    part = icone.size[0] / src.size[0]
    os.makedirs(dossier, exist_ok=True)
    for nom, (w, h) in FORMATS.items():
        img = Image.new('RGB', (w, h), fond)
        cote = round(min(w, h) * part)
        ic = icone.resize((cote, cote), Image.LANCZOS)
        img.paste(ic, ((w - cote) // 2, (h - cote) // 2))
        chemin = os.path.join(dossier, 'placeholder-%s.png' % nom)
        img.save(chemin, optimize=True)
        print(chemin, w, '×', h)


if __name__ == '__main__':
    import argparse
    ap = argparse.ArgumentParser(description=__doc__, formatter_class=argparse.RawDescriptionHelpFormatter)
    sp = ap.add_subparsers(dest='cmd', required=True)
    v = sp.add_parser('vignette')
    v.add_argument('--marque', required=True)
    v.add_argument('--sortie', required=True)
    v.add_argument('--sous-titre', default='Ecommerce')
    lo = sp.add_parser('logo')
    lo.add_argument('--marque', required=True)
    lo.add_argument('--police', required=True, help='fichier .ttf ou .otf (pas de woff2)')
    lo.add_argument('--sortie', required=True)
    lo.add_argument('--couleur', default='#000000')
    lo.add_argument('--taille', type=int, default=96)
    lo.add_argument('--interlettre', type=float, default=0.0, help='en em, ex. -0.02')
    fa = sp.add_parser('favicon')
    fa.add_argument('--marque', required=True)
    fa.add_argument('--police', required=True, help='la police des titres, .ttf ou .otf')
    fa.add_argument('--sortie', required=True)
    fa.add_argument('--fond', default='#111111', help='rôle accent de la palette')
    fa.add_argument('--lettre', default='#FFFFFF', help='rôle sur-accent de la palette')
    fa.add_argument('--arrondi', type=float, default=0.0, help='0 = carré, 0.5 = rond ; suivre la géométrie de la référence')
    pl = sp.add_parser('placeholders')
    pl.add_argument('--source', default=os.path.join(ICI, '..', 'assets', 'placeholders', 'placeholder-1x1.png'))
    pl.add_argument('--dossier', required=True)
    a = ap.parse_args()
    if a.cmd == 'vignette':
        vignette(a.marque, a.sortie, a.sous_titre)
    elif a.cmd == 'logo':
        logo(a.marque, a.police, a.sortie, a.couleur, a.taille, a.interlettre)
    elif a.cmd == 'favicon':
        favicon(a.marque, a.police, a.sortie, a.fond, a.lettre, arrondi=a.arrondi)
    else:
        placeholders(a.source, a.dossier)
