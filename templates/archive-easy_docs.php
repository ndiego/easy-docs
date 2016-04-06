<?php
// Force full width
add_filter( 'genesis_pre_get_option_site_layout', '__genesis_return_full_width_content' );

// Fix the page header
add_action( 'genesis_after_header', 'eds_archive_title', 1 );
function eds_archive_title() {
	?>
	<div class="page-title">
		<div class="wrap">
			<h1 class="entry-title" itemprop="headline">
				<?php _e( 'Documentation', 'easy_docs' ); ?>
			</h1>
			<?php get_search_form(); ?>
		</div>
	</div>
	<?php
}

// Remove the archive loop...
remove_action( 'genesis_loop', 'genesis_do_loop' );

// Print the documentation categories and all their associated docs
add_action( 'genesis_before_loop', 'eds_print_categories', 1 );
function eds_print_categories() {

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
			<div class="doc-wrapper category-<?php echo $category->slug; ?>">
				<div class="doc-heading">
					<h4><?php echo $category->name; ?></h4>
				</div>
				<div class="doc-body">
					<ul>
						<?php while ( $docs->have_posts() ) : $docs->the_post(); ?>
						<li>
							<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
						</li>
						<?php endwhile; ?>
					</ul>
				</div>
			</div>
			<?php
		}
	}
}

genesis();