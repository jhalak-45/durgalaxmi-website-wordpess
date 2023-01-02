<?php
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

new Responsive_Lightbox_Settings();

/**
 * Responsive Lightbox settings class.
 *
 * @class Responsive_Lightbox_Settings
 */
class Responsive_Lightbox_Settings {

	public $settings = [];
	public $tabs = [];
	public $scripts = [];

	/**
	 * Class constructor.
	 *
	 * @return void
	 */
	public function __construct() {
		// set instance
		Responsive_Lightbox()->settings = $this;

		// actions
		add_action( 'admin_init', [ $this, 'register_settings' ] );
		add_action( 'admin_menu', [ $this, 'admin_menu_options' ] );
		add_action( 'after_setup_theme', [ $this, 'load_defaults' ] );
		add_action( 'init', [ $this, 'init_builder' ] );
	}

	/**
	 * Initialize additional stuff for builder.
	 *
	 * @return void
	 */
	public function init_builder() {
		// add categories
		if ( Responsive_Lightbox()->options['builder']['gallery_builder'] && Responsive_Lightbox()->options['builder']['categories'] && Responsive_Lightbox()->options['builder']['archives'] ) {
			$terms = get_terms( [ 'taxonomy' => 'rl_category', 'hide_empty' => false ] );

			if ( ! empty( $terms ) ) {
				foreach ( $terms as $term ) {
					$this->settings['builder']['fields']['archives_category']['options'][$term->slug] = $term->name;
				}
			}
		}

		// flush rewrite rules if needed
		if ( isset( $_GET['flush_rules'] ) )
			flush_rewrite_rules();
	}

	/**
	 * Load default settings.
	 *
	 * @global string $pagenow
	 *
	 * @return void
	 */
	public function load_defaults() {
		// assign main instance
		$rl = Responsive_Lightbox();

		$this->scripts = apply_filters(
			'rl_settings_scripts',
			[
				'swipebox'		 =>  [
					'name'		 => __( 'SwipeBox', 'responsive-lightbox' ),
					'animations' => [
						'css'	 => __( 'CSS', 'responsive-lightbox' ),
						'jquery' => __( 'jQuery', 'responsive-lightbox' )
					],
					'supports'	=> [ 'title' ]
				],
				'prettyphoto'	 =>  [
					'name'				 => __( 'prettyPhoto', 'responsive-lightbox' ),
					'animation_speeds'	 => [
						'slow'	 => __( 'slow', 'responsive-lightbox' ),
						'normal' => __( 'normal', 'responsive-lightbox' ),
						'fast'	 => __( 'fast', 'responsive-lightbox' )
					],
					'themes'			 => [
						'pp_default'	 => __( 'default', 'responsive-lightbox' ),
						'light_rounded'	 => __( 'light rounded', 'responsive-lightbox' ),
						'dark_rounded'	 => __( 'dark rounded', 'responsive-lightbox' ),
						'light_square'	 => __( 'light square', 'responsive-lightbox' ),
						'dark_square'	 => __( 'dark square', 'responsive-lightbox' ),
						'facebook'		 => __( 'facebook', 'responsive-lightbox' )
					],
					'wmodes'			 => [
						'window'		 => __( 'window', 'responsive-lightbox' ),
						'transparent'	 => __( 'transparent', 'responsive-lightbox' ),
						'opaque'		 => __( 'opaque', 'responsive-lightbox' ),
						'direct'		 => __( 'direct', 'responsive-lightbox' ),
						'gpu'			 => __( 'gpu', 'responsive-lightbox' )
					],
					'supports'	=> [ 'inline', 'iframe', 'ajax', 'title', 'caption' ]
				],
				'fancybox'		 =>  [
					'name'			 => __( 'FancyBox', 'responsive-lightbox' ),
					'transitions'	 => [
						'elastic'	 => __( 'elastic', 'responsive-lightbox' ),
						'fade'		 => __( 'fade', 'responsive-lightbox' ),
						'none'		 => __( 'none', 'responsive-lightbox' )
					],
					'scrollings'	 => [
						'auto'	 => __( 'auto', 'responsive-lightbox' ),
						'yes'	 => __( 'yes', 'responsive-lightbox' ),
						'no'	 => __( 'no', 'responsive-lightbox' )
					],
					'easings'		 => [
						'swing'	 => __( 'swing', 'responsive-lightbox' ),
						'linear' => __( 'linear', 'responsive-lightbox' )
					],
					'positions'		 => [
						'outside'	 => __( 'outside', 'responsive-lightbox' ),
						'inside'	 => __( 'inside', 'responsive-lightbox' ),
						'over'		 => __( 'over', 'responsive-lightbox' )
					],
					'supports'	=> [ 'inline', 'iframe', 'ajax', 'title' ]
				],
				'nivo'			=> [
					'name'		=> __( 'Nivo Lightbox', 'responsive-lightbox' ),
					'effects'	=> [
						'fade'		 => __( 'fade', 'responsive-lightbox' ),
						'fadeScale'	 => __( 'fade scale', 'responsive-lightbox' ),
						'slideLeft'	 => __( 'slide left', 'responsive-lightbox' ),
						'slideRight' => __( 'slide right', 'responsive-lightbox' ),
						'slideUp'	 => __( 'slide up', 'responsive-lightbox' ),
						'slideDown'	 => __( 'slide down', 'responsive-lightbox' ),
						'fall'		 => __( 'fall', 'responsive-lightbox' )
					],
					'supports'	=> [ 'inline', 'iframe', 'ajax', 'title' ]
				],
				'imagelightbox'	=> [
					'name'		=> __( 'Image Lightbox', 'responsive-lightbox' ),
					'supports'	=> []
				],
				'tosrus'		=> [
					'name'		=> __( 'TosRUs', 'responsive-lightbox' ),
					'supports'	=> [ 'inline', 'title' ]
				],
				'featherlight'	=> [
					'name'		=> __( 'Featherlight', 'responsive-lightbox' ),
					'supports'	=> [ 'inline', 'iframe', 'ajax' ]
				],
				'magnific'	 	=> [
					'name'		=> __( 'Magnific Popup', 'responsive-lightbox' ),
					'supports'	=> [ 'inline', 'iframe', 'ajax', 'title', 'caption' ]
				]
			]
		);

		$this->image_titles = [
			'default'		=> __( 'None', 'responsive-lightbox' ),
			'title'	 		=> __( 'Image Title', 'responsive-lightbox' ),
			'caption'		=> __( 'Image Caption', 'responsive-lightbox' ),
			'alt'	 		=> __( 'Image Alt Text', 'responsive-lightbox' ),
			'description'	=> __( 'Image Description', 'responsive-lightbox' )
		];

		// get scripts
		foreach ( $this->scripts as $key => $value ) {
			$scripts[$key] = $value['name'];
		}

		// get image sizes
		$sizes = apply_filters(
			'image_size_names_choose',
			[
				'thumbnail'	=> __( 'Thumbnail', 'responsive-lightbox' ),
				'medium'	=> __( 'Medium', 'responsive-lightbox' ),
				'large'		=> __( 'Large', 'responsive-lightbox' ),
				'full'		=> __( 'Full Size', 'responsive-lightbox' )
			]
		);

		// prepare galeries
		$galleries = $builder_galleries = wp_parse_args( apply_filters( 'rl_gallery_types', [] ), $rl->gallery_types );

		unset( $builder_galleries['default'] );

		$this->settings = [
			'settings' => [
				'option_group'	=> 'responsive_lightbox_settings',
				'option_name'	=> 'responsive_lightbox_settings',
				'sections'		=> [
					'responsive_lightbox_settings' => [
						'title' 		=> __( 'General Settings', 'responsive-lightbox' )
					]
				],
				'prefix'		=> 'rl',
				'fields' => [
					'tour' => [
						'title' => __( 'Introduction Tour', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_settings',
						'type' => 'button',
						'label' => __( 'Start Tour', 'responsive-lightbox' ),
						'description' => __( 'Take this tour to quickly learn about the use of this plugin.', 'responsive-lightbox' ),
						'classname' => 'button-primary button-hero',
					],
					'script' => [
						'title' => __( 'Default lightbox', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_settings',
						'type' => 'select',
						'label' => '',
						'description' => sprintf( __( 'Select your preferred ligthbox effect script or get our <a href="%s">premium extensions</a>.', 'responsive-lightbox' ), wp_nonce_url( add_query_arg( [ 'action' => 'rl-hide-notice' ], admin_url( 'admin.php?page=responsive-lightbox-addons' ) ), 'rl_action', 'rl_nonce' ) ),
						'options' => $scripts
					],
					'selector' => [
						'title' => __( 'Selector', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_settings',
						'type' => 'text',
						'description' => __( 'Enter the rel selector lightbox effect will be applied to.', 'responsive-lightbox' )
					],
					'image_links' => [
						'title' => __( 'Images', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_settings',
						'type' => 'boolean',
						'label' => __( 'Enable lightbox for WordPress image links.', 'responsive-lightbox' )
					],
					'image_title' => [
						'title' => __( 'Single image title', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_settings',
						'type' => 'select',
						'description' => __( 'Select title for single images.', 'responsive-lightbox' ),
						'options' => $this->image_titles
					],
					'image_caption' => [
						'title' => __( 'Single image caption', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_settings',
						'type' => 'select',
						'description' => __( 'Select caption for single images (if supported by selected lightbox and/or gallery).', 'responsive-lightbox' ),
						'options' => $this->image_titles
					],
					'images_as_gallery' => [
						'title' => __( 'Single images as gallery', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_settings',
						'type' => 'boolean',
						'label' => __( 'Display single post images as a gallery.', 'responsive-lightbox' )
					],
					'galleries' => [
						'title' => __( 'Galleries', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_settings',
						'type' => 'boolean',
						'label' => __( 'Enable lightbox for WordPress image galleries.', 'responsive-lightbox' )
					],
					'default_gallery' => [
						'title' => __( 'WordPress gallery', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_settings',
						'type' => 'select',
						'description' => __( 'Select your preferred default WordPress gallery style.', 'responsive-lightbox' ),
						'options' => $galleries
					],
					'builder_gallery' => [
						'title' => __( 'Builder gallery', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_settings',
						'type' => 'select',
						'description' => __( 'Select your preferred default builder gallery style.', 'responsive-lightbox' ),
						'options' => $builder_galleries
					],
					'default_woocommerce_gallery' => [
						'title' => __( 'WooCommerce gallery', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_settings',
						'type' => 'select',
						'disabled' => ! class_exists( 'WooCommerce' ),
						'description' => __( 'Select your preferred gallery style for WooCommerce product gallery.', 'responsive-lightbox' ),
						'options' => $galleries
					],
					'gallery_image_size' => [
						'title' => __( 'Gallery image size', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_settings',
						'type' => 'select',
						'description' => __( 'Select image size for gallery image links.', 'responsive-lightbox' ),
						'options' => $sizes
					],
					'gallery_image_title' => [
						'title' => __( 'Gallery image title', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_settings',
						'type' => 'select',
						'description' => __( 'Select title for the gallery images.', 'responsive-lightbox' ),
						'options' => $this->image_titles
					],
					'gallery_image_caption' => [
						'title' => __( 'Gallery image caption', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_settings',
						'type' => 'select',
						'description' => __( 'Select caption for the gallery images (if supported by selected lightbox and/or gallery).', 'responsive-lightbox' ),
						'options' => $this->image_titles
					],
					'videos' => [
						'title' => __( 'Videos', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_settings',
						'type' => 'boolean',
						'label' => __( 'Enable lightbox for YouTube and Vimeo video links.', 'responsive-lightbox' )
					],
					'widgets' => [
						'title' => __( 'Widgets', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_settings',
						'type' => 'boolean',
						'label' => __( 'Enable lightbox for widgets content.', 'responsive-lightbox' )
					],
					'comments' => [
						'title' => __( 'Comments', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_settings',
						'type' => 'boolean',
						'label' => __( 'Enable lightbox for comments content.', 'responsive-lightbox' )
					],
					'force_custom_gallery' => [
						'title' => __( 'Force lightbox', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_settings',
						'type' => 'boolean',
						'label' => __( 'Try to force lightbox for custom WP gallery replacements, like Jetpack or Visual Composer galleries.', 'responsive-lightbox' )
					],
					'woocommerce_gallery_lightbox' => [
						'title' => __( 'WooCommerce lightbox', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_settings',
						'type' => 'boolean',
						'label' => __( 'Replace WooCommerce product gallery lightbox.', 'responsive-lightbox' ),
						'disabled' => ! class_exists( 'WooCommerce' ) || Responsive_Lightbox()->options['settings']['default_woocommerce_gallery'] !== 'default'
					],
					'enable_custom_events' => [
						'title' => __( 'Custom events', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_settings',
						'type' => 'multiple',
						'fields' => [
							'enable_custom_events' => [
								'type' => 'boolean',
								'label' => __( 'Enable triggering lightbox on custom jQuery events.', 'responsive-lightbox' )
							],
							'custom_events' => [
								'type' => 'text',
								'description' => __( 'Enter a space separated list of events.', 'responsive-lightbox' )
							]
						]
					],
					'loading_place' => [
						'title' => __( 'Loading place', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_settings',
						'type' => 'radio',
						'description' => __( 'Select where all the lightbox scripts should be placed.', 'responsive-lightbox' ),
						'options' => [
							'header' => __( 'Header', 'responsive-lightbox' ),
							'footer' => __( 'Footer', 'responsive-lightbox' )
						]
					],
					'conditional_loading' => [
						'title' => __( 'Conditional loading', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_settings',
						'type' => 'boolean',
						'label' => __( 'Enable to load scripts and styles only on pages that have images or galleries in post content.', 'responsive-lightbox' )
					],
					'deactivation_delete' => [
						'title' => __( 'Delete data', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_settings',
						'type' => 'boolean',
						'label' => __( 'Delete all plugin settings on deactivation.', 'responsive-lightbox' ),
						'description' => __( 'Enable this to delete all plugin settings and also delete all plugin capabilities from all users on deactivation.', 'responsive-lightbox' )
					]
				]
			],
			'builder' => [
				'option_group'	=> 'responsive_lightbox_builder',
				'option_name'	=> 'responsive_lightbox_builder',
				'sections'		=> [
					'responsive_lightbox_builder' => [
						'title' 		=> __( 'Gallery Builder Settings', 'responsive-lightbox' )
					]
				],
				'prefix'		=> 'rl',
				'fields' => [
					'gallery_builder' => [
						'title' => __( 'Gallery Builder', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_builder',
						'type' => 'boolean',
						'label' => __( 'Enable advanced gallery builder.', 'responsive-lightbox' )
					],
					'categories' => [
						'title' => __( 'Categories', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_builder',
						'type' => 'boolean',
						'label' => __( 'Enable Gallery Categories.', 'responsive-lightbox' ),
						'description' => __( 'Enable if you want to use Gallery Categories.', 'responsive-lightbox' )
					],
					'tags' => [
						'title' => __( 'Tags', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_builder',
						'type' => 'boolean',
						'label' => __( 'Enable Gallery Tags.', 'responsive-lightbox' ),
						'description' => __( 'Enable if you want to use Gallery Tags.', 'responsive-lightbox' )
					],
					'permalink' => [
						'title' => __( 'Gallery Permalink', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_builder',
						'type' => 'text',
						'description' => '<code>' . site_url() . '/<strong>' . untrailingslashit( esc_html( $rl->options['builder']['permalink'] ) ) . '</strong>/</code><br />' . esc_html__( 'Enter gallery page slug.', 'responsive-lightbox' )
					],
					'permalink_categories' => [
						'title' => __( 'Categories Permalink', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_builder',
						'type' => 'text',
						'description' => '<code>' . site_url() . '/<strong>' . untrailingslashit( esc_html( $rl->options['builder']['permalink_categories'] ) ) . '</strong>/</code><br />' . esc_html__( 'Enter gallery categories archive page slug.', 'responsive-lightbox' )
					],
					'permalink_tags' => [
						'title' => __( 'Tags Permalink', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_builder',
						'type' => 'text',
						'description' => '<code>' . site_url() . '/<strong>' . untrailingslashit( esc_html( $rl->options['builder']['permalink_tags'] ) ) . '</strong>/</code><br />' . esc_html__( 'Enter gallery tags archive page slug.', 'responsive-lightbox' )
					],
					'archives' => [
						'title' => __( 'Archives', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_builder',
						'type' => 'boolean',
						'label' => __( 'Enable gallery archives.', 'responsive-lightbox' )
					],
					'archives_category' => [
						'title' => __( 'Archives category', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_builder',
						'type' => 'select',
						'description' => __( 'Select category for gallery archives.', 'responsive-lightbox' ),
						'options' => [
							'all' => __( 'All', 'responsive-lightbox' )
						]
					]
				]
			],
			'folders' => [
				'option_group'	=> 'responsive_lightbox_folders',
				'option_name'	=> 'responsive_lightbox_folders',
				'sections'		=> [
					'responsive_lightbox_folders' => [
						'title' 		=> __( 'Folders Settings', 'responsive-lightbox' )
					]
				],
				'prefix'		=> 'rl',
				'fields' => [
					'active' => [
						'title' => __( 'Folders', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_folders',
						'type' => 'boolean',
						'label' => __( 'Enable media folders.', 'responsive-lightbox' )
					],
					'media_taxonomy' => [
						'title' => __( 'Media taxonomy', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_folders',
						'type' => 'select',
						'description' => __( 'Select media taxonomy.', 'responsive-lightbox' ) . '<br />' . __( 'If you have ever used custom media taxonomies you may try to <a id="rl_folders_load_old_taxonomies" href="#">load and use them.</a>', 'responsive-lightbox' ),
						'after_field' => '<span class="spinner rl-spinner"></span>',
						'options' => [ $rl->options['folders']['media_taxonomy'] => $rl->options['folders']['media_taxonomy'] . ' (' . __( 'Folders', 'responsive-lightbox' ) . ')' ]
					],
					'media_tags' => [
						'title' => __( 'Media tags', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_folders',
						'type' => 'boolean',
						'label' => __( 'Enable media tags.', 'responsive-lightbox' ),
						'description' => __( 'Enable if you want to use media tags.', 'responsive-lightbox' )
					],
					'show_in_menu' => [
						'title' => __( 'Show in menu', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_folders',
						'type' => 'boolean',
						'label' => __( 'Enable to show the taxonomy in the admin menu.', 'responsive-lightbox' )
					],
					'folders_removal' => [
						'title' => __( 'Subfolder removal', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_folders',
						'type' => 'boolean',
						'label' => __( 'Select to remove subfolders when parent folder is deleted.', 'responsive-lightbox' )
					],
					'jstree_wholerow' => [
						'title' => __( 'Whole row', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_folders',
						'type' => 'boolean',
						'label' => __( 'Enable to highlight folder\'s row as a clickable area.', 'responsive-lightbox' )
					]
				]
			],
			'remote_library' => [
				'option_group'	=> 'responsive_lightbox_remote_library',
				'option_name'	=> 'responsive_lightbox_remote_library',
				'sections'		=> [
					'responsive_lightbox_remote_library' => [
						'title' => __( 'Remote Library Settings', 'responsive-lightbox' )
					],
					'responsive_lightbox_remote_library_providers' => [
						'title' => __( 'Media Providers', 'responsive-lightbox' ),
						'page' => 'responsive_lightbox_remote_library',
						'callback' => [ $this, 'remote_library_providers_description' ]
					]
				],
				'prefix'		=> 'rl',
				'fields'		=> [
					'active' => [
						'title' => __( 'Remote Library', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_remote_library',
						'type' => 'boolean',
						'label' => __( 'Enable remote libraries.', 'responsive-lightbox' ),
						'description' => __( 'Check this to enable remote access to the following image libraries.', 'responsive-lightbox' )
					],
					'caching' => [
						'title' => __( 'Caching', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_remote_library',
						'type' => 'boolean',
						'label' => __( 'Enable remote library requests caching.', 'responsive-lightbox' )
					],
					'cache_expiry' => [
						'title' => __( 'Cache expiry', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_remote_library',
						'type' => 'number',
						'min' => 1,
						'description' => __( 'Enter the cache expiry time.', 'responsive-lightbox' ),
						'append' => __( 'hour(s)', 'responsive-lightbox' )
					]
				]
			],
			'configuration' => [
				'option_group'	=> 'responsive_lightbox_configuration',
				'option_name'	=> 'responsive_lightbox_configuration',
				'sections'		=> [
					'responsive_lightbox_configuration' => [
						'title' 		=> sprintf( __( '%s Settings', 'responsive-lightbox' ), ( isset( $this->scripts[$rl->options['settings']['script']]['name'] ) ? $this->scripts[$rl->options['settings']['script']]['name'] : $this->scripts[$rl->defaults['settings']['script']]['name'] ) )
					],
				],
				'prefix'		=> 'rl',
				'fields'		=> []
			],
			'capabilities' => [
				'option_group'	=> 'responsive_lightbox_capabilities',
				'option_name'	=> 'responsive_lightbox_capabilities',
				'callback'		=> [ $this, 'validate_capabilities' ],
				'sections'		=> [
					'responsive_lightbox_capabilities_fields' => [
						'title' => __( 'Capabilities Settings', 'responsive-lightbox' ),
						'page' => 'responsive_lightbox_capabilities'
					],
					'responsive_lightbox_capabilities' => [
						'callback' => [ $this, 'capabilities_table' ]
					]
				],
				'prefix'		=> 'rl',
				'fields'		=> [
					'active' => [
						'title' => __( 'Capabilities', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_capabilities_fields',
						'type' => 'boolean',
						'label' => __( 'Enable advanced capability management.', 'responsive-lightbox' ),
						'description' => __( 'Check this to enable access to plugin features for selected user roles.', 'responsive-lightbox' )
					]
				]
			],
			'basicgrid_gallery' => [
				'option_group' => 'responsive_lightbox_basicgrid_gallery',
				'option_name' => 'responsive_lightbox_basicgrid_gallery',
				'sections' => [
					'responsive_lightbox_basicgrid_gallery' => [
						'title' => __( 'Basic Grid Gallery Settings', 'responsive-lightbox' )
					]
				],
				'prefix' => 'rl',
				'fields' => [
					'screen_size_columns' => [
						'title' => __( 'Screen sizes', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_basicgrid_gallery',
						'type' => 'multiple',
						'description' => __( 'Number of columns in a gallery depending on the device screen size. (if greater than 0 overrides the Columns option)', 'responsive-lightbox' ),
						'fields' => [
							'columns_lg' => [
								'type' => 'number',
								'min' => 0,
								'max' => 6,
								'append' => __( 'large devices / desktops (&ge;1200px)', 'responsive-lightbox' )
							],
							'columns_md' => [
								'type' => 'number',
								'min' => 0,
								'max' => 6,
								'append' => __( 'medium devices / desktops (&ge;992px)', 'responsive-lightbox' )
							],
							'columns_sm' => [
								'type' => 'number',
								'min' => 0,
								'max' => 6,
								'append' => __( 'small devices / tablets (&ge;768px)', 'responsive-lightbox' )
							],
							'columns_xs' => [
								'type' => 'number',
								'min' => 0,
								'max' => 6,
								'append' => __( 'extra small devices / phones (<768px)', 'responsive-lightbox' )
							]
						]
					],
					'gutter' => [
						'title' => __( 'Gutter', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_basicgrid_gallery',
						'type' => 'number',
						'min' => 0,
						'description' => __( 'Set the pixel width between the columns and rows.', 'responsive-lightbox' ),
						'append' => 'px'
					],
					'force_height' => [
						'title' => __( 'Force height', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_basicgrid_gallery',
						'type' => 'boolean',
						'label' => __( 'Enable to force the thumbnail row height.', 'responsive-lightbox' )
					],
					'row_height' => [
						'title' => __( 'Row height', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_basicgrid_gallery',
						'type' => 'number',
						'min' => 50,
						'description' => __( 'Enter the thumbnail row height in pixels (used if Force height is enabled). Defaults to 150px.', 'responsive-lightbox' ),
						'append' => 'px'
					]
				]
			],
			'basicslider_gallery' => [
				'option_group' => 'responsive_lightbox_basicslider_gallery',
				'option_name' => 'responsive_lightbox_basicslider_gallery',
				'sections' => [
					'responsive_lightbox_basicslider_gallery' => [
						'title' => __( 'Basic Slider Gallery Settings', 'responsive-lightbox' )
					]
				],
				'prefix' => 'rl',
				'fields' => [
					'adaptive_height' => [
						'title' => __( 'Adaptive Height', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_basicslider_gallery',
						'type' => 'boolean',
						'label' => __( 'The slider height should change on the fly according to the current slide.', 'responsive-lightbox' )
					],
					'loop' => [
						'title' => __( 'Loop', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_basicslider_gallery',
						'type' => 'boolean',
						'label' => __( 'Whether the slider should loop (i.e. the first slide goes to the last, the last slide goes to the first).', 'responsive-lightbox' )
					],
					'captions' => [
						'title' => __( 'Captions Position', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_basicslider_gallery',
						'type' => 'select',
						'description' => __( 'Specifies the position of captions or no captions at all.', 'responsive-lightbox' ),
						'options' => [
							'none' => __( 'None', 'responsive-lightbox' ),
							'overlay' => __( 'Overlay', 'responsive-lightbox' ),
							'below' => __( 'Below', 'responsive-lightbox' )
						]
					],
					'init_single' => [
						'title' => __( 'Single Image Slider', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_basicslider_gallery',
						'type' => 'boolean',
						'label' => __( 'Whether the slider should initialize even if there is only one slide element.', 'responsive-lightbox' )
					],
					'responsive' => [
						'title' => __( 'Responsive', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_basicslider_gallery',
						'type' => 'boolean',
						'label' => __( 'Whether the slider should be responsive.', 'responsive-lightbox' )
					],
					'preload' => [
						'title' => __( 'Preload', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_basicslider_gallery',
						'type' => 'select',
						'description' => __( 'Elements that are preloaded before slider shows.', 'responsive-lightbox' ),
						'options' => [
							'all' => __( 'All', 'responsive-lightbox' ),
							'visible' => __( 'Only visible', 'responsive-lightbox' )
						]
					],
					'pager' => [
						'title' => __( 'Pager', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_basicslider_gallery',
						'type' => 'boolean',
						'label' => __( 'Whether the slider should have a pager.', 'responsive-lightbox' )
					],
					'controls' => [
						'title' => __( 'Controls', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_basicslider_gallery',
						'type' => 'boolean',
						'label' => __( 'Whether the slider should have controls (next, previous arrows).', 'responsive-lightbox' )
					],
					'hide_on_end' => [
						'title' => __( 'Hide Controls on End', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_basicslider_gallery',
						'type' => 'boolean',
						'label' => __( 'Hide the previous or next control when it reaches the first or last slide respectively.', 'responsive-lightbox' )
					],
					'slide_margin' => [
						'title' => __( 'Slide Margin', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_basicslider_gallery',
						'type' => 'number',
						'min' => 0,
						'description' => __( 'The spacing between slides.', 'responsive-lightbox' ),
						'append' => '%'
					],
					'transition' => [
						'title' => __( 'Transition', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_basicslider_gallery',
						'type' => 'select',
						'description' => __( 'Transition type to use, or no transitions.', 'responsive-lightbox' ),
						'options' => [
							'none' => __( 'None', 'responsive-lightbox' ),
							'fade' => __( 'Fade', 'responsive-lightbox' ),
							'horizontal' => __( 'Horizontal', 'responsive-lightbox' ),
							'vertical' => __( 'Vertical', 'responsive-lightbox' ),
							'kenburns' => __( 'Ken Burns', 'responsive-lightbox' )
						]
					],
					'kenburns_zoom' => [
						'title' => __( 'Ken Burns Zoom', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_basicslider_gallery',
						'type' => 'number',
						'min' => 0,
						'description' => __( 'Max zoom level use for the Ken Burns transition.', 'responsive-lightbox' ),
						'append' => '%'
					],
					'speed' => [
						'title' => __( 'Transition Speed', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_basicslider_gallery',
						'type' => 'number',
						'min' => 0,
						'description' => __( 'The time the transition takes to complete.', 'responsive-lightbox' ),
						'append' => 'ms'
					],
					'easing' => [
						'title' => __( 'Easing Effect', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_basicslider_gallery',
						'type' => 'select',
						'description' => __( 'The easing effect to use for the selected transition.', 'responsive-lightbox' ),
						'options' => [
							'linear' => 'linear',
							'swing' => 'swing',
							'easeInQuad' => 'easeInQuad',
							'easeOutQuad' => 'easeOutQuad',
							'easeInOutQuad' => 'easeInOutQuad',
							'easeInCubic' => 'easeInCubic',
							'easeOutCubic' => 'easeOutCubic',
							'easeInOutCubic' => 'easeInOutCubic',
							'easeInQuart' => 'easeInQuart',
							'easeOutQuart' => 'easeOutQuart',
							'easeInOutQuart' => 'easeInOutQuart',
							'easeInQuint' => 'easeInQuint',
							'easeOutQuint' => 'easeOutQuint',
							'easeInOutQuint' => 'easeInOutQuint',
							'easeInExpo' => 'easeInExpo',
							'easeOutExpo' => 'easeOutExpo',
							'easeInOutExpo' => 'easeInOutExpo',
							'easeInSine' => 'easeInSine',
							'easeOutSine' => 'easeOutSine',
							'easeInOutSine' => 'easeInOutSine',
							'easeInCirc' => 'easeInCirc',
							'easeOutCirc' => 'easeOutCirc',
							'easeInOutCirc' => 'easeInOutCirc',
							'easeInElastic' => 'easeInElastic',
							'easeOutElastic' => 'easeOutElastic',
							'easeInOutElastic' => 'easeInOutElastic',
							'easeInBack' => 'easeInBack',
							'easeOutBack' => 'easeOutBack',
							'easeInOutBack' => 'easeInOutBack',
							'easeInBounce' => 'easeInBounce',
							'easeOutBounce' => 'easeOutBounce',
							'easeInOutBounce' => 'easeInOutBounce'
						]
					],
					'continuous' => [
						'title' => __( 'Continuous', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_basicslider_gallery',
						'type' => 'boolean',
						'label' => __( 'Whether the slider should run continuously (seamless transition between the first and last slides).', 'responsive-lightbox' )
					],
					'use_css' => [
						'title' => __( 'Use CSS', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_basicslider_gallery',
						'type' => 'boolean',
						'label' => __( 'Whether the slider should use CSS transitions. If the user\'s browser doesn\'t support CSS transitions the slider will fallback to jQuery.', 'responsive-lightbox' )
					],
					'slideshow' => [
						'title' => __( 'Slideshow', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_basicslider_gallery',
						'type' => 'boolean',
						'label' => __( 'Whether the slider should run automatically on load.', 'responsive-lightbox' )
					],
					'slideshow_direction' => [
						'title' => __( 'Slideshow Direction', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_basicslider_gallery',
						'type' => 'select',
						'description' => __( 'Which direction the slider should move in if in slideshow mode.', 'responsive-lightbox' ),
						'options' => [
							'next' => __( 'Next', 'responsive-lightbox' ),
							'prev' => __( 'Previous', 'responsive-lightbox' )
						]
					],
					'slideshow_hover' => [
						'title' => __( 'Slideshow Hover', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_basicslider_gallery',
						'type' => 'boolean',
						'label' => __( 'Whether the slideshow should pause automatically on hover.', 'responsive-lightbox' )
					],
					'slideshow_hover_delay' => [
						'title' => __( 'Slideshow Hover Delay', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_basicslider_gallery',
						'type' => 'number',
						'min' => 0,
						'description' => __( 'The delay (if any) before the slider resumes automatically after hover.', 'responsive-lightbox' ),
						'append' => 'ms'
					],
					'slideshow_delay' => [
						'title' => __( 'Slideshow Delay', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_basicslider_gallery',
						'type' => 'number',
						'min' => 0,
						'description' => __( 'The delay (if any) before the slider runs automatically on load.', 'responsive-lightbox' ),
						'append' => 'ms'
					],
					'slideshow_pause' => [
						'title' => __( 'Slideshow Pause', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_basicslider_gallery',
						'type' => 'number',
						'min' => 0,
						'description' => __( 'The time a slide lasts.', 'responsive-lightbox' ),
						'append' => 'ms'
					]
				]
			],
			'basicmasonry_gallery' => [
				'option_group' => 'responsive_lightbox_basicmasonry_gallery',
				'option_name' => 'responsive_lightbox_basicmasonry_gallery',
				'sections' => [
					'responsive_lightbox_basicmasonry_gallery' => [
						'title' => __( 'Basic Masonry Gallery Settings', 'responsive-lightbox' )
					]
				],
				'prefix' => 'rl',
				'fields' => [
					'screen_size_columns' => [
						'title' => __( 'Screen sizes', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_basicmasonry_gallery',
						'type' => 'multiple',
						'description' => __( 'Number of columns in a gallery depending on the device screen size. (if greater than 0 overrides the Columns option)', 'responsive-lightbox' ),
						'fields' => [
							'columns_lg' => [
								'type' => 'number',
								'min' => 0,
								'max' => 6,
								'default' => 4,
								'append' => __( 'large devices / desktops (&ge;1200px)', 'responsive-lightbox' )
							],
							'columns_md' => [
								'type' => 'number',
								'min' => 0,
								'max' => 6,
								'default' => 3,
								'append' => __( 'medium devices / desktops (&ge;992px)', 'responsive-lightbox' )
							],
							'columns_sm' => [
								'type' => 'number',
								'min' => 0,
								'max' => 6,
								'default' => 2,
								'append' => __( 'small devices / tablets (&ge;768px)', 'responsive-lightbox' )
							],
							'columns_xs' => [
								'type' => 'number',
								'min' => 0,
								'max' => 6,
								'default' => 2,
								'append' => __( 'extra small devices / phones (<768px)', 'responsive-lightbox' )
							]
						]
					],
					'gutter' => [
						'title' => __( 'Gutter', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_basicmasonry_gallery',
						'type' => 'number',
						'description' => __( 'Horizontal space between gallery items.', 'responsive-lightbox' ),
						'append' => 'px'
					],
					'margin' => [
						'title' => __( 'Margin', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_basicmasonry_gallery',
						'type' => 'number',
						'description' => __( 'Vertical space between gallery items.', 'responsive-lightbox' ),
						'append' => 'px'
					],
					'origin_left' => [
						'title' => __( 'Origin Left', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_basicmasonry_gallery',
						'type' => 'boolean',
						'label' => __( 'Enable left-to-right layouts.', 'responsive-lightbox' ),
						'description' => __( 'Controls the horizontal flow of the layout. By default, item elements start positioning at the left. Uncheck it for right-to-left layouts.', 'responsive-lightbox' )
					],
					'origin_top' => [
						'title' => __( 'Origin Top', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_basicmasonry_gallery',
						'type' => 'boolean',
						'label' => __( 'Enable top-to-bottom layouts.', 'responsive-lightbox' ),
						'description' => __( 'Controls the vetical flow of the layout. By default, item elements start positioning at the top. Uncheck it for bottom-up layouts.', 'responsive-lightbox' )
					]
				]
			]
		];

		$this->tabs = apply_filters(
			'rl_settings_tabs',
			[
				'settings' => [
					'name'		=> __( 'General', 'responsive-lightbox' ),
					'key'		=> 'responsive_lightbox_settings',
					'submit'	=> 'save_rl_settings',
					'reset'		=> 'reset_rl_settings'
				],
				'configuration' => [
					'name'				=> __( 'Lightboxes', 'responsive-lightbox' ),
					'key'				=> 'responsive_lightbox_configuration',
					'submit'			=> 'save_' . $this->settings['configuration']['prefix'] . '_configuration',
					'reset'				=> 'reset_' . $this->settings['configuration']['prefix'] . '_configuration',
					'sections'			=> $scripts,
					'default_section'	=> $rl->options['settings']['script']
				],
				'basicgrid_gallery' => [
					'name'		=> __( 'Basic Grid', 'responsive-lightbox' ),
					'key'		=> 'responsive_lightbox_basicgrid_gallery',
					'submit'	=> 'save_rl_basicgrid_gallery',
					'reset'		=> 'reset_rl_basicgrid_gallery'
				],
				'basicslider_gallery' => [
					'name'		=> __( 'Basic Slider', 'responsive-lightbox' ),
					'key'		=> 'responsive_lightbox_basiclider_gallery',
					'submit'	=> 'save_rl_basiclider_gallery',
					'reset'		=> 'reset_rl_basiclider_gallery'
				],
				'basicmasonry_gallery' => [
					'name'		=> __( 'Basic Masonry', 'responsive-lightbox' ),
					'key'		=> 'responsive_lightbox_basicmasonry_gallery',
					'submit'	=> 'save_rl_basicmasonry_gallery',
					'reset'		=> 'reset_rl_basicmasonry_gallery'
				]
			]
		);

		$tabs_copy = $this->tabs;
		$tab_key = '';
		$section_key = isset( $_REQUEST['section'] ) ? sanitize_key( $_REQUEST['section'] ) : '';

		// set current tab and section
		if ( is_admin() && ! wp_doing_ajax() ) {
			global $pagenow;

			// check page
			$page = isset( $_GET['page'] ) ? sanitize_key( $_GET['page'] ) : '';

			// check settings page
			if ( $pagenow === 'options.php' || ( $pagenow == 'admin.php' && $page && preg_match( '/^responsive-lightbox-(' . implode( '|', array_keys( $this->tabs + [ 'gallery' => '', 'addons' => '' ] ) ) . ')$/', $page, $tabs ) === 1 ) ) {
				// set tab key
				$tab_key = isset( $tabs[1] ) ? $tabs[1] : 'settings';

				// set section key
				if ( ! $section_key )
					$section_key = ! empty( $this->tabs[$tab_key]['default_section'] ) ? $this->tabs[$tab_key]['default_section'] : '';
			}
		}

		// get default gallery types
		$gallery_types = $rl->gallery_types;

		// remove default gallery
		if ( isset( $gallery_types['default'] ) )
			unset( $gallery_types['default'] );

		// get available galleries
		$gallery_types = apply_filters( 'rl_gallery_types', $gallery_types );

		if ( $gallery_types ) {
			foreach ( $gallery_types as $key => $name ) {
				unset( $gallery_types[$key] );

				$gallery_types[$key . '_gallery'] = $name;
			}
		}

		// backward compatibility, remove from tabs
		$gallery_tabs = array_intersect( array_keys( $this->tabs ), array_keys( $gallery_types ) );
		$galleries = [];

		if ( ! empty( $gallery_tabs ) ) {
			// unset tabs if exist
			foreach ( $gallery_tabs as $gallery_tab ) {
				$galleries[$gallery_tab] = $this->tabs[$gallery_tab];

				unset( $this->tabs[$gallery_tab] );
			}

			foreach ( $galleries as $key => $gallery ) {
				$gallery_sections[$key] = $gallery['name'];
			}

			if ( $tab_key == 'gallery' ) {
				if ( ! $section_key ) {
					$section_key = in_array( $rl->options['settings']['default_gallery'] . '_gallery', array_keys( $gallery_sections ) ) ? $rl->options['settings']['default_gallery'] . '_gallery' : key( $gallery_sections );
				}
			}

			$this->tabs['gallery'] = [
				'name'				=> __( 'Galleries', 'responsive-lightbox' ),
				'key'				=> 'responsive_lightbox_' . $section_key,
				'submit'			=> array_key_exists( $section_key, $tabs_copy ) ? $tabs_copy[$section_key]['submit'] : 'save_' . $section_key . '_configuration',
				'reset'				=> array_key_exists( $section_key, $tabs_copy ) ? $tabs_copy[$section_key]['reset'] : 'reset_rl_' . $section_key,
				'sections'			=> $gallery_sections,
				'default_section'	=> $section_key
			];
		}

		$this->tabs['builder'] = [
			'name'		=> __( 'Builder', 'responsive-lightbox' ),
			'key'		=> 'responsive_lightbox_builder',
			'submit'	=> 'save_rl_builder',
			'reset'		=> 'reset_rl_builder'
		];

		$this->tabs['folders'] = [
			'name'		=> __( 'Folders', 'responsive-lightbox' ),
			'key'		=> 'responsive_lightbox_folders',
			'submit'	=> 'save_rl_folders',
			'reset'		=> 'reset_rl_folders'
		];

		$this->tabs['capabilities'] = [
			'name'		=> __( 'Capabilities', 'responsive-lightbox' ),
			'key'		=> 'responsive_lightbox_capabilities',
			'submit'	=> 'save_rl_capabilities',
			'reset'		=> 'reset_rl_capabilities'
		];

		$this->tabs['remote_library'] = [
			'name'		=> __( 'Remote Library', 'responsive-lightbox' ),
			'key'		=> 'responsive_lightbox_remote_library',
			'submit'	=> 'save_rl_remote_library',
			'reset'		=> 'reset_rl_remote_library'
		];

		$this->tabs = apply_filters( 'rl_settings_tabs_extra', $this->tabs );

		// push licenses just before the addons
		if ( isset( $this->tabs['licenses'] ) ) {
			unset( $this->tabs['licenses'] );

			$this->tabs['licenses'] = [
				'name'		=> __( 'Licenses', 'responsive-lightbox' ),
				'key'		=> 'responsive_lightbox_licenses',
				'submit'	=> 'save_rl_licenses',
				'reset'		=> 'reset_rl_licenses'
			];
		}

		$this->tabs['addons'] = [
			'name'		=> __( 'Add-ons', 'responsive-lightbox' ),
			'key'		=> 'responsive_lightbox_configuration',
			'callback'	=> [ $this, 'addons_tab_cb' ]
		];

		if ( isset( $this->tabs[$tab_key]['sections'][$section_key] ) && empty( $this->tabs[$tab_key]['sections']['responsive_lightbox_' . $tab_key]['title'] ) )
			$this->settings[$tab_key]['sections']['responsive_lightbox_' . $tab_key]['title'] = sprintf( __( '%s Settings', 'responsive-lightbox' ), $this->tabs[$tab_key]['sections'][$section_key] );

		switch ( ! empty( $section_key ) ? $section_key : $rl->options['settings']['script'] ) {
			case 'swipebox':
				$this->settings['configuration']['prefix'] = 'rl_sb';
				$this->settings['configuration']['fields'] = [
					'animation' => [
						'title' => __( 'Animation type', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_configuration',
						'type' => 'radio',
						'label' => '',
						'description' => __( 'Select a method of applying a lightbox effect.', 'responsive-lightbox' ),
						'options' => $this->scripts['swipebox']['animations'],
						'parent' => 'swipebox'
					],
					'force_png_icons' => [
						'title' => __( 'Force PNG icons', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_configuration',
						'type' => 'boolean',
						'label' => __( 'Enable this if you\'re having problems with navigation icons not visible on some devices.', 'responsive-lightbox' ),
						'parent' => 'swipebox'
					],
					'hide_close_mobile' => [
						'title' => __( 'Hide close on mobile', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_configuration',
						'type' => 'boolean',
						'label' => __( 'Hide the close button on mobile devices.', 'responsive-lightbox' ),
						'parent' => 'swipebox'
					],
					'remove_bars_mobile' => [
						'title' => __( 'Remove bars on mobile', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_configuration',
						'type' => 'boolean',
						'label' => __( 'Hide the top and bottom bars on mobile devices.', 'responsive-lightbox' ),
						'parent' => 'swipebox'
					],
					'hide_bars' => [
						'title' => __( 'Top and bottom bars', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_configuration',
						'type' => 'multiple',
						'fields' => [
							'hide_bars' => [
								'type' => 'boolean',
								'label' => __( 'Hide top and bottom bars after a period of time.', 'responsive-lightbox' ),
								'parent' => 'swipebox'
							],
							'hide_bars_delay' => [
								'type' => 'number',
								'description' => __( 'Enter the time after which the top and bottom bars will be hidden (when hiding is enabled).', 'responsive-lightbox' ),
								'append' => 'ms',
								'parent' => 'swipebox'
							]
						]
					],
					'video_max_width' => [
						'title' => __( 'Video max width', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_configuration',
						'type' => 'number',
						'description' => __( 'Enter the max video width in a lightbox.', 'responsive-lightbox' ),
						'append' => 'px',
						'parent' => 'swipebox'
					],
					'loop_at_end' => [
						'title' => __( 'Loop at end', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_configuration',
						'type' => 'boolean',
						'label' => __( 'True will return to the first image after the last image is reached.', 'responsive-lightbox' ),
						'parent' => 'swipebox'
					]
				];
				break;

			case 'prettyphoto':
				$this->settings['configuration']['prefix'] = 'rl_pp';
				$this->settings['configuration']['fields'] = [
					'animation_speed' => [
						'title' => __( 'Animation speed', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_configuration',
						'type' => 'radio',
						'label' => '',
						'description' => __( 'Select animation speed for lightbox effect.', 'responsive-lightbox' ),
						'options' => $this->scripts['prettyphoto']['animation_speeds'],
						'parent' => 'prettyphoto'
					],
					'slideshow' => [
						'title' => __( 'Slideshow', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_configuration',
						'type' => 'multiple',
						'fields' => [
							'slideshow' => [
								'type' => 'boolean',
								'label' => __( 'Display images as slideshow', 'responsive-lightbox' ),
								'parent' => 'prettyphoto'
							],
							'slideshow_delay' => [
								'type' => 'number',
								'description' => __( 'Enter time (in miliseconds).', 'responsive-lightbox' ),
								'append' => 'ms',
								'parent' => 'prettyphoto'
							]
						]
					],
					'slideshow_autoplay' => [
						'title' => __( 'Slideshow autoplay', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_configuration',
						'type' => 'boolean',
						'label' => __( 'Automatically start slideshow.', 'responsive-lightbox' ),
						'parent' => 'prettyphoto'
					],
					'opacity' => [
						'title' => __( 'Opacity', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_configuration',
						'type' => 'range',
						'description' => __( 'Value between 0 and 100, 100 for no opacity.', 'responsive-lightbox' ),
						'min' => 0,
						'max' => 100,
						'parent' => 'prettyphoto'
					],
					'show_title' => [
						'title' => __( 'Show title', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_configuration',
						'type' => 'boolean',
						'label' => __( 'Display image title.', 'responsive-lightbox' ),
						'parent' => 'prettyphoto'
					],
					'allow_resize' => [
						'title' => __( 'Allow resize big images', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_configuration',
						'type' => 'boolean',
						'label' => __( 'Resize the photos bigger than viewport.', 'responsive-lightbox' ),
						'parent' => 'prettyphoto'
					],
					'allow_expand' => [
						'title' => __( 'Allow expand', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_configuration',
						'type' => 'boolean',
						'label' => __( 'Allow expanding images.', 'responsive-lightbox' ),
						'parent' => 'prettyphoto'
					],
					'width' => [
						'title' => __( 'Video width', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_configuration',
						'type' => 'number',
						'append' => 'px',
						'parent' => 'prettyphoto'
					],
					'height' => [
						'title' => __( 'Video height', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_configuration',
						'type' => 'number',
						'append' => 'px',
						'parent' => 'prettyphoto'
					],
					'theme' => [
						'title' => __( 'Theme', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_configuration',
						'type' => 'radio',
						'description' => __( 'Select the theme for lightbox effect.', 'responsive-lightbox' ),
						'options' => $this->scripts['prettyphoto']['themes'],
						'parent' => 'prettyphoto'
					],
					'horizontal_padding' => [
						'title' => __( 'Horizontal padding', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_configuration',
						'type' => 'number',
						'append' => 'px',
						'parent' => 'prettyphoto'
					],
					'hide_flash' => [
						'title' => __( 'Hide Flash', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_configuration',
						'type' => 'boolean',
						'label' => __( 'Hide all the flash objects on a page. Enable this if flash appears over prettyPhoto.', 'responsive-lightbox' ),
						'parent' => 'prettyphoto'
					],
					'wmode' => [
						'title' => __( 'Flash Window Mode (wmode)', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_configuration',
						'type' => 'radio',
						'description' => __( 'Select flash window mode.', 'responsive-lightbox' ),
						'options' => $this->scripts['prettyphoto']['wmodes'],
						'parent' => 'prettyphoto'
					],
					'video_autoplay' => [
						'title' => __( 'Video autoplay', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_configuration',
						'type' => 'boolean',
						'label' => __( 'Automatically start videos.', 'responsive-lightbox' ),
						'parent' => 'prettyphoto'
					],
					'modal' => [
						'title' => __( 'Modal', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_configuration',
						'type' => 'boolean',
						'label' => __( 'If set to true, only the close button will close the window.', 'responsive-lightbox' ),
						'parent' => 'prettyphoto'
					],
					'deeplinking' => [
						'title' => __( 'Deeplinking', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_configuration',
						'type' => 'boolean',
						'label' => __( 'Allow prettyPhoto to update the url to enable deeplinking.', 'responsive-lightbox' ),
						'parent' => 'prettyphoto'
					],
					'overlay_gallery' => [
						'title' => __( 'Overlay gallery', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_configuration',
						'type' => 'boolean',
						'label' => __( 'If enabled, a gallery will overlay the fullscreen image on mouse over.', 'responsive-lightbox' ),
						'parent' => 'prettyphoto'
					],
					'keyboard_shortcuts' => [
						'title' => __( 'Keyboard shortcuts', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_configuration',
						'type' => 'boolean',
						'label' => __( 'Set to false if you open forms inside prettyPhoto.', 'responsive-lightbox' ),
						'parent' => 'prettyphoto'
					],
					'social' => [
						'title' => __( 'Social (Twitter, Facebook)', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_configuration',
						'type' => 'boolean',
						'label' => __( 'Display links to Facebook and Twitter.', 'responsive-lightbox' ),
						'parent' => 'prettyphoto'
					]
				];
				break;

			case 'fancybox':
				$this->settings['configuration']['prefix'] = 'rl_fb';
				$this->settings['configuration']['fields'] = [
					'modal' => [
						'title' => __( 'Modal', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_configuration',
						'type' => 'boolean',
						'label' => __( 'When true, "overlayShow" is set to true and "hideOnOverlayClick", "hideOnContentClick", "enableEscapeButton", "showCloseButton" are set to false.', 'responsive-lightbox' ),
						'parent' => 'fancybox'
					],
					'show_overlay' => [
						'title' => __( 'Show overlay', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_configuration',
						'type' => 'boolean',
						'label' => __( 'Toggle overlay.', 'responsive-lightbox' ),
						'parent' => 'fancybox'
					],
					'show_close_button' => [
						'title' => __( 'Show close button', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_configuration',
						'type' => 'boolean',
						'label' => __( 'Toggle close button.', 'responsive-lightbox' ),
						'parent' => 'fancybox'
					],
					'enable_escape_button' => [
						'title' => __( 'Enable escape button', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_configuration',
						'type' => 'boolean',
						'label' => __( 'Toggle if pressing Esc button closes lightbox.', 'responsive-lightbox' ),
						'parent' => 'fancybox'
					],
					'hide_on_overlay_click' => [
						'title' => __( 'Hide on overlay click', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_configuration',
						'type' => 'boolean',
						'label' => __( 'Toggle if clicking the overlay should close FancyBox.', 'responsive-lightbox' ),
						'parent' => 'fancybox'
					],
					'hide_on_content_click' => [
						'title' => __( 'Hide on content click', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_configuration',
						'type' => 'boolean',
						'label' => __( 'Toggle if clicking the content should close FancyBox.', 'responsive-lightbox' ),
						'parent' => 'fancybox'
					],
					'cyclic' => [
						'title' => __( 'Cyclic', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_configuration',
						'type' => 'boolean',
						'label' => __( 'When true, galleries will be cyclic, allowing you to keep pressing next/back.', 'responsive-lightbox' ),
						'parent' => 'fancybox'
					],
					'show_nav_arrows' => [
						'title' => __( 'Show nav arrows', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_configuration',
						'type' => 'boolean',
						'label' => __( 'Toggle navigation arrows.', 'responsive-lightbox' ),
						'parent' => 'fancybox'
					],
					'auto_scale' => [
						'title' => __( 'Auto scale', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_configuration',
						'type' => 'boolean',
						'label' => __( 'If true, FancyBox is scaled to fit in viewport.', 'responsive-lightbox' ),
						'parent' => 'fancybox'
					],
					'scrolling' => [
						'title' => __( 'Scrolling (in/out)', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_configuration',
						'type' => 'radio',
						'description' => __( 'Set the overflow CSS property to create or hide scrollbars.', 'responsive-lightbox' ),
						'options' => $this->scripts['fancybox']['scrollings'],
						'parent' => 'fancybox'
					],
					'center_on_scroll' => [
						'title' => __( 'Center on scroll', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_configuration',
						'type' => 'boolean',
						'label' => __( 'When true, FancyBox is centered while scrolling page.', 'responsive-lightbox' ),
						'parent' => 'fancybox'
					],
					'opacity' => [
						'title' => __( 'Opacity', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_configuration',
						'type' => 'boolean',
						'label' => __( 'When true, transparency of content is changed for elastic transitions.', 'responsive-lightbox' ),
						'parent' => 'fancybox'
					],
					'overlay_opacity' => [
						'title' => __( 'Overlay opacity', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_configuration',
						'type' => 'range',
						'description' => __( 'Opacity of the overlay.', 'responsive-lightbox' ),
						'min' => 0,
						'max' => 100,
						'parent' => 'fancybox'
					],
					'overlay_color' => [
						'title' => __( 'Overlay color', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_configuration',
						'type' => 'color_picker',
						'label' => __( 'Color of the overlay.', 'responsive-lightbox' ),
						'parent' => 'fancybox'
					],
					'title_show' => [
						'title' => __( 'Title show', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_configuration',
						'type' => 'boolean',
						'label' => __( 'Toggle title.', 'responsive-lightbox' ),
						'parent' => 'fancybox'
					],
					'title_position' => [
						'title' => __( 'Title position', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_configuration',
						'type' => 'radio',
						'description' => __( 'The position of title.', 'responsive-lightbox' ),
						'options' => $this->scripts['fancybox']['positions'],
						'parent' => 'fancybox'
					],
					'transitions' => [
						'title' => __( 'Transition (in/out)', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_configuration',
						'type' => 'radio',
						'description' => __( 'The transition type.', 'responsive-lightbox' ),
						'options' => $this->scripts['fancybox']['transitions'],
						'parent' => 'fancybox'
					],
					'easings' => [
						'title' => __( 'Easings (in/out)', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_configuration',
						'type' => 'radio',
						'description' => __( 'Easing used for elastic animations.', 'responsive-lightbox' ),
						'options' => $this->scripts['fancybox']['easings'],
						'parent' => 'fancybox'
					],
					'speeds' => [
						'title' => __( 'Speed (in/out)', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_configuration',
						'type' => 'number',
						'description' => __( 'Speed of the fade and elastic transitions, in milliseconds.', 'responsive-lightbox' ),
						'append' => 'ms',
						'parent' => 'fancybox'
					],
					'change_speed' => [
						'title' => __( 'Change speed', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_configuration',
						'type' => 'number',
						'description' => __( 'Speed of resizing when changing gallery items, in milliseconds.', 'responsive-lightbox' ),
						'append' => 'ms',
						'parent' => 'fancybox'
					],
					'change_fade' => [
						'title' => __( 'Change fade', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_configuration',
						'type' => 'number',
						'description' => __( 'Speed of the content fading while changing gallery items.', 'responsive-lightbox' ),
						'append' => 'ms',
						'parent' => 'fancybox'
					],
					'padding' => [
						'title' => __( 'Padding', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_configuration',
						'type' => 'number',
						'description' => __( 'Space between FancyBox wrapper and content.', 'responsive-lightbox' ),
						'append' => 'px',
						'parent' => 'fancybox'
					],
					'margin' => [
						'title' => __( 'Margin', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_configuration',
						'type' => 'number',
						'description' => __( 'Space between viewport and FancyBox wrapper.', 'responsive-lightbox' ),
						'append' => 'px',
						'parent' => 'fancybox'
					],
					'video_width' => [
						'title' => __( 'Video width', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_configuration',
						'type' => 'number',
						'description' => __( 'Width of the video.', 'responsive-lightbox' ),
						'append' => 'px',
						'parent' => 'fancybox'
					],
					'video_height' => [
						'title' => __( 'Video height', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_configuration',
						'type' => 'number',
						'description' => __( 'Height of the video.', 'responsive-lightbox' ),
						'append' => 'px',
						'parent' => 'fancybox'
					]
				];
				break;

			case 'nivo':
				$this->settings['configuration']['prefix'] = 'rl_nv';
				$this->settings['configuration']['fields'] = [
					'effect' => [
						'title' => __( 'Effect', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_configuration',
						'type' => 'radio',
						'description' => __( 'The effect to use when showing the lightbox.', 'responsive-lightbox' ),
						'options' => $this->scripts['nivo']['effects'],
						'parent' => 'nivo'
					],
					'keyboard_nav' => [
						'title' => __( 'Keyboard navigation', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_configuration',
						'type' => 'boolean',
						'label' => __( 'Enable keyboard navigation (left/right/escape).', 'responsive-lightbox' ),
						'parent' => 'nivo'
					],
					'click_overlay_to_close' => [
						'title' => __( 'Click overlay to close', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_configuration',
						'type' => 'boolean',
						'label' => __( 'Enable to close lightbox on overlay click.', 'responsive-lightbox' ),
						'parent' => 'nivo'
					],
					'error_message' => [
						'title' => __( 'Error message', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_configuration',
						'type' => 'text',
						'class' => 'large-text',
						'label' => __( 'Error message if the content cannot be loaded.', 'responsive-lightbox' ),
						'parent' => 'nivo'
					],
				];
				break;

			case 'imagelightbox':
				$this->settings['configuration']['prefix'] = 'rl_il';
				$this->settings['configuration']['fields'] = [
					'animation_speed' => [
						'title' => __( 'Animation speed', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_configuration',
						'type' => 'number',
						'description' => __( 'Animation speed.', 'responsive-lightbox' ),
						'append' => 'ms',
						'parent' => 'imagelightbox'
					],
					'preload_next' => [
						'title' => __( 'Preload next image', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_configuration',
						'type' => 'boolean',
						'label' => __( 'Silently preload the next image.', 'responsive-lightbox' ),
						'parent' => 'imagelightbox'
					],
					'enable_keyboard' => [
						'title' => __( 'Enable keyboard keys', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_configuration',
						'type' => 'boolean',
						'label' => __( 'Enable keyboard shortcuts (arrows Left/Right and Esc).', 'responsive-lightbox' ),
						'parent' => 'imagelightbox'
					],
					'quit_on_end' => [
						'title' => __( 'Quit after last image', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_configuration',
						'type' => 'boolean',
						'label' => __( 'Quit after viewing the last image.', 'responsive-lightbox' ),
						'parent' => 'imagelightbox'
					],
					'quit_on_image_click' => [
						'title' => __( 'Quit on image click', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_configuration',
						'type' => 'boolean',
						'label' => __( 'Quit when the viewed image is clicked.', 'responsive-lightbox' ),
						'parent' => 'imagelightbox'
					],
					'quit_on_document_click' => [
						'title' => __( 'Quit on anything click', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_configuration',
						'type' => 'boolean',
						'label' => __( 'Quit when anything but the viewed image is clicked.', 'responsive-lightbox' ),
						'parent' => 'imagelightbox'
					],
				];
				break;

			case 'tosrus':
				$this->settings['configuration']['prefix'] = 'rl_tr';
				$this->settings['configuration']['fields'] = [
					'effect' => [
						'title' => __( 'Transition effect', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_configuration',
						'type' => 'radio',
						'description' => __( 'What effect to use for the transition.', 'responsive-lightbox' ),
						'options' => [
							'slide' => __( 'slide', 'responsive-lightbox' ),
							'fade' => __( 'fade', 'responsive-lightbox' )
						],
						'parent' => 'tosrus'
					],
					'infinite' => [
						'title' => __( 'Infinite loop', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_configuration',
						'type' => 'boolean',
						'label' => __( 'Whether or not to slide back to the first slide when the last has been reached.', 'responsive-lightbox' ),
						'parent' => 'tosrus'
					],
					'keys' => [
						'title' => __( 'Keyboard navigation', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_configuration',
						'type' => 'boolean',
						'label' => __( 'Enable keyboard navigation (left/right/escape).', 'responsive-lightbox' ),
						'parent' => 'tosrus'
					],
					'autoplay' => [
						'title' => __( 'Autoplay', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_configuration',
						'type' => 'multiple',
						'fields' => [
							'autoplay' => [
								'type' => 'boolean',
								'label' => __( 'Automatically start slideshow.', 'responsive-lightbox' ),
								'parent' => 'tosrus'
							],
							'timeout' => [
								'type' => 'number',
								'description' => __( 'The timeout between sliding to the next slide in milliseconds.', 'responsive-lightbox' ),
								'append' => 'ms',
								'parent' => 'tosrus'
							]
						]
					],
					'pause_on_hover' => [
						'title' => __( 'Pause on hover', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_configuration',
						'type' => 'boolean',
						'label' => __( 'Whether or not to pause on hover.', 'responsive-lightbox' ),
						'parent' => 'tosrus'
					],
					'pagination' => [
						'title' => __( 'Pagination', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_configuration',
						'type' => 'multiple',
						'fields' => [
							'pagination' => [
								'type' => 'boolean',
								'label' => __( 'Whether or not to add a pagination.', 'responsive-lightbox' ),
								'parent' => 'tosrus'
							],
							'pagination_type' => [
								'type' => 'radio',
								'description' => __( 'What type of pagination to use.', 'responsive-lightbox' ),
								'options' => [
									'bullets' => __( 'Bullets', 'responsive-lightbox' ),
									'thumbnails' => __( 'Thumbnails', 'responsive-lightbox' )
								],
								'parent' => 'tosrus'
							]
						]
					],
					'close_on_click' => [
						'title'			=> __( 'Overlay close', 'responsive-lightbox' ),
						'section'		=> 'responsive_lightbox_configuration',
						'type'			=> 'boolean',
						'label'			=> __( 'Enable to close lightbox on overlay click.', 'responsive-lightbox' ),
						'parent'		=> 'tosrus'
					]
				];
				break;

			case 'featherlight':
				$this->settings['configuration']['prefix'] = 'rl_fl';
				$this->settings['configuration']['fields'] = [
					'open_speed' => [
						'title' => __( 'Opening speed', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_configuration',
						'type' => 'number',
						'description' => __( 'Duration of opening animation.', 'responsive-lightbox' ),
						'append' => 'ms',
						'parent' => 'featherlight'
					],
					'close_speed' => [
						'title' => __( 'Closing speed', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_configuration',
						'type' => 'number',
						'description' => __( 'Duration of closing animation.', 'responsive-lightbox' ),
						'append' => 'ms',
						'parent' => 'featherlight'
					],
					'close_on_click' => [
						'title' => __( 'Close on click', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_configuration',
						'type' => 'radio',
						'label' => __( 'Select how to close lightbox.', 'responsive-lightbox' ),
						'options' => [
							'background' => __( 'background', 'responsive-lightbox' ),
							'anywhere' => __( 'anywhere', 'responsive-lightbox' ),
							'false' => __( 'false', 'responsive-lightbox' )
						],
						'parent' => 'featherlight'
					],
					'close_on_esc' => [
						'title' => __( 'Close on Esc', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_configuration',
						'type' => 'boolean',
						'label' => __( 'Toggle if pressing Esc button closes lightbox.', 'responsive-lightbox' ),
						'parent' => 'featherlight'
					],
					'gallery_fade_in' => [
						'title' => __( 'Gallery fade in', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_configuration',
						'type' => 'number',
						'description' => __( 'Animation speed when image is loaded.', 'responsive-lightbox' ),
						'append' => 'ms',
						'parent' => 'featherlight'
					],
					'gallery_fade_out' => [
						'title' => __( 'Gallery fade out', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_configuration',
						'type' => 'number',
						'description' => __( 'Animation speed before image is loaded.', 'responsive-lightbox' ),
						'append' => 'ms',
						'parent' => 'featherlight'
					]
				];
				break;

			case 'magnific':
				$this->settings['configuration']['prefix'] = 'rl_mp';
				$this->settings['configuration']['fields'] = [
					'disable_on' => [
						'title' => __( 'Disable on', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_configuration',
						'type' => 'number',
						'description' => __( 'If window width is less than the number in this option lightbox will not be opened and the default behavior of the element will be triggered. Set to 0 to disable behavior.', 'responsive-lightbox' ),
						'append' => 'px',
						'parent' => 'magnific'
					],
					'mid_click' => [
						'title' => __( 'Middle click', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_configuration',
						'type' => 'boolean',
						'label' => __( 'If option enabled, lightbox is opened if the user clicked on the middle mouse button, or click with Command/Ctrl key.', 'responsive-lightbox' ),
						'parent' => 'magnific'
					],
					'preloader' => [
						'title' => __( 'Preloader', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_configuration',
						'type' => 'boolean',
						'label' => __( 'If option enabled, it\'s always present in DOM only text inside of it changes.', 'responsive-lightbox' ),
						'parent' => 'magnific'
					],
					'close_on_content_click' => [
						'title' => __( 'Close on content click', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_configuration',
						'type' => 'boolean',
						'label' => __( 'Close popup when user clicks on content of it. It\'s recommended to enable this option when you have only image in popup.', 'responsive-lightbox' ),
						'parent' => 'magnific'
					],
					'close_on_background_click' => [
						'title' => __( 'Close on background click', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_configuration',
						'type' => 'boolean',
						'label' => __( 'Close the popup when user clicks on the dark overlay.', 'responsive-lightbox' ),
						'parent' => 'magnific'
					],
					'close_button_inside' => [
						'title' => __( 'Close button inside', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_configuration',
						'type' => 'boolean',
						'label' => __( 'If enabled, Magnific Popup will put close button inside content of popup.', 'responsive-lightbox' ),
						'parent' => 'magnific'
					],
					'show_close_button' => [
						'title' => __( 'Show close button', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_configuration',
						'type' => 'boolean',
						'label' => __( 'Controls whether the close button will be displayed or not.', 'responsive-lightbox' ),
						'parent' => 'magnific'
					],
					'enable_escape_key' => [
						'title' => __( 'Enable escape key', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_configuration',
						'type' => 'boolean',
						'label' => __( 'Controls whether pressing the escape key will dismiss the active popup or not.', 'responsive-lightbox' ),
						'parent' => 'magnific'
					],
					'align_top' => [
						'title' => __( 'Align top', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_configuration',
						'type' => 'boolean',
						'label' => __( 'If set to true popup is aligned to top instead of to center.', 'responsive-lightbox' ),
						'parent' => 'magnific'
					],
					'fixed_content_position' => [
						'title' => __( 'Content position type', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_configuration',
						'type' => 'select',
						'description' => __( 'Popup content position. If set to "auto" popup will automatically disable this option when browser doesn\'t support fixed position properly.', 'responsive-lightbox' ),
						'options' => [
							'auto' => __( 'Auto', 'responsive-lightbox' ),
							'true' => __( 'Fixed', 'responsive-lightbox' ),
							'false' => __( 'Absolute', 'responsive-lightbox' )
						],
						'parent' => 'magnific'
					],
					'fixed_background_position' => [
						'title' => __( 'Fixed background position', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_configuration',
						'type' => 'select',
						'description' => __( 'Dark transluscent overlay content position.', 'responsive-lightbox' ),
						'options' => [
							'auto' => __( 'Auto', 'responsive-lightbox' ),
							'true' => __( 'Fixed', 'responsive-lightbox' ),
							'false' => __( 'Absolute', 'responsive-lightbox' )
						],
						'parent' => 'magnific'
					],
					'auto_focus_last' => [
						'title' => __( 'Auto focus last', 'responsive-lightbox' ),
						'section' => 'responsive_lightbox_configuration',
						'type' => 'boolean',
						'label' => __( 'If set to true last focused element before popup showup will be focused after popup close.', 'responsive-lightbox' ),
						'parent' => 'magnific'
					]
				];
				break;

			default:
				$this->settings['configuration'] = apply_filters( 'rl_settings_' . ( ! empty( $section_key ) ? $section_key : $rl->options['settings']['script'] ) . '_script_configuration', $this->settings['configuration'] );
		}

		if ( isset( $this->tabs[$tab_key]['submit'], $this->tabs[$tab_key]['reset'] ) && ! empty( $this->settings[$tab_key]['prefix'] ) ) {
			$this->tabs[$tab_key]['submit'] = 'save_' . $this->settings[$tab_key]['prefix'] . '_' . $tab_key;
			$this->tabs[$tab_key]['reset'] = 'reset_' . $this->settings[$tab_key]['prefix'] . '_' . $tab_key;
		}
	}

	/**
	 * Remote Library Media Providers description
	 *
	 * @return void
	 */
	public function remote_library_providers_description() {
		echo '<p class="description">' . sprintf( esc_html__( 'Below you\'ll find a list of available remote media libraries. If you\'re looking for Pixabay, Pexels, Instagram and other integrations please check the %s addon.', 'responsive-lightbox' ), '<a href="https://dfactory.eu/products/remote-library-pro/?utm_source=responsive-lightbox-settings&utm_medium=link&utm_campaign=addon" target="_blank">Remote Library Pro</a>' ) . '</p>';
	}

	/**
	 * Register options page
	 *
	 * @return void
	 */
	public function admin_menu_options() {
		// get master capability
		$capability = apply_filters( 'rl_lightbox_settings_capability', Responsive_Lightbox()->options['capabilities']['active'] ? 'edit_lightbox_settings' : 'manage_options' );

		add_menu_page( __( 'General', 'responsive-lightbox' ), __( 'Lightbox', 'responsive-lightbox' ), $capability, 'responsive-lightbox-settings', '', 'dashicons-format-image', '57.1' );

		foreach ( $this->tabs as $key => $options ) {
			add_submenu_page( 'responsive-lightbox-settings', $options['name'], $options['name'], $capability, 'responsive-lightbox-' . $key , [ $this, 'options_page' ] );
		}
	}

	/**
	 * Render options page
	 *
	 * @return void
	 */
	public function options_page() {
		// check page
		$page = isset( $_GET['page'] ) ? sanitize_key( $_GET['page'] ) : '';

		// check settings page
		if ( $page && preg_match( '/^responsive-lightbox-(' . implode( '|', array_keys( $this->tabs ) ) . ')$/', $page, $tabs ) !== 1 )
			return;

		$tab_key = isset( $tabs[1] ) ? $tabs[1] : 'settings';

		// check section
		$section_key = isset( $_GET['section'] ) ? sanitize_key( $_GET['section'] ) : '';

		if ( ! $section_key )
			$section_key = ! empty( $this->tabs[$tab_key]['default_section'] ) ? $this->tabs[$tab_key]['default_section'] : '';

		// assign main instance
		$rl = Responsive_Lightbox();

		// no valid lightbox script?
		if ( $tab_key === 'configuration' && ! array_key_exists( $section_key, $rl->options['configuration'] ) )
			return;

		// no valid gallery?
		if ( $tab_key === 'gallery' && ! array_key_exists( $section_key, $rl->settings->tabs['gallery']['sections'] ) )
			return;

		echo '
		<div class="wrap">';

		settings_errors();

		// hidden h2 tag is needed to display info box properly when saving or resetting settings
		echo '
			<h2 class="hidden">' . esc_html__( 'Responsive Lightbox & Gallery', 'responsive-lightbox' ) . ' - ' . esc_html( $this->tabs[$tab_key]['name'] ) . '</h2>' . '
			<h2 class="nav-tab-wrapper">';

		foreach ( $this->tabs as $key => $options ) {
			echo '
			<a class="nav-tab ' . ( $tab_key === $key ? 'nav-tab-active' : '' ) . '" href="' . esc_url( admin_url( 'admin.php?page=responsive-lightbox-' . $key ) ) . '">' . esc_html( $options['name'] ) . '</a>';
		}

		echo '
			</h2>
			<div class="responsive-lightbox-settings">
				<div class="df-credits">
					<h3 class="hndle">' . esc_html__( 'Responsive Lightbox & Gallery', 'responsive-lightbox' ) . ' ' . esc_html( $rl->defaults['version'] ) . '</h3>
					<div class="inside">
						<h4 class="inner">' . esc_html__( 'Need support?', 'responsive-lightbox' ) . '</h4>
						<p class="inner">' . sprintf( esc_html__( 'If you are having problems with this plugin, please browse it\'s %s or talk about them in the %s.', 'responsive-lightbox' ), '<a href="https://www.dfactory.eu/docs/responsive-lightbox/?utm_source=responsive-lightbox-settings&utm_medium=link&utm_campaign=docs" target="_blank">' . esc_html__( 'Documentation', 'responsive-lightbox' ) . '</a>', '<a href="https://www.dfactory.eu/support/?utm_source=responsive-lightbox-settings&utm_medium=link&utm_campaign=support" target="_blank">' . esc_html__( 'Support forum', 'responsive-lightbox' ) . '</a>' ) . '</p>
						<hr />
						<h4 class="inner">' . esc_html__( 'Do you like this plugin?', 'responsive-lightbox' ) . '</h4>
						<p class="inner">' . sprintf( esc_html__( '%s on WordPress.org', 'responsive-lightbox' ), '<a href="https://wordpress.org/support/plugin/responsive-lightbox/reviews/?filter=5" target="_blank">' . esc_html__( 'Rate it 5', 'responsive-lightbox' ) . '</a>' ) . '<br />' .
						sprintf( esc_html__( 'Blog about it & link to the %s.', 'responsive-lightbox' ), '<a href="https://dfactory.eu/plugins/responsive-lightbox/?utm_source=responsive-lightbox-settings&utm_medium=link&utm_campaign=blog-about" target="_blank">' . esc_html__( 'plugin page', 'responsive-lightbox' ) . '</a>' ) . '<br />' .
						sprintf( esc_html__( 'Check out our other %s.', 'responsive-lightbox' ), '<a href="https://dfactory.eu/plugins/?utm_source=responsive-lightbox-settings&utm_medium=link&utm_campaign=other-plugins" target="_blank">' . esc_html__( 'WordPress plugins', 'responsive-lightbox' ) . '</a>' ) . '
						</p>
						<hr />
						<p class="df-link inner"><a href="https://www.dfactory.eu/?utm_source=responsive-lightbox-settings&utm_medium=link&utm_campaign=created-by" target="_blank" title="Digital Factory"><img src="//rlg-53eb.kxcdn.com/df-black-sm.png' . '" alt="Digital Factory" /></a></p>
					</div>
				</div>
				<form action="options.php" method="post">';

		// views
		if ( ! empty( $this->tabs[$tab_key]['sections'] ) ) {
			$list = [];

			echo '<ul class="subsubsub">';

			foreach ( $this->tabs[$tab_key]['sections'] as $key => $name ) {
				$list[$key] = "\t" . '<li class="' . esc_attr( $key ) . '"><a href="' . esc_url( admin_url( 'admin.php?page=responsive-lightbox-' . $tab_key . '&section=' . $key ) ) . '" class="' . ( $key === $section_key ? 'current' : '' ) . '">' . esc_html( $name ) . '</a>';
			}

			echo implode( " |</li>\n", $list ) . "</li>\n";
			echo '</ul><input type="hidden" name="section" value="' . esc_attr( $section_key ) . '" /><br class="clear">';
		}

		// tab content callback
		if ( ! empty( $this->tabs[$tab_key]['callback'] ) )
			call_user_func( $this->tabs[$tab_key]['callback'] );
		else {
			settings_fields( $this->tabs[$tab_key]['key'] );
			do_settings_sections( $this->tabs[$tab_key]['key'] );

			if ( $tab_key === 'builder' )
				echo '<input type="hidden" name="_wp_http_referer" value="'. esc_attr( wp_unslash( add_query_arg( 'flush_rules', 1, $_SERVER['REQUEST_URI'] ) ) ) . '" />';
		}

		if ( ! empty( $this->tabs[$tab_key]['submit'] ) || ! empty( $this->tabs[$tab_key]['reset'] ) ) {
			echo '<p class="submit">';

			if ( ! empty( $this->tabs[$tab_key]['submit'] ) ) {
				submit_button( '', [ 'primary', 'save-' . $tab_key ], $this->tabs[$tab_key]['submit'], false );
				echo ' ';
			}

			if ( ! empty( $this->tabs[$tab_key]['reset'] ) )
				submit_button( __( 'Reset to defaults', 'responsive-lightbox' ), [ 'secondary', 'reset-responsive-lightbox-settings reset-' . $tab_key ], $this->tabs[$tab_key]['reset'], false );

			echo '</p>';
		}

		echo '
				</form>
			</div>
			<div class="clear"></div>
		</div>';
	}

	/**
	 * Add new capability to manage options.
	 *
	 * @return string
	 */
	public function manage_options_capability() {
		return Responsive_Lightbox()->options['capabilities']['active'] ? 'edit_lightbox_settings' : 'manage_options';
	}

	/**
	 * Render settings function.
	 *
	 * @return void
	 */
	public function register_settings() {
		// assign main instance
		$rl = Responsive_Lightbox();

		foreach ( $this->settings as $_setting_id => $setting ) {
			$setting_id = sanitize_key( $_setting_id );

			if ( ! empty( $setting['option_name'] ) )
				$option_name = sanitize_key( $setting['option_name'] );
			else
				$option_name = $setting_id;

			// set key
			$setting_key = $setting_id;
			$setting_id = 'responsive_lightbox_' . $setting_id;

			// add new capability to manage options
			add_filter( 'option_page_capability_' . $setting_id, [ $this, 'manage_options_capability' ] );

			// register setting
			register_setting( $setting_id, $option_name, ! empty( $setting['callback'] ) ? $setting['callback'] : [ $this, 'validate_settings' ] );

			// register sections
			if ( ! empty( $setting['sections'] ) && is_array( $setting['sections'] ) ) {
				foreach ( $setting['sections'] as $_section_id => $section ) {
					$section_id = sanitize_key( $_section_id );

					add_settings_section(
						$section_id,
						! empty( $section['title'] ) ? esc_html( $section['title'] ) : '',
						! empty( $section['callback'] ) ? $section['callback'] : '',
						! empty( $section['page'] ) ? sanitize_key( $section['page'] ) : $section_id
					);
				}
			}

			// register fields
			if ( ! empty( $setting['fields'] ) && is_array( $setting['fields'] ) ) {
				foreach ( $setting['fields'] as $_field_id => $field ) {
					$field_id = sanitize_key( $_field_id );

					// prefix field id?
					$field_key = $field_id;
					$field_id = ( ! empty( $setting['prefix'] ) ? $setting['prefix'] . '_' : '' ) . $field_id;

					// field args
					$args = [
						'id'			=> ! empty( $field['id'] ) ? $field['id'] : $field_id,
						'class'			=> ! empty( $field['class'] ) ? $field['class'] : '',
						'name'			=> $option_name . ( ! empty( $field['parent'] ) ? '[' . $field['parent'] . ']' : '' ) . '[' . $field_key . ']',
						'type'			=> ! empty( $field['type'] ) ? $field['type'] : 'text',
						'label'			=> ! empty( $field['label'] ) ? $field['label'] : '',
						'description'	=> ! empty( $field['description'] ) ? $field['description'] : '',
						'disabled'		=> isset( $field['disabled'] ) ? (bool) $field['disabled'] : false,
						'append'		=> ! empty( $field['append'] ) ? $field['append'] : '',
						'prepend'		=> ! empty( $field['prepend'] ) ? $field['prepend'] : '',
						'min'			=> isset( $field['min'] ) ? (int) $field['min'] : '',
						'max'			=> isset( $field['max'] ) ? (int) $field['max'] : '',
						'options'		=> ! empty( $field['options'] ) ? $field['options'] : '',
						'fields'		=> ! empty( $field['fields'] ) ? $field['fields'] : '',
						'after_field'	=> ! empty( $field['after_field'] ) ? $field['after_field'] : '',
						'default'		=> $field['type'] === 'multiple' ? '' : ( $this->sanitize_field( ! empty( $field['parent'] ) ? $rl->defaults[$setting_key][$field['parent']][$field_key] : $rl->defaults[$setting_key][$field_key], $field['type'] ) ),
						'value'			=> $field['type'] === 'multiple' ? '' : ( $this->sanitize_field( ! empty( $field['parent'] ) ? $rl->options[$setting_key][$field['parent']][$field_key] : ( isset( $rl->options[$setting_key][$field_key] ) ? $rl->options[$setting_key][$field_key] : $rl->defaults[$setting_key][$field_key] ), $field['type'] ) ),
						'label_for'		=> $field_id,
						'classname'		=> ! empty( $field['classname'] ) ? $field['classname'] : '',
						'callback'		=> ! empty( $field['callback'] ) ? $field['callback'] : '',
						'return'		=> false
					];

					if ( $args['type'] === 'multiple' ) {
						foreach ( $args['fields'] as $subfield_id => $subfield ) {
							$args['fields'][$subfield_id] = wp_parse_args(
								$subfield,
								[
									'id'		=> $field_id . '-' . $subfield_id,
									'class'		=> ! empty( $subfield['class'] ) ? $subfield['class'] : '',
									'name'		=> $option_name . ( ! empty( $subfield['parent'] ) ? '[' . $subfield['parent'] . ']' : '' ) . '[' . $subfield_id . ']',
									'default'	=> $this->sanitize_field( ! empty( $subfield['parent'] ) ? $rl->defaults[$setting_key][$subfield['parent']][$subfield_id] : $rl->defaults[$setting_key][$subfield_id], $subfield['type'] ),
									'value'		=> $this->sanitize_field( ! empty( $subfield['parent'] ) ? $rl->options[$setting_key][$subfield['parent']][$subfield_id] : $rl->options[$setting_key][$subfield_id], $subfield['type'] ),
									'return'	=> true
								]
							);
						}
					}

					add_settings_field(
						$field_id,
						! empty( $field['title'] ) ? esc_html( $field['title'] ) : '',
						[ $this, 'render_field' ],
						! empty( $field['page'] ) ? sanitize_key( $field['page'] ) : $setting_id,
						! empty( $field['section'] ) ? sanitize_key( $field['section'] ) : '',
						$args
					);
				}
			}
		}

		// licenses
		$extensions = apply_filters( 'rl_settings_licenses', [] );

		if ( $extensions ) {
			// add new capability to manage licenses
			add_filter( 'option_page_capability_responsive_lightbox_licenses', [ $this, 'manage_options_capability' ] );

			// register setting
			register_setting( 'responsive_lightbox_licenses', 'responsive_lightbox_licenses', [ $this, 'validate_licenses' ] );

			add_settings_section( 'responsive_lightbox_licenses', esc_html__( 'Licenses', 'responsive-lightbox' ), [ $this, 'licenses_section_cb' ], 'responsive_lightbox_licenses' );

			foreach ( $extensions as $id => $extension ) {
				add_settings_field( sanitize_key( $id ), esc_html( $extension['name'] ), [ $this, 'license_field_cb' ], 'responsive_lightbox_licenses', 'responsive_lightbox_licenses', $extension );
			}
		}
	}

	/**
	 * Render settings field function.
	 *
	 * @param array $args
	 * @return void|string
	 */
	public function render_field( $args ) {
		if ( empty( $args ) || ! is_array( $args ) )
			return '';

		$html = '';

		switch ( $args['type'] ) {
			case 'boolean':
				$html .= '<label><input id="' . esc_attr( $args['id'] ) . '" type="checkbox" name="' . esc_attr( $args['name'] ) . '" value="1" ' . checked( (bool) $args['value'], true, false ) . ( isset( $args['disabled'] ) && $args['disabled'] == true ? ' disabled="disabled"' : '' ) . ' />' . esc_html( $args['label'] ) . '</label>';
				break;

			case 'radio':
				foreach ( $args['options'] as $key => $name ) {
					$html .= '<label><input id="' . esc_attr( $args['id'] . '-' . $key ) . '" type="radio" name="' . esc_attr( $args['name'] ) . '" value="' . esc_attr( $key ) . '" ' . checked( $key, $args['value'], false ) . ( isset( $args['disabled'] ) && $args['disabled'] == true ? ' disabled="disabled"' : '' ) . ' />' . esc_html( $name ) . '</label> ';
				}
				break;

			case 'checkbox':
				foreach ( $args['options'] as $key => $name ) {
					$html .= '<label><input id="' . esc_attr( $args['id'] . '-' . $key ) . '" type="checkbox" name="' . esc_attr( $args['name'] ) . '[' . esc_attr( $key ) . ']" value="1" ' . checked( in_array( $key, $args['value'] ), true, false ) . ( isset( $args['disabled'] ) && $args['disabled'] == true ? ' disabled="disabled"' : '' ) . ' />' . esc_html( $name ) . '</label> ';
				}
				break;

			case 'select':
				$html .= '<select id="' . esc_attr( $args['id'] ) . '" name="' . esc_attr( $args['name'] ) . '" value="' . esc_attr( $args['value'] ) . '" ' . ( isset( $args['disabled'] ) && $args['disabled'] == true ? ' disabled="disabled"' : '' ) . '/>';

				foreach ( $args['options'] as $key => $name ) {
					$html .= '<option value="' . esc_attr( $key ) . '" ' . selected( $args['value'], $key, false ) . '>' . esc_html( $name ) . '</option>';
				}

				$html .= '</select>';
				break;

			case 'multiple':
				$html .= '<fieldset>';

				if ( $args['fields'] ) {
					$count = 1;
					$count_fields = count( $args['fields'] );

					foreach ( $args['fields'] as $subfield_id => $subfield_args ) {
						$html .= $this->render_field( $subfield_args ) . ( $count < $count_fields ? '<br />' : '' );

						$count++;
					}
				}

				$html .= '</fieldset>';
				break;

			case 'range':
				$html .= '<input id="' . esc_attr( $args['id'] ) . '" type="range" name="' . esc_attr( $args['name'] ) . '" value="' . (int) $args['value'] . '" min="' . (int) $args['min'] . '" max="' . (int) $args['max'] . '" oninput="this.form.' . esc_attr( $args['id'] ) . '_range.value=this.value" />';
				$html .= '<output name="' . esc_attr( $args['id'] ) . '_range">' . (int) $args['value'] . '</output>';
				break;

			case 'color_picker':
				$html .= '<input id="' . esc_attr( $args['id'] ) . '" class="color-picker" type="text" value="' . esc_attr( $args['value'] ) . '" name="' . esc_attr( $args['name'] ) . '" data-default-color="' . esc_attr( $args['default'] ) . '" />';
				break;

			case 'number':
				$html .= ( ! empty( $args['prepend'] ) ? '<span>' . esc_html( $args['prepend'] ) . '</span> ' : '' );
				$html .= '<input id="' . esc_attr( $args['id'] ) . '" type="number" value="' . (int) $args['value'] . '" name="' . esc_attr( $args['name'] ) . '" />';
				$html .= ( ! empty( $args['append'] ) ? ' <span>' . esc_html( $args['append'] ) . '</span>' : '' );
				break;

			case 'button':
				$html .= ( ! empty( $args['prepend'] ) ? '<span>' . esc_html( $args['prepend'] ) . '</span> ' : '' );
				$html .= '<a href="' . esc_url( admin_url( 'admin.php?page=responsive-lightbox-tour' ) ) . '" id="' . esc_attr( $args['id'] ) . '" class="button ' . ( ! empty( $args['classname'] ) ? esc_attr( $args['classname'] ) : 'button-secondary' ) . '">' . esc_html( $args['label'] ) . '</a>';
				$html .= ( ! empty( $args['append'] ) ? ' <span>' . esc_html( $args['append'] ) . '</span>' : '' );
				break;

			case 'custom':
				$html .= call_user_func( $args['callback'], $args );
				break;

			case 'text':
			default :
				$html .= ( ! empty( $args['prepend'] ) ? '<span>' . esc_html( $args['prepend'] ) . '</span> ' : '' );
				$html .= '<input id="' . esc_attr( $args['id'] ) . '" class="' . esc_attr( $args['class'] ) . '" type="text" value="' . esc_attr( $args['value'] ) . '" name="' . esc_attr( $args['name'] ) . '" />';
				$html .= ( ! empty( $args['append'] ) ? ' <span>' . esc_html( $args['append'] ) . '</span>' : '' );
		}

		if ( ! empty ( $args['after_field'] ) )
			$html .= wp_kses_post( $args['after_field'] );

		if ( ! empty ( $args['description'] ) )
			$html .= '<p class="description">' . wp_kses_post( $args['description'] ) . '</p>';

		if ( ! empty( $args['return'] ) )
			return $html;
		else
			echo $html;
	}

	/**
	 * Sanitize field function
	 *
	 * @param mixed $value
	 * @param string $type
	 * @param array $args
	 * @return mixed
	 */
	public function sanitize_field( $value = null, $type = '', $args = [] ) {
		if ( is_null( $value ) )
			return null;

		switch ( $type ) {
			case 'button':
			case 'boolean':
				$value = empty( $value ) ? false : true;
				break;

			case 'checkbox':
				$value = is_array( $value ) && ! empty( $value ) ? array_map( 'sanitize_key', $value ) : [];
				break;

			case 'radio':
				$value = is_array( $value ) ? false : sanitize_key( $value );
				break;

			case 'textarea':
			case 'wysiwyg':
				$value = wp_kses_post( $value );
				break;

			case 'color_picker':
				$value = sanitize_hex_color( $value );

				if ( empty( $value ) )
					$value = '#666666';
				break;

			case 'number':
				$value = (int) $value;

				// is value lower than?
				if ( isset( $args['min'] ) && $value < $args['min'] )
					$value = $args['min'];

				// is value greater than?
				if ( isset( $args['max'] ) && $value > $args['max'] )
					$value = $args['max'];
				break;

			case 'custom':
				// do nothing
				break;

			case 'text':
				if ( ! empty( $args ) ) {
					// validate custom events
					if ( $args['setting_id'] === 'settings' ) {
						if ( $args['field_id'] === 'enable_custom_events' && $args['subfield_id'] === 'custom_events' )
							$value = preg_replace( '/[^a-z0-9 ]/i', '', $value );
					} elseif ( $args['setting_id'] === 'builder' ) {
						if ( $args['field_id'] === 'permalink' || $args['field_id'] === 'permalink_categories' || $args['field_id'] === 'permalink_tags' )
							$value = sanitize_title( $value );
					}
				}
			case 'select':
			default:
				$value = is_array( $value ) ? array_map( 'sanitize_text_field', $value ) : sanitize_text_field( $value );
				break;
		}

		return stripslashes_deep( $value );
	}

	/**
	 * Validate settings function
	 *
	 * @param array $input
	 * @return array
	 */
	public function validate_settings( $input ) {
		// assign main instance
		$rl = Responsive_Lightbox();

		// check capability
		if ( ! current_user_can( apply_filters( 'rl_lightbox_settings_capability', $rl->options['capabilities']['active'] ? 'edit_lightbox_settings' : 'manage_options' ) ) )
			return $input;

		// check option page
		$option_page = isset( $_POST['option_page'] ) ? sanitize_key( $_POST['option_page'] ) : '';

		// check page
		if ( ! $option_page )
			return $input;

		foreach ( $this->settings as $id => $setting ) {
			$key = array_search( $option_page, $setting );

			if ( $key ) {
				// set key
				$setting_id = sanitize_key( $id );
				break;
			}
		}

		// check setting id
		if ( ! $setting_id )
			return $input;

		// save settings
		if ( isset( $_POST['save' . '_' . $this->settings[$setting_id]['prefix'] . '_' . $setting_id] ) ) {
			if ( $this->settings[$setting_id]['fields'] ) {
				foreach ( $this->settings[$setting_id]['fields'] as $field_id => $field ) {
					if ( $field['type'] === 'multiple' ) {
						if ( $field['fields'] ) {
							foreach ( $field['fields'] as $subfield_id => $subfield ) {
								$args = $subfield;
								$args['setting_id'] = $setting_id;
								$args['field_id'] = $field_id;
								$args['subfield_id'] = $subfield_id;

								// if subfield has parent
								if ( ! empty( $this->settings[$setting_id]['fields'][$field_id]['fields'][$subfield_id]['parent'] ) ) {
									$field_parent = $this->settings[$setting_id]['fields'][$field_id]['fields'][$subfield_id]['parent'];

									$input[$field_parent][$subfield_id] = isset( $input[$field_parent][$subfield_id] ) ? $this->sanitize_field( $input[$field_parent][$subfield_id], $subfield['type'], $args ) : ( $subfield['type'] === 'boolean' ? false : $rl->defaults[$setting_id][$field_parent][$subfield_id] );
								} else {
									$input[$subfield_id] = isset( $input[$subfield_id] ) ? $this->sanitize_field( $input[$subfield_id], $subfield['type'], $args ) : ( $subfield['type'] === 'boolean' ? false : $rl->defaults[$setting_id][$field_id][$subfield_id] );
								}
							}
						}
					} else {
						$args = $field;
						$args['setting_id'] = $setting_id;
						$args['field_id'] = $field_id;

						// if field has parent
						if ( ! empty( $this->settings[$setting_id]['fields'][$field_id]['parent'] ) ) {
							$field_parent = $this->settings[$setting_id]['fields'][$field_id]['parent'];

							$input[$field_parent][$field_id] = isset( $input[$field_parent][$field_id] ) ? ( $field['type'] === 'checkbox' ? array_keys( $this->sanitize_field( $input[$field_parent][$field_id], $field['type'], $args ) ) : $this->sanitize_field( $input[$field_parent][$field_id], $field['type'], $args ) ) : ( in_array( $field['type'], [ 'boolean', 'checkbox' ] ) ? false : $rl->defaults[$setting_id][$field_parent][$field_id] );
						} else {
							$input[$field_id] = isset( $input[$field_id] ) ? ( $field['type'] === 'checkbox' ? array_keys( $this->sanitize_field( $input[$field_id], $field['type'], $args ) ) : $this->sanitize_field( $input[$field_id], $field['type'], $args ) ) : ( in_array( $field['type'], [ 'boolean', 'checkbox' ] ) ? false : $rl->defaults[$setting_id][$field_id] );
						}
					}
				}
			}

			if ( $setting_id === 'settings' ) {
				// merge scripts settings
				$input = array_merge( $rl->options['settings'], $input );

				// woocommerce lightbox has to be enabled when using rl gallery
				if ( $input['default_woocommerce_gallery'] !== 'default' )
					$input['woocommerce_gallery_lightbox'] = true;
			}

			if ( $setting_id === 'configuration' ) {
				// merge scripts settings
				$input = array_merge( $rl->options['configuration'], $input );
			}

			if ( $setting_id === 'remote_library' )
				$input = apply_filters( 'rl_remote_library_settings', $input );
		} elseif ( isset( $_POST['reset' . '_' . $this->settings[$setting_id]['prefix'] . '_' . $setting_id] ) ) {
			if ( $setting_id === 'configuration' ) {
				$script = key( $input );

				// merge scripts settings
				$input[$script] = $rl->defaults['configuration'][$script];
				$input = array_merge( $rl->options['configuration'], $input );
			} elseif ( $setting_id === 'settings' ) {
				$input = $rl->defaults[$setting_id];
				$input['update_version'] = $rl->options['settings']['update_version'];
				$input['update_notice'] = $rl->options['settings']['update_notice'];
			} else
				$input = $rl->defaults[$setting_id];

			add_settings_error( 'reset_' . $this->settings[$setting_id]['prefix'] . '_' . $setting_id, 'settings_restored', esc_html__( 'Settings restored to defaults.', 'responsive-lightbox' ), 'updated' );
		}

		return $input;
	}

	/**
	 * Validate capabilities.
	 *
	 * @global object $wp_roles
	 *
	 * @param array $input
	 * @return array
	 */
	public function validate_capabilities( $input ) {
		// assign main instance
		$rl = Responsive_Lightbox();

		// check capability
		if ( ! current_user_can( apply_filters( 'rl_lightbox_settings_capability', $rl->options['capabilities']['active'] ? 'edit_lightbox_settings' : 'manage_options' ) ) )
			return $input;

		// validate normal fields
		$input = $this->validate_settings( $input );

		global $wp_roles;

		// save capabilities?
		if ( isset( $_POST['save_rl_capabilities'] ) ) {
			foreach ( $wp_roles->roles as $role_name => $role_label ) {
				// get user role
				$role = $wp_roles->get_role( $role_name );

				// manage new capabilities only for non-admins
				if ( $role_name !== 'administrator' ) {
					foreach ( $rl->capabilities as $capability => $label ) {
						if ( isset( $input['roles'][$role_name][$capability] ) && $input['roles'][$role_name][$capability] === 'true' )
							$role->add_cap( $capability );
						else
							$role->remove_cap( $capability );
					}
				}
			}
		// reset capabilities?
		} elseif ( isset( $_POST['reset_rl_capabilities'] ) ) {
			foreach ( $wp_roles->roles as $role_name => $display_name ) {
				// get user role
				$role = $wp_roles->get_role( $role_name );

				foreach ( $rl->capabilities as $capability => $label ) {
					if ( array_key_exists( $role_name, $rl->defaults['capabilities']['roles'] ) && in_array( $capability, $rl->defaults['capabilities']['roles'][$role_name], true ) )
						$role->add_cap( $capability );
					else
						$role->remove_cap( $capability );
				}
			}

			add_settings_error( 'reset_rl_capabilities', 'settings_restored', esc_html__( 'Settings restored to defaults.', 'responsive-lightbox' ), 'updated' );
		}

		return $input;
	}

	/**
	 * Render capabilities section.
	 *
	 * @global object $wp_roles
	 *
	 * @return void
	 */
	public function capabilities_table() {
		global $wp_roles;

		// get available user roles
		$editable_roles = get_editable_roles();

		$html = '
		<br class="clear" />
		<table class="widefat fixed posts">
			<thead>
				<tr>
					<th>' . esc_html__( 'Role', 'responsive-lightbox' ) . '</th>';

		foreach ( $editable_roles as $role_name => $role_info ) {
			$html .= '<th>' . esc_html( isset( $wp_roles->role_names[$role_name] ) ? translate_user_role( $wp_roles->role_names[$role_name] ) : $role_name ) . '</th>';
		}

		$html .= '
				</tr>
			</thead>
			<tbody id="the-list">';

		$i = 0;

		foreach ( Responsive_Lightbox()->capabilities as $cap_role => $cap_label ) {
			$html .= '
				<tr' . ( ( $i++ % 2 === 0 ) ? ' class="alternate"' : '' ) . '>
					<td>' . esc_html__( $cap_label, 'responsive-lightbox' ) . '</td>';

			foreach ( $editable_roles as $role_name => $role_info ) {
				// get user role
				$role = $wp_roles->get_role( $role_name );

				$html .= '
					<td>
						<input type="checkbox" name="responsive_lightbox_capabilities[roles][' . esc_attr( $role->name ) . '][' . esc_attr( $cap_role ) . ']" value="true" ' . checked( true, ( $role->has_cap( $cap_role ) || $role_name === 'administrator' ), false ) . ' ' . disabled( $role_name, 'administrator', false ) . ' />
					</td>';
			}

			$html .= '
				</tr>';
		}

		$html .= '
			</tbody>
		</table>';

		echo $html;
	}

	/**
	 * Add-ons tab callback
	 *
	 * @return void
	 */
	private function addons_tab_cb() {
		?>
		<h3><?php esc_html_e( 'Add-ons / Extensions', 'responsive-lightbox' ); ?></h3>
		<p class="description"><?php esc_html_e( 'Enhance your website with these beautiful, easy to use extensions, designed with Responsive Lightbox & Gallery integration in mind.', 'responsive-lightbox' ); ?></p>
		<br />
		<?php
		$cache = get_transient( 'responsive_lightbox_addons_feed' );

		if ( $cache === false ) {
			$feed = wp_remote_get( 'https://dfactory.eu/?feed=addons&product=responsive-lightbox', [ 'sslverify' => false ] );

			if ( ! is_wp_error( $feed ) ) {
				if ( isset( $feed['body'] ) && strlen( $feed['body'] ) > 0 )
					$cache = wp_remote_retrieve_body( $feed );
			} else
				$cache = '<div class="error"><p>' . esc_html__( 'There was an error retrieving the extensions list from the server. Please try again later.', 'responsive-lightbox' ) . '</p></div>';
		}

		echo $cache;
	}

	/**
	 * Licenses section callback.
	 *
	 * @return void
	 */
	public function licenses_section_cb() {
		?><p class="description"><?php esc_html_e( 'A list of licenses for your Responsive Lightbox & Gallery extensions.', 'responsive-lightbox' ); ?></p><?php
	}

	/**
	 * License field callback.
	 *
	 * @param array $args
	 * @return void
	 */
	public function license_field_cb( $args ) {
		$licenses = get_option( 'responsive_lightbox_licenses' );

		if ( ! empty( $licenses ) ) {
			$license = isset( $licenses[$args['id']]['license'] ) ? $licenses[$args['id']]['license'] : '';
			$status = ! empty( $licenses[$args['id']]['status'] );
		} else {
			$license = '';
			$status = false;
		} ?>
		<fieldset class="rl_license rl_license-<?php echo esc_attr( $args['id'] ); ?>">
			<input type="text" class="regular-text" name="responsive_lightbox_licenses[<?php echo esc_attr( $args['id'] ); ?>][license]" value="<?php echo esc_attr( $license ); ?>"><span class="dashicons <?php echo ( $status ? 'dashicons-yes' : 'dashicons-no' ); ?>"></span>
			<p class="description"><?php echo esc_html( sprintf( __( 'Enter your license key to activate %s extension and enable automatic upgrade notices.', 'responsive-lightbox' ), $args['name'] ) ); ?></p>
		</fieldset>
		<?php
	}

	/**
	 * Validate licenses function.
	 *
	 * @param array $input
	 * @return array
	 */
	public function validate_licenses( $input ) {
		// check cap
		if ( ! current_user_can( apply_filters( 'rl_lightbox_settings_capability', Responsive_Lightbox()->options['capabilities']['active'] ? 'edit_lightbox_settings' : 'manage_options' ) ) )
			return $input;

		// check option page
		$option_page = isset( $_POST['option_page'] ) ? sanitize_key( $_POST['option_page'] ) : '';

		// check page
		if ( ! $option_page )
			return $input;

		$rl_licenses = isset( $_POST['responsive_lightbox_licenses'] ) && is_array( $_POST['responsive_lightbox_licenses'] ) ? map_deep( $_POST['responsive_lightbox_licenses'], 'sanitize_key' ) : [];

		// check data
		if ( ! $rl_licenses )
			return $input;

		// get extension licenses
		$extensions = apply_filters( 'rl_settings_licenses', [] );

		if ( empty( $extensions ) )
			return $input;

		// save settings
		if ( isset( $_POST['save_rl_licenses'] ) ) {
			$licenses = get_option( 'responsive_lightbox_licenses' );
			$statuses = [ 'updated' => 0, 'error' => 0 ];

			foreach ( $extensions as $extension ) {
				if ( ! isset( $rl_licenses[$extension['id']] ) )
					continue;

				$license = preg_replace( '/[^a-zA-Z0-9]/', '', $rl_licenses[$extension['id']]['license'] );
				$status = ! empty( $licenses ) && ! empty( $licenses[$extension['id']]['status'] );

				// request data
				$request_args = [
					'action'	=> 'activate_license',
					'license'	=> $license,
					'item_name'	=> $extension['item_name']
				];

				// request
				$response = $this->license_request( $request_args );

				// validate request
				if ( is_wp_error( $response ) ) {
					$input[$extension['id']['status']] = false;
					$statuses['error']++;
				} else {
					// decode the license data
					$license_data = json_decode( wp_remote_retrieve_body( $response ) );

					// assign the data
					if ( $license_data->license == 'valid' ) {
						$input[$extension['id']]['status'] = true;

						if ( $status === false )
							$statuses['updated']++;
					} else {
						$input[$extension['id']]['status'] = false;
						$statuses['error']++;
					}
				}
			}

			// success notice
			if ( $statuses['updated'] > 0 )
				add_settings_error( 'rl_licenses_settings', 'license_activated', esc_html( sprintf( _n( '%s license successfully activated.', '%s licenses successfully activated.', (int) $statuses['updated'], 'responsive-lightbox' ), (int) $statuses['updated'] ) ), 'updated' );

			// failed notice
			if ( $statuses['error'] > 0 )
				add_settings_error( 'rl_licenses_settings', 'license_activation_failed', esc_html( sprintf( _n( '%s license activation failed.', '%s licenses activation failed.', (int) $statuses['error'], 'responsive-lightbox' ), (int) $statuses['error'] ) ), 'error' );
		} elseif ( isset( $_POST['reset_rl_licenses'] ) ) {
			$licenses = get_option( 'responsive_lightbox_licenses' );
			$statuses = [
				'updated'	=> 0,
				'error'		=> 0
			];

			foreach ( $extensions as $extension ) {
				$license = ! empty( $licenses ) && isset( $licenses[$extension['id']]['license'] ) ? $licenses[$extension['id']]['license'] : '';
				$status = ! empty( $licenses ) && ! empty( $licenses[$extension['id']]['status'] );

				if ( $status === true || ( $status === false && ! empty( $license ) ) ) {
					// request data
					$request_args = [
						'action'	=> 'deactivate_license',
						'license'	=> trim( $license ),
						'item_name'	=> $extension['item_name']
					];

					// request
					$response = $this->license_request( $request_args );

					// validate request
					if ( is_wp_error( $response ) )
						$statuses['error']++;
					else {
						// decode the license data
						$license_data = json_decode( wp_remote_retrieve_body( $response ) );

						// assign the data
						if ( $license_data->license == 'deactivated' ) {
							$input[$extension['id']]['license'] = '';
							$input[$extension['id']]['status'] = false;

							$statuses['updated']++;
						} else
							$statuses['error']++;
					}
				}
			}

			// success notice
			if ( $statuses['updated'] > 0 )
				add_settings_error( 'rl_licenses_settings', 'license_deactivated', esc_html( sprintf( _n( '%s license successfully deactivated.', '%s licenses successfully deactivated.', (int) $statuses['updated'], 'responsive-lightbox' ), (int) $statuses['updated'] ) ), 'updated' );

			// failed notice
			if ( $statuses['error'] > 0 )
				add_settings_error( 'rl_licenses_settings', 'license_deactivation_failed', esc_html( sprintf( _n( '%s license deactivation failed.', '%s licenses deactivation failed.', (int) $statuses['error'], 'responsive-lightbox' ), (int) $statuses['error'] ) ), 'error' );
		}

		return $input;
	}

	/**
	 * License request function.
	 *
	 * @param array $args
	 * @return mixed
	 */
	private function license_request( $args ) {
		// data to send in our API request
		$api_params = [
			'edd_action'	=> $args['action'],
			'license'		=> sanitize_key( $args['license'] ),
			'item_name'		=> urlencode( $args['item_name'] ),
			// 'item_id'		=> $args['item_id'],
			'url'			=> home_url(),
			'timeout'		=> 60,
			'sslverify'		=> false
		];

		// call the custom API
		$response = wp_remote_get( add_query_arg( $api_params, 'http://dfactory.eu' ) );

		return $response;
	}
}