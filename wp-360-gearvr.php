<?php
/*
 * Plugin Name: 360 Photos in GearVR
 * Version: 1.0
 * Plugin URI: http://mdt.blog
 * Description: Display 360 photos when viewing a post or page using GearVR Samsung Internet browser. Only works with spherical single images.
 * Author: Michael Tieso
 * Author URI: http://mdt.blog
 * Requires at least: 4.7
 * Tested up to: 4.7
 *
 * Text Domain: wp-360-gearvr
 * Domain Path: /lang/
 *
 * @package WordPress
 * @author Michael Tieso
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function wpdocs_theme_name_scripts() {
	wp_enqueue_script('360gearvr', plugin_dir_url(__FILE__) . '360gearvr.js');
}
add_action( 'wp_enqueue_scripts', 'wpdocs_theme_name_scripts' );

function gearvr360_meta_box() {

	$screens = array( 'post', 'page' );

	foreach ( $screens as $screen ) {
		add_meta_box(
			'wp360gearvr',
			__( '360 Image URL', 'sitepoint' ),
			'gearvr360_meta_box_callback',
			$screen
		);
	}
}

add_action( 'add_meta_boxes', 'gearvr360_meta_box' );

function gearvr360_meta_box_callback( $post ) {

    // Add a nonce field so we can check for it later.
	wp_nonce_field( 'wpgearvr360_nonce', 'wpgearvr360_nonce' );

	$value = get_post_meta( $post->ID, '_360img', true );

	echo '<textarea style="width:100%" id="wpgearvr360" name="wpgearvr360">' . esc_attr( $value ) . '</textarea>';
}

/**
 * When the post is saved, saves our custom data.
 *
 * @param int $post_id
 */
function save_360img_meta_box_data( $post_id ) {

	// Check if our nonce is set.
	if ( ! isset( $_POST['wpgearvr360_nonce'] ) ) {
		return;
	}

	// Verify that the nonce is valid.
	if ( ! wp_verify_nonce( $_POST['wpgearvr360_nonce'], 'wpgearvr360_nonce' ) ) {
		return;
	}

	// If this is an autosave, our form has not been submitted, so we don't want to do anything.
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	// Check the user's permissions.
	if ( isset( $_POST['post_type'] ) && 'page' == $_POST['post_type'] ) {

		if ( ! current_user_can( 'edit_page', $post_id ) ) {
			return;
		}

	}
	else {

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

	}

	/* OK, it's safe for us to save the data now. */

	// Make sure that it is set.
	if ( ! isset( $_POST['wpgearvr360'] ) ) {
		return;
	}

	// Sanitize user input.
	$my_data = sanitize_text_field( $_POST['wpgearvr360'] );

	// Update the meta field in the database.
	update_post_meta( $post_id, '_360img', $my_data );
}

add_action( 'save_post', 'save_360img_meta_box_data' );

add_action('wp_head', 'wp_360_gearvr_function');

	function wp_360_gearvr_function(){
		global $post;
		$wp_360_gearvr_img =esc_attr( get_post_meta( $post->ID, '_360img', true ) );

		?>
		<script>
			if ('SamsungChangeSky' in window) {
				window.SamsungChangeSky(
					{ sphere: '<?php echo $wp_360_gearvr_img ?>' }
				);
			}
		</script>
	<?php
};

?>