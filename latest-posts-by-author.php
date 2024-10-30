<?php

/*
Plugin Name: Latest Posts by Author
Plugin URI: http://wordpress.org/extend/plugins/latest-posts-by-author/
Description: Displays a list of recent posts by the specified author
Author: Alex Mansfield
Version: 0.9
Author URI: http://alexmansfield.com/
*/

function latest_posts_by_author( $array ) {
	global $post;

	$defaults = array(
		 'author' => '',
		 'show' => 5,
		 'excerpt' => 'false',
		 'post_type' => 'post'
	);

	// Sets default author if in loop or on single page.	
	if( in_the_loop() || is_single() ){
		$author_id=$post->post_author;
		$defaults['author'] = get_the_author_meta( 'user_login', $author_id );
	}
	
	// Overrides defaults with shortcode settings and separates into individual varaibles.
	extract( shortcode_atts( $defaults, $array ) );

	// Checks to make sure an author has been set.
	if( !empty( $author ) ){
		
		// Checks to see if there are multiple authors set.
		$comma = strpos( $author, ',' );
		if( $comma === false ) {
			
			// Gets the author data for a single author.
			$author_data = get_user_by( 'login', $author );
			if( !empty( $author_data ) ) {
				$args = array(
					'author' => $author_data->ID,
					'posts_per_page' => $show,
					'post__not_in' => array($post->ID),
					'post_type' => $post_type
				);
			}

		} else {
			
			// Gets the author data for multiple authors.
			$authors = explode( ',', $author  );
			$author_data = '';
			foreach( $authors as $author_login ){
				$user = get_user_by( 'login', $author_login );
				$author_data .= $user->ID . ',';
			}

			$args = array(
				'author' => $author_data,
				'posts_per_page' => $show,
				'post__not_in' => array($post->ID)
			);
		}
		
		// Gets posts form database
		$author_query = new WP_Query( $args );
	
		// Displays posts if available
		if( $author_query ) {
			$html = '';
			$html = apply_filters( 'latestbyauthor_list_before', $html );
			$html .= '<ul class="latestbyauthor">';
			while ( $author_query->have_posts() ) : $author_query->the_post();
				$html .= '<li>';
				$html = apply_filters( 'latestbyauthor_link_before', $html );
				
				// Displays a link to the post, using the post title
				$html .= '<a href="' . get_permalink() . '" title="' . get_the_title() . '">';
				$html = apply_filters( 'latestbyauthor_title_before', $html );
				$html .= apply_filters( 'latestbyauthor_title', get_the_title() );
				$html = apply_filters( 'latestbyauthor_title_after', $html );
				$html .= '</a>';
				$html = apply_filters( 'latestbyauthor_link_after', $html );
				
				// Displays the post excerpt if "excerpt" has been set to true
				if($excerpt == 'true'){
	         		$html .= '<p>' . apply_filters( 'latestbyauthor_excerpt', get_the_excerpt() ) . '</p>';
	      		}

				$html .= '</li>';
			endwhile;
			$html .= '</ul>';
			$html = apply_filters( 'latestbyauthor_list_after', $html );
		}
		
		// Resets Post Data
		wp_reset_postdata();
	 
	 	// Returns the results
	   return $html;
	}

}
add_shortcode('latestbyauthor', 'latest_posts_by_author');
