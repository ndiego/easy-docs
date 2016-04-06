<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Posttype class.
 *
 * @since 1.0.0
 *
 * @package Easy Docs
 * @author  Nick Diego
 */
class Easy_Docs_Posttype {

    /**
     * Holds the class object.
     *
     * @since 1.0.0
     *
     * @var object
     */
    public static $instance;


    /**
     * Path to the file.
     *
     * @since 1.0.0
     *
     * @var string
     */
    public $file = __FILE__;


    /**
     * Holds the base class object.
     *
     * @since 1.0.0
     *
     * @var object
     */
    public $base;


    /**
     * Primary class constructor.
     *
     * @since 1.0.0
     */
    public function __construct() {

        // Load the base class object.
        $this->base = Easy_Docs::get_instance();
		
		$this->register_easy_docs();
		$this->register_easy_docs_categories();
		
		add_filter( 'single_template', array( $this, 'eds_single_template' ) );
		add_filter( 'archive_template', array( $this, 'eds_archive_template' ) );
		
		add_filter( 'manage_edit-easy_docs_columns', array( $this, 'add_docs_column_header' ) );
		add_filter( 'manage_easy_docs_posts_custom_column', array( $this, 'add_docs_column_value'), 10, 3);
		
		// Setup taxonomy ordering
		add_filter( 'manage_edit-eds_category_columns', array( $this, 'add_column_header' ) );
		add_filter( 'manage_eds_category_custom_column', array( $this, 'add_column_value'), 10, 3);
		add_action( 'eds_category_add_form_fields', array( $this, 'term_group_add_form_field') );
		add_action( 'eds_category_edit_form_fields', array( $this, 'term_group_edit_form_field') );
		
		add_action( 'create_term', array( $this, 'add_edit_term_group') );
		add_action( 'edit_term', array( $this, 'add_edit_term_group') );
		
		add_action( 'quick_edit_custom_box', array( $this, 'quick_edit_term_group' ), 10, 3 );
		
    }
	
	
	public function register_easy_docs() {
	
		$labels = apply_filters( 'easy_docs_post_type_labels',
			array(
				'name'               => __( 'Documentation', 'blox' ),
				'singular_name'      => __( 'Doc', 'blox' ),
				'add_new'            => __( 'Add New', 'blox' ),
				'add_new_item'       => __( 'Add New Doc', 'blox' ),
				'edit_item'          => __( 'Edit Doc', 'blox' ),
				'new_item'           => __( 'New Doc', 'blox' ),
				'view_item'          => __( 'View Doc', 'blox' ),
				'search_items'       => __( 'Search Documentation', 'blox' ),
				'not_found'          => __( 'No documentation found.', 'blox' ),
				'not_found_in_trash' => __( 'No documentation found in trash.', 'blox' ),
				'parent_item_colon'  => '',
				'menu_name'          => __( 'Documentation', 'blox' )
			)
		);

		$args = apply_filters( 'easy_docs_post_type_args',
			array(
				'labels'              => $labels,
				'public'              => true,
				'publicly_queryable'  => true,
				'show_ui'             => true,
				'show_in_menu'        => true,
				'query_var'           => true,
				'show_ui'			  => true,
				'show_in_admin_bar'   => true,
				'rewrite'             => array( 'slug' => 'documentation' ),
				'menu_position'       => 20,
				'menu_icon'           => 'dashicons-book-alt',
				'has_archive'		  => true,
				'hierarchical'		  => true,
				'supports'            => array( 'title', 'editor', 'page-attributes', 'excerpt' ),
				//'taxonomies'		  => array( 'eds_category' )
			)
		);

		// Register the easy_docs post type
		register_post_type( 'easy_docs', $args ); 
	}
	
	
	public function register_easy_docs_categories() {
	
        $labels = array(
			'name'                       => __( 'Categories' ),
			'singular_name'              => __( 'Category' ),
			'search_items'               => __( 'Search Categories' ),
			'popular_items'              => __( 'Popular Categories' ),
			'all_items'                  => __( 'All Categories' ),
			'parent_item'                => null,
			'parent_item_colon'          => null,
			'edit_item'                  => __( 'Edit Category' ),
			'update_item'                => __( 'Update Category' ),
			'add_new_item'               => __( 'Add New Category' ),
			'new_item_name'              => __( 'New Category Name' ),
			'separate_items_with_commas' => __( 'Separate Categories with commas' ),
			'add_or_remove_items'        => __( 'Add or remove Categories' ),
			'choose_from_most_used'      => __( 'Choose from the most used Categories' ),
			'not_found'                  => __( 'No Categories found.' ),
			'menu_name'                  => __( 'Categories' ),
		);

		$args = array(
			'hierarchical'          => true,
			'labels'                => $labels,
			'show_ui'               => true,
			'show_admin_column'     => true,
			'update_count_callback' => '_update_post_term_count',
			'query_var'             => true,
			'has_archive'           => false,
			'rewrite'               => array( 'slug' => 'eds_category' ),
		);
		
		// Register category taxonomy
		register_taxonomy( 'eds_category', 'easy_docs', $args );
	}
	
	
	public function eds_archive_template( $archive_template ) {
		 global $post;

		 if ( is_post_type_archive ( 'easy_docs' ) ) {
		 	if ( file_exists( EDS_PLUGIN_DIR .  'templates/archive-easy_docs.php' ) ) {
				$archive_template = EDS_PLUGIN_DIR . 'templates/archive-easy_docs.php';
			}
		 }
		 return $archive_template;
	}


	public function eds_single_template( $single_template ) {
		global $post;

		if ($post->post_type == 'easy_docs'){
			if ( file_exists( EDS_PLUGIN_DIR .  'templates/single-easy_docs.php' ) ) {
				$single_template = EDS_PLUGIN_DIR . 'templates/single-easy_docs.php';
			}
		}
		return $single_template;
	}
	
	
	public function add_docs_column_header( $columns ) {
		
		$new_columns = array();
	  	
	  	// Specify where we want to put our column
  		foreach( $columns as $key => $title ) {
    		if ( $key=='date' ) { 
      			$new_columns['menu_order'] = __( 'Order', 'easy-docs' );
      		}
    		$new_columns[$key] = $title;
  		}
  		return $new_columns;
	}
	
	public function add_docs_column_value( $column_name, $post_ID ) {
		if ( $column_name == 'menu_order' ) {

			$order = get_post_field( 'menu_order', $post_ID);

			if ( ! empty( $order ) ) {
				echo $order;
			} else {
			    echo '<span aria-hidden="true">—</span>';
			}
		}
	}


	public function add_column_header( $columns ) {
		
		$columns['term_group'] = __( 'Order', 'easy-docs' );
		return $columns;
	}
	
	public function add_column_value( $empty = '', $column, $term_id ) {
		
		$term = get_term( $term_id, 'eds_category' );
		
		// Here $column is equal to term_group
		return $term->$column;
	}
	
	
	public function add_edit_term_group( $term_id ) {
		
		global $wpdb;
		
		if ( isset($_POST['term_group_order'] ) ) {
			
			$wpdb->update( $wpdb->terms, array( 'term_group' => $_POST['term_group_order'] ), array( 'term_id' => $term_id ) );
		}
	}
	
	public function term_group_add_form_field() {
		
		$form_field = '<div class="form-field"><label for="term_group_order">' . __( 'Order', 'easy-docs' ) . '</label><input name="term_group_order" id="term_group_order" type="text" value="0" style="width:5em" /><p>' . __( 'Choose the category order. This is the order in which the categories will be displayed on the frontend.', 'easy-docs' ) . '</p></div>';
		
		echo $form_field;
	}
	
	public function term_group_edit_form_field( $term ) {
		
		$form_field = '<tr class="form-field"><th scope="row" valign="top"><label for="term_group_order">' . __( 'Order', 'easy-docs' )  . '</label></th><td><input name="term_group_order" id="term_group_order" type="text" value="' . $term->term_group . '" style="width:5em" /><p class="description">' . __( 'Choose the category order. This is the order in which the categories will be displayed on the frontend.', 'easy-docs' ) .'</p></td></tr>';
		
		echo $form_field;
	}
	
	public function quick_edit_term_group() {
		
		$term_group_field = '<fieldset><div class="inline-edit-col"><label><span class="title">' . __( 'Order', 'easy-docs' ) . '</span><span class="input-text-wrap"><input class="ptitle" name="term_group_order" type="text" value="" /></span></label></div></fieldset>';		
		echo $term_group_field;
	}




    /**
     * Returns the singleton instance of the class.
     *
     * @since 1.0.0
     *
     * @return object The Easy_Docs_Posttype object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Easy_Docs_Posttype ) ) {
            self::$instance = new Easy_Docs_Posttype();
        }

        return self::$instance;
    }
}

// Load the posttype class.
$Easy_Docs_Posttype = Easy_Docs_Posttype::get_instance();