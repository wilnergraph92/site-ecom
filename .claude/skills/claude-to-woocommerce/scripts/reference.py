#!/usr/bin/env python3
"""
Isole la page d'une image de référence : retire le cadre de présentation.

Une capture trouvée sur Pinterest, Dribbble, Behance ou une galerie de sites
est souvent posée dans un cadre fait pour la montrer joliment : une bande
unie autour de la page (sombre, colorée), des coins arrondis, une ombre.
Ce cadre n'appartient pas au design : sa couleur n'est pas une couleur du site,
sa marge n'est pas une découpe en blocs encartés, ses coins arrondis ne sont
pas le rayon du site. Relever les invariants dessus, c'est livrer un autre site.

  python3 reference.py <image jointe> <dossier> [--forcer]

Écrit dans <dossier> :
  originale.<ext>   l'image telle qu'elle a été envoyée ;
  page.png          la page seule, cadre retiré — c'est elle qu'on relève,
                    qu'on rouvre à chaque porte de registre et qu'on compare.

Affiche un JSON : le cadre détecté (couleur, épaisseur de chaque côté),
les coins arrondis de présentation, la taille de la page, le facteur
d'échelle vers 1440 px, et les avertissements. Le script ne décide pas seul :
toujours regarder page.png avant de relever les invariants. Un collage de
plusieurs écrans, un appareil (téléphone, ordinateur), une fenêtre de
navigateur ou une mise en perspective se traitent à l'œil (voir phases.md).

Prudence voulue : une bande n'est retirée que si sa couleur tranche nettement
avec le fond de la page (sombre autour d'une page claire, couleur franche).
Une bande proche du fond peut être un cadre clair (gris de Dribbble autour
d'une page blanche) OU les propres marges de la page (blocs encartés) : le
script ne retire rien et le dit. Trancher à l'œil ; si c'est un cadre,
relancer avec --forcer.

Pillow est requis ; s'il manque, il s'installe comme pour visuels.py.
"""
import json
import math
import os
import shutil
import subprocess
import sys
import tempfile


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
from PIL import Image, ImageStat  # noqa: E402

TOL_CADRE = 6        # écart max (par canal) entre une ligne du cadre et sa couleur
DISPERSION = 10      # écart-type max d'une ligne unie (bruit de compression compris)
TRANSITION = 3       # lignes d'ombre ou d'anticrénelage tolérées après le cadre
TOL_TRANSITION = 30
CONTRASTE = 24       # écart minimal entre le cadre et le fond de la page pour retirer d'office


def ecart(a, b):
    return max(abs(x - y) for x, y in zip(a[:3], b[:3]))


def hexa(c):
    return '#%02X%02X%02X' % tuple(int(round(v)) for v in c[:3])


def stat(im, boite):
    s = ImageStat.Stat(im.crop(boite))
    return s.mean[:3], s.stddev[:3]


def boite_ligne(cote, i, l, h):
    return {
        'gauche': (i, 0, i + 1, h),
        'droite': (l - 1 - i, 0, l - i, h),
        'haut': (0, i, l, i + 1),
        'bas': (0, h - 1 - i, l, h - i),
    }[cote]


def epaisseur(im, cote, couleur):
    """Lignes de cadre depuis le bord `cote`, ombre et anticrénelage compris."""
    l, h = im.size
    limite = (l if cote in ('gauche', 'droite') else h) // 4
    n = 0
    while n < limite:
        moy, sd = stat(im, boite_ligne(cote, n, l, h))
        if ecart(moy, couleur) <= TOL_CADRE and max(sd) <= DISPERSION:
            n += 1
        else:
            break
    if n == 0:
        return 0
    t = 0
    while t < TRANSITION and n < limite:
        moy, sd = stat(im, boite_ligne(cote, n, l, h))
        if ecart(moy, couleur) <= TOL_TRANSITION and max(sd) <= DISPERSION + 4:
            n += 1
            t += 1
        else:
            break
    return n


def couleur_dominante(im):
    petit = im.resize((60, max(1, round(60 * im.height / im.width))))
    q = petit.quantize(colors=12)
    palette = q.getpalette()
    compte, indice = max(q.getcolors())
    return tuple(palette[indice * 3:indice * 3 + 3])


def rayon_coins(page, couleur):
    """Rayon approximatif des coins arrondis de présentation (0 si angles droits)."""
    l, h = page.size
    rayons = []
    for (x0, y0, dx, dy) in ((0, 0, 1, 1), (l - 1, 0, -1, 1), (0, h - 1, 1, -1), (l - 1, h - 1, -1, -1)):
        d = 0
        while d < min(l, h) // 8 and ecart(page.getpixel((x0 + dx * d, y0 + dy * d)), couleur) <= 12:
            d += 1
        r = round(d / (1 - 1 / math.sqrt(2))) if d >= 2 else 0
        # un vrai coin arrondi laisse la page reprendre le long des bords, juste après
        # l'arrondi ; une photo sombre dans l'angle, elle, reste sombre le long du bord
        if r and r + 2 < min(l, h):
            bord_h = page.getpixel((x0 + dx * (r + 2), y0))
            bord_v = page.getpixel((x0, y0 + dy * (r + 2)))
            if ecart(bord_h, couleur) <= 20 or ecart(bord_v, couleur) <= 20:
                r = 0
        rayons.append(r)
    return max(rayons)


def main():
    args = [a for a in sys.argv[1:] if a != '--forcer']
    forcer = '--forcer' in sys.argv[1:]
    if len(args) != 2:
        sys.exit(__doc__)
    source, dossier = args
    os.makedirs(dossier, exist_ok=True)
    ext = os.path.splitext(source)[1].lower() or '.png'
    shutil.copyfile(source, os.path.join(dossier, 'originale' + ext))

    im = Image.open(source).convert('RGB')
    l, h = im.size
    rapport = {'originale': [l, h], 'cadre': None, 'avertissements': []}

    coins = [stat(im, b)[0] for b in ((0, 0, 3, 3), (l - 3, 0, l, 3), (0, h - 3, 3, h), (l - 3, h - 3, l, h))]
    couleur = tuple(sum(c[i] for c in coins) / 4 for i in range(3))
    cotes = {'gauche': 0, 'droite': 0, 'haut': 0, 'bas': 0}
    if max(ecart(a, b) for a in coins for b in coins) <= 12:
        cotes = {c: epaisseur(im, c, couleur) for c in cotes}
    else:
        rapport['avertissements'].append(
            "coins de couleurs différentes : pas de cadre uni détecté — vérifier à l'œil "
            "(appareil, fenêtre de navigateur, collage, dégradé)")

    boite = (cotes['gauche'], cotes['haut'], l - cotes['droite'], h - cotes['bas'])
    page = im.crop(boite) if any(cotes.values()) else im

    if any(cotes.values()):
        dominante = couleur_dominante(page)
        if ecart(dominante, couleur) < CONTRASTE and not forcer:
            rapport['avertissements'].append(
                'bande ' + hexa(couleur) + ' autour de la page, proche de son fond ' + hexa(dominante) + ' : '
                "marges de la page (blocs encartés) ou cadre clair ? Rien n'est retiré. Trancher à l'œil : "
                'un cadre entoure la page entière, et les bandes pleine largeur de la page (héros, sections '
                'colorées, pied) s\'arrêtent net à son bord ; si c\'est un cadre, relancer avec --forcer')
            rapport['bande_ambigue'] = {'couleur': hexa(couleur), **cotes}
            cotes = {c: 0 for c in cotes}
            page = im
        else:
            rapport['cadre'] = {'couleur': hexa(couleur), **cotes,
                                'fond_de_la_page': hexa(dominante)}
            r = rayon_coins(page, couleur)
            if r:
                rapport['cadre']['coins_arrondis'] = r
                rapport['avertissements'].append(
                    'coins arrondis de présentation (≈ ' + str(r) + ' px) : ils ne donnent pas le rayon du site')

    pl, ph = page.size
    rapport['page'] = [pl, ph]
    rapport['vers_1440'] = round(1440 / pl, 3)
    if pl < 900:
        rapport['avertissements'].append(
            'page en basse définition (' + str(pl) + ' px de large) : relever les invariants sur ce qui se lit, '
            "ne pas inventer les détails ; une mesure × " + str(rapport['vers_1440']) + ' donne son équivalent à 1440 px')
    if rapport['cadre'] is None and not rapport['avertissements']:
        rapport['avertissements'].append('aucun cadre uni détecté : la page est prise telle quelle')

    sortie = os.path.join(dossier, 'page.png')
    page.save(sortie)
    rapport['page_isolee'] = sortie
    print(json.dumps(rapport, ensure_ascii=False, indent=1))


if __name__ == '__main__':
    main()
