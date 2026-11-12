<?php

/**
 * DOVE SIAMO — étiquettes collées + carte illustrée en SVG (pas d'iframe).
 */

declare(strict_types=1);

/** @var array $CFG */
/** @var array $OPEN */
$c = $CFG['contact'];
?>
<section class="info section section--senape" id="info" aria-labelledby="info-title"
         data-sweep="asfalto" style="--sweep:var(--asfalto)">

    <div class="info__head">
        <h2 class="h2 h2--split" id="info-title">
            <span class="h2__a" data-kinetic="62,125">DOVE</span>
            <span class="h2__b" data-kinetic="62,125">SIAMO</span>
        </h2>
        <span class="info__tag marker">passa, non serve prenotare (ma aiuta)</span>
    </div>

    <div class="info__grid">

        <div class="info__labels">
            <article class="label label--a" style="--rot:<?php echo e((string) bomba_rot('lbl-a', 2.4)); ?>deg">
                <p class="label__k">Dove</p>
                <p class="label__v"><?php echo e($c['address']); ?></p>
                <a class="label__link" href="<?php echo e($c['maps']); ?>" target="_blank" rel="noopener noreferrer">
                    <?php echo e($c['maps_embed_label']); ?>
                    <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><use href="#ico-arrow-ur"></use></svg>
                </a>
                <span class="label__pin" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><use href="#ico-pin"></use></svg>
                </span>
            </article>

            <article class="label label--b" style="--rot:<?php echo e((string) bomba_rot('lbl-b', 2.4)); ?>deg">
                <p class="label__k">Quando</p>
                <ul class="label__hours">
                    <?php foreach ($CFG['hours_display'] as $h) : ?>
                        <li>
                            <span class="label__d"><?php echo e($h['days']); ?></span>
                            <span class="label__dots" aria-hidden="true"></span>
                            <span class="label__s <?php echo $h['slots'][0] === 'chiuso' ? 'label__s--off' : ''; ?>">
                                <?php echo $h['slots'][0] === 'chiuso'
                                    ? e($CFG['booking']['closed_label'])
                                    : e(implode(' · ', $h['slots'])); ?>
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <p class="label__now <?php echo !empty($OPEN['open']) ? 'label__now--open' : ''; ?>">
                    <span class="status__dot" aria-hidden="true"></span><?php echo e((string) $OPEN['label']); ?>
                </p>
            </article>

            <article class="label label--c" style="--rot:<?php echo e((string) bomba_rot('lbl-c', 2.4)); ?>deg">
                <p class="label__k">Contatti</p>
                <ul class="label__contacts">
                    <li>
                        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><use href="#ico-phone"></use></svg>
                        <a href="tel:<?php echo e($c['phone']); ?>"><?php echo e($c['phone_ui']); ?></a>
                    </li>
                    <li>
                        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><use href="#ico-mail"></use></svg>
                        <a href="mailto:<?php echo e($c['email']); ?>"><?php echo e($c['email']); ?></a>
                    </li>
                    <li>
                        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><use href="#ico-instagram"></use></svg>
                        <span><?php echo e($c['handle']); ?></span>
                    </li>
                </ul>
            </article>
        </div>

        <figure class="map" aria-label="Mappa illustrata del quartiere">
            <svg class="map__svg" viewBox="0 0 900 620" role="img" aria-labelledby="map-t map-d">
                <title id="map-t">Mappa illustrata</title>
                <desc id="map-d"><?php echo e($c['address']); ?>. Il centro di Parma, disegnato a mano.</desc>

                <rect class="map__bg" x="0" y="0" width="900" height="620"/>

                <!-- isolati -->
                <g class="map__blocks">
                    <rect x="40" y="40" width="230" height="150" rx="4"/>
                    <rect x="330" y="30" width="180" height="120" rx="4"/>
                    <rect x="580" y="60" width="270" height="170" rx="4"/>
                    <rect x="60" y="260" width="200" height="140" rx="4"/>
                    <rect x="620" y="300" width="230" height="150" rx="4"/>
                    <rect x="120" y="470" width="260" height="120" rx="4"/>
                    <rect x="470" y="480" width="200" height="110" rx="4"/>
                </g>

                <!-- parco -->
                <g class="map__park">
                    <path d="M340 300c60-30 140-20 180 20s30 110-30 130-150 0-180-40 0-90 30-110z"/>
                    <circle cx="400" cy="360" r="14"/><circle cx="450" cy="330" r="11"/>
                    <circle cx="470" cy="392" r="13"/><circle cx="392" cy="410" r="10"/>
                </g>

                <!-- strade -->
                <g class="map__roads">
                    <path d="M0 220h900"/>
                    <path d="M0 450h900"/>
                    <path d="M300 0v620"/>
                    <path d="M560 0v620"/>
                    <path d="M0 620 900 0" class="map__road--diag"/>
                </g>
                <g class="map__roads-thin">
                    <path d="M0 120h900"/><path d="M0 330h900"/><path d="M0 540h900"/>
                    <path d="M150 0v620"/><path d="M430 0v620"/><path d="M740 0v620"/>
                </g>

                <!-- percorso -->
                <path class="map__route" d="M300 450 L470 450 L470 300 L600 300"/>

                <!-- etichette -->
                <g class="map__labels">
                    <text x="24" y="212">VIA ESEMPIO</text>
                    <text x="24" y="442">V.LE BOTTEGO</text>
                    <text x="308" y="600" transform="rotate(-90 308 600)">STRADA REPUBBLICA</text>
                    <text x="406" y="378">PARCO</text>
                    <text x="640" y="120">STAZIONE</text>
                </g>

                <!-- épingle sticker -->
                <g transform="translate(600 300)">
                    <g class="map__pin">
                        <path class="map__pin-w" d="M0-62c20 0 36 16 36 36 0 26-36 62-36 62S-36 0-36-26c0-20 16-36 36-36z"/>
                        <path class="map__pin-r" d="M0-56c17 0 30 13 30 30 0 22-30 53-30 53s-30-31-30-53c0-17 13-30 30-30z"/>
                        <circle class="map__pin-h" cx="0" cy="-27" r="11"/>
                    </g>
                </g>
                <text class="map__pinlabel" x="600" y="238" text-anchor="middle"><?php echo e($CFG['brand']['name']); ?></text>
            </svg>
            <figcaption class="map__cap marker">qui, sotto il neon rosso</figcaption>
        </figure>
    </div>
</section>
