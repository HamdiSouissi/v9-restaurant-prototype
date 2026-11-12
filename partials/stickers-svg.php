<?php

/**
 * Feuille de sprites SVG inline — dessinée à la main, dans le code.
 * ---------------------------------------------------------------------------
 *   #stk-*   stickers vinyle (contour blanc épais, ombre offset)
 *   #ico-*   icônes au trait (héritent de `currentColor`)
 *
 * Les détourés de plats vivent dans /assets/img/cutouts/ (SVG autonomes,
 * remplaçables par des PNG/WebP détourés : l'ordre de résolution est
 * png → webp → jpg → svg → bloc couleur avec le nom du plat).
 *
 * Les couleurs sont posées en attributs de présentation pour que le rendu
 * soit identique quel que soit le contexte (<use>, shadow tree, export).
 * Tout est décoratif → aria-hidden.
 */

declare(strict_types=1);
?>
<svg class="sprite" aria-hidden="true" focusable="false" width="0" height="0" style="position:absolute;overflow:hidden">
<defs>
    <!-- Tracés de texte pour les stickers ronds -->
    <path id="stk-ruota-track" d="M100,100 m-64,0 a64,64 0 1,1 128,0 a64,64 0 1,1 -128,0"/>
    <path id="stk-nonna-track-top" d="M100,100 m-58,0 a58,58 0 0,1 116,0"/>
    <path id="stk-nonna-track-bot" d="M100,100 m-58,0 a58,58 0 0,0 116,0"/>
</defs>

<symbol id="stk-stella-fritto" viewBox="0 0 200 200">
<path fill="#FFFFFF" stroke="#FFFFFF" stroke-width="13" stroke-linejoin="round" paint-order="stroke" d="M100 4l13 30 27-18-4 33 32-6-16 29 32 6-27 19 22 25-33-2-6 33-22-25-22 25-6-33-33 2 22-25-27-19 32-6-16-29 32 6-4-33 27 18z"/> <path fill="#FF3B1F" stroke="#151515" stroke-width="5" stroke-linejoin="round" paint-order="stroke" d="M100 4l13 30 27-18-4 33 32-6-16 29 32 6-27 19 22 25-33-2-6 33-22-25-22 25-6-33-33 2 22-25-27-19 32-6-16-29 32 6-4-33 27 18z"/> <text font-family="Archivo, Arial Black, sans-serif" font-weight="900" font-size="40" letter-spacing="-1" fill="#151515" x="100" y="94" text-anchor="middle">FRITTO</text> <text font-family="Archivo, Arial Black, sans-serif" font-weight="900" font-size="40" letter-spacing="-1" fill="#151515" x="100" y="128" text-anchor="middle">ORA</text></symbol>
<symbol id="stk-ruota-bomba" viewBox="0 0 200 200">
<circle fill="#FFFFFF" stroke="#FFFFFF" stroke-width="13" stroke-linejoin="round" paint-order="stroke" cx="100" cy="100" r="86"/> <circle fill="#151515" stroke="#151515" stroke-width="5" stroke-linejoin="round" paint-order="stroke" cx="100" cy="100" r="86"/> <circle fill="none" stroke="#F5F1E8" stroke-width="3" opacity="0.45" cx="100" cy="100" r="72"/> <text font-family="Archivo, Arial Black, sans-serif" font-weight="800" font-size="23" letter-spacing="3.4" fill="#F5F1E8"><textPath href="#stk-ruota-track" startOffset="0">BOMBA · PARMA · BOMBA · PARMA · </textPath></text> <path fill="#FFC21A" d="M100 62l9 22 24-8-6 24 24 8-24 8 6 24-24-8-9 22-9-22-24 8 6-24-24-8 24-8-6-24 24 8z"/></symbol>
<symbol id="stk-freccia" viewBox="0 0 200 200">
<path fill="none" stroke="#F5F1E8" stroke-width="13" stroke-linecap="round" stroke-linejoin="round" d="M18 152C56 152 92 128 118 92c14-20 24-40 30-58"/> <path fill="none" stroke="#F5F1E8" stroke-width="13" stroke-linecap="round" stroke-linejoin="round" d="M120 30l34 2-8 34"/></symbol>
<symbol id="stk-scribble" viewBox="0 0 200 200">
<path fill="none" stroke="#FF3B1F" stroke-width="11" stroke-linecap="round" stroke-linejoin="round" d="M10 118c30-24 56-24 84-14 26 10 48 12 76-4"/> <path fill="none" stroke="#FF3B1F" stroke-width="11" stroke-linecap="round" stroke-linejoin="round" d="M14 140c32-22 58-22 86-12 26 10 46 10 72-4"/></symbol>
<symbol id="stk-cerchio" viewBox="0 0 200 200">
<path fill="none" stroke="#FF3B1F" stroke-width="11" stroke-linecap="round" stroke-linejoin="round" d="M104 22c40 4 68 34 66 74-2 42-38 72-84 74-44 2-74-26-74-62 0-38 30-70 74-78 22-4 44-2 62 6 16 8 24 18 26 30"/></symbol>
<symbol id="stk-fulmine" viewBox="0 0 200 200">
<path fill="#FFFFFF" stroke="#FFFFFF" stroke-width="13" stroke-linejoin="round" paint-order="stroke" d="M118 6 46 112h40L70 194l86-112h-42z"/> <path fill="#FFC21A" stroke="#151515" stroke-width="5" stroke-linejoin="round" paint-order="stroke" d="M118 6 46 112h40L70 194l86-112h-42z"/></symbol>
<symbol id="stk-fiamma" viewBox="0 0 200 200">
<path fill="#FFFFFF" stroke="#FFFFFF" stroke-width="13" stroke-linejoin="round" paint-order="stroke" d="M104 8c30 34 56 52 56 92 0 40-28 72-62 72S38 140 38 102c0-26 12-42 26-52-4 22 4 34 14 38-6-30 8-58 26-80z"/> <path fill="#FF3B1F" stroke="#151515" stroke-width="5" stroke-linejoin="round" paint-order="stroke" d="M104 8c30 34 56 52 56 92 0 40-28 72-62 72S38 140 38 102c0-26 12-42 26-52-4 22 4 34 14 38-6-30 8-58 26-80z"/> <path fill="#FFC21A" d="M100 84c14 16 24 26 24 44 0 18-11 30-25 30s-25-12-25-30c0-12 6-20 12-25-1 10 3 16 7 18-3-14 1-27 7-37z"/></symbol>
<symbol id="stk-cuore" viewBox="0 0 200 200">
<path fill="#FFFFFF" stroke="#FFFFFF" stroke-width="13" stroke-linejoin="round" paint-order="stroke" d="M100 178S20 128 20 74c0-30 22-52 50-52 18 0 30 10 30 10s12-10 30-10c28 0 50 22 50 52 0 54-80 104-80 104z"/> <path fill="#FF4FA3" stroke="#151515" stroke-width="5" stroke-linejoin="round" paint-order="stroke" d="M100 178S20 128 20 74c0-30 22-52 50-52 18 0 30 10 30 10s12-10 30-10c28 0 50 22 50 52 0 54-80 104-80 104z"/></symbol>
<symbol id="stk-napoli-parma" viewBox="0 0 200 200">
<rect fill="#FFFFFF" stroke="#FFFFFF" stroke-width="13" stroke-linejoin="round" paint-order="stroke" x="10" y="52" width="180" height="96" rx="18" transform="rotate(-5 100 100)"/> <rect fill="#2F5BFF" stroke="#151515" stroke-width="5" stroke-linejoin="round" paint-order="stroke" x="10" y="52" width="180" height="96" rx="18" transform="rotate(-5 100 100)"/> <text font-family="Archivo, Arial Black, sans-serif" font-weight="900" font-size="30" letter-spacing="-1" fill="#F5F1E8" x="100" y="94" text-anchor="middle" transform="rotate(-5 100 100)">100%</text> <text font-family="Archivo, Arial Black, sans-serif" font-weight="900" font-size="22" letter-spacing="-1" fill="#F5F1E8" x="100" y="126" text-anchor="middle" transform="rotate(-5 100 100)">NAPOLI-PARMA</text></symbol>
<symbol id="stk-spicy" viewBox="0 0 200 200">
<path fill="#FFFFFF" stroke="#FFFFFF" stroke-width="13" stroke-linejoin="round" paint-order="stroke" d="M100 12 128 40l38-8-8 38 28 28-28 28 8 38-38-8-28 28-28-28-38 8 8-38-28-28 28-28-8-38 38 8z"/> <path fill="#FF3B1F" stroke="#151515" stroke-width="5" stroke-linejoin="round" paint-order="stroke" d="M100 12 128 40l38-8-8 38 28 28-28 28 8 38-38-8-28 28-28-28-38 8 8-38-28-28 28-28-8-38 38 8z"/> <text font-family="Archivo, Arial Black, sans-serif" font-weight="900" font-size="40" letter-spacing="-1" fill="#F5F1E8" x="100" y="112" text-anchor="middle">SPICY</text></symbol>
<symbol id="stk-aperti-2" viewBox="0 0 200 200">
<rect fill="#FFFFFF" stroke="#FFFFFF" stroke-width="13" stroke-linejoin="round" paint-order="stroke" x="8" y="58" width="184" height="84" rx="42" transform="rotate(4 100 100)"/> <rect fill="#FFC21A" stroke="#151515" stroke-width="5" stroke-linejoin="round" paint-order="stroke" x="8" y="58" width="184" height="84" rx="42" transform="rotate(4 100 100)"/> <text font-family="Archivo, Arial Black, sans-serif" font-weight="900" font-size="22" letter-spacing="-1" fill="#151515" x="100" y="94" text-anchor="middle" transform="rotate(4 100 100)">APERTI FINO</text> <text font-family="Archivo, Arial Black, sans-serif" font-weight="900" font-size="48" letter-spacing="-1" fill="#151515" x="100" y="128" text-anchor="middle" transform="rotate(4 100 100)">ALLE 2</text></symbol>
<symbol id="stk-nonna" viewBox="0 0 200 200">
<circle fill="none" stroke="#FF3B1F" stroke-width="9" cx="100" cy="100" r="88"/> <circle fill="none" stroke="#FF3B1F" stroke-width="3" stroke-dasharray="7 7" cx="100" cy="100" r="74"/> <text font-family="Archivo, Arial Black, sans-serif" font-weight="900" font-size="20" letter-spacing="3" fill="#FF3B1F"><textPath href="#stk-nonna-track-top" startOffset="50%" text-anchor="middle">APPROVATO</textPath></text> <text font-family="Archivo, Arial Black, sans-serif" font-weight="900" font-size="18" letter-spacing="3" fill="#FF3B1F"><textPath href="#stk-nonna-track-bot" startOffset="50%" text-anchor="middle">DALLA NONNA</textPath></text> <path fill="#FF3B1F" d="M100 74l7 16 17 2-13 12 4 17-15-9-15 9 4-17-13-12 17-2z"/></symbol>
<symbol id="ico-bag" viewBox="0 0 24 24">
<path fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M4 8h16l-1.4 12.2a1.8 1.8 0 0 1-1.8 1.6H7.2a1.8 1.8 0 0 1-1.8-1.6z"/> <path fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M8.5 8V6a3.5 3.5 0 0 1 7 0v2"/> <path fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M4.4 12.5 12 10l7.6 2.5"/></symbol>
<symbol id="ico-plus" viewBox="0 0 24 24">
<path fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/></symbol>
<symbol id="ico-minus" viewBox="0 0 24 24">
<path fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M5 12h14"/></symbol>
<symbol id="ico-close" viewBox="0 0 24 24">
<path fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6 6 18"/></symbol>
<symbol id="ico-arrow-ur" viewBox="0 0 24 24">
<path fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M7 17 17 7M9 7h8v8"/></symbol>
<symbol id="ico-arrow-up" viewBox="0 0 24 24">
<path fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M12 20V4M5 11l7-7 7 7"/></symbol>
<symbol id="ico-chev-l" viewBox="0 0 24 24">
<path fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M15 5 8 12l7 7"/></symbol>
<symbol id="ico-chev-r" viewBox="0 0 24 24">
<path fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></symbol>
<symbol id="ico-pepper" viewBox="0 0 24 24">
<path fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M15 5c3 2 4 6 1 11-3 5-8 6-11 4 3-1 5-3 6-6 2-4 3-7 4-9z"/> <path fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M15 5c1-2 3-3 5-2-1 2-3 3-5 3"/></symbol>
<symbol id="ico-star" viewBox="0 0 24 24">
<path fill="currentColor" stroke="none" d="m12 2 2.9 6.3 6.9.8-5 4.8 1.3 6.8L12 17.4 5.9 20.7 7.2 13.9l-5-4.8 6.9-.8z"/></symbol>
<symbol id="ico-check" viewBox="0 0 24 24">
<path fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="m4 13 5 5L20 6"/></symbol>
<symbol id="ico-flame" viewBox="0 0 24 24">
<path fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M12 2c4 4 7 6 7 11a7 7 0 0 1-14 0c0-3 2-5 3-6 0 3 1 4 2 5 0-4 1-7 2-10z"/></symbol>
<symbol id="ico-bell" viewBox="0 0 24 24">
<path fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M6 16V11a6 6 0 0 1 12 0v5l2 3H4z"/><path fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M10 19a2 2 0 0 0 4 0"/></symbol>
<symbol id="ico-scooter" viewBox="0 0 24 24">
<circle fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" cx="5.5" cy="17.5" r="3"/><circle fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" cx="18.5" cy="17.5" r="3"/> <path fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M8.5 17.5h7M15 6h2l1.5 8M15 6l-3 6H9"/> <path fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M9 12 7 17.5"/></symbol>
<symbol id="ico-card" viewBox="0 0 24 24">
<rect fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" x="2.5" y="5" width="19" height="14" rx="2.5"/><path fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M2.5 10h19"/></symbol>
<symbol id="ico-satispay" viewBox="0 0 24 24">
<rect fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" x="3" y="3" width="18" height="18" rx="5"/><path fill="currentColor" stroke="none" d="M8 12h8l-3-4M16 12l-3 4"/></symbol>
<symbol id="ico-cash" viewBox="0 0 24 24">
<rect fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" x="2.5" y="6" width="19" height="12" rx="2"/><circle fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" cx="12" cy="12" r="3"/></symbol>
<symbol id="ico-pin" viewBox="0 0 24 24">
<path fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M12 21s7-6.3 7-11a7 7 0 1 0-14 0c0 4.7 7 11 7 11z"/><circle fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" cx="12" cy="10" r="2.6"/></symbol>
<symbol id="ico-clock" viewBox="0 0 24 24">
<circle fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" cx="12" cy="12" r="9"/><path fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M12 7v5.5l4 2.2"/></symbol>
<symbol id="ico-phone" viewBox="0 0 24 24">
<path fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M6 3h3l2 5-2.2 1.4a12 12 0 0 0 5.8 5.8L16 13l5 2v3a2 2 0 0 1-2.2 2A17 17 0 0 1 4 5.2 2 2 0 0 1 6 3z"/></symbol>
<symbol id="ico-mail" viewBox="0 0 24 24">
<rect fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" x="2.5" y="5" width="19" height="14" rx="2"/><path fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="m3 7 9 6 9-6"/></symbol>
<symbol id="ico-instagram" viewBox="0 0 24 24">
<rect fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" x="3" y="3" width="18" height="18" rx="5.5"/><circle fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" cx="12" cy="12" r="4"/><circle fill="currentColor" stroke="none" cx="17.4" cy="6.6" r="1.3"/></symbol>
<symbol id="ico-tiktok" viewBox="0 0 24 24">
<path fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M14 3v11.5a3.5 3.5 0 1 1-3-3.46"/><path fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M14 3c.6 3 2.6 4.6 5.5 4.8"/></symbol>
<symbol id="ico-facebook" viewBox="0 0 24 24">
<path fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M15 3h-2.5A4.5 4.5 0 0 0 8 7.5V21"/><path fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M8 12h7"/><path fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M13 21V7.5"/></symbol>
<symbol id="ico-burger" viewBox="0 0 24 24">
<path fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M3 7h18M3 12h18M3 17h18"/></symbol>
<symbol id="ico-ticket" viewBox="0 0 24 24">
<path fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M3 8a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v1.5a2.5 2.5 0 0 0 0 5V16a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-1.5a2.5 2.5 0 0 0 0-5z"/></symbol>
<symbol id="ico-trash" viewBox="0 0 24 24">
<path fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M9 7V4h6v3M6 7l1 13h10l1-13"/></symbol>
<symbol id="ico-qr" viewBox="0 0 24 24">
<rect fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" x="3" y="3" width="7" height="7"/><rect fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" x="14" y="3" width="7" height="7"/> <rect fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" x="3" y="14" width="7" height="7"/><path fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M14 14h3v3h-3zM19 14h2M14 19h2M19 19h2v2"/></symbol>
<symbol id="ico-tape" viewBox="0 0 24 24">
<path fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="m3 8 6-3 12 6-6 3z"/><path fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="m9 5 6 12"/></symbol>
<symbol id="ico-leaf" viewBox="0 0 24 24">
<path fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M20 4c0 9-5 14-12 14H5c0-8 6-13 15-14z"/><path fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M5 20c3-6 7-9 11-11"/></symbol>
<symbol id="ico-wheat" viewBox="0 0 24 24">
<path fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M12 21V8"/><path fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M12 8c-3 0-4-2-4-4 3 0 4 2 4 4zM12 8c3 0 4-2 4-4-3 0-4 2-4 4z"/> <path fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M12 14c-3 0-4-2-4-4 3 0 4 2 4 4zM12 14c3 0 4-2 4-4-3 0-4 2-4 4z"/></symbol>
<symbol id="ico-milk" viewBox="0 0 24 24">
<path fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M9 2h6v3l3 5v11a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V10l3-5z"/><path fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M6 13h12"/></symbol>
<symbol id="ico-egg" viewBox="0 0 24 24">
<path fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M12 2c4 0 8 6 8 11a8 8 0 0 1-16 0c0-5 4-11 8-11z"/></symbol>
<symbol id="ico-nut" viewBox="0 0 24 24">
<path fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M12 3c5 3 7 7 7 11a7 7 0 0 1-14 0c0-4 2-8 7-11z"/><path fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M12 6v14"/></symbol>
<symbol id="ico-sesame" viewBox="0 0 24 24">
<ellipse fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" cx="8" cy="8" rx="2.4" ry="3.4" transform="rotate(-20 8 8)"/> <ellipse fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" cx="16" cy="10" rx="2.4" ry="3.4" transform="rotate(25 16 10)"/> <ellipse fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" cx="11" cy="16" rx="2.4" ry="3.4" transform="rotate(-10 11 16)"/></symbol>

</svg>
