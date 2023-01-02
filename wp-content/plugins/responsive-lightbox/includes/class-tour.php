<?php
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

new Responsive_Lightbox_Tour();

/**
 * Responsive_Lightbox_Tour class.
 *
 * @class Responsive_Lightbox_Tour
 */
class Responsive_Lightbox_Tour {

	/**
	 * Class constructor.
	 *
	 * @return void
	 */
	public function __construct() {
		// actions
		add_action( 'admin_menu', [ $this, 'admin_menu' ] );
		add_action( 'admin_init', [ $this, 'init_tour' ] );
		add_action( 'wp_ajax_rl-ignore-tour', [ $this, 'ignore_tour' ] );
	}

	/**
	 * Initialize tour.
	 *
	 * @global string $pagenow
	 *
	 * @return void
	 */
	public function init_tour() {
		if ( ! current_user_can( apply_filters( 'rl_lightbox_settings_capability', 'manage_options' ) ) )
			return;

		global $pagenow;

		if ( $pagenow === 'admin.php' && isset( $_GET['page'] ) && $_GET['page'] === 'responsive-lightbox-tour' ) {
			set_transient( 'rl_active_tour', 1, 0 );

			if ( Responsive_Lightbox()->options['builder']['gallery_builder'] )
				wp_redirect( admin_url( 'edit.php?post_type=rl_gallery' ) );
			else
				wp_redirect( admin_url( 'admin.php?page=responsive-lightbox-settings' ) );

			exit;
		}

		if ( (int) get_transient( 'rl_active_tour' ) === 1 ) {
			add_action( 'admin_enqueue_scripts', [ $this, 'tour_scripts_styles' ] );
			add_action( 'admin_print_footer_scripts', [ $this, 'start_tour' ] );
		}
	}

	/**
	 * Add temporary admin menu.
	 *
	 * @global string $pagenow
	 *
	 * @return void
	 */
	public function admin_menu() {
		global $pagenow;

		if ( $pagenow === 'admin.php' && isset( $_GET['page'] ) && $_GET['page'] === 'responsive-lightbox-tour' )
			add_submenu_page( 'responsive-lightbox-settings', '', '', apply_filters( 'rl_lightbox_settings_capability', 'manage_options' ), 'responsive-lightbox-tour', function() {} );
	}

	/**
	 * Load pointer scripts.
	 *
	 * @return void
	 */
	public function tour_scripts_styles() {
		// enqueue styles
		wp_enqueue_style( 'wp-pointer' );

		// enqueue scripts
		wp_enqueue_script( 'jquery-ui' );
		wp_enqueue_script( 'wp-pointer' );
		wp_enqueue_script( 'utils' );
	}

	/**
	 * Load the introduction tour.
	 *
	 * @global string $pagenow
	 *
	 * @return void
	 */
	public function start_tour() {
		global $pagenow;

		$pointer = [];
		$rl = Responsive_Lightbox();

		// get page
		$page = isset( $_GET['page'] ) ? sanitize_key( $_GET['page'] ) : '';

		// get post type
		$post_type = isset( $_GET['post_type'] ) ? sanitize_key( $_GET['post_type'] ) : '';

		// get taxonomy
		$taxonomy = isset( $_GET['taxonomy'] ) ? sanitize_key( $_GET['taxonomy'] ) : '';

		// galleries
		if ( $pagenow === 'edit.php' ) {
			if ( $post_type && $post_type === 'rl_gallery' && $rl->options['builder']['gallery_builder'] ) {
				$pointer = [
					'content'	=> '<h3>' . esc_html__( 'Gallery Builder', 'responsive-lightbox' ) . '</h3><p>' . esc_html__( 'This is an advanced gallery builder. Here you can see a preview of all created galleries along with their settings, such as the name, type, source of images, author or date of publication. You can also add a new gallery, edit existing ones or quickly copy the code allowing its use on the site.', 'responsive-lightbox' ) . '</p>',
					'button2'	=> __( 'Next', 'responsive-lightbox' ),
					'id'		=> '#wpbody-content h1'
				];

				// next categories?
				if ( $rl->options['builder']['categories'] )
					$pointer['function'] = 'window.location="' . esc_url_raw( admin_url( 'edit-tags.php?taxonomy=rl_category&post_type=rl_gallery' ) ) . '";';
				// next tags?
				elseif ( $rl->options['builder']['tags'] )
					$pointer['function'] = 'window.location="' . esc_url_raw( admin_url( 'edit-tags.php?taxonomy=rl_tag&post_type=rl_gallery' ) ) . '";';
				// or settings?
				else
					$pointer['function'] = 'window.location="' . esc_url_raw( admin_url( 'admin.php?page=responsive-lightbox-settings' ) ) . '";';
			}
		// gallery taxonomies
		} elseif ( $pagenow === 'edit-tags.php' ) {
			if ( $post_type && $taxonomy && $post_type === 'rl_gallery' ) {
				if ( $taxonomy === 'rl_category' ) {
					$pointer = [
						'content'	=> '<h3>' . esc_html__( 'Gallery Categories', 'responsive-lightbox' ) . '</h3><p>' . esc_html__( 'Gallery categories allow you to arrange galleries into individual groups that you can potentially use. Here you can create, name and edit them. However, assigning the gallery to the category takes place on the gallery editing screen.', 'responsive-lightbox' ) . '</p>',
						'button2'	=> __( 'Next', 'responsive-lightbox' ),
						'id'		=> '#wpbody-content h1'
					];

					// next tags?
					if ( $rl->options['builder']['tags'] )
						$pointer['function'] = 'window.location="' . esc_url_raw( admin_url( 'edit-tags.php?taxonomy=rl_tag&post_type=rl_gallery' ) ) . '";';
					// or settings?
					else
						$pointer['function'] = 'window.location="' . esc_url_raw( admin_url( 'admin.php?page=responsive-lightbox-settings' ) ) . '";';
				} elseif ( $taxonomy === 'rl_tag' ) {
					$pointer = [
						'content'	=> '<h3>' . esc_html__( 'Gallery Tags', 'responsive-lightbox' ) . '</h3><p>' . esc_html__( 'Gallery tags, like categories, allow you to arrange galleries into groups. You can think of them as keywords, which you can use to further specify your galleries. Here you can create, name and edit them.', 'responsive-lightbox' ) . '</p>',
						'button2'	=> __( 'Next', 'responsive-lightbox' ),
						'id'		=> '#wpbody-content h1',
						'function'	=> 'window.location="' . esc_url_raw( admin_url( 'admin.php?page=responsive-lightbox-settings' ) ) . '";'
					];
				}
			}
		// settings
		} elseif ( $pagenow === 'admin.php' && $page ) {
			// general
			if ( $page === 'responsive-lightbox-settings' ) {
				$pointer = [
					'content'	=> '<h3>' . esc_html__( 'General Settings', 'responsive-lightbox' ) . '</h3><p>' . esc_html__( "Here are the main settings for Responsive Lightbox & Gallery. They allow you to specify general rules of the plugin's operation and technical parameters of the lightbox effect and gallery. For example - you can choose your favorite lightbox effect, specify for which elements it will automatically launch and set its parameters. You can also choose the default gallery and its settings.", 'responsive-lightbox' ) . '</p>',
					'button2'	=> __( 'Next', 'responsive-lightbox' ),
					'id'		=> '#wpbody-content .wrap .nav-tab-active',
					'function'	=> 'window.location="' . esc_url_raw( admin_url( 'admin.php?page=responsive-lightbox-configuration' ) ) . '";'
				];
			// lightboxes
			} elseif ( $page === 'responsive-lightbox-configuration' ) {
				$pointer = [
					'content'	=> '<h3>' . esc_html__( 'Lightboxes Settings', 'responsive-lightbox' ) . '</h3><p>' . esc_html__( 'Each lightbox has different look, possibilities and parameters. Here is a list of available lightbox effects along with their settings. After entering the tab you can see the settings of the currently selected lightbox, but you can also modify or restore the settings of the others.', 'responsive-lightbox' ) . '</p>',
					'button2'	=> __( 'Next', 'responsive-lightbox' ),
					'id'		=> '#wpbody-content .wrap .nav-tab-active',
					'function'	=> 'window.location="' . esc_url_raw( admin_url( 'admin.php?page=responsive-lightbox-gallery' ) ) . '";'
				];
			// galleries
			} elseif ( $page === 'responsive-lightbox-gallery' ) {
				$pointer = [
					'content'	=> '<h3>' . esc_html__( 'Galleries Settings', 'responsive-lightbox' ) . '</h3><p>' . esc_html__( "This is the screen of the default gallery settings. As in the case of lightbox effects, there is a list of available galleries and their parameters. After entering the tab you can see the settings of the currently selected gallery. You can modify and adjust them to your needs or restore it's default settings.", 'responsive-lightbox' ) . '</p>',
					'button2'	=> __( 'Next', 'responsive-lightbox' ),
					'id'		=> '#wpbody-content .wrap .nav-tab-active',
					'function'	=> 'window.location="' . esc_url_raw( admin_url( 'admin.php?page=responsive-lightbox-builder' ) ) . '";'
				];
			// builder
			} elseif ( $page === 'responsive-lightbox-builder' ) {
				$pointer = [
					'content'	=> '<h3>' . esc_html__( 'Builder Settings', 'responsive-lightbox' ) . '</h3><p>' . esc_html__( 'You can use the galleries in many ways - insert them into posts using the Add Gallery button, insert manually using shortcodes or add to the theme using functions. But you can also display them in archives just like other post types. Use these settings to specify the functionality of the gallery builder like categories, tags, archives and permalinks.', 'responsive-lightbox' ) . '</p>',
					'button2'	 => __( 'Next', 'responsive-lightbox' ),
					'id'		 => '#wpbody-content .wrap .nav-tab-active',
					'function'	 => 'window.location="' . esc_url_raw( admin_url( 'admin.php?page=responsive-lightbox-folders' ) ) . '";'
				];
			// media folders
			} elseif ( $page === 'responsive-lightbox-folders' ) {
				$pointer = [
					'content'	=> '<h3>' . esc_html__( 'Folders Settings', 'responsive-lightbox' ) . '</h3><p>' . esc_html__( 'Responsive Lithbox & Gallery comes with an optional Media Folders feature that extends your WordPress Media Library with visual folders. It allows you to organize your attachments in a folder tree structure. Move, copy, rename and delete files and folders with a nice drag and drop interface.', 'responsive-lightbox' ) . '</p>',
					'button2'	=> __( 'Next', 'responsive-lightbox' ),
					'id'		=> '#wpbody-content .wrap .nav-tab-active',
					'function'	=> 'window.location="' . esc_url_raw( admin_url( 'admin.php?page=responsive-lightbox-capabilities' ) ) . '";'
				];
			// capabilities
			} elseif ( $page === 'responsive-lightbox-capabilities' ) {
				$pointer = [
					'content'	=> '<h3>' . esc_html__( 'Capabilities Settings', 'responsive-lightbox' ) . '</h3><p>' . esc_html__( 'Capabilities give you the ability to control what users can and cannot do within the plugin. By default only the Administrator role allows a user to perform all possible capabilities. But you can fine tune these settings to match your specific requirements.', 'responsive-lightbox' ) . '</p>',
					'button2'	=> __( 'Next', 'responsive-lightbox' ),
					'id'		=> '#wpbody-content .wrap .nav-tab-active',
					'function'	=> 'window.location="' . esc_url_raw( admin_url( 'admin.php?page=responsive-lightbox-remote_library' ) ) . '";'
				];
			// remote library
			} elseif ( $page === 'responsive-lightbox-remote_library' ) {
				// get tabs
				$tabs = array_keys( $rl->settings->tabs );

				// get current tab index
				$tab_index = (int) array_search( 'remote_library', $tabs, true );

				$pointer = [
					'content'	=> '<h3>' . esc_html__( 'Remote Library Settings', 'responsive-lightbox' ) . '</h3><p>' . esc_html__( 'Are you looking for free royalty free public domain and CC0-Licensed images for your website? Or you need to access your images stored in photo-sharing apps? Remote Library allows you to use images from multiple sources like Unsplash, Pixabay, Flickr or Instagram directly in your WordPress Media Manager. Now you can create galleries, browse, insert and import images as never before.', 'responsive-lightbox' ) . '</p>',
					'button2'	=> __( 'Next', 'responsive-lightbox' ),
					'id'		=> '#wpbody-content .wrap .nav-tab-active',
					'function'	=> 'window.location="' . esc_url_raw( admin_url( 'admin.php?page=responsive-lightbox-' . $tabs[$tab_index + 1] ) ) . '";'
				];
			// licenses
			} elseif ( $page === 'responsive-lightbox-licenses' ) {
				$pointer = [
					'content'	=> '<h3>' . esc_html__( 'Licenses Settings', 'responsive-lightbox' ) . '</h3><p>' . esc_html__( 'This section contains a list of currently installed premium extensions. Activate your licenses to have access to automatic updates from your site. To activate the license, copy and paste the license key for the extension and save the changes. Available license keys can be found on your account on our website.', 'responsive-lightbox' ) . '</p>',
					'button2'	=> __( 'Next', 'responsive-lightbox' ),
					'id'		=> '#wpbody-content .wrap .nav-tab-active',
					'function'	=> 'window.location="' . esc_url_raw( admin_url( 'admin.php?page=responsive-lightbox-addons' ) ) . '";'
				];
			// addons
			} elseif ( $page === 'responsive-lightbox-addons' ) {
				$pointer = [
					'content'	=> '<h3>' . esc_html__( 'Add-ons', 'responsive-lightbox' ) . '</h3><p>' . esc_html__( 'Responsive Lightbox & Gallery is more than that. Do you need a beautiful lightbox effect, integration with social media, an attractive image gallery? Among our products you will surely find something for yourself. Boost your creativity and enhance your website with these beautiful, easy to use extensions, designed with Responsive Lightbox & Gallery integration in mind.', 'responsive-lightbox' ) . '</p>',
					'button2'	=> '',
					'id'		=> '#wpbody-content .wrap .nav-tab-active',
					'function'	=> ''
				];
			// plugins related tabs
			} else
				$pointer = apply_filters( 'rl_tour_pointer', [], $page );
		}

		// valid pointer?
		if ( ! empty( $pointer ) ) {
			$this->print_scripts(
				$pointer['id'],
				[
					'content'		=> $pointer['content'],
					'pointerWidth'	=> 400,
					'position'		=> [
						'edge'	=> 'top',
						'align'	=> is_rtl() ? 'right' : 'left'
					]
				],
				__( 'Close', 'responsive-lightbox' ),
				$pointer['button2'],
				$pointer['function']
			);
		}
	}

	/**
	 * Ignore tour.
	 *
	 * @return void
	 */
	public function ignore_tour() {
		if ( isset( $_POST['rl_nonce'] ) && ctype_alnum( $_POST['rl_nonce'] ) && wp_verify_nonce( $_POST['rl_nonce'], 'rl-ignore-tour' ) !== false )
			delete_transient( 'rl_active_tour' );

		exit;
	}

	/**
	 * Print the pointer script.
	 *
	 * @return void
	 */
	public function print_scripts( $selector, $options, $button1, $button2 = false, $function = '' ) {
		?>
		<script type="text/javascript">
			//<![CDATA[
			( function( $ ) {
				// ready event
				$( function() {
					var rlPointerOptions = <?php echo json_encode( $options ); ?>;
					var setup;

					function rlSetIgnore( option, hide, nonce ) {
						$.post( ajaxurl, {
							action: 'rl-ignore-tour',
							rl_nonce: nonce
						}, function( data ) {
							if ( data ) {
								$( '#' + hide ).hide();
								$( '#hidden_ignore_' + option ).val( 'ignore' );
							}
						} );
					}

					rlPointerOptions = $.extend( rlPointerOptions, {
						buttons: function( event, t ) {
							var button = $( '<a id="rl-pointer-close" style="margin-left: 5px;" class="button-secondary">' + '<?php esc_html_e( $button1 ); ?>' + '</a>' );

							button.on( 'click.pointer', function() {
								t.element.pointer( 'close' );
							} );

							return button;
						},
						close: function() {}
					} );

					setup = function() {
						$( '<?php echo esc_js( $selector ); ?>' ).pointer( rlPointerOptions ).pointer( 'open' );

						<?php if ( $button2 ) { ?>

							$( '#rl-pointer-close' ).after( '<a id="pointer-primary" class="button-primary">' + '<?php esc_html_e( $button2 ); ?>' + '</a>' );
							$( '#pointer-primary' ).on( 'click', function() {
								<?php echo $function; ?>
							} );

						<?php } ?>

						$( '#rl-pointer-close' ).on( 'click', function() {
							rlSetIgnore( 'tour', 'wp-pointer-0', '<?php echo esc_js( wp_create_nonce( 'rl-ignore-tour' ) ); ?>' );
						} );
					};

					if ( rlPointerOptions.position && rlPointerOptions.position.defer_loading )
						$( window ).on( 'load.wp-pointers', setup );
					else
						$( document ).ready( setup );
				} );
			} )( jQuery );
			//]]>
		</script>
		<?php
	}
}