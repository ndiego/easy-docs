<?php

// Force right sidebar
add_filter( 'genesis_pre_get_option_site_layout', '__genesis_return_content_sidebar' );

// Reposition Page Title
remove_action( 'genesis_entry_header', 'genesis_do_post_title' );
add_action( 'genesis_after_header', 'eds_open_post_title', 1 );
add_action( 'genesis_after_header', 'genesis_do_post_title', 2 );
add_action( 'genesis_after_header', 'eds_close_post_title', 3 );

function eds_open_post_title() {
	global $post;
	
	echo '<div class="page-title"><div class="wrap">';
	
	$categories = get_the_terms( $post->ID, 'eds_category' );
	
	echo '<span class="eds-categories">';

	foreach ( $categories as $category ) { 
		echo $category->name . ' '; 
	}
	
	echo '</span>';

	
}
function eds_close_post_title() {
	echo '</div></div>';
}


add_action( 'genesis_after_header', 'genesis_do_breadcrumbs' );

// Remove Genesis empty widget notice, since we might not actually have any real widgets in the sidebar
remove_action( 'genesis_sidebar', 'genesis_do_sidebar' );

// Print the documentation categories and all their associated docs in the sidebar
//add_action( 'genesis_sidebar', 'eds_print_sidebar', 1 );
function eds_print_sidebar() {

	$current_doc = get_the_title();

	$args = array(
		'orderby'           => 'term_group', 
		'order'             => 'ASC',
		'hide_empty'        => true, 
		'fields'            => 'all', 
		'hierarchical'      => true, 
	); 

	$categories = get_terms( 'eds_category', $args );
	
	foreach( $categories as $category ) {
		
		$args = array(
			'post_type' => 'easy_docs',
			'tax_query' => array(
				array(
					'taxonomy' => 'eds_category',
					'field'    => 'slug',
					'terms'    => $category->slug,
				),
			),
			'orderby'   => 'menu_order', 
			'order'     => 'ASC',
		);
		
		$docs = new WP_Query( $args );
		
		if ( ! empty ( $docs ) ) {
			?>
			<section id="eds_category_<?php echo $category->slug; ?>" class="widget eds-category">
				<div class="widget-wrap">
					<h4 class="widget-title widgettitle"><?php echo $category->name; ?></h4>
					<div class="eds-menu-container">
						<ul class="menu">
							<?php while ( $docs->have_posts() ) : $docs->the_post(); ?>
							<li class="menu-item <?php echo $current_doc == get_the_title() ? 'current_menu_item' : ''; ?>"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></li>
							<?php endwhile; ?>
						</ul>
					</div>
				</div>
			</section>
			<?php
		}
	}
}


add_action( 'genesis_sidebar', 'eds_print_toc', 2 );
function eds_print_toc() {
	?>
	<section class="widget eds-widget eds-toc">
		<div class="widget-wrap">
			<h4 class="widget-title widgettitle"><?php _e( 'Table of Contents', 'easy-docs' ); ?></h4>
			<div class="eds-menu-container">
				<ol class="menu">
				</ol>
			</div>
		</div>
	</section>

	<?php
}


// Print the documentation categories and all their associated docs in the sidebar
add_action( 'genesis_sidebar', 'eds_print_selected_sidebar', 2 );
function eds_print_selected_sidebar() {

	$current_doc = get_the_title();
	
	$categories = get_the_terms( get_the_ID(), 'eds_category' );
	
	foreach( $categories as $category ) {
		
		$args = array(
			'post_type' => 'easy_docs',
			'tax_query' => array(
				array(
					'taxonomy' => 'eds_category',
					'field'    => 'slug',
					'terms'    => $category->slug,
				),
			),
			'orderby'   => 'menu_order', 
			'order'     => 'ASC',
		);
		
		$docs = new WP_Query( $args );
		
		if ( ! empty ( $docs ) ) {
			?>
			<section id="eds_category_<?php echo $category->slug; ?>" class="widget eds-widget eds-category">
				<div class="widget-wrap">
					<h4 class="widget-title widgettitle"><?php echo $category->name; ?></h4>
					<div class="eds-menu-container">
						<ul class="menu">
							<?php while ( $docs->have_posts() ) : $docs->the_post(); ?>
							<li class="menu-item <?php echo $current_doc == get_the_title() ? 'current_menu_item' : ''; ?>"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></li>
							<?php endwhile; ?>
						</ul>
					</div>
					<div class="eds-documentation-home">
						<a href="/documentation"><?php _e( 'All Documentation', 'easy-docs' ); ?></a>
					</div>
				</div>
			</section>
			<?php
		}
	}
}



// Remove the entry header content
remove_action( 'genesis_entry_header', 'genesis_post_info', 12 );

// Remove the entry footer markup and content
remove_action( 'genesis_entry_footer', 'genesis_entry_footer_markup_open', 5 );
remove_action( 'genesis_entry_footer', 'genesis_entry_footer_markup_close', 15 );
remove_action( 'genesis_after_entry', 'genesis_do_author_box_single', 8 );

// Add edit button on admin side
add_action( 'genesis_after_entry', 'eds_edit_button' );

function eds_edit_button() {
	edit_post_link( '(Edit)' );
}

genesis();