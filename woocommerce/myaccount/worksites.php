<?php
defined( 'ABSPATH' ) || exit;

$user = get_current_user_id();
$wsid = get_query_var('chantier');
$worksite = null;

if ($wsid === 'new') {
    $worksite = 'new';
} elseif ($wsid) {
    $worksite = get_post((int) $wsid);
    if ($worksite && ($worksite->post_type !== 'worksite' || $worksite->post_author != $user)) {
        $worksite = null;
    }
}

if ($worksite):
    if ($worksite === 'new') {
        $location_details = '';
        $location = '';
        $inclusive = false;
    } else {
        $location_details = get_post_meta($worksite->ID, 'castors_location_details', true) ?: '';
        $location = $location_details ? json_decode(htmlspecialchars_decode($location_details)) : '';
        $inclusive = get_post_meta($worksite->ID, 'castors_inclusive', true) ? true : false;
    }
?>
    <form class="castors-EditWorksiteForm edit-worksite" action="" method="post" <?php do_action('castors_edit_worksite_form_tag'); ?> >
        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide validate-required">
            <label for="worksite_title"><?php esc_html_e("Nom du chantier", 'castors'); ?>&nbsp;<abbr class="required" title="<?= esc_attr__('required', 'woocommerce')  ?>">*</abbr></label>
            <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="worksite_title" id="worksite_title" value="<?= esc_attr($worksite === 'new' ? '' : $worksite->post_title); ?>" />
        </p>
        <div class="clear"></div>
        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <label for="worksite_content"><?php esc_html_e("Description", 'castors'); ?></label>
            <?php
                wp_editor($worksite === 'new' ? '' : $worksite->post_content, 'worksite_content', [
                    'wpautop'       => true,
                    'media_buttons' => false,
                    'editor_height' => 300,
                    'teeny'         => true,
                ]);
            ?>
        </p>
        <div class="clear"></div>
        <p class="woocommerce-form-row woocommerce-form-row--first form-row form-row-first castors-location-wrap">
            <label for="location"><?php esc_html_e("Localisation du chantier", 'castors'); ?></label>
            <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="location" id="location" value="<?= esc_attr($location ? $location->value : ''); ?>" />
            <input type="hidden" name="location-details" id="location-details" value="<?= htmlentities($location_details) ?>" />
        </p>
        <p class="woocommerce-form-row woocommerce-form-row--last form-row form-row-last castors-location-nomap">
            <label for="location-nomap">
                <input type="checkbox" class="woocommerce-Input woocommerce-Input--checkbox input-checkbox" name="location-nomap" id="location-nomap" value="1" />
                <?php esc_html_e("Ne pas afficher sur la carte des adhérents", 'castors'); ?>
            </label>
        </p>
        <div class="clear"></div>
        <p class="arkabase-field-desc"><em>
            <?php esc_html_e("Entrez le code postal pour sélectionner la ville où se situe votre chantier, puis cocher la case pour l'afficher sur la carte.", 'castors' ); ?>
            <?php esc_html_e("Vous pouvez indiquer l'adresse exacte dans la description, ou la transmettre en privé aux personnes désireuses d'y participer.", 'castors' ); ?>
        </em></p>
        <p class="woocommerce-form-row woocommerce-form-row--first form-row form-row-first castors-transfer-wrap">
            <label for="tags"><?php esc_html_e("Détails du chantier", 'castors'); ?></label>
            <div id="transfer"></div>
            <input type="hidden" name="worksite_tags" id="worksite_tags" value="" />
        </p>
        <div class="clear"></div>
        <p class="arkabase-field-desc"><em>
            <?php esc_html_e("Cochez dans la liste de gauche les éléments significatifs de votre chantier, puis cliquez sur la flèche pour les transférer dans la liste de droite et les associer au chantier.", 'castors' ); ?>
        </em></p>
        <div class="clear"></div>
        <p>
            <?php wp_nonce_field('save_worksite', 'save-worksite-nonce'); ?>
            <button type="submit" class="woocommerce-Button button<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>" name="save_worksite" value="<?php esc_attr_e( 'Save changes', 'woocommerce' ); ?>">
                <?= $worksite === 'new' ? esc_html('Créer le chantier', 'castors') : esc_html('Save changes', 'woocommerce'); ?>
            </button>
            <input type="hidden" name="action" value="save_worksite" />
        </p>
    </form>
    <?php wp_footer(); ?>
    <?php Castors_User::locationAutocompleteScript('.castors-EditWorksiteForm #location', '.castors-EditWorksiteForm #location-details'); ?>

<?php
else:
    $query = new WP_Query([
        'post_type' => 'worksite',
        'author'    => $user,
    ]);
?>
    <div class="u-columns castors-worksites col2-set worksites">
<?php
    if ($query->have_posts()):
        $col = 1;
        while ($query->have_posts()):
            $query->the_post();
            $id = get_the_ID();
?>
        <div class="u-column1 col-<?= $col++ ?> castors-Worksite">
            <header class="castors-Worksite-title title">
                <?php the_title('<h3>', '</h3>') ?>
                <div class="links">
                    <a href="<?= get_permalink($id) ?>" class="display">Voir</a><br />
                    <a href="/compte/chantiers/?chantier=<?= $id ?>" class="edit">Modifier</a>
                </div>
		    </header>
            <address>
                <?php the_post_thumbnail() ?>
                <?php the_excerpt() ?>
            </address>
        </div>
<?php
        endwhile;
    else:
?>
        <p><?= esc_html("Créez votre premier chantier en cliquant sur le bouton ci-dessous"); ?></p>
<?php
    endif;
?>
    </div>
    <p>
        <a href="/compte/chantiers/?chantier=new" class="woocommerce-Button button<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>" name="add_worksite" value="<?php esc_attr_e("Nouveau chantier", 'castors' ); ?>">
            <?= esc_html("Nouveau chantier", 'castors'); ?>
</a>
    </p>
<?php
endif;
