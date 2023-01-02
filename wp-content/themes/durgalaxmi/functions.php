<?php
if (!file_exists(get_template_directory() . '/wp-bootstrap-navwalker.php')) {
    // File does not exist... return an error.
    return new WP_Error('class-wp-bootstrap-navwalker-missing', __('It appears the class-wp-bootstrap-navwalker.php file may be missing.', 'wp-bootstrap-navwalker'));
} else {
    // File exists... require it.
    require_once get_template_directory() . '/wp-bootstrap-navwalker.php';
}


// bootstrap navwalker 

class bootstrap_5_wp_nav_menu_walker extends Walker_Nav_menu
{
    private $current_item;
    private $dropdown_menu_alignment_values = [
        'dropdown-menu-start',
        'dropdown-menu-end',
        'dropdown-menu-sm-start',
        'dropdown-menu-sm-end',
        'dropdown-menu-md-start',
        'dropdown-menu-md-end',
        'dropdown-menu-lg-start',
        'dropdown-menu-lg-end',
        'dropdown-menu-xl-start',
        'dropdown-menu-xl-end',
        'dropdown-menu-xxl-start',
        'dropdown-menu-xxl-end'
    ];

    function start_lvl(&$output, $depth = 0, $args = null)
    {
        $dropdown_menu_class[] = '';
        foreach ($this->current_item->classes as $class) {
            if (in_array($class, $this->dropdown_menu_alignment_values)) {
                $dropdown_menu_class[] = $class;
            }
        }
        $indent = str_repeat("\t", $depth);
        $submenu = ($depth > 0) ? ' sub-menu' : '';
        $output .= "\n$indent<ul class=\"dropdown-menu$submenu " . esc_attr(implode(" ", $dropdown_menu_class)) . " depth_$depth\">\n";
    }

    function start_el(&$output, $item, $depth = 0, $args = null, $id = 0)
    {
        $this->current_item = $item;

        $indent = ($depth) ? str_repeat("\t", $depth) : '';

        $li_attributes = '';
        $class_names = $value = '';

        $classes = empty($item->classes) ? array() : (array) $item->classes;

        $classes[] = ($args->walker->has_children) ? 'dropdown' : '';
        $classes[] = 'nav-item';
        $classes[] = 'nav-item-' . $item->ID;
        if ($depth && $args->walker->has_children) {
            $classes[] = 'dropdown-menu dropdown-menu-end';
        }

        $class_names =  join(' ', apply_filters('nav_menu_css_class', array_filter($classes), $item, $args));
        $class_names = ' class="' . esc_attr($class_names) . '"';

        $id = apply_filters('nav_menu_item_id', 'menu-item-' . $item->ID, $item, $args);
        $id = strlen($id) ? ' id="' . esc_attr($id) . '"' : '';

        $output .= $indent . '<li ' . $id . $value . $class_names . $li_attributes . '>';

        $attributes = !empty($item->attr_title) ? ' title="' . esc_attr($item->attr_title) . '"' : '';
        $attributes .= !empty($item->target) ? ' target="' . esc_attr($item->target) . '"' : '';
        $attributes .= !empty($item->xfn) ? ' rel="' . esc_attr($item->xfn) . '"' : '';
        $attributes .= !empty($item->url) ? ' href="' . esc_attr($item->url) . '"' : '';

        $active_class = ($item->current || $item->current_item_ancestor || in_array("current_page_parent", $item->classes, true) || in_array("current-post-ancestor", $item->classes, true)) ? 'active' : '';
        $nav_link_class = ($depth > 0) ? 'dropdown-item ' : 'nav-link ';
        $attributes .= ($args->walker->has_children) ? ' class="' . $nav_link_class . $active_class . ' dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"' : ' class="' . $nav_link_class . $active_class . '"';

        $item_output = $args->before;
        $item_output .= '<a' . $attributes . '>';
        $item_output .= $args->link_before . apply_filters('the_title', $item->title, $item->ID) . $args->link_after;
        $item_output .= '</a>';
        $item_output .= $args->after;

        $output .= apply_filters('walker_nav_menu_start_el', $item_output, $item, $depth, $args);
    }
}



add_post_type_support('post', 'excerpt');
// register a new menu
// register_nav_menu('main-menu', 'Main menu');
add_theme_support('custom-background');
register_nav_menus(array(
    'primary' => 'Primary Menu',
));
if (!function_exists('theme_enquee_scripts')) {
    function theme_enquee_scripts()
    {
        //css
        wp_enqueue_style('style', get_template_directory_uri() . '/style.css');

    }
}
add_action('wp_enqueue_scripts', 'theme_enquee_scripts');



function custom_excerpt()
{
    $excerpt = get_the_content();
    $excerpt = preg_replace(" ([.*?])", '', $excerpt);
    $excerpt = strip_shortcodes($excerpt);
    $excerpt = strip_tags($excerpt);
    $excerpt = substr($excerpt, 0,200);
    $excerpt = substr($excerpt, 0, strripos($excerpt, " "));
    $excerpt = trim(preg_replace('/\s+/', ' ', $excerpt));
    $excerpt = $excerpt . '... <a href="' . get_the_permalink() . '"></a>';
    return $excerpt;
}
add_filter('jetpack_offline_mode', '__return_true');

add_theme_support('post-thumbnails');
add_theme_support('custom-logo');
add_theme_support('post_thumbnails');
add_theme_support('menus');
add_theme_support('excerpt');

function themename_widgets_init()
{
    register_sidebar(array(
        'name'          => __('sidebar 1', 'theme_name'),
        'id'            => 'sidebar-1',

    ));
    register_sidebar(array(
        'name'          => __('Sidebar 2', 'theme_name'),
        'id'            => 'sidebar-2',

    ));
}
add_action('sidebars', 'themename_widgets_init');
function cptui_register_my_cpts()
{

    /**
     * Post Type: Academics.
     */

    $labels = [
        "name" => esc_html__("Academics", "custom-post-type-ui"),
        "singular_name" => esc_html__("Academics", "custom-post-type-ui"),
    ];

    $args = [
        "label" => esc_html__("Academics", "custom-post-type-ui"),
        "labels" => $labels,
        "description" => "",
        "public" => true,
        "publicly_queryable" => true,
        "show_ui" => true,
        "show_in_rest" => true,
        "rest_base" => "",
        "rest_controller_class" => "WP_REST_Posts_Controller",
        "rest_namespace" => "wp/v2",
        "has_archive" => false,
        "show_in_menu" => true,
        "show_in_nav_menus" => true,
        "delete_with_user" => false,
        "exclude_from_search" => false,
        "capability_type" => "post",
        "map_meta_cap" => true,
        "hierarchical" => false,
        "can_export" => false,
        "rewrite" => ["slug" => "academics", "with_front" => true],
        "query_var" => true,
        "menu_icon" => "dashicons-welcome-learn-more",
        "supports" => ["title", "editor", "thumbnail", "excerpt", "trackbacks", "custom-fields", "comments", "revisions", "author", "page-attributes", "post-formats"],
        "show_in_graphql" => false,
    ];

    register_post_type("academics", $args);

    /**
     * Post Type: Clubs.
     */

    $labels = [
        "name" => esc_html__("Clubs", "custom-post-type-ui"),
        "singular_name" => esc_html__("Clubs", "custom-post-type-ui"),
    ];

    $args = [
        "label" => esc_html__("Clubs", "custom-post-type-ui"),
        "labels" => $labels,
        "description" => "",
        "public" => true,
        "publicly_queryable" => true,
        "show_ui" => true,
        "show_in_rest" => true,
        "rest_base" => "",
        "rest_controller_class" => "WP_REST_Posts_Controller",
        "rest_namespace" => "wp/v2",
        "has_archive" => false,
        "show_in_menu" => true,
        "show_in_nav_menus" => true,
        "delete_with_user" => false,
        "exclude_from_search" => false,
        "capability_type" => "post",
        "map_meta_cap" => true,
        "hierarchical" => false,
        "can_export" => false,
        "rewrite" => ["slug" => "clubs", "with_front" => true],
        "query_var" => true,
        "menu_icon" => "dashicons-networking",
        "supports" => ["title", "editor", "thumbnail", "excerpt", "trackbacks", "custom-fields", "comments", "revisions", "author", "page-attributes", "post-formats"],
        "show_in_graphql" => false,
    ];

    register_post_type("clubs", $args);

    /**
     * Post Type: About Us.
     */

    $labels = [
        "name" => esc_html__("About Us", "custom-post-type-ui"),
        "singular_name" => esc_html__("About Us", "custom-post-type-ui"),
    ];

    $args = [
        "label" => esc_html__("About Us", "custom-post-type-ui"),
        "labels" => $labels,
        "description" => "",
        "public" => true,
        "publicly_queryable" => true,
        "show_ui" => true,
        "show_in_rest" => true,
        "rest_base" => "",
        "rest_controller_class" => "WP_REST_Posts_Controller",
        "rest_namespace" => "wp/v2",
        "has_archive" => false,
        "show_in_menu" => true,
        "show_in_nav_menus" => true,
        "delete_with_user" => false,
        "exclude_from_search" => false,
        "capability_type" => "post",
        "map_meta_cap" => true,
        "hierarchical" => false,
        "can_export" => false,
        "rewrite" => ["slug" => "about", "with_front" => true],
        "query_var" => true,
        "menu_icon" => "dashicons-admin-home",
        "supports" => ["title", "editor", "thumbnail", "excerpt", "custom-fields", "comments", "revisions", "author"],
        "show_in_graphql" => false,
    ];

    register_post_type("about", $args);

    /**
     * Post Type: Staffs.
     */

    $labels = [
        "name" => esc_html__("Staffs", "custom-post-type-ui"),
        "singular_name" => esc_html__("Staff", "custom-post-type-ui"),
    ];

    $args = [
        "label" => esc_html__("Staffs", "custom-post-type-ui"),
        "labels" => $labels,
        "description" => "",
        "public" => true,
        "publicly_queryable" => true,
        "show_ui" => true,
        "show_in_rest" => true,
        "rest_base" => "",
        "rest_controller_class" => "WP_REST_Posts_Controller",
        "rest_namespace" => "wp/v2",
        "has_archive" => false,
        "show_in_menu" => true,
        "show_in_nav_menus" => true,
        "delete_with_user" => false,
        "exclude_from_search" => false,
        "capability_type" => "post",
        "map_meta_cap" => true,
        "hierarchical" => false,
        "can_export" => false,
        "rewrite" => ["slug" => "staff", "with_front" => true],
        "query_var" => true,
        "menu_icon" => "dashicons-universal-access-alt",
        "supports" => ["title", "custom-fields"],
        "show_in_graphql" => false,
    ];

    register_post_type("staff", $args);

    /**
     * Post Type: Management Teams.
     */

    $labels = [
        "name" => esc_html__("Management Teams", "custom-post-type-ui"),
        "singular_name" => esc_html__("Management Team", "custom-post-type-ui"),
    ];

    $args = [
        "label" => esc_html__("Management Teams", "custom-post-type-ui"),
        "labels" => $labels,
        "description" => "",
        "public" => true,
        "publicly_queryable" => true,
        "show_ui" => true,
        "show_in_rest" => true,
        "rest_base" => "",
        "rest_controller_class" => "WP_REST_Posts_Controller",
        "rest_namespace" => "wp/v2",
        "has_archive" => false,
        "show_in_menu" => true,
        "show_in_nav_menus" => true,
        "delete_with_user" => false,
        "exclude_from_search" => false,
        "capability_type" => "post",
        "map_meta_cap" => true,
        "hierarchical" => false,
        "can_export" => false,
        "rewrite" => ["slug" => "management_committe", "with_front" => true],
        "query_var" => true,
        "menu_icon" => "dashicons-image-filter",
        "supports" => ["title", "custom-fields"],
        "show_in_graphql" => false,
    ];

    register_post_type("management_committe", $args);
}

add_action('init', 'cptui_register_my_cpts');
