<?php
/*
Plugin Name: Castors Worksites
Plugin URI: https://les-castors.fr
Description: Worksites management for "Les Castors" child of Astra theme.
Author: Marc Delpont
Version: 1.0.0
Author URI: https://arkabase.fr
License: GPLv2
Text Domain: castors-worksites
*/

// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}

define('CASTORS_WORKSITES_PLUGIN_URI', plugin_dir_url(__FILE__));
define('CASTORS_WORKSITES_PLUGIN_DIR', plugin_dir_path(__FILE__));

require_once('worksite.php');

function castors_worksites_activate() {
	Castors_Worksite::activate();
	flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'castors_worksites_activate');

function castors_worksites_deactivate() { 
    Castors_Worksite::deactivate();
	flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'castors_worksites_deactivate' );

add_action('wp_enqueue_scripts', ['Castors_Worksite', 'enqueue_scripts']);
add_action('admin_enqueue_scripts', ['Castors_Worksite', 'admin_enqueue_scripts']);
add_action('init', ['Castors_Worksite', 'init']);
add_action('admin_init', ['Castors_Worksite', 'admin_init']);

function castors_worksites_locate_template($template, $template_name) {
    $template_path = CASTORS_WORKSITES_PLUGIN_DIR . 'woocommerce/';
    if (file_exists($template_path . $template_name)) {
        return $template_path . $template_name;
    }
    return $template;
}
add_filter('woocommerce_locate_template', 'castors_worksites_locate_template', 10, 2);