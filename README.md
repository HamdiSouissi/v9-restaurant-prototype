# BOMBA — Street Food Italiano (Parma)

Prototype commercial **une page** pour une marque fictive de street food italienne.
Objectif : le montrer à des restaurants de Parme qui n'ont pas de site et leur vendre
le résultat. Look campagne streetwear, fait main, jamais « généré ».

Tout est **simulé** : aucune commande, aucun paiement, aucune réservation réelle,
aucune base de données. Le PHP rend la page et trois endpoints JSON renvoient des
réponses déterministes.

---

## Lancer le site

PHP 8.1 ou plus récent, aucune extension obligatoire, aucun build :

```bash
php -S localhost:8000
# puis ouvrir http://localhost:8000
```

Un sous-dossier `/api` répond en JSON (`order.php`, `booking.php`, `slots.php`).
Il n'y a **aucune autre page** : tout vit dans `index.php`.

Les librairies arrivent en CDN (aucune installation) : GSAP 3 + ScrollTrigger +
Draggable + InertiaPlugin + SplitText, Lenis, Swiper 11, Matter.js.
Pas de jQuery, pas de Bootstrap, pas de Tailwind — le CSS est écrit à la main
(`assets/css/style.css`, ~2 470 lignes, variables CSS, mobile-first).

Si un CDN tombe, le site continue de fonctionner : chaque brique JS démarre dans
son propre `try/catch`, le panier, le menu, la réservation et la galerie restent
utilisables, les rubans horizontaux retombent sur le scroll natif
(`html.no-lenis`).

---

## Architecture

```
index.php                 assemblage + helpers (e(), bomba_money(), bomba_cutout(), …)
config.php                LA source unique : marque, couleurs, menu, horaires, livraison…
partials/
  head.php                polices, meta/OG, JSON-LD Restaurant, couleurs dans :root
  preloader.php           ≤ 1,8 s, une fois par session (sessionStorage)
  header.php              barre « ticket » fixe + statut ouvert/fermé + nav mobile
  hero.php                titre cinétique, collage détouré parallaxe, canvas spray, néon
  ticker.php              deux rubalises diagonales pilotées par la vélocité Lenis
  bestsellers.php         défilement horizontal épinglé + posters cliquables
  menu.php                menu façon scontrino : onglets, filtres, impression, aperçu
  combo.php               configurateur 3 étapes + plateau où les plats s'empilent
  story.php               polaroid, scotch, tampons, compteurs animés
  booking.php             calendrier maison + créneaux /api/slots.php + billet
  gallery.php             mur de polaroid déplaçables + lightbox accessible
  reviews.php             avis + note 4,8
  info.php                horaires, adresse, carte SVG dessinée à la main
  footer.php              arène à stickers (physique Matter.js) + newsletter
  cart-drawer.php         sacchetto kraft : Consegna/Asporto, frais, minimum
  checkout-modal.php      caisse 3 étapes → scontrino imprimé → suivi du scooter
  toast.php               toasts, barre mobile fixe, pastille « Demo »
  stickers-svg.php        47 <symbol> SVG dessinés à la main (aucun clip-art)
api/
  slots.php               GET  ?date=AAAA-MM-JJ[&guests=N] → créneaux free/last/full
  order.php               POST {…items} → { code: "BMB-####", lines, subtotal, fee, total }
  booking.php             POST {…}      → { code: "BMB-T-####", date_ui, ics }
assets/css/style.css      tout le style, 26 sections numérotées
assets/js/                main, kinetic, spray, stickers, cart, combo, booking, gallery
assets/img/               photos + cutouts/ (détourés) + piatti/ (plats)
```

### Changer la marque en 30 secondes

`config.php` renvoie un tableau : `brand`, `colors`, `contact`, `hours`, `socials`,
`menu`, `bestsellers`, `combo`, `gallery`, `reviews`, `booking`, `delivery`,
`stickers`, `demo_badge`, `hero`, `cdn`.

* changer `brand` → nom, baseline, ville, tone of voice, textes du hero ;
* changer `colors` → toute la palette (injectée dans `:root` par `head.php`,
  sections dominantes alternées) ;
* changer `menu` → le menu rendu en PHP **et** `window.MENU` consommé par le JS
  (panier, combo, prix recalculés côté serveur par `api/order.php`).

Toutes les sorties sont échappées avec `htmlspecialchars()` via `e()`.

---

## Ce qu'il faut cliquer pendant la démo

1. **Preloader** rouge « BOMBA » qui respire, étoile qui éclate, page révélée en
   `clip-path: circle()` — une seule fois par session.
2. **Hero** : bouger la souris → spray de peinture sur canvas ; le titre change de graisse et de largeur au scroll ; les plats détourés suivent la souris en parallaxe ;
   cliquer un plat → miettes + ajout au sacchetto.
3. **Rubalise** : scroller vite, les deux bandes diagonales accélèrent en sens
   opposés, ralentissent au survol.
4. **Best-sellers** : la section s'épingle et défile à l'horizontale (desktop).
5. **Menu scontrino** : onglets (le ticket se réimprime ligne par ligne), filtres
   allergènes, survol d'une ligne → le plat apparaît en aperçu sous le curseur,
   boutons `– 0 +` synchronisés avec le sacchetto.
6. **Combo** : choisir 3 éléments → ils volent vers le plateau et s'empilent en
   rebond, prix barré € 17 → € 13,90, « RISPARMI € 3,10 ».
7. **Sacchetto** (icône en haut à droite) : kraft, bascule Consegna/Asporto, frais
   € 1,90 offerts dès 20 €, minimum 10 €, suppression avec **Annulla** 5 s,
   persisté en `localStorage`.
8. **Caisse** : 3 étapes validées champ par champ, récap, puis le **scontrino**
   s'imprime du haut de l'écran, le tampon **PAGATO** s'écrase, code `BMB-####`,
   suivi avec le scooter qui avance (démo accélérée).
9. **Prenota** : calendrier (lundis fermés, passé grisé), créneaux servis par
   `/api/slots.php` (`full` barré, `last` signalé), validation, billet détachable
   avec code `BMB-T-####`, faux QR dessiné en SVG et **vrai fichier `.ics`**.
10. **Galleria** : traîner les polaroid à la souris (inertie), `GUARDA` au survol,
    lightbox plein écran (flèches, Échap, swipe, focus piégé, compteur).
11. **Footer** : l'arène à stickers Matter.js — les stickers tombent, s'empilent,
    se font pousser ; « PRENDIMI » au survol.

---

## Accessibilité & photosensibilité

* `lang="it"`, HTML sémantique, chaque `section` a son `aria-labelledby`.
* Navigation clavier complète, contour de focus lime 3 px + ombre décalée,
  focus piégé puis rendu dans le tiroir, la caisse, la nav mobile et la lightbox,
  Échap partout.
* **Tout le texte visible est en italien** (y compris les libellés `aria-*`,
  les placeholders et les messages générés en JS).
* Aucun clignotement au-dessus de 3 Hz : le néon fait 2 à 3 micro-coupures
  toutes les 6 à 10 s (`neonFlicker 9s`), rien d'autre ne clignote.
* `prefers-reduced-motion` : pas de spray, pas de physique, pas de rubalise,
  typographie figée en `wdth 100`, fondus simples, preloader raccourci.
* Contrastes AA : le texte papier sur lime est interdit (encre sur lime),
  le rouge de section utilise une variante assombrie `--rosso-deep`.
* Les éléments décoratifs sont `aria-hidden="true"`, les images ont `alt`,
  `width`/`height` et `loading="lazy"` (sauf les deux premières, above the fold).
* Canvas spray et moteur Matter.js mis en pause hors écran.

---

## Images

10 photos sont en place (`hero-bg`, `crew`, `locale-01…03`, `bancone`, `cucina`,
`notte`, `furgone`, `piatti/fritti`). Les **détourés de plats sont 19 SVG vectoriels
écrits à la main** (`assets/img/cutouts/*.svg`) : contours vinyle blanc, ombres
décalées, aucun clip-art.

Pour remplacer un visuel par une vraie photo, il suffit de déposer le fichier avec
le même slug — la résolution se fait dans `bomba_cutout_src()` :

```
assets/img/cutouts/suppli.png   (ou .webp, .jpg, .jpeg, .svg)
assets/img/piatti/suppli.jpg
```

Priorité : `png → webp → jpg → jpeg → svg`. Si aucun fichier n'existe, le site
affiche un bloc à la couleur de la palette avec le nom du plat en grand —
**jamais d'image cassée** (aucune référence vide aujourd'hui : 37 assets locaux,
tous présents).

---

## Vérifications déjà passées

* `token_get_all(…, TOKEN_PARSE)` sur les 23 fichiers PHP : aucune erreur.
* Rendu de la page : HTTP 200, aucun `Warning`/`Notice`/`Fatal`.
* `node --check` sur les 8 modules JS : OK.
* 81 assertions fonctionnelles (panier, quantités, combo, caisse 3 étapes,
  scontrino, suivi, calendrier, créneaux, billet, `.ics`, lightbox, filtres,
  navigation mobile) validées dans 4 configurations : avec/sans CDN ×
  mouvement normal/`prefers-reduced-motion`.
* `/api` : corps vide, XSS dans le nom, id inexistant, quantité 9999, prix
  trafiqué côté client, date passée, lundi fermé, date à plus de 6 mois →
  tous refusés avec un message italien et un champ à corriger ; les prix sont
  **recalculés côté serveur**.
