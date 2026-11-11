// Contrôle du menu mobile — boutique-woocommerce (phases 3b, 4, 5 et porte 3).
//
// Usage : régler le viewport (390 × 844, puis 768 × 1024, puis 1440 × 900), recharger la page,
// puis passer le contenu de ce fichier, tel quel, à l'outil JavaScript du navigateur.
// Il rend { ok, largeur, connecte, defauts: [...], mesures: {...} }.
// ok: false → corriger avant d'avancer. Chaque défaut dit ce qui cloche, en clair.
//
// ≤ 1024 px : le menu est replié ; il s'ouvre, ses liens sont décalés du bord et lisibles,
// la croix fait au moins 40 px, n'est recouverte par rien (barre d'admin, widget d'hébergeur),
// Échap et la croix ferment, la page défile de nouveau, et ce que l'en-tête masque figure dans le panneau.
// > 1024 px : menu en ligne sur une seule ligne, bouton masqué, liens « menu-mobile-seul » invisibles,
// rien ne se chevauche dans l'en-tête.
await (async () => {
  const pause = ms => new Promise(r => setTimeout(r, ms));
  const boite = e => { const r = e.getBoundingClientRect(); return [Math.round(r.x), Math.round(r.y), Math.round(r.width), Math.round(r.height)]; };
  const visible = e => {
    if (!e) return false;
    const s = getComputedStyle(e), r = e.getBoundingClientRect();
    return s.display !== 'none' && s.visibility !== 'hidden' && parseFloat(s.opacity) > 0 && r.width > 0 && r.height > 0;
  };
  const nom = e => !e ? 'rien' : e.tagName.toLowerCase() + (typeof e.className === 'string' && e.className.trim() ? '.' + e.className.trim().split(/\s+/)[0] : '') + (e.id ? '#' + e.id : '');
  const texte = e => (e.textContent || e.getAttribute('aria-label') || '').trim().replace(/\s+/g, ' ');
  const chemin = a => { try { const u = new URL(a.href, location.href); return u.origin + u.pathname.replace(/\/$/, ''); } catch (_) { return a.href; } };
  const chevauchements = elements => {
    const d = [];
    for (let i = 0; i < elements.length; i++) for (let j = i + 1; j < elements.length; j++) {
      const a = elements[i], b = elements[j];
      if (a.contains(b) || b.contains(a)) continue;
      const p = a.getBoundingClientRect(), q = b.getBoundingClientRect();
      if (p.left < q.right - 1 && q.left < p.right - 1 && p.top < q.bottom - 1 && q.top < p.bottom - 1) d.push(`chevauchement dans l'en-tête : ${nom(a)} « ${texte(a).slice(0, 24)} » / ${nom(b)} « ${texte(b).slice(0, 24)} »`);
    }
    return d;
  };

  scrollTo(0, 0); await pause(200);
  const L = innerWidth, defauts = [], mesures = {};
  const connecte = document.body.classList.contains('admin-bar');
  const barre = document.getElementById('wpadminbar');
  const navs = [...document.querySelectorAll('.wp-block-navigation')].filter(n => n.querySelector('.wp-block-navigation__responsive-container'));
  const nav = navs.find(n => n.closest('header')) || navs[0];
  const fin = () => JSON.stringify({ ok: defauts.length === 0, largeur: L, connecte, defauts, mesures });
  if (!nav) { defauts.push('aucun bloc Navigation avec panneau mobile dans la page'); return fin(); }

  const entete = nav.closest('header') || document.querySelector('header');
  const ouvrir = nav.querySelector('.wp-block-navigation__responsive-container-open');
  const panneau = nav.querySelector('.wp-block-navigation__responsive-container');
  const premierNiveau = () => [...panneau.querySelectorAll('.wp-block-navigation__container > .wp-block-navigation-item')].filter(visible);
  const dansEntete = sel => entete ? [...entete.querySelectorAll(sel)].filter(visible) : [];

  mesures.sw = document.documentElement.scrollWidth;
  if (mesures.sw > L) defauts.push(`défilement horizontal : scrollWidth ${mesures.sw} > ${L}`);

  // ---------- Ordinateur ----------
  if (L > 1024) {
    if (visible(ouvrir)) defauts.push('bouton du menu visible en ordinateur');
    const seuls = [...nav.querySelectorAll('.menu-mobile-seul')].filter(visible);
    if (seuls.length) defauts.push(`${seuls.length} lien(s) « menu-mobile-seul » visibles en ordinateur`);
    const items = premierNiveau();
    mesures.liens = items.map(e => texte(e.querySelector('.wp-block-navigation-item__content') || e));
    if (new Set(items.map(e => Math.round(e.getBoundingClientRect().top))).size > 1)
      defauts.push("menu d'ordinateur sur plusieurs lignes : trop de liens pour l'en-tête → catégories en sous-menu du catalogue");
    defauts.push(...chevauchements([...dansEntete('.wp-block-site-logo, .wp-block-site-title, .wp-block-woocommerce-mini-cart, .wp-block-woocommerce-customer-account, .wp-block-buttons'), ...items]));
    return fin();
  }

  // ---------- Mobile et tablette : menu replié ----------
  if (!visible(ouvrir)) { defauts.push(`bouton du menu invisible à ${L} px : le menu doit être replié jusqu'à 1024 px`); return fin(); }
  mesures.bouton = boite(ouvrir);
  const centre = mesures.bouton[0] + mesures.bouton[2] / 2;
  if (centre > L / 3 && centre < L * 2 / 3) defauts.push(`bouton du menu isolé au milieu de l'en-tête (x = ${mesures.bouton[0]} px) : le placer à gauche du logo ou à droite des actions`);
  if (Math.min(ouvrir.getBoundingClientRect().width, ouvrir.getBoundingClientRect().height) < 40) defauts.push(`bouton du menu trop petit (${mesures.bouton[2]} × ${mesures.bouton[3]} px, viser 44)`);
  if (visible(panneau) && !panneau.classList.contains('is-menu-open')) defauts.push('liens du menu affichés en ligne alors que le bouton est visible');
  if (!dansEntete('.wp-block-woocommerce-mini-cart').length) defauts.push('panier invisible dans l\'en-tête mobile');
  defauts.push(...chevauchements([ouvrir, ...dansEntete('.wp-block-site-logo, .wp-block-site-title, .wp-block-woocommerce-mini-cart, .wp-block-woocommerce-customer-account, .wp-block-buttons')]));

  // Ce que l'en-tête masque à cette largeur doit figurer dans le panneau.
  const masques = entete ? [...entete.querySelectorAll('a[href]')].filter(a => !nav.contains(a)
    && !a.closest('.wp-block-site-logo, .wp-block-site-title, .wp-block-woocommerce-mini-cart, .wp-block-woocommerce-customer-account')
    && !a.getAttribute('href').startsWith('#') && !visible(a)) : [];

  // Ouverture
  ouvrir.click(); await pause(900);
  if (!panneau.classList.contains('is-menu-open')) { defauts.push("le menu ne s'ouvre pas"); return fin(); }
  const croix = panneau.querySelector('.wp-block-navigation__responsive-container-close');
  const rc = croix.getBoundingClientRect();
  mesures.croix = boite(croix);
  if (Math.min(rc.width, rc.height) < 40) defauts.push(`croix trop petite (${mesures.croix[2]} × ${mesures.croix[3]} px, viser 44)`);
  if (rc.left < 0 || rc.right > L) defauts.push('croix hors de l\'écran');
  const dessus = document.elementFromPoint(rc.x + rc.width / 2, rc.y + rc.height / 2);
  if (dessus?.closest('button') !== croix) defauts.push(`croix recouverte par ${nom(dessus)} : impossible de fermer le menu`);
  if (connecte && visible(barre) && rc.top < barre.getBoundingClientRect().bottom) defauts.push("croix sous la barre d'admin");

  const sp = getComputedStyle(panneau);
  mesures.fond = sp.backgroundColor;
  if (/^(transparent|rgba\(0, 0, 0, 0\))$/.test(sp.backgroundColor)) defauts.push('panneau transparent : la page se lit à travers');
  const rp = panneau.getBoundingClientRect();
  if (rp.width < L - 1) defauts.push(`panneau plus étroit que l'écran (${Math.round(rp.width)} px)`);

  const liens = [...panneau.querySelectorAll('.wp-block-navigation-item__content')].filter(visible);
  mesures.liens = liens.map(a => `${texte(a)} @${Math.round(a.getBoundingClientRect().left)}`);
  mesures.focus = panneau.contains(document.activeElement);
  if (!liens.length) defauts.push('aucun lien visible dans le panneau');
  else {
    const gauche = Math.min(...liens.map(a => a.getBoundingClientRect().left));
    const droite = Math.max(...liens.map(a => a.getBoundingClientRect().right));
    if (gauche < 12) defauts.push(`liens collés au bord gauche (x = ${Math.round(gauche)} px) : le panneau n'a pas de marges`);
    if (droite > L + 1) defauts.push('un lien dépasse à droite de l\'écran');
    const couleur = getComputedStyle(liens[0]).color;
    if (couleur === sp.backgroundColor) defauts.push('liens de la couleur du fond : illisibles');
    const petits = liens.filter(a => parseFloat(getComputedStyle(a).fontSize) < 16);
    if (petits.length) defauts.push(`${petits.length} lien(s) en dessous de 16 px`);
    const bas = premierNiveau().map(e => e.querySelector('.wp-block-navigation-item__content')).filter(Boolean).filter(a => a.getBoundingClientRect().height < 40);
    if (bas.length) defauts.push(`cibles tactiles trop basses : ${bas.map(texte).join(', ')} (viser 44 px)`);
    const plusBas = Math.max(...liens.map(a => a.getBoundingClientRect().bottom));
    if (plusBas > innerHeight && !/(auto|scroll)/.test(sp.overflowY)) defauts.push('derniers liens hors écran et panneau non défilable');
    const chemins = new Set(liens.map(chemin));
    masques.forEach(a => { if (!chemins.has(chemin(a))) defauts.push(`« ${texte(a)} » est masqué dans l'en-tête mobile et absent du menu (ajouter un lien « menu-mobile-seul »)`); });
  }

  // Fermeture à Échap (l'événement part de l'élément qui a le focus, comme au clavier)
  const cible = panneau.contains(document.activeElement) ? document.activeElement : croix;
  cible.dispatchEvent(new KeyboardEvent('keydown', { key: 'Escape', code: 'Escape', keyCode: 27, bubbles: true }));
  await pause(600);
  if (panneau.classList.contains('is-menu-open')) { defauts.push('Échap ne ferme pas le menu'); croix.click(); await pause(600); }

  // Fermeture à la croix
  ouvrir.click(); await pause(900);
  croix.click(); await pause(600);
  if (panneau.classList.contains('is-menu-open')) defauts.push('la croix ne ferme pas le menu');
  else {
    if (document.documentElement.classList.contains('has-modal-open')) defauts.push('page bloquée après fermeture (has-modal-open reste posé)');
    if (visible(panneau)) defauts.push('liens restés affichés après fermeture');
  }
  return fin();
})()
