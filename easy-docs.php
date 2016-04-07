<?php
/**
 * Plugin Name: Easy Docs
 * Plugin URI:  http://www.outermostdesign.com
 * Description: Easily add a documentation manager to your Wordpress site.
 * Author:      Nick Diego
 * Author URI:  http://www.outermostdesign.com
 * Version:     1.0.0
 * Text Domain: easy-docs
 * Domain Path: languages
 *
 * Easy Docs is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * Easy Docs is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Easy Docs. If not, visit <http://www.gnu.org/licenses/>.
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Main plugin class.
 *
 * @since 1.0.0
 *
 * @package EDS
 * @author  Nick Diego
 */
class Easy_Docs {

    /**
     * Holds the class object.
     *
     * @since 1.0.0
     *
     * @var object
     */
    public static $instance;

    /**
     * Plugin version, used for cache-busting of style and script file references.
     *
     * @since 1.0.0
     *
     * @var string
     */
    public $version = '1.0.0';

    /**
     * The name of the plugin.
     *
     * @since 1.0.0
     *
     * @var string
     */
    public $plugin_name = 'Easy Docs';
    
    /**
     * The unique slug of the plugin.
     *
     * @since 1.0.0
     *
     * @var string
     */
    public $plugin_slug = 'easy-docs';

    /**
     * Plugin file.
     *
     * @since 1.0.0
     *
     * @var string
     */
    public $file = __FILE__;

    /**
     * Primary class constructor.
     *
     * @since 1.0.0
     */
    public function __construct() {

        // Fire a hook before the class is setup.
        do_action( 'eds_pre_init' );

        // Load the plugin textdomain.
        add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );

        // Load the plugin.
        add_action( 'init', array( $this, 'init' ), 0 );

        // Add additional links to the plugin's row on the admin plugin page
        //add_filter( 'plugin_action_links', array( $this, 'plugin_action_links' ), 10, 2 );
		//add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2 );
		
		$this->setup_constants();
    }
    
    
    /**
	 * Setup plugin constants
	 *
	 * @since 1.0.0
	 */
	private function setup_constants() {

		// Plugin version
		if ( ! defined( 'EDS_VERSION' ) ) {
			define( 'EDS_VERSION', '1.0.0' );
		}

		// Plugin Folder Path
		if ( ! defined( 'EDS_PLUGIN_DIR' ) ) {
			define( 'EDS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
		}

		// Plugin Folder URL
		if ( ! defined( 'EDS_PLUGIN_URL' ) ) {
			define( 'EDS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
		}

		// Plugin Root File
		if ( ! defined( 'EDS_PLUGIN_FILE' ) ) {
			define( 'EDS_PLUGIN_FILE', __FILE__ );
		}
	}
	
	
	/**
     * Loads the plugin textdomain for translation.
     *
     * @since 1.0.0
     */
    public function load_textdomain() {

        load_plugin_textdomain( 'easy-docs', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
    }
    

    /**
     * Loads the plugin into WordPress.
     *
     * @since 1.0.0
     */
    public function init() {
    
    	// Run hook once Easy Docs has been initialized.
        do_action( 'eds_init' );

       	// Plugin utility classes
        require EDS_PLUGIN_DIR . 'includes/global/posttype.php';
        
        add_action( 'wp_enqueue_scripts', array( $this, 'frontend_scripts_styles' ) );
        
        add_action( 'wp_ajax_load_search_results', array( $this, 'load_search_results' ) );
		add_action( 'wp_ajax_nopriv_load_search_results', array( $this, 'load_search_results' ) );
		
		add_filter( 'genesis_search_form', array( $this, 'modify_search_form' ), 10, 3 );
		add_action( 'genesis_after_header', array( $this, 'search_results' ) );     
    }
    
    
    /**
     * Loads styles and scripts for our content blocks
     *
     * @since 1.0.0
     */
    public function frontend_scripts_styles() {
		
		if ( is_tax( 'eds_category' ) || get_post_type() == 'easy_docs' ){
			
			wp_register_style( 'eds-frontend-styles', plugins_url( 'assets/css/frontend.css', $this->file ), array() );
			wp_enqueue_style( 'eds-frontend-styles' );
			
			// Ajax stuff for the search form
			wp_enqueue_script( 'ajax-search', plugins_url( 'assets/js/ajax-search.js', $this->file ), array( 'jquery' ), '1.0.0', true );
			wp_localize_script( 'ajax-search', 'SearchTerm', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
			
        }
        
        if ( is_singular() && get_post_type() == 'easy_docs' ){
        
        	wp_enqueue_script( 'single-frontend-scripts', plugins_url( 'assets/js/single.js', $this->file ), array( 'jquery' ), '1.0.0', true );
        }
    }
    


	public function load_search_results() {
		$query = $_POST['query'];

		$args = array(
			'post_type' => 'easy_docs',
			'post_status' => 'publish',
			's' => $query
		);
		$search = new WP_Query( $args );

		ob_start();
		?>
		<h2><?php printf( __( 'Search Results for "%s"', 'easy_docs' ), '<strong>' . $query . '</strong>' ); ?></h2>

			<?php
			if ( $search->have_posts() ) {
				?>
				<div class="search-results-wrapper">
				<?php
				while ( $search->have_posts() ) : $search->the_post();
			
					$categories = get_the_terms( get_the_ID(), 'eds_category' );
					if ( $categories && ! is_wp_error( $categories ) ) {
						$category_array = array();
						foreach ($categories as $category) {
							$category_array[] = $category->name;
						}
						$category_string = join( ", ", $category_array);
					}
				
					?>
					<article class="search-result"> 			
						<h3>
							<span class="categories"><?php echo $category_string; ?><span>
							<a href="<?php the_permalink() ?>"><?php the_title(); ?></a>
						</h3>
						<p><?php echo get_the_excerpt();?></p>
					</article>
					<?php
				endwhile;
				?>
				</div>
				<?php
			} else {
				?>
				<div class="no-search-results">
					Sorry, no results were found. You may need to send in a support ticket...
				</div>
				<?php
			}
		$content = ob_get_clean();

		echo $content;
		die();
		
	}
	
	
	/**
	 * Modify Search form
	 *
	 * @param string search form
	 * @param string search text
	 * @param string button text
	 * @return string modified search form
	 */
	 public function modify_search_form( $form, $search_text, $button_text ) {
		if ( ! ( is_post_type_archive( 'easy_docs' ) || is_singular( 'easy_docs' ) ) ) {
			return $form;
		}	
		
		$search_text = __( 'Search Documentation...', 'easy-docs' );
		$onfocus     = " onfocus=\"if (this.value == '$search_text') {this.value = '';}\"";
		$onblur      = " onblur=\"if (this.value == '') {this.value = '$search_text';}\"";
		$docs_form = '
		<div id="search-container">
			<form>
			<input type="text" placeholder="'. $search_text .'" name="s"'. $onfocus . $onblur .' />
			<input type="hidden" name="post_type" value="easy_docs" />
			<input type="submit" value="'. $button_text .'" />
			</form>
		</div>
		';
		
		return $docs_form;
	}
	
	
	/** 
	 * Add search result div
     *
     * @since 1.0.0
	 */
	public function search_results() {
		if ( is_post_type_archive( 'easy_docs' ) || is_singular( 'easy_docs' ) ) {
			echo '<div id="search_results"></div>';
		}
	}	
    
	
	/**
     * Returns the singleton instance of the class.
     *
     * @since 1.0.0
     *
     * @return object The Easy_Docs object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Easy_Docs ) ) {
            self::$instance = new Easy_Docs();
        }

        return self::$instance;

    }
}

// Load the main plugin class.
$Easy_Docs = Easy_Docs::get_instance();
