<?php
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

new Responsive_Lightbox_Frontend();

/**
 * Responsive Lightbox frontend class.
 *
 * @class Responsive_Lightbox_Frontend
 */
class Responsive_Lightbox_Frontend {

	public $gallery_no = 0;

	/**
	 * Class constructor.
	 *
	 * @return void
	 */
	public function __construct() {
		// set instance
		Responsive_Lightbox()->frontend = $this;

		// actions
		add_action( 'wp_enqueue_scripts', [ $this, 'wp_dequeue_scripts' ], 100 );
		add_action( 'rl_before_gallery', [ $this, 'before_gallery' ], 10, 2 );
		add_action( 'rl_after_gallery', [ $this, 'after_gallery' ], 10, 2 );
		add_action( 'after_setup_theme', [ $this, 'woocommerce_gallery_init' ], 1000 );

		// filters
		add_filter( 'rl_gallery_container_class', [ $this, 'gallery_container_class' ], 10, 3 );
		add_filter( 'the_content', [ $this, 'gallery_preview' ] );
		add_filter( 'the_content', [ $this, 'add_lightbox' ], 11 );
		add_filter( 'wp_get_attachment_link', [ $this, 'wp_get_attachment_link' ], 1000, 2 );
		add_filter( 'get_comment_text', [ $this, 'get_comment_text' ] );
		add_filter( 'dynamic_sidebar_params', [ $this, 'dynamic_sidebar_params' ] );
		add_filter( 'rl_widget_output', [ $this, 'widget_output' ], 10, 3 );
		add_filter( 'post_gallery', [ $this, 'gallery_attributes' ], 1000, 2 );
		add_filter( 'post_gallery', [ $this, 'basic_grid_gallery_shortcode' ], 1001, 2 );
		add_filter( 'post_gallery', [ $this, 'basic_slider_gallery_shortcode' ], 1001, 2 );
		add_filter( 'post_gallery', [ $this, 'basic_masonry_gallery_shortcode' ], 1001, 2 );
		add_filter( 'post_gallery', [ $this, 'force_custom_gallery_lightbox' ], 2000 );

		// visual composer
		add_filter( 'vc_shortcode_content_filter_after', [ $this, 'vc_shortcode_content_filter_after' ], 10, 2 );

		// woocommerce
		add_filter( 'woocommerce_single_product_image_html', [ $this, 'woocommerce_single_product_image_html' ], 100 );
		add_filter( 'woocommerce_single_product_image_thumbnail_html', [ $this, 'woocommerce_single_product_image_thumbnail_html' ], 100, 2 );
	}

	/**
	 * Add lightbox to images, galleries and videos.
	 *
	 * @param string $content HTML content
	 * @return string
	 */
	public function add_lightbox( $content ) {
		// get main instance
		$rl = Responsive_Lightbox();

		// get current script
		$script = $rl->get_lightbox_script();

		// prepare arguments
		$args = [
			'selector'	=> $rl->options['settings']['selector'],
			'script'	=> $script,
			'settings'	=> [
				'script'	=> $rl->options['configuration'][$script],
				'plugin'	=> $rl->options['settings']
			],
			'supports'	=> $rl->settings->scripts[$script]['supports']
		];

		// workaround for builder galleries to bypass images_as_gallery option, applied only to rl_gallery posts
		if ( is_singular( 'rl_gallery' ) )
			$args['settings']['plugin']['images_as_gallery'] = true;

		// search for links containing data-rl_content attribute
		preg_match_all( '/<a.*?data-rl_content=(?:\'|")(.*?)(?:\'|").*?>/i', $content, $links );

		// found any links?
		if ( ! empty ( $links[0] ) ) {
			foreach ( $links[0] as $link_number => $link ) {
				// set content type
				$args['content'] = $links[1][$link_number];

				// set link number
				$args['link_number'] = $link_number;

				// update link
				$content = str_replace( $link, $this->lightbox_content_link( $link, $args ), $content );
			}
		}

		// images
		if ( $args['settings']['plugin']['image_links'] || $args['settings']['plugin']['images_as_gallery'] || $args['settings']['plugin']['force_custom_gallery'] ) {
			// search for image links
			preg_match_all( '/<a([^>]*?)href=(?:\'|")([^>]*?)\.(bmp|gif|jpeg|jpg|png|webp)(?:\'|")(.*?)>(.*?)<\/a>/is', $content, $links );

			// found any links?
			if ( ! empty ( $links[0] ) ) {
				// generate hash for single images gallery
				if ( $args['settings']['plugin']['images_as_gallery'] )
					$args['rel_hash'] = '-gallery-' . $this->generate_hash();
				else
					$args['rel_hash'] = '';

				foreach ( $links[0] as $link_number => $link ) {
					// get attachment id
					$args['image_id'] = $this->get_attachment_id_by_url( $links[2][$link_number] . '.' . $links[3][$link_number] );

					// set link number
					$args['link_number'] = $link_number;

					// link parts
					$args['link_parts'] = [ $links[1][$link_number], $links[2][$link_number], $links[3][$link_number], $links[4][$link_number], $links[5][$link_number] ];

					// get title type
					$title_arg = $args['settings']['plugin']['force_custom_gallery'] ? $args['settings']['plugin']['gallery_image_title'] : $args['settings']['plugin']['image_title'];

					// update title if needed
					if ( $title_arg !== 'default' && $args['image_id'] )
						$args['title'] = $this->get_attachment_title( $args['image_id'], apply_filters( 'rl_lightbox_attachment_image_title_arg', $title_arg, $args['image_id'], $links[2][$link_number] . '.' . $links[3][$link_number] ) );
					else
						$args['title'] = '';

					// get caption type
					$caption_arg = $args['settings']['plugin']['force_custom_gallery'] ? $args['settings']['plugin']['gallery_image_caption'] : $args['settings']['plugin']['image_caption'];

					// update caption if needed
					if ( $caption_arg !== 'default' && $args['image_id'] )
						$args['caption'] = $this->get_attachment_title( $args['image_id'], apply_filters( 'rl_lightbox_attachment_image_title_arg', $caption_arg, $args['image_id'], $links[2][$link_number] . '.' . $links[3][$link_number] ) );
					else
						$args['caption'] = '';

					// rl gallery link?
					if ( preg_match( '/class="(?:.*?)rl-gallery-link[^"]*?"/i', $links[1][$link_number] ) === 1 || preg_match( '/class="(?:.*?)rl-gallery-link[^"]*?"/i', $links[4][$link_number] ) === 1 ) {
						// update link allowing only filter to run, bypass default changes
						$content = str_replace( $link, $this->lightbox_image_link( $link, $args, true ), $content );
					} else {
						// update link
						$content = str_replace( $link, $this->lightbox_image_link( $link, $args ), $content );
					}
				}
			}
		}

		// videos
		if ( $args['settings']['plugin']['videos'] ) {
			// search for video links
			preg_match_all('/<a([^>]*?)href=(?:\'|")((http|https)(?::\/\/|)(?:(?:(?:youtu\.be\/|(?:www\.)?youtube\.com\/)(?:embed\/|v\/|watch\?v=)?([\w-]{11})(?:\?)?([a-z0-9;:@#&%=+\/\$_.-]*))|(?:(?:www\.)?vimeo\.com\/([0-9]+)(?:\?)?([a-z0-9;:@#&%=+\/\$_.-]*))))(?:\'|")(.*?)>(.*?)<\/a>/i', $content, $links );

			// set empty video arguments
			$args['video_id'] = $args['video_type'] = $args['video_query'] = $args['video_protocol'] = '';

			// found any links?
			if ( ! empty ( $links[0] ) ) {
				foreach ( $links[0] as $link_number => $link ) {
					// youtube?
					if ( $links[4][$link_number] !== '' ) {
						$args['video_id'] = $links[4][$link_number];
						$args['video_type'] = 'youtube';
						$args['video_query'] = $links[5][$link_number];
					// vimeo?
					} elseif ( $links[6][$link_number] !== '' ) {
						$args['video_id'] = $links[6][$link_number];
						$args['video_type'] = 'vimeo';
						$args['video_query'] = $links[7][$link_number];
					}

					// set video protocol
					$args['video_protocol'] = $links[3][$link_number];

					// set link number
					$args['link_number'] = $link_number;

					// link parts
					$args['link_parts'] = [ $links[1][$link_number], $links[2][$link_number], $links[8][$link_number], $links[9][$link_number] ];

					// rl gallery link?
					if ( preg_match( '/class="(?:.*?)rl-gallery-link[^"]*?"/i', $links[1][$link_number] ) === 1 || preg_match( '/class="(?:.*?)rl-gallery-link[^"]*?"/i', $links[8][$link_number] ) === 1 ) {
						// update link allowing only filter to run, bypass default changes
						$content = str_replace( $link, $this->lightbox_video_link( $link, $args, true ), $content );
					} else {
						// update link
						$content = str_replace( $link, $this->lightbox_video_link( $link, $args ), $content );
					}
				}
			}
		}

		return $content;
	}

	/**
	 * Add lightbox to video links.
	 *
	 * @param string $link Video link
	 * @param array $args Link arguments
	 * @param bool $only_filter Whether function should run only filter
	 * @return string
	 */
	public function lightbox_video_link( $link, $args, $only_filter = false ) {
		if ( ! $only_filter ) {
			// link already contains data-rel attribute?
			if ( preg_match( '/<a.*?(?:data-rel)=(?:\'|")(.*?)(?:\'|").*?>/is', $link, $result ) === 1 ) {
				// allow to modify link?
				if ( $result[1] !== 'norl' ) {
					// swipebox video fix
					if ( $args['script'] === 'swipebox' && $args['video_type'] === 'vimeo' )
						$link = str_replace( $args['link_parts'][1], add_query_arg( 'width', $args['settings']['script']['video_max_width'], $args['link_parts'][1] ), $link );

					// replace data-rel
					$link = preg_replace( '/data-rel=(\'|")(.*?)(\'|")/', 'data-rel="' . esc_attr( $args['selector'] ) . '-video-' . (int) $args['link_number'] . '"', $link );

					if ( $args['script'] === 'magnific' )
						$link = preg_replace( '/(<a.*?)>/is', '$1 data-magnific_type="video">', $link );
				}
			} else {
				// swipebox video fix
				if ( $args['script'] === 'swipebox' && $args['video_type'] === 'vimeo' )
					$args['link_parts'][1] = add_query_arg( 'width', $args['settings']['script']['video_max_width'], $args['link_parts'][1] );

				// add data-rel
				$link = '<a' . $args['link_parts'][0] . 'href="' . $args['link_parts'][1] . '" data-rel="' . esc_attr( $args['selector'] ) . '-video-' . (int) $args['link_number'] . '"' . $args['link_parts'][2] . '>' . $args['link_parts'][3] . '</a>';

				if ( $args['script'] === 'magnific' )
					$link = preg_replace( '/(<a.*?)>/is', '$1 data-magnific_type="video">', $link );
			}
		}

		return apply_filters( 'rl_lightbox_video_link', $link, $args );
	}

	/**
	 * Add lightbox to image links.
	 *
	 * @param string $link Image link
	 * @param array $args Link arguments
	 * @param bool $only_filter Whether function should run only filter
	 * @return string
	 */
	public function lightbox_image_link( $link, $args, $only_filter = false ) {
		if ( ! $only_filter ) {
			if ( isset( $_GET['rl_gallery_no'], $_GET['rl_page'] ) )
				$this->gallery_no = (int) $_GET['rl_gallery_no'];

			// link already contains data-rel attribute?
			if ( preg_match( '/<a.*?(?:data-rel)=(?:\'|")(.*?)(?:\'|").*?>/is', $link, $result ) === 1 ) {
				// allow to modify link?
				if ( $result[1] !== 'norl' ) {
					// gallery?
					if ( $args['settings']['plugin']['images_as_gallery'] || $args['settings']['plugin']['force_custom_gallery'] )
						$link = preg_replace( '/data-rel=(\'|")(.*?)(\'|")/s', 'data-rel="' . esc_attr( $args['selector'] ) . '-gallery-' . esc_attr( base64_encode( $result[1] ) ) . '" data-rl_title="__RL_IMAGE_TITLE__" data-rl_caption="__RL_IMAGE_CAPTION__"' . ( $args['script'] === 'magnific' ? ' data-magnific_type="gallery"' : '' ) . ( $args['script'] === 'imagelightbox' ? ' data-imagelightbox="' . (int) $args['link_number'] . '"' : '' ), $link );
					// single image
					else
						$link = preg_replace( '/data-rel=(\'|")(.*?)(\'|")/s', 'data-rel="' . esc_attr( $args['selector'] ) . '-image-' . esc_attr( base64_encode( $result[1] ) ) . '"' . ( $args['script'] === 'magnific' ? ' data-magnific_type="image"' : '' ) . ( $args['script'] === 'imagelightbox' ? ' data-imagelightbox="' . (int) $args['link_number'] . '"' : '' ) . ' data-rl_title="__RL_IMAGE_TITLE__" data-rl_caption="__RL_IMAGE_CAPTION__"', $link );
				}
			// link without data-rel
			} else {
				// force images?
				if ( $args['settings']['plugin']['force_custom_gallery'] ) {
					// link already contains rel attribute?
					if ( preg_match( '/<a.*?(?:rel)=(?:\'|")(.*?)(?:\'|").*?>/is', $link, $result ) === 1 ) {
						// allow to modify link?
						if ( $result[1] !== 'norl' )
							$link = preg_replace( '/rel=(\'|")(.*?)(\'|")/', 'data-rel="' . esc_attr( $args['selector'] ) . '-gallery-' . (int) $this->gallery_no . '" data-rl_title="__RL_IMAGE_TITLE__" data-rl_caption="__RL_IMAGE_CAPTION__"' . ( $args['script'] === 'magnific' ? ' data-magnific_type="gallery"' : '' ) . ( $args['script'] === 'imagelightbox' ? ' data-imagelightbox="' . (int) $args['link_number'] . '"' : '' ), $link );
					} else
						$link = '<a' . $args['link_parts'][0] . ' href="' . $args['link_parts'][1] . '.' . $args['link_parts'][2] . '" data-rel="' . esc_attr( $args['selector'] ) . '-gallery-' . (int) $this->gallery_no . '" data-rl_title="__RL_IMAGE_TITLE__" data-rl_caption="__RL_IMAGE_CAPTION__"' . ( $args['script'] === 'magnific' ? ' data-magnific_type="gallery"' : '' ) . ( $args['script'] === 'imagelightbox' ? ' data-imagelightbox="' . (int) $args['link_number'] . '"' : '' ) . $args['link_parts'][3] . '>' . $args['link_parts'][4] . '</a>';
				} else
					$link = '<a' . $args['link_parts'][0] . 'href="' . $args['link_parts'][1] . '.' . $args['link_parts'][2] . '"' . $args['link_parts'][3] . ' data-rel="' . esc_attr( $args['selector'] ) . ( $args['settings']['plugin']['images_as_gallery'] ? esc_attr( $args['rel_hash'] ) : '-image-' . (int) $args['link_number'] ) . '"' . ( $args['script'] === 'magnific' ? ' data-magnific_type="image"' : '' ) . ( $args['script'] === 'imagelightbox' ? ' data-imagelightbox="' . (int) $args['link_number'] . '"' : '' ) . ' data-rl_title="__RL_IMAGE_TITLE__" data-rl_caption="__RL_IMAGE_CAPTION__">' . $args['link_parts'][4] . '</a>';
			}

			// prepare title and caption
			$title = trim ( nl2br( $args['title'] ) );
			$caption = trim( nl2br( $args['caption'] ) );

			if ( ! rl_current_lightbox_supports( 'html_caption' ) ) {
				$title = wp_strip_all_tags( $title, true );
				$caption = wp_strip_all_tags( $caption, true );
			}

			// use safe replacement for data-rl_title and data-rl_caption
			$link = str_replace( '__RL_IMAGE_TITLE__', esc_attr( $title ), str_replace( '__RL_IMAGE_CAPTION__', esc_attr( $caption ), $link ) );

			// title exists?
			if ( preg_match( '/<a.*? title=(?:\'|").*?(?:\'|").*?>/is', $link ) === 1 ) {
				$link = preg_replace( '/(<a.*? title=(?:\'|")).*?((?:\'|").*?>)/s', '${1}__RL_IMAGE_TITLE__$2', $link );
			} else
				$link = preg_replace( '/(<a.*?)>/s', '$1 title="__RL_IMAGE_TITLE__">', $link );

			// last safe replacement for title
			$link = str_replace( '__RL_IMAGE_TITLE__', esc_attr( $title ), $link );
		}

		return apply_filters( 'rl_lightbox_image_link', $link, $args );
	}

	/**
	 * Add lightbox to gallery image links.
	 *
	 * @param string $link
	 * @param int|object $id
	 * @return string
	 */
	public function wp_get_attachment_link( $link, $id ) {
		// get main instance
		$rl = Responsive_Lightbox();

		if ( $rl->options['settings']['galleries'] && wp_attachment_is_image( $id ) ) {
			// get current script
			$script = $rl->get_lightbox_script();

			// prepare arguments
			$args = [
				'selector'	=> $rl->options['settings']['selector'],
				'script'	=> $script,
				'settings'	=> [
					'script'	=> $rl->options['configuration'][$script],
					'plugin'	=> $rl->options['settings']
				],
				'supports'	=> $rl->settings->scripts[$script]['supports'],
				'image_id'	=> $id,
				'title'		=> '',
				'caption'	=> '',
				'src'		=> []
			];

			$link = $this->lightbox_gallery_link( $link, $args );
		}

		return $link;
	}

	/**
	 * Add lightbox to gallery image links.
	 *
	 * @param string $link Gallery image link
	 * @param array $args Gallery link arguments
	 * @return string
	 */
	public function lightbox_gallery_link( $link, $args ) {
		// gallery image title
		$title = ! empty( $args['title'] ) ? $args['title'] : '';

		// get title type
		$title_arg = $args['settings']['plugin']['gallery_image_title'];

		// update title if needed
		if ( ! empty( $args['image_id'] ) && $title_arg !== 'default' ) {
			// original title
			$args['title'] = $this->get_attachment_title( $args['image_id'], apply_filters( 'rl_lightbox_attachment_image_title_arg', $title_arg, $args['image_id'], $link ) );
		}

		// prepare title
		$title = trim ( nl2br( $args['title'] ) );

		if ( ! rl_current_lightbox_supports( 'html_caption' ) )
			$title = wp_strip_all_tags( $title, true );

		// use safe replacement for title and data-rl_title
		if ( preg_match( '/<a.*? title=(?:\'|").*?(?:\'|").*?>/is', $link ) === 1 )
			$link = str_replace( '__RL_IMAGE_TITLE__', esc_attr( $title ), preg_replace( '/(<a.*? title=(?:\'|")).*?((?:\'|").*?>)/s', '$1__RL_IMAGE_TITLE__" data-rl_title="__RL_IMAGE_TITLE__$2', $link ) );
		else
			$link = str_replace( '__RL_IMAGE_TITLE__', esc_attr( $title ), preg_replace( '/(<a.*?)>/s', '$1 title="__RL_IMAGE_TITLE__" data-rl_title="__RL_IMAGE_TITLE__">', $link ) );

		// add class if needed
		if ( preg_match( '/<a[^>]*? class=(?:\'|").*?(?:\'|").*?>/is', $link ) === 1 )
			$link = preg_replace( '/(<a.*?) class=(?:\'|")(.*?)(?:\'|")(.*?>)/s', '$1 class="$2 rl-gallery-link" $3', $link );
		else
			$link = preg_replace( '/(<a.*?)>/s', '$1 class="rl-gallery-link">', $link );

		// gallery image caption
		$caption = ! empty( $args['caption'] ) ? $args['caption'] : '';

		// get caption type
		$caption_arg = $args['settings']['plugin']['gallery_image_caption'];

		// update caption if needed
		if ( ! empty( $args['image_id'] ) && $caption_arg !== 'default' ) {
			// original caption
			$args['caption'] = $this->get_attachment_title( $args['image_id'], apply_filters( 'rl_lightbox_attachment_image_title_arg', $caption_arg, $args['image_id'], $link ) );
		}

		// prepare caption
		$caption = trim( nl2br( $args['caption'] ) );

		if ( ! rl_current_lightbox_supports( 'html_caption' ) )
			$caption = wp_strip_all_tags( $caption, true );

		// use safe replacement for data-rl_caption
		$link = str_replace( '__RL_IMAGE_CAPTION__', esc_attr( $caption ), preg_replace( '/(<a.*?)>/s', '$1 data-rl_caption="__RL_IMAGE_CAPTION__">', $link ) );

		if ( isset( $_GET['rl_gallery_no'], $_GET['rl_page'] ) )
			$this->gallery_no = (int) $_GET['rl_gallery_no'];

		// link already contains data-rel attribute?
		if ( preg_match( '/<a.*?data-rel=(\'|")(.*?)(\'|").*?>/is', $link, $result ) === 1 ) {
			if ( $result[2] !== 'norl' )
				$link = preg_replace( '/(<a.*?data-rel=(?:\'|").*?)((?:\'|").*?>)/s', '${1}' . esc_attr( $args['selector'] ) . '-gallery-' . (int) $this->gallery_no . '$2', $link );
		} else
			$link = preg_replace( '/(<a.*?)>/s', '$1 data-rel="' . esc_attr( $args['selector'] ) . '-gallery-' . (int) $this->gallery_no . '">', $link );

		if ( ! ( isset( $args['link'] ) && $args['link'] !== 'file' ) ) {
			// gallery image size
			if ( ! empty( $args['image_id'] ) ) {
				if ( empty( $args['src'] ) )
					$args['src'] = wp_get_attachment_image_src( $args['image_id'], $args['settings']['plugin']['gallery_image_size'] );

				// valid source?
				if ( ! empty( $args['src'][0] ) ) {
					if ( preg_match( '/<a.*? href=("|\').*?("|\').*?>/is', $link ) === 1 )
						$link = preg_replace( '/(<a.*? href=(?:"|\')).*?((?:"|\').*?>)/', '$1' . $args['src'][0] . '$2', $link );
					else
						$link = preg_replace( '/(<a.*?)>/', '$1 href="' . $args['src'][0] . '">', $link );
				}
			}

			if ( $args['script'] === 'magnific' )
				$link = preg_replace( '/(<a.*?)>/is', '$1 data-magnific_type="gallery">', $link );
		}

		return apply_filters( 'rl_lightbox_gallery_link', $link, $args );
	}

	/**
	 * Add lightbox to content links.
	 *
	 * @param string $link Content link
	 * @param array $args Content arguments
	 * @return string
	 */
	public function lightbox_content_link( $link, $args ) {
		if ( in_array( $args['content'], $args['supports'], true ) ) {
			// link already contains data-rel attribute?
			if ( preg_match( '/<a.*?(?:data-rel)=(?:\'|")(.*?)(?:\'|").*?>/is', $link, $result ) === 1 )
				$link = preg_replace( '/data-rel=(\'|")(.*?)(\'|")/s', 'data-rel="' . esc_attr( $args['selector'] ) . '-content-' . esc_attr( base64_encode( $result[1] ) ) . '"', $link );
			else
				$link = preg_replace( '/(<a.*?)>/s', '$1 data-rel="' . esc_attr( $args['selector'] ) . '-content-' . (int) $args['link_number'] . '">', $link );

			switch ( $args['script'] ) {
				case 'nivo':
					$link = preg_replace( '/(<a.*?)>/s', '$1 data-lightbox-type="' . esc_attr( $args['content'] ) . '">', $link );
					break;

				case 'featherlight':
					$link = preg_replace( '/(<a.*?)>/s', '$1 data-featherlight="' . esc_attr( $args['content'] ) . '">', $link );
					break;

				case 'fancybox':
					if ( $args['content'] === 'iframe' )
						$link = preg_replace( '/(<a.*?href=(?:\'|"))(.*?)((?:\'|").*?>)/is', '$1' . add_query_arg( 'iframe', '', '$2' ) . '$3', $link );
					break;

				case 'prettyphoto':
					if ( $args['content'] === 'iframe' )
						$link = preg_replace( '/(<a.*?href=(?:\'|"))(.*?)((?:\'|").*?>)/is', '$1' . add_query_arg( [ 'iframe' => 'true', 'width' => (int) $args['settings']['width'], 'height' => (int) $args['settings']['height'] ], '$2' ) . '$3', $link );
			}
		}

		return apply_filters( 'rl_lightbox_content_link', $link, $args );
	}

	/**
	 * Get gallery fields.
	 *
	 * @param string $type Gallery type
	 * @return array
	 */
	public function get_gallery_fields( $type ) {
		// get main instance
		$rl = Responsive_Lightbox();

		// get gallery fields
		$gallery_fields = $rl->settings->settings[$type . '_gallery']['fields'];

		// assign settings and defaults
		$gallery_defaults = $rl->defaults[$type . '_gallery'];
		$gallery_values = $rl->options[$type . '_gallery'];

		// make a copy
		$fields_copy = $gallery_fields;

		foreach ( $fields_copy as $field_key => $field ) {
			if ( $field['type'] === 'multiple' ) {
				foreach ( $field['fields'] as $subfield_key => $subfield ) {
					$gallery_fields[$field_key]['fields'][$subfield_key]['default'] = $gallery_defaults[$subfield_key];
					$gallery_fields[$field_key]['fields'][$subfield_key]['value'] = array_key_exists( $subfield_key, $gallery_values ) ? $gallery_values[$subfield_key] : $gallery_defaults[$subfield_key];
				}
			} else {
				$gallery_fields[$field_key]['default'] = $gallery_defaults[$field_key];
				$gallery_fields[$field_key]['value'] = array_key_exists( $field_key, $gallery_values ) ? $gallery_values[$field_key] : $gallery_defaults[$field_key];
			}
		}

		// get shortcode gallery fields combined with defaults
		return apply_filters( 'rl_get_gallery_fields', $this->get_unique_fields( $this->get_default_gallery_fields(), $gallery_fields ) );
	}

	/**
	 * Get unique gallery fields.
	 *
	 * @param array $defaults Default gallery fields
	 * @param array $fields Custom gallery fields
	 * @return array
	 */
	public function get_unique_fields( $defaults, $fields ) {
		// check duplicated fields
		$duplicates = array_intersect_key( $defaults, $fields );

		// any duplicated fields?
		if ( ! empty( $duplicates ) ) {
			foreach ( $duplicates as $field_id => $field ) {
				unset( $defaults[$field_id] );
			}
		}

		// get default and custom fields all together
		return $defaults + $fields;
	}

	/**
	 * Get gallery fields combined with shortcode attributes.
	 *
	 * @param array $fields Gallery fields
	 * @param array $shortcode_atts Gallery shortcode attributes
	 * @param bool $gallery Whether is it rl_gallery shortcode
	 * @return array
	 */
	public function get_gallery_fields_atts( $fields, $shortcode_atts, $gallery = true ) {
		// prepare default values
		$field_atts = [];

		// get all default field values
		foreach ( $fields as $field_key => $field ) {
			if ( $field['type'] === 'multiple' ) {
				foreach ( $field['fields'] as $subfield_key => $subfield ) {
					$field_atts[$subfield_key] = array_key_exists( 'value', $subfield ) ? $subfield['value'] : $subfield['default'];
				}
			} else
				$field_atts[$field_key] = array_key_exists( 'value', $field ) ? $field['value'] : $field['default'];
		}

		// is it rl gallery?
		if ( $gallery ) {
			$tabs = Responsive_Lightbox()->galleries->tabs;

			if ( ! empty( $tabs ) ) {
				foreach ( $tabs as $key => $args ) {
					if ( in_array( $key, [ 'images', 'config' ] ) )
						continue;

					// get additional fields
					$data = get_post_meta( $shortcode_atts['rl_gallery_id'], '_rl_' . $key, true );

					// add those fields
					if ( ! empty( $data['menu_item'] ) && is_array( $data[$data['menu_item']] ) )
						$field_atts += $data[$data['menu_item']];
				}
			}

			if ( $field_atts['hover_effect'] !== '0' )
				$field_atts['gallery_custom_class'] .= ' rl-hover-effect-' . $field_atts['hover_effect'];

			if ( $field_atts['show_icon'] !== '0' )
				$field_atts['gallery_custom_class'] .= ' rl-hover-icon-' . $field_atts['show_icon'];
		}

		return apply_filters( 'rl_get_gallery_fields_atts', $field_atts );
	}

	/**
	 * Get default gallery fields.
	 *
	 * @return array
	 */
	public function get_default_gallery_fields() {
		$sizes = get_intermediate_image_sizes();
		$sizes['full'] = 'full';

		return [
			'size' => [
				'title'			=> __( 'Size', 'responsive-lightbox' ),
				'type'			=> 'select',
				'description'	=> __( 'Specify the image size to use for the thumbnail display.', 'responsive-lightbox' ),
				'default'		=> 'medium',
				'options'		=> array_combine( $sizes, $sizes )
			],
			'link' => [
				'title'			=> __( 'Link To', 'responsive-lightbox' ),
				'type'			=> 'select',
				'description'	=> __( 'Specify where you want the image to link.', 'responsive-lightbox' ),
				'default'		=> 'file',
				'options'		=> [
					'post'	=> __( 'Attachment Page', 'responsive-lightbox' ),
					'file'	=> __( 'Media File', 'responsive-lightbox' ),
					'none'	=> __( 'None', 'responsive-lightbox' )
				]
			],
			'orderby' => [
				'title'			=> __( 'Orderby', 'responsive-lightbox' ),
				'type'			=> 'select',
				'description'	=> __( 'Specify how to sort the display thumbnails.', 'responsive-lightbox' ),
				'default'		=> 'menu_order',
				'options'		=> [
					'id'			=> __( 'ID', 'responsive-lightbox' ),
					'title'			=> __( 'Title', 'responsive-lightbox' ),
					'post_date'		=> __( 'Date', 'responsive-lightbox' ),
					'menu_order'	=> __( 'Menu Order', 'responsive-lightbox' ),
					'rand'			=> __( 'Random', 'responsive-lightbox' )
				]
			],
			'order' => [
				'title'			=> __( 'Order', 'responsive-lightbox' ),
				'type'			=> 'radio',
				'description'	=> __( 'Specify the sort order.', 'responsive-lightbox' ),
				'default'		=> 'asc',
				'options'		=> [
					'asc'	=> __( 'Ascending', 'responsive-lightbox' ),
					'desc'	=> __( 'Descending', 'responsive-lightbox' )
				]
			],
			'columns' => [
				'title'			=> __( 'Columns', 'responsive-lightbox' ),
				'type'			=> 'number',
				'description'	=> __( 'Specify the number of columns.', 'responsive-lightbox' ),
				'default'		=> 3,
				'min'			=> 1,
				'max'			=> 12
			]
		];
	}

	/**
	 * Sanitize shortcode gallery arguments.
	 *
	 * @param array $atts Shortcode arguments
	 * @param array $fields Gallery fields
	 * @return array
	 */
	public function sanitize_shortcode_args( $atts, $fields ) {
		// get main instance
		$rl = Responsive_Lightbox();

		// validate gallery fields
		foreach ( $fields as $field_key => $field ) {
			// checkbox field?
			if ( $field['type'] === 'checkbox' ) {
				// valid argument?
				if ( array_key_exists( $field_key, $atts ) ) {
					if ( is_array( $atts[$field_key] ) )
						$array = $atts[$field_key];
					elseif ( is_string( $atts[$field_key] ) ) {
						if ( $atts[$field_key] === '' )
							$array = [];
						else
							$array = explode( ',', $atts[$field_key] );
					} else
						$array = [];

					$atts[$field_key] = $rl->galleries->sanitize_field( $field_key, array_flip( $array ), $field );
				}
			// boolean field?
			} elseif ( $field['type'] === 'boolean' ) {
				// multiple field?
				if ( $field['type'] === 'multiple' ) {
					foreach ( $field['fields'] as $subfield_key => $subfield ) {
						// valid argument?
						if ( array_key_exists( $subfield_key, $atts ) ) {
							// true value?
							if ( $atts[$subfield_key] === true || $atts[$subfield_key] === 'true' || $atts[$subfield_key] === '1' )
								$atts[$subfield_key] = 1;
							// false value?
							elseif ( $atts[$subfield_key] === false || $atts[$subfield_key] === 'false' || $atts[$subfield_key] === '0' || $atts[$subfield_key] === '' )
								$atts[$subfield_key] = 0;
							// default value
							else
								$atts[$subfield_key] = (int) $field['default'];
						}
					}
				} else {
					// valid argument?
					if ( array_key_exists( $field_key, $atts ) ) {
						// true value?
						if ( $atts[$field_key] === true || $atts[$field_key] === 'true' || $atts[$field_key] === '1' )
							$atts[$field_key] = 1;
						// false value?
						elseif ( $atts[$field_key] === false || $atts[$field_key] === 'false' || $atts[$field_key] === '0' || $atts[$field_key] === '' )
							$atts[$field_key] = 0;
						// default value
						else
							$atts[$field_key] = (int) $field['default'];
					}
				}
			// multiple field?
			} elseif ( $field['type'] === 'multiple' ) {
				foreach ( $field['fields'] as $subfield_key => $subfield ) {
					// valid argument?
					if ( array_key_exists( $subfield_key, $atts ) )
						$atts[$subfield_key] = $rl->galleries->sanitize_field( $subfield_key, $atts[$subfield_key], $subfield );
				}
			// other field?
			} else {
				// valid argument?
				if ( array_key_exists( $field_key, $atts ) )
					$atts[$field_key] = $rl->galleries->sanitize_field( $field_key, $atts[$field_key], $field );
			}
		}

		return apply_filters( 'rl_sanitize_shortcode_args', $atts );
	}

	/**
	 * Get gallery images.
	 *
	 * @param array $args Gallery arguments
	 * @return array
	 */
	public function get_gallery_shortcode_images( $shortcode_atts ) {
		// get main instance
		$rl = Responsive_Lightbox();

		if ( ! isset( $shortcode_atts['show_title'] ) || $shortcode_atts['show_title'] === 'global' )
			$shortcode_atts['show_title'] = $rl->options['settings']['gallery_image_title'];

		if ( ! isset( $shortcode_atts['show_caption'] ) || $shortcode_atts['show_caption'] === 'global' )
			$shortcode_atts['show_caption'] = $rl->options['settings']['gallery_image_caption'];

		$images = [];

		// get gallery id
		$gallery_id = ! empty( $shortcode_atts['rl_gallery_id'] ) ? absint( $shortcode_atts['rl_gallery_id'] ) : 0;

		// get images from gallery
		if ( $gallery_id ) {
			$images = $rl->galleries->get_gallery_images(
				$gallery_id,
				[
					'exclude'			=> true,
					'image_size'		=> $shortcode_atts['src_size'],
					'thumbnail_size'	=> $shortcode_atts['size'],
					'preview'			=> ( isset( $_GET['rl_gallery_revision_id'], $_GET['preview'] ) && $_GET['preview'] === 'true' ) || ( isset( $_POST['action'], $_POST['preview'] ) && $_POST['action'] === 'rl-get-gallery-page-content' && $_POST['preview'] === 'true' && wp_doing_ajax() )
				]
			);
		// get images from shortcode atts
		} else {
			$ids = [];

			if ( ! empty( $shortcode_atts['include'] ) ) {
				// filter attachment IDs
				$include = array_unique( array_filter( array_map( 'intval', explode( ',', $shortcode_atts['include'] ) ) ) );

				// any attachments?
				if ( ! empty( $include ) ) {
					// get attachments
					$ids = get_posts(
						[
							'include'			=> implode( ',', $include ),
							'post_status'		=> 'inherit',
							'post_type'			=> 'attachment',
							'post_mime_type'	=> 'image',
							'order'				=> $shortcode_atts['order'],
							'orderby'			=> ( $shortcode_atts['orderby'] === 'menu_order' || $shortcode_atts['orderby'] === '' ? 'post__in' : $shortcode_atts['orderby'] ),
							'fields'			=> 'ids'
						]
					);
				}
			} elseif ( ! empty( $exclude ) ) {
				// filter attachment IDs
				$exclude = array_unique( array_filter( array_map( 'intval', explode( ',', $shortcode_atts['exclude'] ) ) ) );

				// any attachments?
				if ( ! empty( $exclude ) ) {
					// get attachments
					$ids = get_children(
						[
							'post_parent'		=> $shortcode_atts['id'],
							'exclude'			=> $exclude,
							'post_status'		=> 'inherit',
							'post_type'			=> 'attachment',
							'post_mime_type'	=> 'image',
							'order'				=> $shortcode_atts['order'],
							'orderby'			=> $shortcode_atts['orderby'],
							'fields'			=> 'ids'
						]
					);
				}
			} else {
				// get attachments
				$ids = get_children(
					[
						'post_parent'		=> $shortcode_atts['id'],
						'post_status'		=> 'inherit',
						'post_type'			=> 'attachment',
						'post_mime_type'	=> 'image',
						'order'				=> $shortcode_atts['order'],
						'orderby'			=> $shortcode_atts['orderby'],
						'fields'			=> 'ids'
					]
				);
			}

			// any attachments?
			if ( ! empty( $ids ) ) {
				foreach ( $ids as $attachment_id ) {
					// get thumbnail image data
					$images[] = $rl->galleries->get_gallery_image_src( $attachment_id, $shortcode_atts['src_size'], $shortcode_atts['size'] );
				}
			}
		}

		// apply adjustments, as per settings
		if ( $images ) {
			// get current script
			$script = $rl->get_lightbox_script();

			// prepare arguments
			$args = [
				'selector'	=> $rl->options['settings']['selector'],
				'script'	=> $script,
				'settings'	=> [
					'script'	=> $rl->options['configuration'][$script],
					'plugin'	=> $rl->options['settings']
				],
				'supports'	=> $rl->settings->scripts[$script]['supports'],
				'image_id'	=> 0,
				'caption'	=> '',
				'title'		=> '',
				'src'		=> []
			];

			// lightbox image title
			$args['settings']['plugin']['gallery_image_title'] = ! empty( $shortcode_atts['lightbox_image_title'] ) ? ( $shortcode_atts['lightbox_image_title'] === 'global' ? $rl->options['settings']['gallery_image_title'] : $shortcode_atts['lightbox_image_title'] ) : $rl->options['settings']['gallery_image_title'];

			// lightbox image caption
			$args['settings']['plugin']['gallery_image_caption'] = ! empty( $shortcode_atts['lightbox_image_caption'] ) ? ( $shortcode_atts['lightbox_image_caption'] === 'global' ? $rl->options['settings']['gallery_image_caption'] : $shortcode_atts['lightbox_image_caption'] ) : $rl->options['settings']['gallery_image_caption'];

			// get gallery image link
			$args['link'] = isset( $shortcode_atts['link'] ) ? $shortcode_atts['link'] : '';

			// copy images
			$images_tmp = $images;

			// apply adjustments, according to gallery settings
			foreach ( $images_tmp as $index => $image ) {
				// assign image
				$new_image = $images[$index] = array_merge( $image, $rl->galleries->get_gallery_image_src( $image, $shortcode_atts['src_size'], $shortcode_atts['size'] ) );

				// create image source data
				$args['src'] = [ $new_image['url'], $new_image['width'], $new_image['height'], $new_image ];

				// update image id
				if ( ! empty( $new_image['id'] ) )
					$args['image_id'] = $new_image['id'];

				// set alt text
				$images[$index]['alt'] = $shortcode_atts['alt'] = ! empty( $new_image['alt'] ) ? $new_image['alt'] : ( ! empty( $new_image['id'] ) ? get_post_meta( $new_image['id'], '_wp_attachment_image_alt', true ) : '' );

				// set lightbox image title
				if ( $args['settings']['plugin']['gallery_image_title'] === 'default' )
					$images[$index]['title'] = $args['title'] = '';
				else {
					// embed element?
					if ( preg_match( '/^e\d+$/', $new_image['id'] ) === 1 )
						$shortcode_atts['title'] = $images[$index]['title'] = $args['title'] = $this->get_embed_title( $new_image['id'], apply_filters( 'rl_lightbox_embed_image_title_arg', $args['settings']['plugin']['gallery_image_title'], $images[$index]['link'] ), $new_image );
					else
						$images[$index]['title'] = $args['title'] = ! empty( $new_image['id'] ) ? $this->get_attachment_title( $new_image['id'], apply_filters( 'rl_lightbox_attachment_image_title_arg', $args['settings']['plugin']['gallery_image_title'], $new_image['id'], $images[$index]['link'] ) ) : $new_image['title'];
				}

				// set lightbox image caption
				if ( $args['settings']['plugin']['gallery_image_caption'] === 'default' )
					$images[$index]['caption'] = $args['caption'] = '';
				else {
					// embed element?
					if ( preg_match( '/^e\d+$/', $new_image['id'] ) === 1 )
						$shortcode_atts['caption'] = $images[$index]['caption'] = $args['caption'] = $this->get_embed_title( $new_image['id'], apply_filters( 'rl_lightbox_embed_image_title_arg', $args['settings']['plugin']['gallery_image_caption'], $images[$index]['link'] ), $new_image );
					else
						$images[$index]['caption'] = $args['caption'] = ! empty( $new_image['id'] ) ? $this->get_attachment_title( $new_image['id'], apply_filters( 'rl_lightbox_attachment_image_title_arg', $args['settings']['plugin']['gallery_image_caption'], $images[$index]['link'] ) ) : $new_image['caption'];
				}

				// set image gallery link
				$images[$index]['link'] = $this->lightbox_gallery_link( $this->get_gallery_image_link( $new_image['id'], $args['src'], [ $new_image['thumbnail_url'], $new_image['thumbnail_width'], $new_image['thumbnail_height'] ], $shortcode_atts ), $args );

				// is lightbox active?
				if ( isset( $shortcode_atts['lightbox_enable'] ) && $shortcode_atts['lightbox_enable'] === 0 )
					$images[$index]['link'] = preg_replace( '/data-rel=(\'|")(.*?)(\'|")/', 'data-rel="norl"', $images[$index]['link'] );
			}
		}

		return apply_filters( 'rl_get_gallery_shortcode_images', $images, $gallery_id, $shortcode_atts );
	}

	/**
	 * Get gallery image link.
	 *
	 * @param int $attachment_id Attachment ID
	 * @param array $image Source image data
	 * @param array $thumbnail Thumbnail image data
	 * @param array $args Arguments
	 * @return string
	 */
	function get_gallery_image_link( $attachment_id, $image, $thumbnail, $args ) {
		// link type
		switch ( $args['link'] ) {
			case 'post':
				// embed element?
				if ( preg_match( '/^e\d+$/', $attachment_id ) === 1 )
					$attr = [ 'href' => $image[0] ];
				else
					$attr = [ 'href' => get_permalink( $attachment_id ) ];
				break;

			case 'none':
				$attr = [ 'href' => 'javascript:void(0);', 'style' => 'cursor: default;' ];
				break;

			default:
			case 'file':
				$attr = [ 'href' => $image[0] ];
		}

		// filter attributes
		$attr = apply_filters( 'rl_gallery_image_link_attributes', $attr, $attachment_id, $image, $args );

		// start link
		$link = '<a';

		// escape attributes
		foreach ( $attr as $name => $value ) {
			$link .= ' ' . esc_attr( $name ) . '="' . ( $name === 'href' ? esc_url( $value ) : esc_attr( $value ) ) . '"';
		}

		$link .= '>';
		$link .= apply_filters( 'rl_gallery_image_link_before', '', $attachment_id, $args );
		$link .= '<img src="' . esc_url( $thumbnail[0] ) . '" width="' . (int) $thumbnail[1] . '" height="' . (int) $thumbnail[2] . '" alt="' . esc_attr( $args['alt'] ) . '" />';

		// embed element?
		if ( preg_match( '/^e\d+$/', $attachment_id ) === 1 ) {
			$title = ! empty( $args['show_title'] ) ? $this->get_embed_title( $attachment_id, $args['show_title'], $args ) : '';
			$caption = ! empty( $args['show_caption'] ) ? $this->get_embed_title( $attachment_id, $args['show_caption'], $args ) : '';
		} else {
			$title = ! empty( $args['show_title'] ) ? $this->get_attachment_title( $attachment_id, $args['show_title'] ) : '';
			$caption = ! empty( $args['show_caption'] ) ? $this->get_attachment_title( $attachment_id, $args['show_caption'] ) : '';
		}

		if ( $title || $caption ) {
			$link .= '<span class="rl-gallery-caption">';

			if ( $title )
				$link .= '<span class="rl-gallery-item-title">' . esc_html( $title ) . '</span>';

			if ( $caption )
				$link .= '<span class="rl-gallery-item-caption">' . esc_html( $caption ) . '</span>';

			$link .= '</span>';
		}

		$link .= apply_filters( 'rl_gallery_image_link_after', '', $attachment_id, $args );
		$link .= '</a>';

		return apply_filters( 'rl_gallery_image_link', $link, $attachment_id, $image, $thumbnail, $args );
	}

	/**
	 * Add lightbox to Jetpack tiled gallery.
	 *
	 * @param string $content
	 * @return string
	 */
	public function force_custom_gallery_lightbox( $content ) {
		// get main instance
		$rl = Responsive_Lightbox();

		if ( $rl->options['settings']['force_custom_gallery'] ) {
			// search for image links
			preg_match_all( '/<a(.*?)href=(?:\'|")([^<]*?)\.(bmp|gif|jpeg|jpg|png|webp)(?:\'|")(.*?)>/i', $content, $links );

			// found any links?
			if ( ! empty ( $links[0] ) ) {
				// get current script
				$script = $rl->get_lightbox_script();

				foreach ( $links[0] as $link_number => $link ) {
					// get attachment id
					$image_id = $this->get_attachment_id_by_url( $links[2][$link_number] . '.' . $links[3][$link_number] );

					// get title type
					$title_arg = $rl->options['settings']['gallery_image_title'];

					// update title if needed
					if ( $title_arg !== 'default' && $image_id )
						$title = wp_strip_all_tags( $this->get_attachment_title( $image_id, apply_filters( 'rl_lightbox_attachment_image_title_arg', $title_arg, $image_id, $links[2][$link_number] . '.' . $links[3][$link_number] ) ), true );
					else
						$title = '';

					// get caption type
					$caption_arg = $rl->options['settings']['gallery_image_caption'];

					// update caption if needed
					if ( $caption_arg !== 'default' )
						$caption = wp_strip_all_tags( $this->get_attachment_title( $image_id, apply_filters( 'rl_lightbox_attachment_image_title_arg', $caption_arg, $image_id, $links[2][$link_number] . '.' . $links[3][$link_number] ) ), true );
					else
						$caption = '';

					// link already contains data-rel attribute?
					if ( preg_match( '/<a.*?(?:data-rel)=(?:\'|")(.*?)(?:\'|").*?>/', $link, $result ) === 1 ) {
						// do not modify this link
						if ( $result[1] === 'norl' )
							continue;

						$content = str_replace( $link, preg_replace( '/data-rel=(?:\'|")(.*?)(?:\'|")/', 'data-rel="' . esc_attr( $rl->options['settings']['selector'] ) . '-gallery-' . esc_attr( base64_encode( $result[1] ) ) . '" data-rl_title="' . esc_attr( $title ) . '" data-rl_caption="' . esc_attr( $caption ) . '"' . ( $script === 'imagelightbox' ? ' data-imagelightbox="' . (int) $link_number . '"' : '' ), $link ), $content );
					} elseif ( preg_match( '/<a.*?(?:rel)=(?:\'|")(.*?)(?:\'|").*?>/', $link, $result ) === 1 ) {
						// do not modify this link
						if ( $result[1] === 'norl' )
							continue;

						$content = str_replace( $link, preg_replace( '/rel=(?:\'|")(.*?)(?:\'|")/', 'data-rel="' . esc_attr( $rl->options['settings']['selector'] ) . '-gallery-' . esc_attr( base64_encode( $result[1] ) ) . '" data-rl_title="' . esc_attr( $title ) . '" data-rl_caption="' . esc_attr( $caption ) . '"' . ( $script === 'imagelightbox' ? ' data-imagelightbox="' . (int) $link_number . '"' : '' ), $link ), $content );
					} else
						$content = str_replace( $link, '<a' . $links[1][$link_number] . ' href="' . $links[2][$link_number] . '.' . $links[3][$link_number] . '" data-rel="' . esc_attr( $rl->options['settings']['selector'] ) . '-gallery-' . esc_attr( base64_encode( $this->gallery_no ) ) . '" data-rl_title="' . esc_attr( $title ) . '" data-rl_caption="' . esc_attr( $caption ) . '"' . ( $script === 'imagelightbox' ? ' data-imagelightbox="' . (int) $link_number . '"' : '' ) . $links[4][$link_number] . '>', $content );
				}
			}
		}

		return $content;
	}

	/**
	 * Remove specific styles and scripts.
	 *
	 * @global object $woocommerce
	 *
	 * @return void
	 */
	public function wp_dequeue_scripts() {
		// woocommerce
		if ( class_exists( 'WooCommerce' ) ) {
			global $woocommerce;

			// get main instance
			$rl = Responsive_Lightbox();

			// specific woocommerce gallery?
			if ( ! empty( $rl->options['settings']['default_woocommerce_gallery'] ) && $rl->options['settings']['default_woocommerce_gallery'] !== 'default' ) {
				// replace default woocommerce lightbox?
				if ( $rl->options['settings']['woocommerce_gallery_lightbox'] === true ) {
					if ( version_compare( $woocommerce->version, '3.0', ">=" ) ) {
						// dequeue scripts
						wp_dequeue_script( 'flexslider' );
						wp_dequeue_script( 'photoswipe' );
						wp_dequeue_script( 'photoswipe-ui-default' );

						// dequeue styles
						wp_dequeue_style( 'photoswipe' );
						wp_dequeue_style( 'photoswipe-default-skin' );

						// remove theme supports
						remove_theme_support( 'wc-product-gallery-lightbox' );
						remove_theme_support( 'wc-product-gallery-slider' );
					} else {
						// remove styles
						wp_dequeue_style( 'woocommerce_prettyPhoto_css' );

						// remove scripts
						wp_dequeue_script( 'prettyPhoto' );
						wp_dequeue_script( 'prettyPhoto-init' );
						wp_dequeue_script( 'fancybox' );
						wp_dequeue_script( 'enable-lightbox' );
					}
				} else {
					if ( version_compare( $woocommerce->version, '3.0', ">=" ) ) {
						// dequeue scripts
						wp_dequeue_script( 'flexslider' );
					}
				}
			// default gallery?
			} else {
				// replace default woocommerce lightbox?
				if ( $rl->options['settings']['woocommerce_gallery_lightbox'] === true ) {
					if ( version_compare( $woocommerce->version, '3.0', ">=" ) ) {
						// dequeue scripts
						wp_dequeue_script( 'photoswipe' );
						wp_dequeue_script( 'photoswipe-ui-default' );

						// dequeue styles
						wp_dequeue_style( 'photoswipe' );
						wp_dequeue_style( 'photoswipe-default-skin' );

						// remove theme supports
						remove_theme_support( 'wc-product-gallery-lightbox' );
					} else {
						// remove styles
						wp_dequeue_style( 'woocommerce_prettyPhoto_css' );

						// remove scripts
						wp_dequeue_script( 'prettyPhoto' );
						wp_dequeue_script( 'prettyPhoto-init' );
						wp_dequeue_script( 'fancybox' );
						wp_dequeue_script( 'enable-lightbox' );
					}
				}
			}
		}

		// visual composer
		if ( class_exists( 'Vc_Manager' ) ) {
			wp_dequeue_script( 'prettyphoto' );
			wp_deregister_script( 'prettyphoto' );
			wp_dequeue_style( 'prettyphoto' );
			wp_deregister_style( 'prettyphoto' );
		}
	}

	/**
	 * Apply lightbox to WooCommerce product image.
	 *
	 * @param string $html
	 * @return string
	 */
	public function woocommerce_single_product_image_html( $html ) {
		if ( Responsive_Lightbox()->options['settings']['woocommerce_gallery_lightbox'] )
			$html = preg_replace( '/data-rel=\"(.*?)\"/', 'data-rel="' . esc_attr( Responsive_Lightbox()->options['settings']['selector'] ) . '-gallery-' . (int) $this->gallery_no . '"', $html );

		return $html;
	}

	/**
	 * Apply lightbox to WooCommerce product gallery.
	 *
	 * @param string $html
	 * @param int $attachment_id
	 * @return string
	 */
	public function woocommerce_single_product_image_thumbnail_html( $html, $attachment_id ) {
		if ( Responsive_Lightbox()->options['settings']['woocommerce_gallery_lightbox'] ) {
			// make sure main product image has same gallery number
			$gallery_no = $this->gallery_no + 1;

			$html = preg_replace( '/data-rel=\"(.*?)\"/', 'data-rel="' . esc_attr( Responsive_Lightbox()->options['settings']['selector'] ) . '-gallery-' . (int) $gallery_no . '"', $html );

			preg_match( '/<a(.*?)((?:data-rel)=(?:\'|").*?(?:\'|"))(.*?)>/i', $html, $result );

			// no data-rel?
			if ( empty( $result ) ) {
				preg_match( '/^(.*?)<a(.*?)((?:href)=(?:\'|").*?(?:\'|"))(.*?)>(.*?)$/i', $html, $result );

				// found valid link?
				if ( ! empty( $result ) )
					$html = $result[1] . '<a' . $result[2] . 'data-rel="' . esc_attr( Responsive_Lightbox()->options['settings']['selector'] ) . '-gallery-' . (int) $gallery_no . '" ' . $result[3] . $result[4] . '>' . $result[5];
			}

			$html = $this->woocommerce_gallery_link( $html, $attachment_id );
		}

		return $html;
	}

	/**
	 * Add title and caption to WooCommerce gallery image links.
	 *
	 * @param string $link
	 * @param int $attachment_id
	 * @return string
	 */
	public function woocommerce_gallery_link( $link, $attachment_id ) {
		// get main instance
		$rl = Responsive_Lightbox();

		// gallery image title
		$title = '';

		// get title type
		$title_arg = $rl->options['settings']['gallery_image_title'];

		// update title if needed
		if ( $title_arg !== 'default' ) {
			// original title
			$title = $this->get_attachment_title( $attachment_id, apply_filters( 'rl_lightbox_attachment_image_title_arg', $title_arg, $attachment_id, $link ) );
		}

		if ( $title !== '' ) {
			// title
			$title = trim( nl2br( $title ) );

			if ( ! rl_current_lightbox_supports( 'html_caption' ) )
				$title = wp_strip_all_tags( $title, true );

			// add title and rl_title if needed
			if ( preg_match( '/<a[^>]*?title=(?:\'|")[^>]*?(?:\'|").*?>/is', $link ) === 1 )
				$link = str_replace( '__RL_IMAGE_TITLE__', esc_attr( $title ), preg_replace( '/(<a[^>]*?title=(?:\'|"))[^>]*?((?:\'|").*?>)/is', '$1__RL_IMAGE_TITLE__" data-rl_title="__RL_IMAGE_TITLE__$2', $link ) );
			else
				$link = str_replace( '__RL_IMAGE_TITLE__', esc_attr( $title ), preg_replace( '/(<a[^>]*?)>/is', '$1 title="__RL_IMAGE_TITLE__" data-rl_title="__RL_IMAGE_TITLE__">', $link ) );
		}

		// gallery image caption
		$caption = '';

		// get caption type
		$caption_arg = $rl->options['settings']['gallery_image_caption'];

		// update caption if needed
		if ( $caption_arg !== 'default' ) {
			// original caption
			$caption = $this->get_attachment_title( $attachment_id, apply_filters( 'rl_lightbox_attachment_image_title_arg', $caption_arg, $attachment_id, $link ) );
		}

		if ( $caption !== '' ) {
			// caption
			$caption = trim( nl2br( $caption ) );

			if ( ! rl_current_lightbox_supports( 'html_caption' ) )
				$caption = wp_strip_all_tags( $caption, true );

			// add rl_caption
			$link = str_replace( '__RL_IMAGE_CAPTION__', esc_attr( $caption ), preg_replace( '/(<a[^>]*?)>/is', '$1 data-rl_caption="__RL_IMAGE_CAPTION__">', $link ) );
		}

		if ( $rl->get_lightbox_script() === 'magnific' )
			$link = preg_replace( '/(<a[^>]*?)>/is', '$1 data-magnific_type="gallery">', $link );

		return $link;
	}

	/**
	 * WooCommerce gallery init.
	 *
	 * @return void
	 */
	public function woocommerce_gallery_init() {
		if ( ( $priority = has_action( 'woocommerce_product_thumbnails', 'woocommerce_show_product_thumbnails' ) ) !== false && ! empty( Responsive_Lightbox()->options['settings']['default_woocommerce_gallery'] ) && Responsive_Lightbox()->options['settings']['default_woocommerce_gallery'] !== 'default' ) {
			// remove default gallery
			remove_action( 'woocommerce_product_thumbnails', 'woocommerce_show_product_thumbnails', $priority );

			// handle product gallery
			add_action( 'woocommerce_product_thumbnails', [ $this, 'woocommerce_gallery' ], $priority );
		}
	}

	/**
	 * WooCommerce gallery support.
	 *
	 * @global object $product
	 *
	 * @return void
	 */
	public function woocommerce_gallery() {
		global $product;

		$attachment_ids = [];

		// woocommerce 3.x
		if ( method_exists( $product, 'get_gallery_image_ids' ) )
			$attachment_ids = $product->get_gallery_image_ids();
		// woocommerce 2.x
		elseif ( method_exists( $product, 'get_gallery_attachment_ids' ) )
			$attachment_ids = $product->get_gallery_attachment_ids();

		if ( ! empty( $attachment_ids ) && is_array( $attachment_ids ) )
			echo do_shortcode( '[gallery type="' . esc_attr( Responsive_Lightbox()->options['settings']['default_woocommerce_gallery'] ) . '" size="medium" ids="' . esc_attr( implode( ',', $attachment_ids ) ) . '"]' );
	}

	/**
	 * Get embed text.
	 *
	 * @param string $id
	 * @param string $title_arg
	 * @param array $embed
	 * @return false|string
	 */
	public function get_embed_title( $id, $title_arg, $embed ) {
		if ( empty( $title_arg ) || empty( $id ) )
			return false;

		switch( $title_arg ) {
			case 'title':
				$text = $embed['title'];
				break;

			// caption is always the same for these options
			case 'caption':
			case 'alt':
			case 'description':
				$text = $embed['caption'];
				break;

			default:
				$text = '';
		}

		return trim( apply_filters( 'rl_get_embed_title', $text, $id, $title_arg, $embed ) );
	}

	/**
	 * Get attachment text.
	 *
	 * @param int $id
	 * @param string $title_arg
	 * @return false|string
	 */
	public function get_attachment_title( $id, $title_arg ) {
		if ( empty( $title_arg ) || empty( $id ) )
			return false;

		switch( $title_arg ) {
			case 'title':
				$text = get_the_title( $id );
				break;

			case 'caption':
				$text = get_post_field( 'post_excerpt', $id ) ;
				break;

			case 'alt':
				$text = get_post_meta( $id, '_wp_attachment_image_alt', true );
				break;

			case 'description':
				$text = get_post_field( 'post_content', $id ) ;
				break;

			default:
				$text = '';
		}

		return trim( apply_filters( 'rl_get_attachment_title', $text, $id, $title_arg ) );
	}

	/**
	 * Get attachment id by url function, adjusted to work for cropped and scaled images.
	 *
	 * @param string $url
	 * @return int
	 */
	public function get_attachment_id_by_url( $url ) {
		// parse url
		$url = ! empty( $url ) ? esc_url_raw( $url ) : '';

		// set post id
		$post_id = 0;

		// get cached data
		$post_ids = get_transient( 'rl-attachment_ids_by_url' );

		// cached url not found?
		if ( $post_ids === false || ! in_array( $url, array_keys( $post_ids ) ) ) {
			// try to get post id
			$post_id = (int) attachment_url_to_postid( $url );

			// no post id?
			if ( ! $post_id ) {
				$dir = wp_upload_dir();
				$path = $url;

				if ( strpos( $path, $dir['baseurl'] . '/' ) === 0 )
					$path = substr( $path, strlen( $dir['baseurl'] . '/' ) );

				// try to check full size image
				if ( preg_match( '/^(.*)(\-\d*x\d*)(\.\w{1,})/i', $path, $matches ) )
					$post_id = (int) attachment_url_to_postid( $dir['baseurl'] . '/' . $matches[1] . $matches[3] );

				// try to check scaled size image
				if ( ! $post_id && ! empty( $matches[1] ) && ! empty( $matches[3] ) )
					$post_id = (int) attachment_url_to_postid( $dir['baseurl'] . '/' . $matches[1] . '-scaled' . $matches[3] );
			}

			// set the cache expiration, 24 hours by default
			$expire = (int) apply_filters( 'rl_object_cache_expire', DAY_IN_SECONDS );

			// update post ids
			$post_ids[$url] = $post_id;

			// set transient
			set_transient( 'rl-attachment_ids_by_url', $post_ids, $expire );
		// cached url found
		} elseif ( ! empty( $post_ids[$url] ) )
			$post_id = (int) $post_ids[$url];

		return (int) apply_filters( 'rl_get_attachment_id_by_url', $post_id, $url );
	}

	/**
	 * Get image size by URL.
	 *
	 * @param string $url Image URL
	 * @return array
	 */
	public function get_image_size_by_url( $url ) {
		// parse url
		$url = ! empty( $url ) ? esc_url_raw( $url ) : '';
		$size = [ 0, 0 ];

		if ( ! empty( $url ) ) {
			// get cached data
			$image_sizes = get_transient( 'rl-image_sizes_by_url' );

			// cached url not found?
			if ( $image_sizes === false || ! in_array( $url, array_keys( $image_sizes ) ) || empty( $image_sizes[$url] ) ) {
				if ( class_exists( 'Responsive_Lightbox_Fast_Image' ) ) {
					// loading image
					$image = new Responsive_Lightbox_Fast_Image( $url );

					// get size
					$size = $image->get_size();
				} else {
					// get size using php
					$size = getimagesize( $url );
				}

				// set the cache expiration, 24 hours by default
				$expire = absint( apply_filters( 'rl_object_cache_expire', DAY_IN_SECONDS ) );

				$image_sizes[$url] = $size;

				set_transient( 'rl-image_sizes_by_url', $image_sizes, $expire );
			// cached url found
			} elseif ( ! empty( $image_sizes[$url] ) )
				$size = array_map( 'absint', $image_sizes[$url] );
		}

		return (array) apply_filters( 'rl_get_image_size_by_url', $size, $url );
	}

	/**
	 * Add gallery shortcode to gallery post content.
	 *
	 * @param string $content
	 * @return string
	 */
	public function gallery_preview( $content ) {
		if ( get_post_type() === 'rl_gallery' && ! ( is_archive() && is_main_query() ) )
			$content .= do_shortcode( '[rl_gallery id="' . (int) get_the_ID() . '"]' );

		return $content;
	}

	/**
	 * Helper: gallery number function.
	 *
	 * @param string $content
	 * @return string
	 */
	public function gallery_attributes( $content, $shortcode_atts ) {
		// check forced gallery number
		if ( isset( $shortcode_atts['rl_gallery_no'] ) ) {
			$shortcode_atts['rl_gallery_no'] = (int) $shortcode_atts['rl_gallery_no'];

			if ( $shortcode_atts['rl_gallery_no'] > 0 )
				$this->gallery_no = $shortcode_atts['rl_gallery_no'];
		} else
			++$this->gallery_no;

		// add inline style, to our galleries only
		if ( isset( $shortcode_atts['type'] ) ) {
			// gallery style
			wp_enqueue_style( 'responsive-lightbox-gallery' );

			// is there rl_gallery ID?
			$rl_gallery_id = ! empty( $shortcode_atts['rl_gallery_id'] ) ? (int) $shortcode_atts['rl_gallery_id'] : 0;

			// is it rl gallery?
			$rl_gallery = Responsive_Lightbox()->options['builder']['gallery_builder'] && $rl_gallery_id && get_post_type( $rl_gallery_id ) === 'rl_gallery';

			// is it rl gallery? add design options
			if ( $rl_gallery ) {
				$fields = Responsive_Lightbox()->galleries->fields['design']['options'];

				// get gallery fields attributes
				$field_atts = rl_get_gallery_fields_atts( $fields, $shortcode_atts, $rl_gallery );

				// get only valid arguments
				$atts = shortcode_atts( $field_atts, array_merge( $field_atts, $shortcode_atts ), 'gallery' );

				// sanitize gallery fields
				$atts = $this->sanitize_shortcode_args( $atts, $fields );

				// convert color
				$background_color = Responsive_Lightbox()->hex2rgb( $atts['background_color'] );

				// invalid color?
				if ( ! $background_color )
					$background_color = '0,0,0';
				else
					$background_color = implode( ',', $background_color );

				// get opacity
				$opacity = (string) round( $atts['background_opacity'] / 100, 2 );

				// add inline style
				wp_add_inline_style(
					'responsive-lightbox-gallery',
					'.rl-gallery .rl-gallery-link {
						border: ' . (int) $atts['border_width'] . 'px solid ' . esc_attr( $atts['border_color'] ) . ';
					}
					.rl-gallery .rl-gallery-link .rl-gallery-item-title {
						color: ' . esc_attr( $atts['title_color'] ) . ';
					}
					.rl-gallery .rl-gallery-link .rl-gallery-item-caption {
						color: ' . esc_attr( $atts['caption_color'] ) . ';
					}
					.rl-gallery .rl-gallery-link .rl-gallery-caption,
					.rl-gallery .rl-gallery-link:after {
						background-color: rgba( ' . esc_attr( $background_color ) . ', ' . esc_attr( $opacity ) . ' );
					}
					[class^="rl-hover-icon-"] .rl-gallery-link:before,
					[class*=" rl-hover-icon-"] .rl-gallery-link:before {
						color: ' . esc_attr( $atts['title_color'] ) . ';
						background-color: rgba( ' . esc_attr( $background_color ) . ', ' . esc_attr( $opacity ) . ' );
					}'
				);
			}
		}

		return $content;
	}

	/**
	 * Generate unique hash.
	 *
	 * @param int $length
	 * @return string
	*/
	private function generate_hash( $length = 8 ) {
		$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		$hash = '';

		for( $i = 0; $i < $length; $i++ ) {
			$hash .= substr( $chars, mt_rand( 0, strlen( $chars ) - 1 ), 1 );
		}

		return $hash;
	}

	/**
	 * Replace widget callback function.
	 *
	 * @global array $wp_registered_widgets
	 *
	 * @param array $sidebar_params
	 * @return array
	 */
	public function dynamic_sidebar_params( $sidebar_params ) {
		if ( ( is_admin() && ! wp_doing_ajax() ) || Responsive_Lightbox()->options['settings']['widgets'] !== true )
			return $sidebar_params;

		global $wp_registered_widgets;

		$widget_id = $sidebar_params[0]['widget_id'];
		$wp_registered_widgets[ $widget_id ]['original_callback'] = $wp_registered_widgets[ $widget_id ]['callback'];
		$wp_registered_widgets[ $widget_id ]['callback'] = [ $this, 'widget_callback_function' ];

		return $sidebar_params;
	}

	/**
	 * Widget callback function.
	 *
	 * @global array $wp_registered_widgets
	 *
	 * @return void
	 */
	public function widget_callback_function() {
		global $wp_registered_widgets;

		$original_callback_params = func_get_args();
		$widget_id = $original_callback_params[0]['widget_id'];
		$original_callback = $wp_registered_widgets[ $widget_id ]['original_callback'];
		$wp_registered_widgets[ $widget_id ]['callback'] = $original_callback;
		$widget_id_base = $wp_registered_widgets[ $widget_id ]['callback'][0]->id_base;

		if ( is_callable( $original_callback ) ) {
			ob_start();

			call_user_func_array( $original_callback, $original_callback_params );

			$widget_output = ob_get_clean();

			echo apply_filters( 'rl_widget_output', $widget_output, $widget_id_base, $widget_id );
		}
	}

	/**
	 * Filter widget output.
	 *
	 * @param string $content
	 * @param string $widget_id_base
	 * @param id $widget_id
	 * @return string
	 */
	public function widget_output( $content, $widget_id_base, $widget_id ) {
		return $this->add_lightbox( $content );
	}

	/**
	 * Filter comment content.
	 *
	 * @param string $content
	 * @return string
	 */
	public function get_comment_text( $content ) {
		if ( ( is_admin() && ! wp_doing_ajax() ) || Responsive_Lightbox()->options['settings']['comments'] !== true )
			return $content;

		return $this->add_lightbox( $content );
	}

	/**
	 * Modify gallery container class.
	 *
	 * @param string $class
	 * @param array $args
	 * @param int $gallery_id
	 * @return string
	 */
	public function gallery_container_class( $class, $args, $gallery_id ) {
		if ( $gallery_id ) {
			$class .= ' rl-loading';

			if ( $args['pagination'] )
				$class .= ' rl-pagination-' . $args['pagination_type'];
		}

		return $class;
	}

	/**
	 * Display content before the gallery.
	 *
	 * @param array $args
	 * @param int $gallery_id
	 * @return void
	 */
	public function before_gallery( $args, $gallery_id ) {
		if ( $gallery_id ) {
			// get current post id
			if ( isset( $_POST['action'], $_POST['post_id'] ) && $_POST['action'] === 'rl-get-gallery-page-content' && wp_doing_ajax() )
				$current_id = (int) $_POST['post_id'];
			else
				$current_id = (int) get_the_ID();

			if ( isset( $args['gallery_title_position'] ) && $args['gallery_title_position'] === 'top' && get_post_type( $current_id ) )
				echo '<h4 class="rl-gallery-title">' . esc_html( get_the_title( $gallery_id ) ) . '</h4>';

			if ( isset( $args['gallery_description_position'] ) && $args['gallery_description_position'] === 'top' )
				echo '<div class="rl-gallery-description">' . nl2br( esc_html( $args['gallery_description'] ) ) . '</div>';
		}
	}

	/**
	 * Display content after the gallery.
	 *
	 * @param array $args
	 * @param int $gallery_id
	 * @return void
	 */
	public function after_gallery( $args, $gallery_id ) {
		if ( $gallery_id ) {
			// get current post id
			if ( isset( $_POST['action'], $_POST['post_id'] ) && $_POST['action'] === 'rl-get-gallery-page-content' && wp_doing_ajax() )
				$current_id = (int) $_POST['post_id'];
			else
				$current_id = (int) get_the_ID();

			if ( isset( $args['gallery_title_position'] ) && $args['gallery_title_position'] === 'bottom' )
				echo '<h4 class="rl-gallery-title">' . esc_html( get_the_title( $gallery_id ) ) . '</h4>';

			if ( isset( $args['gallery_description_position'] ) && $args['gallery_description_position'] === 'bottom' )
				echo '<div class="rl-gallery-description">' . nl2br( esc_html( $args['gallery_description'] ) ) . '</div>';
		}
	}

	/**
	 * Add lightbox to Visual Composer shortcodes.
	 *
	 * @param string $content HTML content
	 * @param string $shortcode Shortcode type
	 * @return string
	 */
	public function vc_shortcode_content_filter_after( $content, $shortcode ) {
		if ( in_array( $shortcode, apply_filters( 'rl_lightbox_vc_allowed_shortcode', [ 'vc_gallery', 'vc_single_image', 'vc_images_carousel' ] ), true ) )
			$content = $this->add_lightbox( $content );

		return $content;
	}

	/**
	 * Render Basic Grid gallery shortcode.
	 *
	 * @global object $post
	 *
	 * @param string $output HTML output
	 * @param array $shortcode_atts Shortcode attributes
	 * @return string
	 */
	public function basic_grid_gallery_shortcode( $output, $shortcode_atts ) {
		if ( ! empty( $output ) )
			return $output;

		global $post;

		$defaults = [
			'rl_gallery_id'	=> 0,
			'id'			=> isset( $post->ID ) ? (int) $post->ID : 0,
			'class'			=> '',
			'include'		=> '',
			'exclude'		=> '',
			'urls'			=> '',
			'type'			=> '',
			'order'			=> 'asc',
			'orderby'		=> 'menu_order',
			'size'			=> 'medium',
			'link'			=> 'file',
			'columns'		=> 3
		];

		// get main instance
		$rl = Responsive_Lightbox();

		if ( ! is_array( $shortcode_atts ) )
			$shortcode_atts = wp_parse_args( $shortcode_atts, $defaults );

		// is there rl_gallery ID?
		$rl_gallery_id = $defaults['rl_gallery_id'] = ! empty( $shortcode_atts['rl_gallery_id'] ) ? (int) $shortcode_atts['rl_gallery_id'] : 0;

		// is it rl gallery?
		$rl_gallery = $rl->options['builder']['gallery_builder'] && $rl_gallery_id && get_post_type( $rl_gallery_id ) === 'rl_gallery';

		if ( ! array_key_exists( 'type', $shortcode_atts ) )
			$shortcode_atts['type'] = '';

		// break if it is not basic grid gallery - first check
		if ( ! ( $shortcode_atts['type'] === 'basicgrid' || ( $shortcode_atts['type'] === '' && ( ( $rl_gallery && $rl->options['settings']['builder_gallery'] === 'basicgrid' ) || ( ! $rl_gallery && $rl->options['settings']['default_gallery'] === 'basicgrid' ) ) ) ) )
			return $output;

		// get shortcode gallery fields combined with defaults
		$fields = rl_get_gallery_fields( 'basicgrid' );

		// get gallery fields attributes
		$field_atts = rl_get_gallery_fields_atts( $fields, $shortcode_atts, $rl_gallery );

		// is it rl gallery? add misc and lightbox fields
		if ( $rl_gallery )
			$fields += $rl->galleries->fields['lightbox']['options'] + $rl->galleries->fields['misc']['options'];

		// get only valid arguments
		$atts = shortcode_atts( array_merge( $defaults, $field_atts ), $shortcode_atts, 'gallery' );

		// sanitize gallery fields
		$atts = $this->sanitize_shortcode_args( $atts, $fields );

		// break if it is not basic grid gallery
		if ( ! ( $atts['type'] === 'basicgrid' || ( $atts['type'] === '' && ( ( $rl_gallery && $rl->options['settings']['builder_gallery'] === 'basicgrid' ) || ( ! $rl_gallery && $rl->options['settings']['default_gallery'] === 'basicgrid' ) ) ) ) )
			return $output;

		// ID
		$atts['id'] = (int) $atts['id'];

		// add custom classes if needed
		if ( $rl_gallery )
			$atts['class'] .= ' ' . $atts['gallery_custom_class'];

		// any classes?
		if ( $atts['class'] !== '' ) {
			$atts['class'] = trim( $atts['class'] );

			// more than 1 class?
			if ( strpos( $atts['class'], ' ' ) !== false ) {
				// get unique valid HTML classes
				$atts['class'] = array_unique( array_filter( array_map( 'sanitize_html_class', explode( ' ', $atts['class'] ) ) ) );

				if ( ! empty( $atts['class'] ) )
					$atts['class'] = implode( ' ', $atts['class'] );
				else
					$atts['class'] = '';
			// single class
			} else
				$atts['class'] = sanitize_html_class( $atts['class'] );
		}

		// orderby
		if ( empty( $atts['orderby'] ) ) {
			$atts['orderby'] = sanitize_sql_orderby( $atts['orderby'] );

			if ( empty( $atts['orderby'] ) )
				$atts['orderby'] = $defaults['orderby'];
		}

		// order
		if ( strtolower( $atts['order'] ) === 'rand' )
			$atts['orderby'] = 'rand';

		// check columns
		if ( $atts['columns_lg'] === 0 )
			$atts['columns_lg'] = $atts['columns'];

		if ( $atts['columns_md'] === 0 )
			$atts['columns_md'] = $atts['columns'];

		if ( $atts['columns_sm'] === 0 )
			$atts['columns_sm'] = $atts['columns'];

		if ( $atts['columns_xs'] === 0 )
			$atts['columns_xs'] = $atts['columns'];

		// gallery lightbox source size
		if ( ! empty( $atts['lightbox_image_size'] ) ) {
			if ( $atts['lightbox_image_size'] === 'global' )
				$atts['src_size'] = $rl->options['settings']['gallery_image_size'];
			elseif ( $atts['lightbox_image_size'] === 'lightbox_custom_size' && isset( $atts['lightbox_custom_size_width'], $atts['lightbox_custom_size_height'] ) )
				$atts['src_size'] = [ $atts['lightbox_custom_size_width'], $atts['lightbox_custom_size_height'] ];
			else
				$atts['src_size'] = $atts['lightbox_image_size'];
		} else
			$atts['src_size'] = $rl->options['settings']['gallery_image_size'];

		// filter all shortcode arguments
		$atts = apply_filters( 'rl_gallery_shortcode_atts', $atts, $rl_gallery_id );

		// get gallery images
		$images = rl_get_gallery_shortcode_images( $atts );

		if ( empty( $images ) || is_feed() || defined( 'IS_HTML_EMAIL' ) )
			return $output;

		// make sure it is integer
		$gallery_no = (int) $this->gallery_no;

		ob_start();

		// $gallery_no and $rl_gallery_id are both integers ?>
		<div class="rl-gallery-container<?php echo esc_attr( apply_filters( 'rl_gallery_container_class', '', $atts, $rl_gallery_id ) ); ?>" id="rl-gallery-container-<?php echo $gallery_no; ?>" data-gallery_id="<?php echo $rl_gallery_id; ?>">

			<?php do_action( 'rl_before_gallery', $atts, $rl_gallery_id ); ?>

			<div class="rl-gallery rl-basicgrid-gallery <?php echo esc_attr( $atts['class'] ); ?>" id="rl-gallery-<?php echo $gallery_no; ?>" data-gallery_no="<?php echo $gallery_no; ?>">

			<?php foreach ( $images as $image ) {
				// $image['link'] is already escaped
				echo '<div class="rl-gallery-item">' . $image['link'] . '</div>';
			} ?>

			</div>

			<?php do_action( 'rl_after_gallery', $atts, $rl_gallery_id ); ?>

		</div>

		<?php $gallery_html = ob_get_contents();

		ob_end_clean();

		// styles
		wp_enqueue_style( 'responsive-lightbox-basicgrid-gallery', plugins_url( 'css/gallery-basicgrid.css', dirname( __FILE__ ) ), [], $rl->defaults['version'] );

		// add inline style
		$inline_css = '
			#rl-gallery-container-' . $gallery_no . ' .rl-basicgrid-gallery .rl-gallery-item {
				width: calc(' . (string) round( 100 / (int) $atts['columns'], 2 ) . '% - ' . (int) $atts['gutter'] . 'px);
				margin: ' . (string) round( (int) $atts['gutter'] / 2, 2 ) . 'px;
			}
			@media all and (min-width: 1200px) {
				#rl-gallery-container-' . $gallery_no . ' .rl-basicgrid-gallery .rl-gallery-item {
					width: calc(' . (string) round( 100 / (int) $atts['columns_lg'], 2 ) . '% - ' . (int) $atts['gutter'] . 'px);
				}
			}
			@media all and (min-width: 992px) and (max-width: 1200px) {
				#rl-gallery-container-' . $gallery_no . ' .rl-basicgrid-gallery .rl-gallery-item {
					width: calc(' . (string) round( 100 / (int) $atts['columns_md'], 2 ) . '% - ' . (int) $atts['gutter'] . 'px);
				}
			}
			@media all and (min-width: 768px) and (max-width: 992px) {
				#rl-gallery-container-' . $gallery_no . ' .rl-basicgrid-gallery .rl-gallery-item {
					width: calc(' . (string) round( 100 / (int) $atts['columns_sm'], 2 ) . '% - ' . (int) $atts['gutter'] . 'px);
				}
			}
			@media all and (max-width: 768px) {
				#rl-gallery-container-' . $gallery_no . ' .rl-basicgrid-gallery .rl-gallery-item {
					width: calc(' . (string) round( 100 / (int) $atts['columns_xs'], 2 ) . '% - ' . (int) $atts['gutter'] . 'px);
				}
			}
		';

		if ( $atts['force_height'] ) {
			$inline_css .= '
			#rl-gallery-container-' . $gallery_no . ' .rl-basicgrid-gallery .rl-gallery-item {
				height: ' . (int) $atts['row_height'] . 'px;
			}
			#rl-gallery-container-' . $gallery_no . ' .rl-basicgrid-gallery .rl-gallery-item img {
				height: ' . (int) $atts['row_height'] . 'px;
				object-fit: cover;
				max-width: 100%;
				min-width: 100%;
			}';
		}

		wp_add_inline_style( 'responsive-lightbox-basicgrid-gallery', $inline_css );

		// remove any new lines from the output so that the reader parses it better
		return apply_filters( 'rl_gallery_shortcode_html', trim( preg_replace( '/\s+/', ' ', $gallery_html ) ), $atts, $rl_gallery_id );
	}

	/**
	 * Render Basic Slider gallery shortcode.
	 *
	 * @global object $post
	 *
	 * @param string $output HTML output
	 * @param array $shortcode_atts Shortcode attributes
	 * @return string
	 */
	public function basic_slider_gallery_shortcode( $output, $shortcode_atts ) {
		if ( ! empty( $output ) )
			return $output;

		global $post;

		$defaults = [
			'rl_gallery_id'	=> 0,
			'id'			=> isset( $post->ID ) ? (int) $post->ID : 0,
			'class'			=> '',
			'include'		=> '',
			'exclude'		=> '',
			'urls'			=> '',
			'type'			=> '',
			'order'			=> 'asc',
			'orderby'		=> 'menu_order',
			'size'			=> 'medium',
			'link'			=> 'file',
			'columns'		=> 3
		];

		// get main instance
		$rl = Responsive_Lightbox();

		if ( ! is_array( $shortcode_atts ) )
			$shortcode_atts = wp_parse_args( $shortcode_atts, $defaults );

		// is there rl_gallery ID?
		$rl_gallery_id = $defaults['rl_gallery_id'] = ! empty( $shortcode_atts['rl_gallery_id'] ) ? (int) $shortcode_atts['rl_gallery_id'] : 0;

		// is it rl gallery?
		$rl_gallery = $rl->options['builder']['gallery_builder'] && $rl_gallery_id && get_post_type( $rl_gallery_id ) === 'rl_gallery';

		if ( ! array_key_exists( 'type', $shortcode_atts ) )
			$shortcode_atts['type'] = '';

		// break if it is not basic slider gallery - first check
		if ( ! ( $shortcode_atts['type'] === 'basicslider' || ( $shortcode_atts['type'] === '' && ( ( $rl_gallery && $rl->options['settings']['builder_gallery'] === 'basicslider' ) || ( ! $rl_gallery && $rl->options['settings']['default_gallery'] === 'basicslider' ) ) ) ) )
			return $output;

		// get shortcode gallery fields combined with defaults
		$fields = rl_get_gallery_fields( 'basicslider' );

		// get gallery fields attributes
		$field_atts = rl_get_gallery_fields_atts( $fields, $shortcode_atts, $rl_gallery );

		// is it rl gallery? add misc and lightbox fields
		if ( $rl_gallery )
			$fields += $rl->galleries->fields['lightbox']['options'] + $rl->galleries->fields['misc']['options'];

		// get only valid arguments
		$atts = shortcode_atts( array_merge( $defaults, $field_atts ), $shortcode_atts, 'gallery' );

		// sanitize gallery fields
		$atts = $this->sanitize_shortcode_args( $atts, $fields );

		// break if it is not basic slider gallery
		if ( ! ( $atts['type'] === 'basicslider' || ( $atts['type'] === '' && ( ( $rl_gallery && $rl->options['settings']['builder_gallery'] === 'basicslider' ) || ( ! $rl_gallery && $rl->options['settings']['default_gallery'] === 'basicslider' ) ) ) ) )
			return $output;

		// ID
		$atts['id'] = (int) $atts['id'];

		// add custom classes if needed
		if ( $rl_gallery )
			$atts['class'] .= ' ' . $atts['gallery_custom_class'];

		// any classes?
		if ( $atts['class'] !== '' ) {
			$atts['class'] = trim( $atts['class'] );

			// more than 1 class?
			if ( strpos( $atts['class'], ' ' ) !== false ) {
				// get unique valid HTML classes
				$atts['class'] = array_unique( array_filter( array_map( 'sanitize_html_class', explode( ' ', $atts['class'] ) ) ) );

				if ( ! empty( $atts['class'] ) )
					$atts['class'] = implode( ' ', $atts['class'] );
				else
					$atts['class'] = '';
			// single class
			} else
				$atts['class'] = sanitize_html_class( $atts['class'] );
		}

		// orderby
		if ( empty( $atts['orderby'] ) ) {
			$atts['orderby'] = sanitize_sql_orderby( $atts['orderby'] );

			if ( empty( $atts['orderby'] ) )
				$atts['orderby'] = $defaults['orderby'];
		}

		// order
		if ( strtolower( $atts['order'] ) === 'rand' )
			$atts['orderby'] = 'rand';

		// gallery lightbox source size
		if ( ! empty( $atts['lightbox_image_size'] ) ) {
			if ( $atts['lightbox_image_size'] === 'global' )
				$atts['src_size'] = $rl->options['settings']['gallery_image_size'];
			elseif ( $atts['lightbox_image_size'] === 'lightbox_custom_size' && isset( $atts['lightbox_custom_size_width'], $atts['lightbox_custom_size_height'] ) )
				$atts['src_size'] = [ $atts['lightbox_custom_size_width'], $atts['lightbox_custom_size_height'] ];
			else
				$atts['src_size'] = $atts['lightbox_image_size'];
		} else
			$atts['src_size'] = $rl->options['settings']['gallery_image_size'];

		// filter all shortcode arguments
		$atts = apply_filters( 'rl_gallery_shortcode_atts', $atts, $rl_gallery_id );

		// get gallery images
		$images = rl_get_gallery_shortcode_images( $atts );

		if ( empty( $images ) || is_feed() || defined( 'IS_HTML_EMAIL' ) )
			return $output;

		// make sure it is integer
		$gallery_no = (int) $this->gallery_no;

		ob_start();

		// $gallery_no and $rl_gallery_id are both integers ?>
		<div class="rl-gallery-container<?php echo esc_attr( apply_filters( 'rl_gallery_container_class', '', $atts, $rl_gallery_id ) ); ?>" id="rl-gallery-container-<?php echo $gallery_no; ?>" data-gallery_id="<?php echo $rl_gallery_id; ?>">

			<?php do_action( 'rl_before_gallery', $atts, $rl_gallery_id ); ?>

			<ul class="rl-gallery rl-basicslider-gallery <?php echo esc_attr( $atts['class'] ); ?>" id="rl-gallery-<?php echo $gallery_no; ?>" data-gallery_no="<?php echo $gallery_no; ?>">

			<?php foreach ( $images as $image ) {
				// $image['link'] is already escaped
				echo '<li class="rl-gallery-item">' . $image['link'] . '</li>';
			} ?>

			</ul>

			<?php do_action( 'rl_after_gallery', $atts, $rl_gallery_id ); ?>

		</div>

		<?php $gallery_html = ob_get_contents();

		ob_end_clean();

		// scripts
		wp_register_script( 'responsive-lightbox-basicslider-gallery-js', plugins_url( 'assets/slippry/slippry' . ( ! ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '.min' : '' ) . '.js', dirname( __FILE__ ) ), [ 'jquery' ], $rl->defaults['version'], ( $rl->options['settings']['loading_place'] === 'footer' ) );
		wp_enqueue_script( 'responsive-lightbox-basicslider-gallery', plugins_url( 'js/front-basicslider.js', dirname( __FILE__ ) ), [ 'jquery', 'responsive-lightbox-basicslider-gallery-js' ], $rl->defaults['version'], ( $rl->options['settings']['loading_place'] === 'footer' ) );

		// styles
		wp_enqueue_style( 'responsive-lightbox-basicslider-gallery', plugins_url( 'assets/slippry/slippry' . ( ! ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '.min' : '' ) . '.css', dirname( __FILE__ ) ), [], $rl->defaults['version'] );

		wp_localize_script(
			'responsive-lightbox-basicslider-gallery',
			'rlArgsBasicSliderGallery' . ( $gallery_no + 1 ),
			[
				'data' => [
					'adaptive_height'		=> $atts['adaptive_height'],
					'loop'					=> $atts['loop'],
					'captions'				=> $atts['captions'],
					'init_single'			=> $atts['init_single'],
					'responsive'			=> $atts['responsive'],
					'preload'				=> $atts['preload'],
					'pager'					=> $atts['pager'],
					'controls'				=> $atts['controls'],
					'hide_on_end'			=> $atts['hide_on_end'],
					'slide_margin'			=> $atts['slide_margin'],
					'transition'			=> $atts['transition'],
					'kenburns_zoom'			=> $atts['kenburns_zoom'],
					'speed'					=> $atts['speed'],
					'easing'				=> $atts['easing'],
					'continuous'			=> $atts['continuous'],
					'use_css'				=> $atts['use_css'],
					'slideshow'				=> $atts['slideshow'],
					'slideshow_direction'	=> $atts['slideshow_direction'],
					'slideshow_hover'		=> $atts['slideshow_hover'],
					'slideshow_hover_delay'	=> $atts['slideshow_hover_delay'],
					'slideshow_delay'		=> $atts['slideshow_delay'],
					'slideshow_pause'		=> $atts['slideshow_pause']
				]
			]
		);

		// remove any new lines from the output so that the reader parses it better
		return apply_filters( 'rl_gallery_shortcode_html', trim( preg_replace( '/\s+/', ' ', $gallery_html ) ), $atts, $rl_gallery_id );
	}

	/**
	 * Render Basic Masonry gallery shortcode.
	 *
	 * @global object $post
	 *
	 * @param string $output HTML output
	 * @param array $shortcode_atts Shortcode attributes
	 * @return string
	 */
	public function basic_masonry_gallery_shortcode( $output, $shortcode_atts ) {
		if ( ! empty( $output ) )
			return $output;

		global $post;

		$defaults = [
			'rl_gallery_id'	=> 0,
			'id'			=> isset( $post->ID ) ? (int) $post->ID : 0,
			'class'			=> '',
			'include'		=> '',
			'exclude'		=> '',
			'urls'			=> '',
			'type'			=> '',
			'order'			=> 'asc',
			'orderby'		=> 'menu_order',
			'size'			=> 'medium',
			'link'			=> 'file',
			'columns'		=> 3
		];

		// get main instance
		$rl = Responsive_Lightbox();

		if ( ! is_array( $shortcode_atts ) )
			$shortcode_atts = wp_parse_args( $shortcode_atts, $defaults );

		// is there rl_gallery ID?
		$rl_gallery_id = $defaults['rl_gallery_id'] = ! empty( $shortcode_atts['rl_gallery_id'] ) ? (int) $shortcode_atts['rl_gallery_id'] : 0;

		// is it rl gallery?
		$rl_gallery = $rl->options['builder']['gallery_builder'] && $rl_gallery_id && get_post_type( $rl_gallery_id ) === 'rl_gallery';

		if ( ! array_key_exists( 'type', $shortcode_atts ) )
			$shortcode_atts['type'] = '';

		// break if it is not basic masonry gallery - first check
		if ( ! ( $shortcode_atts['type'] === 'basicmasonry' || ( $shortcode_atts['type'] === '' && ( ( $rl_gallery && $rl->options['settings']['builder_gallery'] === 'basicmasonry' ) || ( ! $rl_gallery && $rl->options['settings']['default_gallery'] === 'basicmasonry' ) ) ) ) )
			return $output;

		// get shortcode gallery fields combined with defaults
		$fields = rl_get_gallery_fields( 'basicmasonry' );

		// get gallery fields attributes
		$field_atts = rl_get_gallery_fields_atts( $fields, $shortcode_atts, $rl_gallery );

		// is it rl gallery? add misc and lightbox fields
		if ( $rl_gallery )
			$fields += $rl->galleries->fields['lightbox']['options'] + $rl->galleries->fields['misc']['options'];

		// get only valid arguments
		$atts = shortcode_atts( array_merge( $defaults, $field_atts ), $shortcode_atts, 'gallery' );

		// sanitize gallery fields
		$atts = $this->sanitize_shortcode_args( $atts, $fields );

		// break if it is not basic masonry gallery
		if ( ! ( $atts['type'] === 'basicmasonry' || ( $atts['type'] === '' && ( ( $rl_gallery && $rl->options['settings']['builder_gallery'] === 'basicmasonry' ) || ( ! $rl_gallery && $rl->options['settings']['default_gallery'] === 'basicmasonry' ) ) ) ) )
			return $output;

		// ID
		$atts['id'] = (int) $atts['id'];

		// add custom classes if needed
		if ( $rl_gallery )
			$atts['class'] .= ' ' . $atts['gallery_custom_class'];

		// any classes?
		if ( $atts['class'] !== '' ) {
			$atts['class'] = trim( $atts['class'] );

			// more than 1 class?
			if ( strpos( $atts['class'], ' ' ) !== false ) {
				// get unique valid HTML classes
				$atts['class'] = array_unique( array_filter( array_map( 'sanitize_html_class', explode( ' ', $atts['class'] ) ) ) );

				if ( ! empty( $atts['class'] ) )
					$atts['class'] = implode( ' ', $atts['class'] );
				else
					$atts['class'] = '';
			// single class
			} else
				$atts['class'] = sanitize_html_class( $atts['class'] );
		}

		// orderby
		if ( empty( $atts['orderby'] ) ) {
			$atts['orderby'] = sanitize_sql_orderby( $atts['orderby'] );

			if ( empty( $atts['orderby'] ) )
				$atts['orderby'] = $defaults['orderby'];
		}

		// order
		if ( strtolower( $atts['order'] ) === 'rand' )
			$atts['orderby'] = 'rand';

		// check columns
		if ( $atts['columns_lg'] === 0 )
			$atts['columns_lg'] = $atts['columns'];

		if ( $atts['columns_md'] === 0 )
			$atts['columns_md'] = $atts['columns'];

		if ( $atts['columns_sm'] === 0 )
			$atts['columns_sm'] = $atts['columns'];

		if ( $atts['columns_xs'] === 0 )
			$atts['columns_xs'] = $atts['columns'];

		// gallery lightbox source size
		if ( ! empty( $atts['lightbox_image_size'] ) ) {
			if ( $atts['lightbox_image_size'] === 'global' )
				$atts['src_size'] = $rl->options['settings']['gallery_image_size'];
			elseif ( $atts['lightbox_image_size'] === 'lightbox_custom_size' && isset( $atts['lightbox_custom_size_width'], $atts['lightbox_custom_size_height'] ) )
				$atts['src_size'] = [ $atts['lightbox_custom_size_width'], $atts['lightbox_custom_size_height'] ];
			else
				$atts['src_size'] = $atts['lightbox_image_size'];
		} else
			$atts['src_size'] = $rl->options['settings']['gallery_image_size'];

		// filter all shortcode arguments
		$atts = apply_filters( 'rl_gallery_shortcode_atts', $atts, $rl_gallery_id );

		// get gallery images
		$images = rl_get_gallery_shortcode_images( $atts );

		if ( empty( $images ) || is_feed() || defined( 'IS_HTML_EMAIL' ) )
			return $output;

		// make sure it is integer
		$gallery_no = (int) $this->gallery_no;

		ob_start();

		// $gallery_no and $rl_gallery_id are both integers ?>
		<div class="rl-gallery-container<?php echo esc_attr( apply_filters( 'rl_gallery_container_class', '', $atts, $rl_gallery_id ) ); ?>" id="rl-gallery-container-<?php echo $gallery_no; ?>" data-gallery_id="<?php echo $rl_gallery_id; ?>">

			<?php do_action( 'rl_before_gallery', $atts, $rl_gallery_id ); ?>

			<div class="rl-gallery rl-basicmasonry-gallery <?php echo esc_attr( $atts['class'] ); ?>" id="rl-gallery-<?php echo $gallery_no; ?>" data-gallery_no="<?php echo $gallery_no; ?>">

			<?php
			$count = 0;

			if ( $count === 0 )
				echo '<div class="rl-gutter-sizer"></div><div class="rl-grid-sizer"></div>';

			foreach ( $images as $image ) {
				// $image['link'] is already escaped
				echo '
				<div class="rl-gallery-item' . ( $count === 0 ? ' rl-gallery-item-width-4' : '' ) . '" ' . implode( ' ', apply_filters( 'rl_gallery_item_extra_args', [], $atts, $image ) ) . '>
					<div class="rl-gallery-item-content">
						' . $image['link'] . '
					</div>
				</div>';

				$count++;
			} ?>

			</div>

			<?php do_action( 'rl_after_gallery', $atts, $rl_gallery_id ); ?>

		</div>

		<?php $gallery_html = ob_get_contents();

		ob_clean();

		// scripts
		wp_enqueue_script( 'responsive-lightbox-basicmasonry-gallery', plugins_url( 'js/front-basicmasonry.js', dirname( __FILE__ ) ), [ 'jquery', 'responsive-lightbox-masonry', 'responsive-lightbox-images-loaded' ], $rl->defaults['version'], ( $rl->options['settings']['loading_place'] === 'footer' ) );

		// styles
		wp_enqueue_style( 'responsive-lightbox-basicmasonry-gallery', plugins_url( 'css/gallery-basicmasonry.css', dirname( __FILE__ ) ), [], $rl->defaults['version'] );

		// add inline style
		wp_add_inline_style( 'responsive-lightbox-basicmasonry-gallery', '
			#rl-gallery-container-' . $gallery_no . ' .rl-basicmasonry-gallery {
				margin: ' . -(string) round( (int) $atts['margin'] / 2, 1 ) . 'px ' . -(string) round( (int) $atts['gutter'] / 2, 1 ) . 'px;
				padding: ' . (int) $atts['margin'] . 'px 0;
			}
			#rl-gallery-container-' . $gallery_no . ' .rl-pagination-bottom {
				margin-top: ' . ( (int) $atts['margin'] / 2 ) . 'px
			}
			#rl-gallery-container-' . $gallery_no . ' .rl-pagination-top {
				margin-bottom: ' . ( (int) $atts['margin'] / 2 ) . 'px
			}
			#rl-gallery-container-' . $gallery_no . ' .rl-basicmasonry-gallery .rl-gallery-item,
			#rl-gallery-container-' . $gallery_no . ' .rl-basicmasonry-gallery .rl-grid-sizer {
				width: calc(' . (string) round( 100 / (int) $atts['columns'], 2 ) . '% - ' . (int) $atts['gutter'] . 'px);
				margin: ' . ( (int) $atts['margin'] / 2 ) . 'px ' . ( (int) $atts['gutter'] / 2 ) . 'px;
			}
			@media all and (min-width: 1200px) {
				#rl-gallery-container-' . $gallery_no . ' .rl-basicmasonry-gallery .rl-gallery-item,
				#rl-gallery-container-' . $gallery_no . ' .rl-basicmasonry-gallery .rl-grid-sizer {
					width: calc(' . (string) round( 100 / (int) $atts['columns_lg'], 2 ) . '% - ' . (int) $atts['gutter'] . 'px);
					margin: ' . ( (int) $atts['margin'] / 2 ) . 'px ' . ( (int) $atts['gutter'] / 2 ) . 'px;
				}
			}
			@media all and (min-width: 992px) and (max-width: 1200px) {
				#rl-gallery-container-' . $gallery_no . ' .rl-basicmasonry-gallery .rl-gallery-item,
				#rl-gallery-container-' . $gallery_no . ' .rl-basicmasonry-gallery .rl-grid-sizer {
					width: calc(' . (string) round( 100 / (int) $atts['columns_md'], 2 ) . '% - ' . (int) $atts['gutter'] . 'px);
					margin: ' . ( (int) $atts['margin'] / 2 ) . 'px ' . ( (int) $atts['gutter'] / 2 ) . 'px;
				}
			}
			@media all and (min-width: 768px) and (max-width: 992px) {
				#rl-gallery-container-' . $gallery_no . ' .rl-basicmasonry-gallery .rl-gallery-item,
				#rl-gallery-container-' . $gallery_no . ' .rl-basicmasonry-gallery .rl-grid-sizer {
					width: calc(' . (string) round( 100 / (int) $atts['columns_sm'], 2 ) . '% - ' . (int) $atts['gutter'] . 'px);
					margin: ' . ( (int) $atts['margin'] / 2 ) . 'px ' . ( (int) $atts['gutter'] / 2 ) . 'px;
				}
			}
			@media all and (max-width: 768px) {
				#rl-gallery-container-' . $gallery_no . ' .rl-basicmasonry-gallery .rl-gallery-item,
				#rl-gallery-container-' . $gallery_no . ' .rl-basicmasonry-gallery .rl-grid-sizer {
					width: calc(' . (string) round( 100 / (int) $atts['columns_xs'], 2 ) . '% - ' . (int) $atts['gutter'] . 'px);
					margin: ' . ( (int) $atts['margin'] / 2 ) . 'px ' . ( (int) $atts['gutter'] / 2 ) . 'px;
				}
			}'
		);

		wp_localize_script(
			'responsive-lightbox-basicmasonry-gallery',
			'rlArgsBasicMasonryGallery' . ( $gallery_no + 1 ),
			[
				'data' => [
					'originLeft'	=> $atts['origin_left'],
					'originTop'		=> $atts['origin_top']
				]
			]
		);

		// remove any new lines from the output so that the reader parses it better
		return apply_filters( 'rl_gallery_shortcode_html', trim( preg_replace( '/\s+/', ' ', $gallery_html ) ), $atts, $rl_gallery_id );
	}
}