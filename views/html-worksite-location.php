<?php
if (!defined('ABSPATH'))  exit;

$location = json_decode(htmlspecialchars_decode($location_details));
?>

<div class="inside castors-location-wrap">
    <label for="location"><?= esc_html__("Localisation", 'castors') ?></label>
    <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="location" id="location" value="<?= $location ? $location->value : '' ?>" />
    <input type="hidden" name="location-details" id="location-details" value="<?= htmlentities($location_details) ?>" />
    &nbsp;&nbsp;&nbsp;
    <label for="inclusive-worksite">
        <input type="checkbox" name="inclusive-worksite" id="inclusive-worksite" value="1"<?= $inclusive ? ' checked="checked"' : '' ?> />
        <?= esc_html__("Chantier participatif", 'castors') ?>
    </label>
    <p><?= esc_html__("Entrer le code postal pour sélectionner la ville. Un chantier participatif apparaîtra sur la carte.", 'castors') ?></p>
</div>