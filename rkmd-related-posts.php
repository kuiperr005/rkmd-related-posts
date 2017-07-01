<?php
/*
Plugin Name: RKMD Related Posts Shortcode
Plugin URI: http://www.rkmediadesign.nl/
Description: A simple plugin to add related posts to the main content .
Version: 0.1
Author: Ruben Kuipers
Author URI: http://www.rkmediadesign.nl/
License: GPLv2 or later
Text Domain: rkmd
Domain Path: /languages
*/

// Make sure we don't expose any info if called directly
if ( !defined( 'ABSPATH' ) ){
	exit;
}

define( 'RKMD__VERSION', '0.1' );
define( 'RKMD__PLUGIN_URL', plugin_dir_url( __FILE__ ) );

if( !class_exists( 'RKMDTinymceExample' ) ) {
	class RKMDTinymceExample {

		private static $instance;

		/**
		 * Initiator
		 * @since 0.1
		 */
		public static function init() {
			return self::$instance;
		}

		/**
		 * Constructor
		 * @since 0.1
		 */
		public function __construct($args = array()) {
			add_shortcode( 'rkmd_post', array( $this, 'shortcode_handler' ) );
			if( is_admin() ) {
				add_action( 'admin_footer', array( $this, 'find_posts_div' ) );
				add_action( 'admin_head', array( $this, 'mce_button' ) );
				add_action("admin_print_styles", array( $this, 'plugin_admin_styles' ) );
				add_action("admin_print_scripts", array( $this, 'plugin_admin_scripts' ) );
			}
		}

		function plugin_admin_styles() {
		    wp_enqueue_style('thickbox'); // needed for find posts div
		}
		 
		function plugin_admin_scripts() {
		    wp_enqueue_script('thickbox'); // needed for find posts div
		    wp_enqueue_script('media');
		    wp_enqueue_script('wp-ajax-response');
		}

		/**
		 * shortcode_handler
		 * @param  array  $atts shortcode attributes
		 * @param  string $content shortcode content
		 * @return string
		 */
		function shortcode_handler($atts){
			// Attributes
			$atts = shortcode_atts( array(
				'id' => '',
			), $atts, 'rkmd_post_title' );
			
			$related_post = get_post($atts['id']);
			$related_post_link = get_permalink($atts['id']);
			$related_post_title = get_the_title($atts['id']);

			if($related_post->post_type == 'post'):
				$related_post_type = 'Kennis';
			elseif($related_post->post_type == 'cooktechnique'):
				$related_post_type = 'Video';
			elseif($related_post->post_type == 'receipt'):
				$related_post_type = 'Recept';
			elseif($related_post->post_type == 'course'):
				$related_post_type = 'Cursus';
			else:
				$related_post_type = 'Kennis';
			endif;

			if($related_post->post_type != 'cooktechnique') :
				$raw_related_post_image = wp_get_attachment_image_src( get_post_thumbnail_id( $atts['id'] ), 'thumbnail' );
				$related_post_image = $raw_related_post_image[0];
			else :
				$video_url = get_post_meta($atts['id'], '_cmb2_video_embed_object', true );
				$related_post_image = video_image($video_url);
			endif;

			//start panel markup
			$output = '<article class="related-post '.$related_post->post_type.' post-'.$related_post->ID.'">';
				$output .= '<a href="'.$related_post_link.'" class="row related-post__link" target="_blank">';
					$output .= '<div class="hidden-xs-down col-sm-2 col-md-2 related-post__thumb" style="background-image: url('.$related_post_image.')"></div>';
					$output .= '<div class="col-sm-10 col-md-10 related-post__content">';
						$output .= '<span class="related-post__type">'.$related_post_type.'</span>';
						$output .= '<h4 class="related-post__title">'.$related_post_title.'</h4>';
					$output .= '</div>';
				$output .= '</a>';
			//add closing div tag
			$output .= '</article>';

			//return shortcode output
			return $output;
		}

		// Hooks your functions into the correct filters
		function mce_button() {
			// check user permissions
			if ( !current_user_can( 'edit_posts' ) && !current_user_can( 'edit_pages' ) ) {
				return;
			}
			// check if WYSIWYG is enabled
			if ( 'true' == get_user_option( 'rich_editing' ) ) {
				add_filter( 'mce_external_plugins', array( $this, 'add_mce_plugin' ) );
				add_filter( 'mce_buttons', array( $this, 'register_mce_button' ) );
			}
		}

		// Script for our mce button
		function add_mce_plugin( $plugin_array ) {
			$plugin_array['rkmd_mce_button'] = RKMD__PLUGIN_URL . 'mce.js';
			return $plugin_array;
		}

		// Register our button in the editor
		function register_mce_button( $buttons ) {
			array_push( $buttons, 'rkmd_mce_button' );
			return $buttons;
		}
 		
		/**
		 * Function to output find posts div
		 * @since  1.6
		 * @return string
		 */
		public function find_posts_div() {
			// create nonce
			global $pagenow;
			var_dump($pagenow);
			if( $pagenow != 'admin.php' ){
				$nonce = wp_create_nonce( 'rkmd-nonce' );
				?>

				<form name="plugin_form" id="plugin_form" method="post" action="">
				    <?php wp_nonce_field('rkmd-nonce'); ?> 
				    <?php find_posts_div(); ?>
				</form>
				<?php
			}
		}

		
	} // Mce Class
}

/**
 *  Kicking this off
 */

$rkmd_mce = new RKMDTinymceExample();
$rkmd_mce->init();