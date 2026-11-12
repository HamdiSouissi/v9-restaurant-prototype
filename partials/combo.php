<?php

/**
 * CREA IL TUO COMBO — configurateur 3 étapes + plateau physique.
 */

declare(strict_types=1);

/** @var array $CFG */
$combo = $CFG['combo'];
?>
<section class="combo section section--lime" id="combo" aria-labelledby="combo-title"
         data-sweep="asfalto" style="--sweep:var(--asfalto)">

    <div class="combo__head">
        <h2 class="h2 h2--split" id="combo-title">
            <span class="h2__a" data-kinetic="62,125">CREA IL TUO</span>
            <span class="h2__b" data-kinetic="62,125">COMBO</span>
        </h2>
        <p class="combo__intro"><?php echo e($combo['intro']); ?></p>
    </div>

    <div class="combo__grid">

        <div class="combo__steps">
            <?php foreach ($combo['steps'] as $si => $step) :
                $skey = (string) $step['key']; ?>
                <fieldset class="step" data-step="<?php echo e($skey); ?>">
                    <legend class="step__legend">
                        <span class="step__n" aria-hidden="true"><?php echo (int) ($si + 1); ?></span>
                        <span class="step__t"><?php echo e($step['title']); ?></span>
                        <span class="step__hint marker"><?php echo e((string) ($step['hint'] ?? '')); ?></span>
                    </legend>

                    <div class="step__opts">
                        <?php foreach ($step['options'] as $oid) :
                            $item = bomba_item($CFG, (string) $oid);
                            if ($item === null) {
                                continue;
                            } ?>
                            <label class="opt" data-opt="<?php echo e($item['id']); ?>"
                                   style="--rot:<?php echo e((string) bomba_rot('opt-' . $item['id'], 3.4)); ?>deg">
                                <input class="opt__radio" type="radio" name="combo-<?php echo e($skey); ?>"
                                       value="<?php echo e($item['id']); ?>" data-step-key="<?php echo e($skey); ?>">
                                <span class="opt__box">
                                    <span class="opt__art"><?php echo bomba_cutout((string) $item['cutout'], $item['name'], 'opt__cut'); ?></span>
                                    <span class="opt__name"><?php echo e($item['name']); ?></span>
                                    <span class="opt__price"><?php echo e(bomba_money((float) $item['price'])); ?></span>
                                    <svg class="opt__tick" viewBox="0 0 200 200" aria-hidden="true" focusable="false"><use href="#ico-check"></use></svg>
                                </span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </fieldset>
            <?php endforeach; ?>
        </div>

        <aside class="combo__side" aria-label="Il tuo combo">
            <div class="tray" data-tray>
                <p class="tray__title">IL PIATTO</p>
                <div class="tray__stack" data-tray-stack aria-hidden="true"></div>
                <p class="tray__empty" data-tray-empty>Scegli e guarda la roba atterrare.</p>
                <div class="tray__floor" aria-hidden="true"></div>
            </div>

            <div class="combo__totals">
                <p class="combo__was marker" data-combo-was aria-hidden="true">€ 0</p>
                <p class="combo__now" data-combo-now>€ 0</p>
                <p class="combo__save" data-combo-save hidden>RISPARMI € 0</p>
                <p class="combo__live" aria-live="polite" data-combo-live>Combo incompleto.</p>
            </div>

            <button class="btn btn--ink btn--lg btn--block" type="button" data-combo-add disabled>
                <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><use href="#ico-bag"></use></svg>
                <span><?php echo e($combo['cta']); ?></span>
            </button>
            <p class="combo__missing marker" data-combo-missing>Manca ancora: il principale</p>
        </aside>
    </div>
</section>
