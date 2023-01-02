<?php
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

new Responsive_Lightbox_Widgets();

/**
 * Responsive Lightbox Widgets class.
 *
 * @class Responsive_Lightbox_Widgets
 */
class Responsive_Lightbox_Widgets {

	/**
	 * Class constructor.
	 *
	 * @return void
	 */
	public function __construct() {
		// actions
		add_action( 'widgets_init', [ $this, 'register_widgets' ] );
	}

	/**
	 * Register widgets.
	 *
	 * @return void
	 */
	public function register_widgets() {
		register_widget( 'Responsive_Lightbox_Gallery_Widget' );
		register_widget( 'Responsive_Lightbox_Image_Widget' );
	}
}

/**
 * Responsive Lightbox Gallery Widget class.
 *
 * @class Responsive_Lightbox_Gallery_Widget
 */
class Responsive_Lightbox_Gallery_Widget extends WP_Widget {

	private $rlg_defaults = [];
	private $rlg_orders = [];
	private $rlg_order_types = [];
	private $rlg_image_sizes = [];
	private $rlg_gallery_types = [];

	/**
	 * Class constructor.
	 *
	 * @return void
	 */
	public function __construct() {
		parent::__construct(
			'Responsive_Lightbox_Gallery_Widget',
			__( 'Gallery', 'responsive-lightbox' ),
			[
				'description'	=> __( 'Displays an image gallery.', 'responsive-lightbox' ),
				'classname'		=> 'rl-gallery-widget'
			]
		);

		$this->rlg_defaults = [
			'title'		=> __( 'Gallery', 'responsive-lightbox' ),
			'orderby'	=> 'menu_order',
			'order'		=> 'asc',
			'columns'	=> 3,
			'size'		=> 'thumbnail',
			'type'		=> 'none',
			'atts'		=> '',
			'ids'		=> ''
		];

		$this->rlg_orders = [
			'menu_order'	=> __( 'Menu order', 'responsive-lightbox' ),
			'title'			=> __( 'Title', 'responsive-lightbox' ),
			'post_date'		=> __( 'Image date', 'responsive-lightbox' ),
			'ID'			=> __( 'ID', 'responsive-lightbox' ),
			'rand'			=> __( 'Random', 'responsive-lightbox' )
		];

		$this->rlg_order_types = [
			'asc'	 => __( 'Ascending', 'responsive-lightbox' ),
			'desc'	 => __( 'Descending', 'responsive-lightbox' )
		];

		$gallery_types = apply_filters( 'rl_gallery_types', Responsive_Lightbox()->gallery_types );

		if ( ! empty( $gallery_types ) ) {
			$this->rlg_gallery_types = array_merge(
				[
					'none'		=> __( 'None', 'responsive-lightbox' ),
					'default'	=> __( 'Default', 'responsive-lightbox' )
				],
				$gallery_types
			);
		}

		$this->rlg_image_sizes = array_merge( [ 'full' ], get_intermediate_image_sizes() );

		sort( $this->rlg_image_sizes, SORT_STRING );
	}

	/**
	 * Display widget.
	 *
	 * @param array $args
	 * @param object $instance
	 * @return void
	 */
	public function widget( $args, $instance ) {
		$instance['title'] = apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base );

		$html = $args['before_widget'] . $args['before_title'] . ( $instance['title'] !== '' ? esc_html( $instance['title'] ) : '' ) . $args['after_title'];

		$atts = [];

		// escape atts
		if ( $instance['atts'] !== '' ) {
			$atts_exp = explode( '" ', $instance['atts'] );

			if ( ! empty( $atts_exp ) ) {
				end( $atts_exp );

				$last = key( $atts_exp );

				reset( $atts_exp );

				foreach ( $atts_exp as $id => $attribute ) {
					$check = $attribute . ( $last === $id ? '' : '"' );

					if ( preg_match( '/^([a-z0-9_-]+)=\"(.+?)\"$/', $check, $matches ) === 1 )
						$atts[] = $matches[1] . '="' . esc_attr( $matches[2] ) . '"';
				}
			}
		}

		if ( ! empty( $atts ) )
			$instance['atts'] = implode( ' ', $atts );
		else
			$instance['atts'] = '';

		$html .= do_shortcode( '[gallery link="file" columns="' . (int) $instance['columns'] . '" size="' . esc_attr( $instance['size'] ) . '" ' . ( $instance['type'] !== 'none' ? 'type="' . esc_attr( $instance['type'] ) . '"' : '' ) . ' ids="' . ( ! empty( $instance['ids'] ) ? esc_attr( $instance['ids'] ) : '' ) . '" orderby="' . esc_attr( $instance['orderby'] ) . '" order="' . esc_attr( $instance['order'] ) . '"' . ( $instance['atts'] !== '' ? ' ' . $instance['atts'] : '' ) . ']' );
		$html .= $args['after_widget'];

		echo apply_filters( 'rl_gallery_widget_html', $html, $instance );
	}

	/** Render widget form.
	 *
	 * @param object $instance
	 * @return void
	 */
	public function form( $instance ) {
		$attachments = ! empty( $instance['ids'] ) ? array_filter( explode( ',', $instance['ids'] ) ) : [];

		$html = '
		<div class="rl-gallery-widget-container">
			<p>
				<label for="' . $this->get_field_id( 'title' ) . '">' . esc_html__( 'Title', 'responsive-lightbox' ) . ':</label>
				<input id="' . $this->get_field_id( 'title' ) . '" class="widefat" name="' . $this->get_field_name( 'title' ) . '" type="text" value="' . esc_attr( isset( $instance['title'] ) ? $instance['title'] : $this->rlg_defaults['title'] ) . '" />
			</p>
			<div id="' . $this->get_field_id( 'gallery' ) . '" class="rl-gallery-widget' . ( ! empty( $attachments ) ? ' has-image' : '' ) . '">
				<input type="hidden" class="rl-gallery-ids" id="' . $this->get_field_id( 'ids' ) . '" name="' . $this->get_field_name( 'ids' ) . '" value="' . ( ! empty( $instance['ids'] ) ? esc_attr( $instance['ids'] ) : '' ) . '">';

			$html .= '
				<a href="#" class="rl-gallery-widget-select button button-secondary">' . esc_html__( 'Select images', 'responsive-lightbox' ) . '</a>
				<div class="rl-gallery-widget-content">
					<ul id="' . $this->get_field_id( 'gallery-images' ) . '" class="rl-gallery-images">';

			if ( $attachments ) {
				foreach ( $attachments as $attachment_id ) {
					if ( ! $attachment_id || ! wp_attachment_is_image( $attachment_id ) )
						continue;

					$html .= '
						<li class="rl-gallery-image" data-attachment_id="' . (int) $attachment_id . '">
							<div class="rl-gallery-inner">' . wp_get_attachment_image( $attachment_id, 'thumbnail' ) . '</div>
							<div class="rl-gallery-actions"><a href="#" class="rl-gallery-image-remove dashicons dashicons-no" title="' . esc_attr__( 'Delete image', 'responsive-lightbox' ) . '"></a></div>
						</li>';
				}
			}

		$html .= '
					</ul>
				</div>
			</div>
			<p>';

		if ( ! empty( $this->rlg_gallery_types ) ) {
			$html .= '
				<label for="' . $this->get_field_id( 'type' ) . '">' . esc_html__( 'Gallery type', 'responsive-lightbox' ) . ':</label>
				<select id="' . $this->get_field_id( 'type' ) . '" class="widefat" name="' . $this->get_field_name( 'type' ) . '">';

		foreach ( $this->rlg_gallery_types as $id => $type ) {
			$html .= '
					<option value="' . esc_attr( $id ) . '" ' . selected( $id, ( isset( $instance['type'] ) ? $instance['type'] : $this->rlg_defaults['type'] ), false ) . '>' . esc_html( $type ) . '</option>';
		}

		$html .= '
				</select>
			</p>
			<p>';
		}

		$html .= '
				<label for="' . $this->get_field_id( 'orderby' ) . '">' . esc_html__( 'Order by', 'responsive-lightbox' ) . ':</label>
				<select id="' . $this->get_field_id( 'orderby' ) . '" class="widefat" name="' . $this->get_field_name( 'orderby' ) . '">';

		foreach ( $this->rlg_orders as $id => $orderby ) {
			$html .= '
					<option value="' . esc_attr( $id ) . '" ' . selected( $id, ( isset( $instance['orderby'] ) ? $instance['orderby'] : $this->rlg_defaults['orderby'] ), false ) . '>' . esc_html( $orderby ) . '</option>';
		}

		$html .= '
				</select>
			</p>
			<p>
				<label for="' . $this->get_field_id( 'order' ) . '">' . esc_html__( 'Order', 'responsive-lightbox' ) . ':</label>
				<select id="' . $this->get_field_id( 'order' ) . '" class="widefat" name="' . $this->get_field_name( 'order' ) . '">';

		foreach ( $this->rlg_order_types as $id => $order ) {
			$html .= '
					<option value="' . esc_attr( $id ) . '" ' . selected( $id, ( isset( $instance['order'] ) ? $instance['order'] : $this->rlg_defaults['order'] ), false ) . '>' . esc_html( $order ) . '</option>';
		}

		$html .= '
				</select>
			</p>
			<p>
				<label for="' . $this->get_field_id( 'size' ) . '">' . esc_html__( 'Image size', 'responsive-lightbox' ) . ':</label>
				<select id="' . $this->get_field_id( 'size' ) . '" class="widefat" name="' . $this->get_field_name( 'size' ) . '">';

		foreach ( $this->rlg_image_sizes as $size ) {
			$html .= '
					<option value="' . esc_attr( $size ) . '" ' . selected( $size, ( isset( $instance['size'] ) ? $instance['size'] : $this->rlg_defaults['size'] ), false ) . '>' . esc_html( $size ) . '</option>';
		}

		$html .= '
				</select>
			</p>
			<p>
					<label for="' . $this->get_field_id( 'columns' ) . '">' . esc_html__( 'Number of columns', 'responsive-lightbox' ) . ':</label>
					<input id="' . $this->get_field_id( 'columns' ) . '" class="small-text" name="' . $this->get_field_name( 'columns' ) . '" type="number" min="0" value="' . (int) ( isset( $instance['columns'] ) ? $instance['columns'] : $this->rlg_defaults['columns'] ) . '" />
			</p>
			<p>
				<label for="' . $this->get_field_id( 'atts' ) . '">' . esc_html__( 'Custom gallery shortcode attributes', 'responsive-lightbox' ) . ':</label>
				<br />
				<textarea id="' . $this->get_field_id( 'atts' ) . '" class="widefat" name="' . $this->get_field_name( 'atts' ) . '">' . esc_textarea( isset( $instance['atts'] ) ? $instance['atts'] : $this->rlg_defaults['atts'] ) . '</textarea>
			</p>
		</div>';

		echo $html;
	}

	/**
	 * Save widget form.
	 *
	 * @param array $new_instance
	 * @param array $old_instance
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {
		// title
		$old_instance['title'] = sanitize_text_field( isset( $new_instance['title'] ) ? trim( $new_instance['title'] ) : $this->rlg_defaults['title'] );

		// order by
		$old_instance['orderby'] = isset( $new_instance['orderby'] ) && array_key_exists( $new_instance['orderby'], $this->rlg_orders ) ? $new_instance['orderby'] : $this->rlg_defaults['orderby'];

		// order
		$old_instance['order'] = isset( $new_instance['order'] ) && array_key_exists( $new_instance['order'], $this->rlg_order_types ) ? $new_instance['order'] : $this->rlg_defaults['order'];

		// image size
		$old_instance['size'] = isset( $new_instance['size'] ) && in_array( $new_instance['size'], $this->rlg_image_sizes, true ) ? $new_instance['size'] : $this->rlg_defaults['size'];

		// gallery type
		$old_instance['type'] = isset( $new_instance['type'] ) && array_key_exists( $new_instance['type'], $this->rlg_gallery_types ) ? $new_instance['type'] : $this->rlg_defaults['type'];

		// number of columns
		$old_instance['columns'] = isset( $new_instance['columns'] ) ? ( ( $columns = (int) $new_instance['columns'] ) > 0 ? $columns : $this->rlg_defaults['columns'] ) : $this->rlg_defaults['columns'];

		// image ids
		if ( ! empty( $new_instance['ids'] ) && is_string( $new_instance['ids'] ) ) {
			// get unique and non empty attachment ids only
			$old_instance['ids'] = implode( ',', array_unique( array_filter( array_map( 'intval', explode( ',', $new_instance['ids'] ) ) ) ) );
		} else
			$old_instance['ids'] = $this->rlg_defaults['ids'];

		// custom attributes
		$atts = sanitize_textarea_field( preg_replace( '/\s+/', ' ', trim( str_replace( [ "\r\n", "\n\r", "\n", "\r" ], ' ', isset( $new_instance['atts'] ) ? $new_instance['atts'] : $this->rlg_defaults['atts'] ) ) ) );

		$new_atts = [];

		if ( $atts !== '' ) {
			$atts_exp = explode( '" ', $atts );

			if ( ! empty( $atts_exp ) ) {
				end( $atts_exp );

				$last = key( $atts_exp );

				reset( $atts_exp );

				foreach ( $atts_exp as $id => $attribute ) {
					$check = $attribute . ( $last === $id ? '' : '"' );

					if ( preg_match( '/^[a-z0-9_-]+=\"(.+?)\"$/', $check ) === 1 )
						$new_atts[] = $check;
				}
			}
		}

		if ( ! empty( $new_atts ) )
			$old_instance['atts'] = implode( ' ', $new_atts );
		else
			$old_instance['atts'] = '';

		return $old_instance;
	}
}

/**
 * Responsive Lightbox Gallery Widget class.
 *
 * @class Responsive_Lightbox_Gallery_Widget
 */
class Responsive_Lightbox_Image_Widget extends WP_Widget {

	private $rli_defaults = [];
	private $rli_text_positions = [];
	private $rli_link_to = [];
	private $rli_aligns = [];
	private $rli_image_sizes = [];

	/**
	 * Class constructor.
	 *
	 * @return void
	 */
	public function __construct() {
		parent::__construct(
			'Responsive_Lightbox_Image_Widget',
			__( 'Image', 'responsive-lightbox' ),
			[
				'description'	=> __( 'Displays a single image.', 'responsive-lightbox' ),
				'classname'		=> 'rl-image-widget'
			]
		);

		$this->rli_defaults = [
			'title'				 => __( 'Image', 'responsive-lightbox' ),
			'image_id'			 => 0,
			'responsive'		 => true,
			'size'				 => 'thumbnail',
			'link_to'			 => 'file',
			'link_custom_url'	 => '',
			'image_align'		 => 'none',
			'text'				 => '',
			'autobr'			 => false,
			'text_position'		 => 'below_image',
			'text_align'		 => 'none'
		];

		$this->rli_text_positions = [
			'below_image'	=> __( 'Below the image', 'responsive-lightbox' ),
			'above_image' => __( 'Above the image', 'responsive-lightbox' )
		];

		$this->rli_link_to = [
			'none'	 => __( 'None', 'responsive-lightbox' ),
			'file'	 => __( 'Media File', 'responsive-lightbox' ),
			'post'	 => __( 'Attachment Page', 'responsive-lightbox' ),
			'custom' => __( 'Custom URL', 'responsive-lightbox' )
		];

		$this->rli_aligns = [
			'none'		 => __( 'None', 'responsive-lightbox' ),
			'left'		 => __( 'Left', 'responsive-lightbox' ),
			'center'	 => __( 'Center', 'responsive-lightbox' ),
			'right'		 => __( 'Right', 'responsive-lightbox' ),
			'justify'	 => __( 'Justify', 'responsive-lightbox' )
		];

		$this->rli_image_sizes = array_merge( [ 'full' ], get_intermediate_image_sizes() );

		sort( $this->rli_image_sizes, SORT_STRING );
	}

	/**
	 * Display widget.
	 *
	 * @param array $args
	 * @param array $instance
	 * @return void
	 */
	public function widget( $args, $instance ) {
		switch ( $instance['link_to'] ) {
			case 'file':
				$file = wp_get_attachment_image_src( $instance['image_id'], 'full', false );
				$href = $file[0];
				break;

			case 'post':
				$href = get_permalink( $instance['image_id'] );
				break;

			case 'custom':
				$href = $instance['link_custom_url'];
				break;

			case 'none':
			default:
				$href = '';
		}

		// image align
		if ( $instance['image_align'] === 'left' )
			$image_align = 'float: left;';
		elseif ( $instance['image_align'] === 'center' )
			$image_align = 'margin-left: auto; margin-right: auto; display: block;';
		elseif ( $instance['image_align'] === 'right' )
			$image_align = 'float: right;';
		else
			$image_align = '';

		// text align
		if ( $instance['text_align'] === 'left' )
			$text_align = 'text-align: left; display: block;';
		elseif ( $instance['text_align'] === 'center' )
			$text_align = 'text-align: center; display: block;';
		elseif ( $instance['text_align'] === 'right' )
			$text_align = 'text-align: right; display: block;';
		elseif ( $instance['text_align'] === 'justify' )
			$text_align = 'text-align: justify; display: block;';
		else
			$text_align = '';

		// get image data
		$image = wp_get_attachment_image_src( $instance['image_id'], $instance['size'], false );

		$width = $instance['responsive'] === false ? $image[1] : '100%';
		$height = $instance['responsive'] === false ? $image[2] : 'auto';
		$post = get_post( $instance['image_id'] );
		$image_title = isset( $post->post_title ) ? $post->post_title : '';
		$alt = (string) get_post_meta( $instance['image_id'], '_wp_attachment_image_alt', true );
		$instance['title'] = apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base );

		// start output
		$html = $args['before_widget'] . $args['before_title'] . ( $instance['title'] !== '' ? esc_html( $instance['title'] ) : '' ) . $args['after_title'];

		if ( $instance['autobr'] === true )
			$escaped_text = wpautop( esc_html( $instance['text'] ) );
		else
			$escaped_text = esc_html( $instance['text'] );

		$container_html = '<div class="rl-image-widget-text" style="' . esc_attr( $text_align ) . '">' . $escaped_text . '</div>';
		$image_html = ( $href !== '' ? '<a href="' . esc_url( $href ) . '" class="rl-image-widget-link">' : '' ) . '<img class="rl-image-widget-image" src="' . esc_url( $image[0] ) . '" width="' . esc_attr( $width ) . '" height="' . esc_attr( $height ) . '" title="' . esc_attr( $image_title ) . '" alt="' . esc_attr( $alt ) . '" style="' . esc_attr( $image_align ) . '" />' . ( $href !== '' ? '</a>' : '' );

		if ( $instance['text_position'] === 'below_image' )
			$html .= $image_html . $container_html;
		else
			$html .= $container_html . $image_html;

		$html .= $args['after_widget'];

		echo apply_filters( 'rl_image_widget_html', $html, $instance );
	}

	/** Render widget form.
	 *
	 * @param array $instance
	 * @return void
	 */
	public function form( $instance ) {
		$image_id = (int) ( isset( $instance['image_id'] ) ? $instance['image_id'] : $this->rli_defaults['image_id'] );
		$image = '';

		if ( ! empty( $image_id ) )
			$image = wp_get_attachment_image( $image_id, 'medium', false );

		if ( ! $image )
			$image = wp_get_attachment_image( $image_id, 'full', false );

		$html = '
		<div class="rl-image-widget-container">
			<p>
				<label for="' . $this->get_field_id( 'title' ) . '">' . esc_html__( 'Title', 'responsive-lightbox' ) . '</label>
				<input id="' . $this->get_field_id( 'title' ) . '" class="widefat" name="' . $this->get_field_name( 'title' ) . '" type="text" value="' . esc_attr( isset( $instance['title'] ) ? $instance['title'] : $this->rli_defaults['title'] ) . '" />
			</p>
			<div class="rl-image-widget' . ( ! empty( $image_id ) ? ' has-image' : '' ) . '">
				<input class="rl-image-widget-image-id" type="hidden" name="' . $this->get_field_name( 'image_id' ) . '" value="' . (int) $image_id . '" />
				<a href="#" class="rl-image-widget-select button button-secondary">' . esc_html__( 'Select image', 'responsive-lightbox' ) . '</a>
				<div class="rl-image-widget-content">';

		if ( ! empty( $image ) )
			$html .= $image;

		$html .= '
				</div>
			</div>
			<p>
				<input id="' . $this->get_field_id( 'responsive' ) . '" type="checkbox" name="' . $this->get_field_name( 'responsive' ) . '" value="responsive" ' . checked( true, ( isset( $instance['responsive'] ) ? $instance['responsive'] : $this->rli_defaults['responsive'] ), false ) . ' /> <label for="' . $this->get_field_id( 'responsive' ) . '">' . esc_html__( 'Force responsive', 'responsive-lightbox' ) . '</label>
			</p>';

		$html .= '
			<p>
				<label for="' . $this->get_field_id( 'size' ) . '">' . esc_html__( 'Size', 'responsive-lightbox' ) . '</label>
				<select class="rl-image-size-select widefat" id="' . $this->get_field_id( 'size' ) . '" name="' . $this->get_field_name( 'size' ) . '">';

		$size_type = ( isset( $instance['size'] ) ? $instance['size'] : $this->rli_defaults['size'] );

		foreach ( $this->rli_image_sizes as $size ) {
			$html .= '
					<option value="' . esc_attr( $size ) . '" ' . selected( $size, $size_type, false ) . '>' . esc_html( $size ) . '</option>';
		}

		$html .= '
				</select>
			</p>
			<p>
				<label for="' . $this->get_field_id( 'link_to' ) . '">' . esc_html__( 'Link to', 'responsive-lightbox' ) . '</label>
				<select class="rl-image-link-to widefat" id="' . $this->get_field_id( 'link_to' ) . '" name="' . $this->get_field_name( 'link_to' ) . '">';

		$link_type = ( isset( $instance['link_to'] ) ? $instance['link_to'] : $this->rli_defaults['link_to'] );

		foreach ( $this->rli_link_to as $id => $type ) {
			$html .= '
					<option value="' . esc_attr( $id ) . '" ' . selected( $id, $link_type, false ) . '>' . esc_html( $type ) . '</option>';
		}

		$html .= '
				</select>
			</p>
			<p class="rl-image-link-url"' . ( $link_type === 'custom' ? '' : ' style="display: none;"' ) . '>
				<label for="' . $this->get_field_id( 'link_custom_url' ) . '">' . esc_html__( 'URL', 'responsive-lightbox' ) . '</label>
				<input id="' . $this->get_field_id( 'link_custom_url' ) . '" class="widefat" name="' . $this->get_field_name( 'link_custom_url' ) . '" type="text" value="' . esc_attr( isset( $instance['link_custom_url'] ) ? $instance['link_custom_url'] : $this->rli_defaults['link_custom_url'] ) . '" />
			</p>';

		$html .= '
		<p>
				<label for="' . $this->get_field_id( 'image_align' ) . '">' . esc_html__( 'Image align', 'responsive-lightbox' ) . '</label>
				<select id="' . $this->get_field_id( 'image_align' ) . '" class="widefat" name="' . $this->get_field_name( 'image_align' ) . '">';

		foreach ( $this->rli_aligns as $id => $image_align ) {
			if ( $id !== 'justify' )
				$html .= '
					<option value="' . esc_attr( $id ) . '" ' . selected( $id, ( isset( $instance['image_align'] ) ? $instance['image_align'] : $this->rli_defaults['image_align'] ), false ) . '>' . esc_html( $image_align ) . '</option>';
		}

		$html .= '
				</select>
			</p>
			<p>
				<label for="' . $this->get_field_id( 'text' ) . '">' . esc_html__( 'Text', 'responsive-lightbox' ) . '</label>
				<textarea id="' . $this->get_field_id( 'text' ) . '" class="widefat" name="' . $this->get_field_name( 'text' ) . '" rows="4">' . esc_html( isset( $instance['text'] ) ? $instance['text'] : $this->rli_defaults['text'] ) . '</textarea>
			</p>
			<p>
				<input id="' . $this->get_field_id( 'autobr' ) . '" type="checkbox" name="' . $this->get_field_name( 'autobr' ) . '" value="autobr" ' . checked( true, ( isset( $instance['autobr'] ) ? $instance['autobr'] : $this->rli_defaults['autobr'] ), false ) . ' /> <label for="' . $this->get_field_id( 'autobr' ) . '">' . esc_html__( 'Automatically add paragraphs', 'responsive-lightbox' ) . '</label>
			</p>';

		$html .= '
			<p>
				<label for="' . $this->get_field_id( 'text_position' ) . '">' . esc_html__( 'Text position', 'responsive-lightbox' ) . '</label>
				<select id="' . $this->get_field_id( 'text_position' ) . '" class="widefat" name="' . $this->get_field_name( 'text_position' ) . '">';

		foreach ( $this->rli_text_positions as $id => $text_position ) {
			$html .= '
					<option value="' . esc_attr( $id ) . '" ' . selected( $id, ( isset( $instance['text_position'] ) ? $instance['text_position'] : $this->rli_defaults['text_position'] ), false ) . '>' . esc_html( $text_position ) . '</option>';
		}

		$html .= '
				</select>
			</p>
			<label for="' . $this->get_field_id( 'text_align' ) . '">' . esc_html__( 'Text align', 'responsive-lightbox' ) . '</label>
			<select id="' . $this->get_field_id( 'text_align' ) . '" class="widefat" name="' . $this->get_field_name( 'text_align' ) . '">';

		foreach ( $this->rli_aligns as $id => $text_align ) {
			$html .= '
				<option value="' . esc_attr( $id ) . '" ' . selected( $id, ( isset( $instance['text_align'] ) ? $instance['text_align'] : $this->rli_defaults['text_align'] ), false ) . '>' . esc_html( $text_align ) . '</option>';
		}

		$html .= '
			</select>

		</div>';

		echo $html;
	}

	/**
	 * Save widget form.
	 *
	 * @param array $new_instance
	 * @param array $old_instance
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {
		// whitelists
		$old_instance['size'] = isset( $new_instance['size'] ) && in_array( $new_instance['size'], $this->rli_image_sizes, true ) ? $new_instance['size'] : $this->rli_defaults['size'];
		$old_instance['link_to'] = isset( $new_instance['link_to'] ) && in_array( $new_instance['link_to'], array_keys( $this->rli_link_to ), true ) ? $new_instance['link_to'] : $this->rli_defaults['link_to'];
		$old_instance['image_align'] = isset( $new_instance['image_align'] ) && in_array( $new_instance['image_align'], array_keys( $this->rli_aligns ), true ) ? $new_instance['image_align'] : $this->rli_defaults['image_align'];
		$old_instance['text_position'] = isset( $new_instance['text_position'] ) && in_array( $new_instance['text_position'], array_keys( $this->rli_text_positions ), true ) ? $new_instance['text_position'] : $this->rli_defaults['text_position'];
		$old_instance['text_align'] = isset( $new_instance['text_align'] ) && in_array( $new_instance['text_align'], array_keys( $this->rli_aligns ), true ) ? $new_instance['text_align'] : $this->rli_defaults['text_align'];

		// booleands
		$old_instance['responsive'] = ! empty( $new_instance['responsive'] );
		$old_instance['autobr'] = ! empty( $new_instance['autobr'] );

		// texts
		$old_instance['title'] = sanitize_text_field( isset( $new_instance['title'] ) ? $new_instance['title'] : $this->rli_defaults['title'] );
		$old_instance['text'] = isset( $new_instance['text'] ) ? wp_kses_post( $new_instance['text'] ) : $this->rli_defaults['text'];

		// integers
		$old_instance['image_id'] = isset( $new_instance['image_id'] ) ? (int) $new_instance['image_id'] : $this->rli_defaults['image_id'];

		// urls
		$old_instance['link_custom_url'] = isset( $new_instance['link_custom_url'] ) ? esc_url( $new_instance['link_custom_url'] ) : $this->rli_defaults['link_custom_url'];

		return $old_instance;
	}

}