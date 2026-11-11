#!/usr/bin/env python3
"""
Assemble deploiement.json en un seul paquet autonome.

Toute valeur de chaîne qui commence par « @ » est un chemin relatif au dossier
de deploiement.json : le fichier est lu et son contenu remplace la référence.
C'est ce qui permet d'écrire les contenus de pages dans des fichiers séparés
(contenus/accueil.html…) tout en n'envoyant qu'UN fichier au serveur.

Usage :  python3 paquet.py projet/deploiement.json > projet/.atelier/paquet.json

Le même paquet sert à l'atelier (monté dans Playground) et à la production
(téléversé par novamira/create-upload-link) : c'est la garantie que la
répétition générale joue exactement ce qui partira chez le client.
"""
import json
import os
import sys


def resoudre(v, base):
    if isinstance(v, str) and v.startswith('@'):
        chemin = os.path.join(base, v[1:])
        with open(chemin, encoding='utf-8') as f:
            return f.read()
    if isinstance(v, list):
        return [resoudre(x, base) for x in v]
    if isinstance(v, dict):
        return {k: resoudre(x, base) for k, x in v.items()}
    return v


if __name__ == '__main__':
    if len(sys.argv) != 2:
        sys.exit(__doc__)
    source = os.path.abspath(sys.argv[1])
    with open(source, encoding='utf-8') as f:
        donnees = json.load(f)
    json.dump(resoudre(donnees, os.path.dirname(source)), sys.stdout, ensure_ascii=False)
