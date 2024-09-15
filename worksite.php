<?php
if (!defined('ABSPATH'))  exit;

require_once CASTORS_WORKSITES_PLUGIN_DIR . 'class-walker-worksite-tags-checklist.php';

class Castors_Worksite {
    public static function activate() {
        static::register();
        static::register_taxonomy();
        if (class_exists('Castors_Helper')) {
            Castors_Helper::add_caps('administrator', 'worksite', null, true, true);
            Castors_Helper::add_caps('customer', 'worksite');
        }
    }

    public static function deactivate() {
	    unregister_post_type('worksite');
	    unregister_taxonomy('worksite-tag');
        if (class_exists('Castors_Helper')) {
            Castors_Helper::remove_caps('administrator', 'worksite');
            Castors_Helper::remove_caps('customer', 'worksite');
        }
    }

    public static function enqueue_scripts() {
        wp_enqueue_style('castors-worksite', CASTORS_WORKSITES_PLUGIN_URI . 'css/worksite.css');
        wp_enqueue_script('castors-worksite', CASTORS_WORKSITES_PLUGIN_URI . 'js/worksite.min.js', ['jquery']);
    }

    public static function admin_enqueue_scripts() {
        wp_enqueue_style('castors-admin-worksite', CASTORS_WORKSITES_PLUGIN_URI . 'css/admin-worksite.css');
    }

    public static function admin_init() {
        global $submenu;
        if (get_stylesheet() !== 'les-castors') {
            add_action('admin_notices', [__CLASS__, 'disabled_notice']);
            unregister_post_type('worksite');
            unregister_taxonomy('worksite-tag');
            deactivate_plugins('castors-worksites/castors-worksites.php', true);
            return;
        }
        add_action('add_meta_boxes_worksite', [__CLASS__, 'edit_meta_boxes'], 99);
        add_filter('wp_terms_checklist_args', [__CLASS__, 'terms_checklist'], 10, 2);
        add_filter('user_has_cap', [__CLASS__, 'user_has_cap'], 99);
        add_action('save_post_worksite', [__CLASS__, 'worksite_saved'], 10, 2);
    }

    public static function init() {
        add_rewrite_endpoint('chantiers', EP_PAGES);
        add_filter('query_vars', [__CLASS__, 'query_vars']);
        add_filter('astra_get_option_ast-dynamic-single-worksite-metadata', [__CLASS__, 'get_option_ast']);
        add_filter('astra_meta_case_location', [__CLASS__, 'meta_location'], 10, 3);
        add_action('woocommerce_account_worksites_endpoint', [__CLASS__, 'account_worksites_endpoint']);
        add_filter('woocommerce_get_query_vars', [__CLASS__, 'woo_query_vars']);
        add_filter('woocommerce_account_menu_items', [__CLASS__, 'account_menu_items'], 45);
        add_filter('body_class', [__CLASS__, 'body_class'], 99);
        add_filter('get_the_terms', [__CLASS__, 'get_the_terms'], 99, 3);
        add_filter('get_the_archive_title', [__CLASS__, 'archive_title']);
        add_filter('get_the_archive_description', [__CLASS__, 'archive_description']);
        static::register();
        static::register_taxonomy();
    }

    public static function disabled_notice() {
        echo '<div class="notice notice-error is-dismissible"><p>';
        printf(__("Le thème %s doit être activé pour que l'extension %s fonctionne !", 'castors'), '<b>Les Castors</b>', '<b>Castors Worksites</b>');
        echo '</p></div>';
    }

    public static function body_class($classes) {
        if (in_array('woocommerce-no-js', $classes)) {
            $classes = array_diff($classes, ['woocommerce-no-js']);
            $classes[] = 'woocommerce-js';
        }
        return $classes;
    }

    public static function register() {
        register_post_type('worksite', [
            'labels' => [
                'name' => __("Chantiers", 'castors'),
                'singular_name' => __("Chantier", 'castors'),
                'add_new' => __("Ajouter", 'castors'),
                'add_new_item' => __("Ajouter un chantier", 'castors'),
                'edit_item' => __("Modifier un chantier", 'castors'),
                'new_item' => __("Nouveau chantier", 'castors'),
                'view_item' => __("Voir le chantier", 'castors'),
                'view_items' => __("Voir les chantiers", 'castors'),
                'search_items' => __("Rechercher un chantier", 'castors'),
                'not_found' => __("Aucun chantier n'a été trouvé", 'castors'),
                'not_found_in_trash' => __("Aucun chantier n'a été trouvé", 'castors'),
                'all_items' => __("Tous les chantiers", 'castors'),
                'filter_items_list' => __("Filtrer les chantiers", 'castors'),
            ],
            'description' => __("Chantier géré par un adhérent", 'castors'),
            'public' => true,
            'menu_position' => 30,
            'menu_icon' => 'data:image/svg+xml;base64,' . base64_encode('<svg width="20" height="20" viewBox="0 0 576 512" xmlns="http://www.w3.org/2000/svg"><path fill="black" d="M208 64a48 48 0 1 1 96 0 48 48 0 1 1 -96 0zM9.8 214.8c5.1-12.2 19.1-18 31.4-12.9L60.7 210l22.9-38.1C99.9 144.6 129.3 128 161 128c51.4 0 97 32.9 113.3 81.7l34.6 103.7 79.3 33.1 34.2-45.6c6.4-8.5 16.6-13.3 27.2-12.8s20.3 6.4 25.8 15.5l96 160c5.9 9.9 6.1 22.2 .4 32.2s-16.3 16.2-27.8 16.2H288c-11.1 0-21.4-5.7-27.2-15.2s-6.4-21.2-1.4-31.1l16-32c5.4-10.8 16.5-17.7 28.6-17.7h32l22.5-30L22.8 246.2c-12.2-5.1-18-19.1-12.9-31.4zm82.8 91.8l112 48c11.8 5 19.4 16.6 19.4 29.4v96c0 17.7-14.3 32-32 32s-32-14.3-32-32V405.1l-60.6-26-37 111c-5.6 16.8-23.7 25.8-40.5 20.2S-3.9 486.6 1.6 469.9l48-144 11-33 32 13.7z"/></svg>'),
            'capability_type' => 'worksite',
            'supports' => ['title', 'author', 'thumbnail'],
            'register_meta_box_cb' => [__CLASS__, 'register_metaboxes'],
            'has_archive' => 'worksite',
            'rewrite' => [
                'slug' => 'chantier',
				'with_front' => false,
            ],
            'delete_with_user' => true,
        ]);
    }

    public static function register_taxonomy() {
        register_taxonomy('worksite_tag', ['worksite'], [
            'hierarchical' => true,
			'public'    => true,
			'label'     => __("Etiquettes de chantier", 'castors'),
			'singular_label' => __("Etiquette de chantier", 'castors'),
            'labels'    => [
                'name'                       => __("Etiquettes de chantier", 'castors'),
                'singular_name'              => __('Tag', 'woocommerce'),
                'menu_name'                  => _x('Tags', 'Admin menu name', 'woocommerce'),
                'search_items'               => __('Search tags', 'woocommerce'),
                'all_items'                  => __('All tags', 'woocommerce'),
                'edit_item'                  => __('Edit tag', 'woocommerce'),
                'update_item'                => __('Update tag', 'woocommerce'),
                'add_new_item'               => __('Add new tag', 'woocommerce'),
                'new_item_name'              => __('New tag name', 'woocommerce'),
                'popular_items'              => __('Popular tags', 'woocommerce'),
                'separate_items_with_commas' => __('Separate tags with commas', 'woocommerce'),
                'add_or_remove_items'        => __('Add or remove tags', 'woocommerce'),
                'choose_from_most_used'      => __('Choose from the most used tags', 'woocommerce'),
                'not_found'                  => __('No tags found', 'woocommerce'),
                'item_link'                  => __("Lien d'étiquette de chantier", 'castors'),
                'item_link_description'      => __("Un lien vers une étiquette de chantier", 'castors'),
            ],
            'rewrite' => [
                'slug' => 'chantier/etiquette',
                'with_front' => false,
                'hierarchical' => true,
            ],
            'capabilities' => [
                'manage_terms' => 'manage_worksite_terms',
                'edit_terms' => 'manage_worksite_terms',
                'delete_terms' => 'manage_worksite_terms',
                'assign_terms' => 'edit_worksites',
            ],
        ]);
    }

    public static function get_the_terms($terms, $post_id, $taxonomy) {
        if ($taxonomy !== 'worksite_tag' || !is_single($post_id)) {
            return $terms;
        }

        $parents = array_flip(get_terms(['taxonomy' => 'worksite_tag', 'parent' => 0, 'fields' => 'ids']));

        $terms = array_map(function($t) use ($parents) {
            $t->name = $t->description;
            $t->order = $parents[$t->parent];
            return $t;
        }, $terms);

        usort($terms, function($a, $b) {
            if ($a->order === $b->order) {
                return 0;
            }
            return ($a->order < $b->order) ? -1 : 1;
        });

        return $terms;
    }

    public static function archive_title($title) {
        $term = get_queried_object();
        if ($term->taxonomy === 'worksite_tag') {
            return $term->description;
        }
        return $title;
    }

    public static function archive_description($desc) {
        $term = get_queried_object();
        if ($term->taxonomy === 'worksite_tag') {
            return __("Liste des chantiers", 'castors');
        }
        return $desc;
    }

    public static function query_vars($vars) {
        $vars['worksite'] = 'chantier';
        $vars['worksite_tag'] = 'chantier/etiquette';
        return $vars;
    }

    public static function get_option_ast($value) {
        global $post;
        if ($post->post_type === 'worksite') {
            $value = array_diff($value, ['date']);
            $value[] = 'location';
        }
        return $value;
    }

    public static function meta_location($output, $loop, $separator) {
        global $post;

        $location_details = get_post_meta($post->ID, 'castors_location_details', true) ?: '';
        $location = json_decode(htmlspecialchars_decode($location_details));
        if ($location) {
            $output .= ' ' . $separator . ' ' . $location->city;
            $output .= ' <a href="" title="' . __("Voir sur la carte", 'castors') . '" class="castors-meta-link"><i class="fa-solid fa-map-location-dot fa-lg"></i></a>';
        }
        return $output;
    }

    public static function woo_query_vars($vars) {
        $vars['worksites'] = 'chantiers';
        return $vars;
    }

    public static function account_menu_items($items) {
        if (current_user_can('edit_worksites')) {
            Castors_Helper::array_insert_after_key($items, 'membership', [
                'worksites' => __("Mes chantiers", 'castors'),
            ]);
        }
        return $items;
    }

    public static function account_worksites_endpoint() {
        wc_get_template('myaccount/worksites.php');
    }

    public static function edit_meta_boxes() {
        global $wp_meta_boxes;

        $imagediv = $wp_meta_boxes['worksite']['side']['low']['postimagediv'];
        add_meta_box($imagediv['id'], $imagediv['title'], $imagediv['callback'], 'worksite', 'normal', 'default', $imagediv['args']);

        $tagdiv = $wp_meta_boxes['worksite']['side']['core']['worksite_tagdiv'];
        add_meta_box($tagdiv['id'], $tagdiv['title'], $tagdiv['callback'], 'worksite', 'normal', 'default', $tagdiv['args']);

        add_meta_box('worksite_editor', __("Description", 'castors'), [__CLASS__, 'render_custom_editor'], 'worksite', 'normal', 'high');

        remove_meta_box('postimagediv', 'worksite', 'side');
        remove_meta_box('worksite_tagdiv', 'worksite', 'side');
        remove_meta_box('postcustom', 'worksite', 'normal');
        remove_meta_box('astra_settings_meta_box', 'worksite', 'side');
    }

    public static function render_custom_editor($post) {
        wp_editor($post->post_content, 'post_content', [
            'wpautop'       => true,
            'media_buttons' => false,
            'editor_height' => 300,
            'teeny'         => true,
        ]);
    }

    public static function terms_checklist($args, $post_id) {
        $post = get_post($post_id);
        if ($post->post_type === 'worksite') {
            $args['walker'] = new Walker_Worksite_tags_Checklist();
            $args['checked_ontop'] = false;
        }
        return $args;
    }

    public static function user_has_cap($allcaps) {
        global $post;

        if ($post && $post->post_type === 'worksite') {
            $allcaps['manage_categories'] = false;
        }
        return $allcaps;
    }

    public static function location_metabox($post, $metabox) {
        $location_details = get_post_meta($post->ID, 'castors_location_details', true) ?: '';
        $inclusive = get_post_meta($post->ID, 'castors_inclusive', true) ? true : '';
		include __DIR__ . '/views/html-worksite-location.php';
        Castors_Map::locationAutocompleteScript();
    }

    public static function register_metaboxes() {
        add_meta_box('castors_worksite_location', __("Localisation du chantier", 'castors'), [__CLASS__, 'location_metabox'], 'worksite', 'normal', 'core');
    }

    public static function worksite_saved($post_id, $admin = true) {
        if ($admin) {
            if (empty($_POST['location-details'])) {
                delete_post_meta($post_id, 'castors_location_details');
            } else {
                $location_details = wc_clean(wp_unslash($_POST['location-details']));
                $location = json_decode(htmlspecialchars_decode($location_details));
                if ($location) {
                    update_post_meta($post_id, 'castors_location_details', $location_details);
                }
            }
            if (empty($_POST['inclusive-worksite'])) {
                delete_post_meta($post_id, 'castors_inclusive');
            } else {
                update_post_meta($post_id, 'castors_inclusive', 1);
            }
        }

        update_post_meta($post_id, 'ast-site-content-layout', 'normal-width-container');
        update_post_meta($post_id, 'site-content-style', 'unboxed');
        update_post_meta($post_id, 'site-sidebar-layout', 'default');
        update_post_meta($post_id, 'site-sidebar-style', 'default');
        update_post_meta($post_id, 'theme-transparent-header-meta', 'default');
    }
}
