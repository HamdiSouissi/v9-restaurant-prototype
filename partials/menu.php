<?php

/**
 * IL MENU — « scontrino » géant.
 * Onglets par catégorie, filtres, impression animée, aperçu au survol.
 */

declare(strict_types=1);

/** @var array $CFG */
/** @var array $MENU */

$allergenIcons = [
    'glutine'         => 'ico-wheat',
    'lattosio'        => 'ico-milk',
    'uova'            => 'ico-egg',
    'frutta-a-guscio' => 'ico-nut',
    'sesamo'          => 'ico-sesame',
];
$allergenLabels = $CFG['allergens'];

$cats   = array_keys($CFG['menu']);
$first  = true;
$lineNo = 0;
?>
<section class="menu section section--asfalto" id="menu" aria-labelledby="menu-title"
         data-sweep="lime" style="--sweep:var(--lime)">

    <div class="menu__head">
        <h2 class="h2 h2--split" id="menu-title">
            <span class="h2__a" data-kinetic="62,125">IL</span>
            <span class="h2__b" data-kinetic="62,125">MENU</span>
        </h2>
        <span class="menu__tag marker" aria-hidden="true">tutto fatto qui</span>
        <svg class="menu__tag-arrow" viewBox="0 0 200 200" aria-hidden="true" focusable="false"><path class="hand-draw" d="M18 152C56 152 92 128 118 92c14-20 24-40 30-58"/><path class="hand-draw hand-draw--2" d="M120 30l34 2-8 34"/></svg>
    </div>

    <div class="menu__controls">
        <div class="pills" role="tablist" aria-label="Categorie del menu" data-tabs>
            <span class="pills__thumb" data-pills-thumb aria-hidden="true"></span>
            <?php foreach ($CFG['menu'] as $key => $cat) : ?>
                <button class="pill" type="button" role="tab" id="tab-<?php echo e($key); ?>"
                        aria-controls="panel-<?php echo e($key); ?>"
                        aria-selected="<?php echo $first ? 'true' : 'false'; ?>"
                        tabindex="<?php echo $first ? '0' : '-1'; ?>"
                        data-tab="<?php echo e($key); ?>">
                    <?php echo e($cat['label']); ?>
                </button>
            <?php $first = false; endforeach; ?>
        </div>

        <div class="chips" role="group" aria-label="Filtra i piatti" data-filters>
            <?php foreach ($CFG['filters'] as $f) : ?>
                <button class="chip" type="button" data-filter="<?php echo e($f['id']); ?>" aria-pressed="<?php echo $f['id'] === 'tutti' ? 'true' : 'false'; ?>">
                    <?php if (!empty($f['icon'])) : ?>
                        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><use href="#ico-pepper"></use></svg>
                    <?php endif; ?>
                    <span><?php echo e($f['label']); ?></span>
                </button>
            <?php endforeach; ?>
        </div>
    </div>

    <?php $first = true; foreach ($CFG['menu'] as $key => $cat) : ?>
        <div class="panel" role="tabpanel" id="panel-<?php echo e($key); ?>"
             aria-labelledby="tab-<?php echo e($key); ?>"
             data-panel="<?php echo e($key); ?>"
             <?php echo $first ? '' : 'hidden'; ?>>

            <div class="receipt" data-receipt>
                <div class="receipt__top" aria-hidden="true"></div>

                <div class="receipt__in">
                    <p class="receipt__brand"><?php echo e($CFG['brand']['name']); ?></p>
                    <p class="receipt__meta"><?php echo e($CFG['contact']['address']); ?></p>
                    <p class="receipt__meta receipt__meta--sep"><?php echo e($cat['label']); ?> — <?php echo e((string) ($cat['note'] ?? '')); ?></p>

                    <ul class="receipt__list" data-menu-list>
                        <?php foreach ($cat['items'] as $item) :
                            $lineNo++;
                            $id   = (string) $item['id'];
                            $tags = array_values($item['tags'] ?? []);
                        ?>
                        <li class="rrow" data-row="<?php echo e($id); ?>" data-tags="<?php echo e(implode(' ', $tags)); ?>"
                            data-cutout="<?php echo e((string) ($item['cutout'] ?? '')); ?>"
                            data-cutsrc="<?php echo e((string) (bomba_cutout_src((string) ($item['cutout'] ?? '')) ?? '')); ?>"
                            data-price="<?php echo e(bomba_number((float) $item['price'])); ?>"
                            style="--i:<?php echo (int) $lineNo; ?>">

                            <div class="rrow__main">
                                <span class="rrow__n" aria-hidden="true"><?php echo str_pad((string) $lineNo, 2, '0', STR_PAD_LEFT); ?></span>

                                <div class="rrow__txt">
                                    <p class="rrow__name">
                                        <?php echo e($item['name']); ?>
                                        <?php foreach ($tags as $t) : ?>
                                            <span class="rrow__flag rrow__flag--<?php echo e($t); ?>">
                                                <?php if ($t === 'piccante') : ?>
                                                    <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><use href="#ico-pepper"></use></svg>
                                                <?php elseif ($t === 'veg') : ?>
                                                    <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><use href="#ico-leaf"></use></svg>
                                                <?php endif; ?>
                                                <span><?php echo e($t === 'gluten-free' ? 'senza glutine' : $t); ?></span>
                                            </span>
                                        <?php endforeach; ?>
                                        <?php if (!empty($item['note'])) : ?>
                                            <span class="rrow__note marker"><?php echo e((string) $item['note']); ?></span>
                                        <?php endif; ?>
                                    </p>
                                    <p class="rrow__desc"><?php echo e($item['desc']); ?></p>

                                    <?php if (!empty($item['allergens'])) : ?>
                                        <ul class="rrow__allergens">
                                            <?php foreach ($item['allergens'] as $al) : ?>
                                                <li>
                                                    <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                                        <use href="#<?php echo e($allergenIcons[$al] ?? 'ico-wheat'); ?>"></use>
                                                    </svg>
                                                    <span><?php echo e($allergenLabels[$al] ?? $al); ?></span>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>
                                </div>

                                <span class="rrow__dots" aria-hidden="true"></span>
                                <span class="rrow__price"><?php echo e(bomba_money((float) $item['price'])); ?></span>
                            </div>

                            <div class="qty" data-qty="<?php echo e($id); ?>">
                                <button class="qty__btn qty__minus" type="button" data-dec="<?php echo e($id); ?>"
                                        aria-label="Togli un <?php echo e($item['name']); ?> dal sacchetto" tabindex="-1">
                                    <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><use href="#ico-minus"></use></svg>
                                </button>
                                <span class="qty__n" data-qty-n aria-hidden="true">0</span>
                                <button class="qty__btn qty__plus" type="button" data-inc="<?php echo e($id); ?>"
                                        aria-label="Aggiungi <?php echo e($item['name']); ?> al sacchetto — <?php echo e(bomba_money((float) $item['price'])); ?>">
                                    <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><use href="#ico-plus"></use></svg>
                                </button>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>

                    <p class="receipt__empty" data-empty hidden>Niente qui con questo filtro. Prova <strong>Tutti</strong>.</p>

                    <div class="receipt__foot">
                        <p class="receipt__note">Prezzi IVA inclusa. Allergie? Chiedi pure, ci pensiamo noi.</p>
                        <svg class="receipt__barcode" viewBox="0 0 200 34" aria-hidden="true" focusable="false">
                            <?php for ($b = 0; $b < 46; $b++) :
                                $w = ($b % 4 === 0) ? 3 : (($b % 3 === 0) ? 2 : 1);
                                $x = $b * 4.3; ?>
                                <rect x="<?php echo e((string) round($x, 1)); ?>" y="0" width="<?php echo $w; ?>" height="34" fill="currentColor"/>
                            <?php endfor; ?>
                        </svg>
                        <p class="receipt__code"><?php echo e($CFG['brand']['number']); ?> — <?php echo e(strtoupper($key)); ?></p>
                    </div>
                </div>

                <div class="receipt__bot" aria-hidden="true"></div>
            </div>
        </div>
    <?php $first = false; endforeach; ?>

    <div class="menu__peek" data-menu-peek aria-hidden="true"></div>
</section>
