<?php
/**
 * Plugin Name: WPSE 279493 Banner Titles!
 * Plugin URI: 	https://wordpress.stackexchange.com/q/279493/7355
 * Description: Add Postmeta For Subtitles
 * Version: 	1.0.0
 */

 
if( ! defined( 'ABSPATH' ) ) {
	exit( "There was a HOLE here. It&#8217;s gone now..." );
}


/**
 * Encapsulation
 */
Class WPSE279493_Banner_Titles {
	
	
	/**
	 * Hold Singleton Object
	 *
	 * @var WPSE279493_Banner_Titles Object instance
	 */
	private static $_instance = null;
	
	
	/**
	 * Hold default post types
	 *
	 * @var Array of post types
	 */
	private $post_types;
	
	
	/**
	 * Singleton Instance
	 *
	 * Return a single instance if asked
	 *
	 * @return WPSE279493_Banner_Titles Object
	 */
	public static function instance() {
		
		if( is_null( self::$_instance ) ) {
			self::$_instance = new self();
			
			// Add Hooks Upon Initial Instantiation
			self::$_instance->add_hooks();
		}
		
		return self::$_instance;
		
	}
	
	
	/**
	 * Plugin Construct
	 *
	 * @return void
	 */
	private function __construct() {
		
		$this->post_types = array( 'page' );	// Default Post Types
		
	}
	
	
	/**
	 * Add in any hooks we may need to run this
	 *
	 * @return void
	 */
	private function add_hooks() {
		
		add_action( 'wpse_banner_title', 	array( $this, 'banner_title' ) 					);	// Display title(s) Action
		add_action( 'edit_form_after_title',array( $this, 'subtitle_metadisplay' )			);	// Setup Subtitle Meta Display
		add_action( 'save_post',			array( $this, 'save_subtitle_metadata' ), 10, 2 );	// Save Subtitle Metadata
		
	}
	
	
	/**
	 * Get the title, possible top most title
	 *
	 * @param Boolean $top - Whether or not to get ancestors title to show, false will be the current page title
	 * @param Boolean $subtitle - If given, will attempt to retrieve custom title from key
	 *
	 * @return String $title - Title of the final page
	 */
	private function get_the_title( $top, $subtitle = false ) {
		
		global $post;
		
		$post_id	= 0;
		$title		= '';
		
		// Grab title from post object if it's accessible
		if( isset( $post ) && is_object( $post ) && is_a( $post, 'WP_Post' ) ) {
			$post_id = $post->ID;
		}
		
		// Blog Page?
		if( is_home() || is_category() || is_tag() || ( is_singular() && is_singular( 'post' ) ) ) {
			$post_id = get_option( 'page_for_posts' );
		}
		
		// Get very top title if asked nicely
		if( $top && ! empty( $post_id ) ) {
			
			$has_parent = get_post_field( 'post_parent', $post_id );
	
			if( ! is_wp_error( $has_parent ) && $has_parent ) {
				$ancestors	= get_post_ancestors( $post_id );
				$root		= count( $ancestors ) - 1;
				$post_id	= $ancestors[ $root ];
			}
			
		}
		
		// Custom Title?
		if( ! empty( $post_id ) ) {
			
			if( ! empty( $subtitle ) ) {
				$title = get_post_meta( $post_id, 'wpse_subtitle', true );
			} else { // No Custom Title
				$title = get_the_title( $post_id );
			}
			
		}
		
		
		/**
		 * Allow the user to modify the title should it be incorrect or otherwise
		 *
		 * @param String $title 		- Possible Post Title
		 * @param Integer $post_id		- Possible WP Post ID
		 *
		 * @return String $title		- Title to wrap / show
		 */
		return apply_filters( 'wpse_modify_banner_title', $title, $post_id );
		
	}
	
	
	/**
	 * Display the banner title
	 *
	 * @return String $html - HTML Content of title
	 */
	public function banner_title( $args = array() ) {
		
		$default_args = array(
			'wrapper'		=> 'h1',		// HTML element to wrap around the title
			'classes'		=> array(),		// Classes attached to HTML wrap
			'attributes'	=> array(),		// Any additional attributes, ex: array( 'data-attr' => 'test' ) = data-attr="test"
			'before_title'	=> '',			// Any text / html before the actual title but inside wrapper
			'after_title'	=> '',			// Any text / html after the actual title but inside wrapper
			'top_title'		=> false,		// Whether or not to get ancestors title to show, false will be the current page title
			'subtitle'		=> false,		// Whether or not to show the subtitle field
		);
		
		$open_tag 	= false;
		$close_tag	= false;
		$atts 		= wp_parse_args( $args, $default_args );
		$title 		= $this->get_the_title( $atts['top_title'], $atts['subtitle'] );
		
		// Could not get title - bail out
		if( empty( $title ) ) {
			return false;
		}
		
		if( ! empty( $atts['wrapper'] ) ) {
			
			$wrapper_classes 	= false;
			$wrapper_attributes	= false;
			
			// Classes Attribute
			if( ! empty( $atts['classes'] ) ) {
				
				if( is_array( $atts['classes'] ) ) {
					$wrapper_classes = ' class="' . implode( ' ', $atts['classes'] ) . '"';
				} else {
					$wrapper_classes = ' class="' . $atts['classes'] . '"';
				}
				
			}
			
			// Any other Attributes
			if( ! empty( $atts['attributes'] ) && is_array( $atts['attributes'] ) ) {

				foreach( $atts['attributes'] as $key => $val ) {
					
					if( is_numeric( $key ) ) { continue; }
					
					$wrapper_attributes = " {$key}=\"{$val}\""; 
					
				}
				
			}
			
			$open_tag 	= sprintf( '<%1$s%2$s%3$s>', $atts['wrapper'], $wrapper_classes, $wrapper_attributes );
			$close_tag 	= sprintf( '</%1$s>', $atts['wrapper'] );
			
		}
		
		// Print title to the screen
		print( $open_tag . $atts['before_title'] . $title . $atts['after_title'] . $close_tag );
		
	}
	
	
	/**
	 * Edit the post-edit form after title input
	 *
	 * @param WP_Post $post - Current post object
	 *
	 * @return void
	 */
	public function subtitle_metadisplay( $post ) {
		
		
		/**
		 * List of acceptable post types to display this metadata on
		 *
		 * @param Array - Array of default post types
		 *
		 * @return Array
		 */
		$acceptable_types = apply_filters( 'wpse_subtitle_post_types', $this->post_types );
		
		// Ensure this post type meets our criteria
		if( empty( $acceptable_types ) || ! in_array( $post->post_type, $acceptable_types ) ) {
			return;
		}
		
		$subtitle = get_post_meta( $post->ID, 'wpse_subtitle', true );
		
		wp_nonce_field( 'wpse279493_subtitle_metadisplay', 'wpse279493_subtitle_metadisplay_field' );
		
		printf( '<input type="text" name="wpse_subtitle" id="wpse_subtitle" class="widefat" value="%1$s" placeholder="%2$s" style="margin:8px 0 0 0;padding:3px 8px;font-size:1.7em;" />',
				esc_attr( $subtitle ),
				__( 'Enter subtitle here' )
		);
		
	}
	
	
	/**
	 * Save the subtitle metadata
	 *
	 * @param Integer $post_id - Current Post ID
	 * @param WP_Post Object $post - Current Post Object
	 *
	 * @return void
	 */
	function save_subtitle_metadata( $post_id, $post ) {
		
		// If we're not in the right place, bailout!
		if( ! isset( $post ) || wp_is_post_autosave( $post ) || wp_is_post_revision( $post ) ) {
			return false;
		}
		
		
		/**
		 * List of acceptable post types to display this metadata on
		 *
		 * @param Array - Array of dfeault post types
		 *
		 * @return Array
		 */
		$acceptable_types = apply_filters( 'wpse_subtitle_post_types', $this->post_types );
		
		// If this isn't an acceptable post type, bailout!
		if( empty( $acceptable_types ) || ! in_array( $post->post_type, $acceptable_types ) ) {
			return;
		}

		// Ensure our nonce is intact
		if( isset( $_POST, $_POST['wpse279493_subtitle_metadisplay_field'] ) && wp_verify_nonce( $_POST['wpse279493_subtitle_metadisplay_field'], 'wpse279493_subtitle_metadisplay' ) ) {
			
			// Save our subtitle metadata OR delete postmeta if left empty
			if( isset( $_POST['wpse_subtitle'] ) && ! empty( $_POST['wpse_subtitle'] ) ) {
				update_post_meta( $post_id, 'wpse_subtitle', sanitize_text_field( $_POST['wpse_subtitle'] ) );
			} else {
				delete_post_meta( $post_id, 'wpse_subtitle' );
			}
			
		}
		
	}
	
	
} // END WPSE279493_Banner_Titles


/**
 * Setup Instance of Plugin
 *
 * @return void
 */
function wpse279493_banner_titles_init() {
	
	WPSE279493_Banner_Titles::instance();
	
}
add_action( 'setup_theme', 'wpse279493_banner_titles_init' );