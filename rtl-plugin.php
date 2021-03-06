<?php
/**
* Plugin Name: RTL Meta Box
* Plugin URI: 
* Description: Add meta box to every page and post. If active on a page the css changes the body screen reading to rtl (right to left) for the_content & the_title. 
* Version: 1.1
* Author: espiat
* Author URI: www.m.espiat.com
*/
class Rational_Meta_Box {
	private $screens = array(
		'post',
		'page',
	);
	private $fields = array(
		array(
			'id' => 'active',
			'label' => 'Active?',
			'type' => 'radio',
			'options' => array(
				'No',
				'Yes',
			),
		),
	);

	/**
	 * Class construct method. Adds actions to their respective WordPress hooks.
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post', array( $this, 'save_post' ) );
	}

	/**
	 * Hooks into WordPress' add_meta_boxes function.
	 * Goes through screens (post types) and adds the meta box.
	 */
	public function add_meta_boxes() {
		foreach ( $this->screens as $screen ) {
			add_meta_box(
				'rtl-switch',
				__( 'RTL Switch', 'RTL Switch' ),
				array( $this, 'add_meta_box_callback' ),
				$screen,
				'advanced',
				'default'
			);
		}
	}

	/**
	 * Generates the HTML for the meta box
	 * 
	 * @param object $post WordPress post object
	 */
	public function add_meta_box_callback( $post ) {
		wp_nonce_field( 'rtl_switch_data', 'rtl_switch_nonce' );
		echo 'Switch if dir RTL should be active on this site. 
Effects the_content & the_title';
		$this->generate_fields( $post );
	}

	/**
	 * Generates the field's HTML for the meta box.
	 */
	public function generate_fields( $post ) {
		$output = '';
		foreach ( $this->fields as $field ) {
			$label = '<label for="' . $field['id'] . '">' . $field['label'] . '</label>';
			$db_value = get_post_meta( $post->ID, 'rtl_switch_' . $field['id'], true );
			switch ( $field['type'] ) {
				case 'radio':
					$input = '<fieldset>';
					$input .= '<legend class="screen-reader-text">' . $field['label'] . '</legend>';
					$i = 0;
					foreach ( $field['options'] as $key => $value ) {
						$field_value = !is_numeric( $key ) ? $key : $value;
						$input .= sprintf(
							'<label><input %s id="%s" name="%s" type="radio" value="%s"> %s</label>%s',
							$db_value === $field_value ? 'checked' : '',
							$field['id'],
							$field['id'],
							$field_value,
							$value,
							$i < count( $field['options'] ) - 1 ? '<br>' : ''
						);
						$i++;
					}
					$input .= '</fieldset>';
					break;
				default:
					$input = sprintf(
						'<input %s id="%s" name="%s" type="%s" value="%s">',
						$field['type'] !== 'color' ? 'class="regular-text"' : '',
						$field['id'],
						$field['id'],
						$field['type'],
						$db_value
					);
			}
			$output .= $this->row_format( $label, $input );
		}
		echo '<table class="form-table"><tbody>' . $output . '</tbody></table>';
	}

	/**
	 * Generates the HTML for table rows.
	 */
	public function row_format( $label, $input ) {
		return sprintf(
			'<tr><th scope="row">%s</th><td>%s</td></tr>',
			$label,
			$input
		);
	}
	/**
	 * Hooks into WordPress' save_post function
	 */
	public function save_post( $post_id ) {
		if ( ! isset( $_POST['rtl_switch_nonce'] ) )
			return $post_id;

		$nonce = $_POST['rtl_switch_nonce'];
		if ( !wp_verify_nonce( $nonce, 'rtl_switch_data' ) )
			return $post_id;

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return $post_id;

		foreach ( $this->fields as $field ) {
			if ( isset( $_POST[ $field['id'] ] ) ) {
				switch ( $field['type'] ) {
					case 'email':
						$_POST[ $field['id'] ] = sanitize_email( $_POST[ $field['id'] ] );
						break;
					case 'text':
						$_POST[ $field['id'] ] = sanitize_text_field( $_POST[ $field['id'] ] );
						break;
				}
				update_post_meta( $post_id, 'rtl_switch_' . $field['id'], $_POST[ $field['id'] ] );
			} else if ( $field['type'] === 'checkbox' ) {
				update_post_meta( $post_id, 'rtl_switch_' . $field['id'], '0' );
			}
		}
	}
}
new Rational_Meta_Box;

// 
function rtl_title( $title) {
	if (   in_the_loop() && is_page()   ){
		$rtlwert = get_post_meta(  get_the_ID(), 'rtl_switch_active', true );
				
				if($rtlwert === "Yes")
					{
						$title = '<div dir="RTL">'. $title. '</div>';
						return $title;
					}
				else{
					return $title;
				}
		}


	else{ return $title;}
}
add_filter( 'the_title', 'rtl_title', 10, 2 );


function rtl_content( $content) {
	if (   in_the_loop() && is_page()   ){
		$rtlwert = get_post_meta(  get_the_ID(), 'rtl_switch_active', true );
				
				if($rtlwert === "Yes")
					{
						$content = '<div dir="RTL">'. $content. '</div>';
						return $content;
					}
				else{
					return $content;
				}
		}


	else{ return $content;}
}
add_filter( 'the_content', 'rtl_title', 10, 2 );

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
?>
