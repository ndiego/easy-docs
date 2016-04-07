jQuery(document).ready(function($){
	
	// Fine for personal use but need error checking for public release
	$( '.single-easy_docs .content h2' ).each(function() {

		title = $(this).text();
	  	slug  = 'eds_' + title.toLowerCase().replace(/ /g,"_")
	  
	  	$(this).attr( 'id', slug );
	  	
	  	link = '<li><a href="#' + slug + '">' + title + '</a></li>';
	  	
	  	$( '.single-easy_docs .eds-toc ol' ).append(link);

	});

});