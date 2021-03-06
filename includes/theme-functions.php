<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * SDS Theme Options Functions
 *
 * Description: This file contains functions for utilizing options within themes (displaying site logo, tagline, etc...)
 *
 * @version 1.2.1
 */


// Globalize Theme options
$sds_theme_options = SDS_Theme_Options::get_sds_theme_options();


/***********************
 * Pluggable Functions *
 ***********************/

/**
 * This function displays either a logo, or the site title depending on options.
 *
 * @uses site_url()
 * @uses get_bloginfo()
 * @uses wp_get_attachment_image()
 * @uses bloginfo()
 */
if ( ! function_exists( 'sds_logo' ) ) {
	function sds_logo() {
		global $sds_theme_options;

		// Logo
		if ( ! empty( $sds_theme_options['logo_attachment_id'] ) ) :
	?>
		<h1 id="title" class="site-title site-title-logo has-logo">
			<a href="<?php echo esc_url( home_url() ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
				<?php echo wp_get_attachment_image( $sds_theme_options['logo_attachment_id'], 'full' ); ?>
			</a>
		</h1>
	<?php
		// No logo
		else :
	?>
		<h1 id="title" class="site-title site-title-no-logo no-logo">
			<a href="<?php echo esc_url( home_url() ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
				<?php bloginfo( 'name' ); ?>
			</a>
		</h1>
	<?php
		endif;
	}
}

/**
 * This function displays the site tagline, optionally with a CSS class to hide it depending on options.
 *
 * @uses bloginfo()
 */
if ( ! function_exists( 'sds_tagline' ) ) {
	function sds_tagline() {
		global $sds_theme_options;
	?>
		<h2 id="slogan" class="slogan <?php echo ( $sds_theme_options['hide_tagline'] ) ? 'hide hidden hide-tagline hide-slogan' : false; ?>">
			<?php bloginfo( 'description' ); ?>
		</h2>
	<?php
	}
}

/**
 * This function displays featured images based on options.
 *
 * @param $link_image, Boolean, link featured image to post.
 *
 * @uses the_permalink()
 * @uses has_post_thumbnail()
 * @uses the_post_thumbnail()
 */
if ( ! function_exists( 'sds_featured_image' ) ) {
	function sds_featured_image( $link_image = false, $size = false ) {	
		// Allow size to be over-written by function call
		if ( $size )
			$featured_image_size = $size;
		else
			$featured_image_size = apply_filters( 'sds_theme_options_default_featured_image_size', '' );

		$featured_image_size = apply_filters( 'sds_featured_image_size', $featured_image_size, $link_image );

		// Featured Image
		if ( has_post_thumbnail() && $link_image ) :
	?>
		<figure class="post-image <?php echo $featured_image_size . '-featured-image ' . $featured_image_size . '-post-image'; ?>">
			<a href="<?php the_permalink(); ?>">
				<?php the_post_thumbnail( $featured_image_size ); ?>
			</a>
		</figure>
	<?php
		elseif ( has_post_thumbnail() ) :
	?>
		<figure class="post-image <?php echo $featured_image_size . '-featured-image ' . $featured_image_size . '-post-image'; ?>">
			<?php the_post_thumbnail( $featured_image_size ); ?>
		</figure>
	<?php
		endif;
	}
}

/**
 * This function adds the current site name after the title in the <head> section.
 */
if ( ! function_exists( 'sds_wp_title' ) ) {
	add_filter( 'wp_title', 'sds_wp_title' );

	function sds_wp_title( $title ) {
		// Ignore on feeds
		if ( ! is_feed() )
			$title .= get_bloginfo( 'name' );

		return $title;
	}
}

/**
 * This function outputs a fallback menu and is used when the Primary Menu is inactive.
 */
if ( ! function_exists( 'sds_primary_menu_fallback' ) ) {
	function sds_primary_menu_fallback() {
		wp_page_menu( array(
			'menu_class'  => 'primary-nav menu',
			'echo'        => true,
			'show_home'   => true,
			'link_before' => '',
			'link_after'  => ''
		) );
	}
}


/**
 * This function outputs a sitemap (most typically found on a 404 template).
 */
if ( ! function_exists( 'sds_sitemap' ) ) {
	function sds_sitemap() {
	?>
		<section class="sds-sitemap sitemap">
			<section class="sitemap-pages page-list">
				<h2 title="<?php esc_attr_e( 'Pages', 'capture' ); ?>"><?php _e( 'Pages', 'capture' ); ?></h2>
				<ul>
					<?php wp_list_pages( array( 'title_li' => '' ) ); ?>
				</ul>
			</section>

			<section class="sitemap-archives sitemap-monthly-archives monthly-archives archive-list">
				<h2 title="<?php esc_attr_e( 'Monthly Archives', 'capture' ); ?>"><?php _e( 'Monthly Archives', 'capture' ); ?></h2>
				<ul>
					<?php wp_get_archives(); ?>
				</ul>
			</section>

			<section class="sitemap-categories category-list">
				<h2 title="<?php esc_attr_e( 'Blog Categories', 'capture' ); ?>"><?php _e( 'Blog Categories', 'capture' ); ?></h2>
				<ul>
					<?php wp_list_categories( array( 'title_li' => '' ) ); ?>
				</ul>
			</section>


			<?php
				// Output all public post types except attachments and pages (see above for pages)
				foreach( get_post_types( array( 'public' => true ) ) as $post_type ) {
					if ( ! in_array( $post_type, array( 'attachment', 'page' ) ) ) {
					$post_type_object = get_post_type_object( $post_type );

					$query = new WP_Query( array(
						'post_type' => $post_type,
						'posts_per_page' => wp_count_posts( $post_type )->publish
					) );

					if( $query->have_posts() ) :
					?>
						<section class="sitemap-post-type-list sitemap-<?php echo $post_type_object->name; ?>-list post-type-list">
							<h2 title="<?php echo esc_attr( $post_type_object->labels->name ); ?>">
								<?php echo $post_type_object->labels->name; ?>
							</h2>

							<ul>
								<?php while( $query->have_posts() ) : $query->the_post(); ?>
									<li>
										<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
									</li>
								<?php endwhile; ?>
							</ul>
						</section>
					<?php
					endif;
				}
			}
			?>
		</section>
	<?php
	}
}

/**
 * This function outputs a title for Archive page templates.
 */
if ( ! function_exists( 'sds_archive_title' ) ) {
	function sds_archive_title() {
		// Author
		if ( is_author() ) :
			$author = get_user_by( 'slug', get_query_var( 'author_name' ) ); // Get user data by slug with value of author_name in query
		?>
			<h1 title="<?php esc_attr_e( 'Author Archive:', 'capture' ); ?> <?php echo ( $author instanceof WP_User ) ? $author->display_name : false; ?>" class="page-title author-archive-title">
				<?php _e( 'Author Archive:', 'capture' ); ?> <?php echo ( $author instanceof WP_User ) ? $author->display_name : false; ?>
			</h1>
		<?php
		// Categories
		elseif ( is_category() ) :
		?>
			<h1 title="<?php single_cat_title( __( 'Category Archive: ', 'capture' ) ); ?>" class="page-title category-archive-title">
				<?php single_cat_title( __( 'Category Archive: ', 'capture' ) ); ?>
			</h1>
		<?php 
		// Tags
		elseif ( is_tag() ) :
		?>
			<h1 title="<?php single_tag_title( __( 'Tag Archive: ', 'capture' ) ); ?>" class="page-title tag-archive-title">
				<?php single_tag_title( __( 'Tag Archive: ', 'capture' ) ); ?>
			</h1>
		<?php
		// Daily Archives
		elseif ( is_day() ) :
			$the_date = get_the_date();
		?>
			<h1 title="<?php esc_attr_e( 'Daily Archives:', 'capture' ); ?> <?php echo $the_date; ?>" class="page-title day-archive-title daily-archive-title">
				<?php _e( 'Daily Archives:', 'capture' ); ?> <?php echo $the_date; ?>
			</h1>
		<?php
		// Monthly Archives
		elseif ( is_month() ) :
			$the_date = get_the_date( 'F Y' );
		?>
			<h1 title="<?php esc_attr_e( 'Monthly Archives:', 'capture' ); ?> <?php echo $the_date; ?>" class="page-title month-archive-title monthly-archive-title">
				<?php _e( 'Monthly Archives:', 'capture' ); ?> <?php echo $the_date; ?>
			</h1>
		<?php
		// Yearly Archives
		elseif ( is_year() ) :
			$the_date = get_the_date( 'Y' );
		?>
			<h1 title="<?php esc_attr_e( 'Yearly Archives:', 'capture' ); ?> <?php echo $the_date; ?>" class="page-title year-archive-title yearly-archive-title">
				<?php _e( 'Yearly Archives:', 'capture' ); ?> <?php echo $the_date; ?>
			</h1>
		<?php
		endif;
	}
}

/**
 * This function outputs a "no posts" message when no posts are found in a The Loop.
 */
if ( ! function_exists( 'sds_no_posts' ) ) {
	function sds_no_posts() {
	?>
		<section class="no-results no-posts">
			<p><?php _e( 'We were not able to find any posts. Please try again.', 'capture' ); ?></p>
		</section>
	<?php
	}
}

/**
 * This function outputs next/prev navigation on single posts.
 */
if ( ! function_exists( 'sds_single_post_navigation' ) ) {
	function sds_single_post_navigation() {
	?>
		<section class="single-post-navigation post-navigation">
			<section class="previous-posts">
				<?php next_post_link( '%link', '&laquo; %title' ); ?>
			</section>
			<section class="next-posts">
				<?php previous_post_link( '%link', '%title &raquo;' ); ?>
			</section>
		</section>
	<?php
	}
}

/**
 * This function outputs next/prev navigation on single image attachments.
 */
if ( ! function_exists( 'sds_single_image_navigation' ) ) {
	function sds_single_image_navigation() {
	?>
		<section class="single-post-navigation post-navigation single-image-navigation image-navigation">
			<section class="previous-posts">
				<?php previous_image_link( false, '&laquo; Previous Image' ); ?>
			</section>
			<section class="next-posts">
				<?php next_image_link( false, 'Next Image &raquo;' ); ?>
			</section>
		</section>
	<?php
	}
}

/**
 * This function outputs the site's copyright as well as the SDS copyright.
 */
if ( ! function_exists( 'sds_copyright' ) ) {
	function sds_copyright( $theme_name ) {
	?>
		<span class="site-copyright">
			<?php echo apply_filters( 'sds_copyright', 'Copyright &copy; ' . date( 'Y' ) . ' <a href="' . esc_url( home_url() ) . '">' . get_bloginfo( 'name' ) . '</a>. Все права защищены.' ); ?>
		</span>
		<span class="slocum-credit">
			<?php echo apply_filters( 'sds_copyright_branding', '<a href="http://slocumthemes.com/" target="_blank">' . $theme_name . ' by Slocum Studio</a>', $theme_name ); ?>
		</span>
	<?php
	}
}

/**
 * This function outputs a list of selected social networks based on options. Can be called throughout the theme and is used in the Social Media Widget.
 */
if ( ! function_exists( 'sds_social_media' ) ) {
	function sds_social_media() {
		global $sds_theme_options;

		if ( ! empty( $sds_theme_options['social_media'] ) ) {
			// Map the correct values for social icon display (FontAwesome webfont, i.e. 'fa-rss' = RSS icon)
			$social_font_map = array(
				'facebook_url' => 'fa fa-facebook',
				'twitter_url' => 'fa fa-twitter',
				'linkedin_url' => 'fa fa-linkedin',
				'google_plus_url' => 'fa fa-google-plus',
				'youtube_url' => 'fa fa-youtube',
				'vimeo_url' => 'fa fa-vimeo-square', // previously fa-play
				'pinterest_url' => 'fa fa-pinterest',
				'instagram_url' => 'fa fa-instagram',
				'flickr_url' => 'fa fa-flickr',
				//'yelp_url' => '',
				'foursquare_url' => 'fa fa-foursquare',
				'rss_url' => 'fa fa-rss'
			);

			$social_font_map = apply_filters( 'sds_social_icon_map', $social_font_map );
		?>
			<section class="social-media-icons">
			<?php
				foreach( $sds_theme_options['social_media'] as $key => $url ) :
					// RSS (use site RSS feed, $url is Boolean this case)
					if ( $key === 'rss_url_use_site_feed' && $url ) :
					?>
						<a href="<?php bloginfo( 'rss2_url' ); ?>" class="rss_url <?php echo $social_font_map['rss_url']; ?>" target="_blank"></a>
					<?php
					// RSS (use custom RSS feed)
					elseif ( $key === 'rss_url' && ! $sds_theme_options['social_media']['rss_url_use_site_feed'] && ! empty( $url ) ) :
					?>
						<a href="<?php echo esc_attr( $url ); ?>" class="rss_url <?php echo $social_font_map['rss_url']; ?>" target="_blank"></a>
					<?php
					// All other networks
					elseif ( $key !== 'rss_url_use_site_feed' && $key !== 'rss_url' && ! empty( $url ) ) :
					?>
						<a href="<?php echo esc_url( $url ); ?>" class="<?php echo $key; ?> <?php echo $social_font_map[$key]; ?>" target="_blank" rel="me"></a>
					<?php
					endif;
				endforeach;
			?>
			</section>
		<?php
		}
	}
}

/**
 * This function displays meta for the current post (including categories and tags).
 */
if ( ! function_exists( 'sds_post_meta' ) ) {
	function sds_post_meta() {
		$cats = get_the_category();
		$tags = get_the_tags();

		// Categories and tags
		if ( $cats && $tags ):
		?>
			<p>
			<?php
				printf( __( 'Категория: %1$s Метки: %2$s.', 'capture' ),
				get_the_category_list( ', ', 'single' ),
				get_the_tag_list( '', ', ' ) );
			?>
			</p>
		<?php
		// Categories and no tags
		elseif ( $cats && ! $tags ) :
		?>
			<p>
			<?php
				printf( __( 'Категория: %1$s.', 'capture' ),
				get_the_category_list( ', ', 'single' ) );
			?>
			</p>
		<?php
		// Tags and no categories
		elseif ( $tags && ! $cats ) :
		?>
			<p>
			<?php
				printf( __( 'Метки: %1$s.', 'capture' ),
				get_the_tag_list( '', ', ' ) );
			?>
			</p>
		<?php
		endif;
	}
}


/*
 * This function displays pagination links based on arguments
 * @uses paginate_links for output
 */
if ( ! function_exists( 'sds_post_navigation' ) ) {
	function sds_post_navigation( $return = false ) {
		global $wp_query;

		$pagination_links = paginate_links( array(
			'base' => esc_url( get_pagenum_link() ) . '%_%', // %_% will be replaced with format below
			'format' => ( ( get_option( 'permalink_structure' ) && ! $wp_query->is_search ) || ( is_home() && get_option( 'show_on_front' ) !== 'page' && ! get_option( 'page_on_front' ) ) ) ? '?paged=%#%' : '&paged=%#%', // %#% will be replaced with page number
			'current' => max( 1, get_query_var( 'paged' ) ), // Get whichever is the max out of 1 and the current page count
			'total' => $wp_query->max_num_pages, // Get total number of pages in current query
			'next_text' => 'Вперед &#8594;',
			'prev_text' => '&#8592; Назад',
			'type' => ( $return ) ? 'array' : 'Последнее'  // Output this as an array or unordered list
		) );

		if( $return )
			return $pagination_links;
		else
			echo $pagination_links;
	}
}

/**
 * Template for comments and pingbacks.
 *
 * Used as a callback by wp_list_comments() for displaying the comments.
 *
 * @param object $comment Comment to display.
 * @param array $args Optional args.
 * @param int $depth Depth of comment.
 */
if ( ! function_exists( 'sds_comment' ) ) {
	function sds_comment( $comment, $args, $depth ) {
		$GLOBALS['comment'] = $comment;
		switch ( $comment->comment_type ) :
			case 'pingback' :
			case 'trackback' :
			// Display trackbacks differently than normal comments.
		?>
		<li id="comment-<?php comment_ID(); ?>" <?php comment_class(); ?>>
			<p><?php _e( 'Pingback:', 'capture' ); ?> <?php comment_author_link(); ?> <?php edit_comment_link( 'Редактировать', '<span class="ping-meta"><span class="edit-link">', '</span></span>' ); ?></p>
		</li>
		<?php
			break;
			default :
			// Proceed with normal comments.
		?>
		<li id="li-comment-<?php comment_ID(); ?>">
			<article id="comment-<?php comment_ID(); ?>" <?php comment_class(); ?>>
				<section class="comment-author vcard">
					<section class="author-details">
						<?php echo get_avatar( $comment, 74 ); ?>
						<span class="author-link"><?php comment_author_link(); ?></span>
						<br />
						<header class="comment-meta">
							<cite class="fn">
								<?php
									printf( __( '<a href="%1$s"><time datetime="%2$s" itemprop="commentTime">%3$s</time></a>', 'capture' ),
										esc_url( get_comment_link( $comment->comment_ID ) ),
										get_comment_time( 'c' ),
										sprintf( __( '%1$s в %2$s', 'capture' ), get_comment_date(), get_comment_time() )
									);
								?>

								<?php edit_comment_link( __( 'Редактировать', 'capture' ), '<span class="edit-link">', '<span>' ); ?>
							</cite>
						</header>
					</section>
				</section>

				<section class="comment-content-container">
					<?php if ( $comment->comment_approved == '0' ) : ?>
						<p class="comment-awaiting-moderation">Спасибо! Ваш комментарий ожидает проверки.</p>
					<?php endif; ?>

					<section class="comment-content">
						<?php comment_text(); ?>
					</section>
				</section>

				<section class="clear">&nbsp;</section>

				<section class="reply">
					<?php comment_reply_link( array_merge( $args, array( 'reply_text' => __( 'Ответ', 'capture' ), 'depth' => $depth, 'max_depth' => $args['max_depth'] ) ) ); ?>
				</section>
			</article>
		</li>
		<?php
			break;
		endswitch;
	}
}

/***************************
 * Non-Pluggable Functions *
 ***************************/

/**
 * This function ties into the TGM Plugin Activation Class and recommends plugins to the user.
 */
add_action( 'tgmpa_register', 'sds_tgmpa_register' );

function sds_tgmpa_register() {
	$plugins = array( 
        // Soliloquy
        array(
            'name'      => 'Soliloquy Lite',
            'slug'      => 'soliloquy-lite',
            'required'  => false
        )
	);

	$plugins = apply_filters( 'sds_tgmpa_plugins', $plugins );

	tgmpa( $plugins );
}

/**
 * This function enqueues all necessary scripts/styles based on options.
 */
add_action( 'wp_enqueue_scripts', 'sds_wp_enqueue_scripts' );

function sds_wp_enqueue_scripts() {
	global $sds_theme_options;

	// Color Schemes
	if ( function_exists( 'sds_color_schemes' ) && ! empty( $sds_theme_options['color_scheme'] ) ) {
		$color_schemes = sds_color_schemes();

		if ( ! empty( $sds_theme_options['color_scheme'] ) && isset( $color_schemes[$sds_theme_options['color_scheme']] ) ) {
			$selected_color_scheme = array_key_exists( $sds_theme_options['color_scheme'], $color_schemes ) ? $color_schemes[$sds_theme_options['color_scheme']] : false;

			// Make sure this is not the default color scheme
			if ( ! empty( $selected_color_scheme ) && ( ! isset( $selected_color_scheme['default'] ) || ! $selected_color_scheme['default'] ) )
				wp_enqueue_style( $selected_color_scheme['deps'] . '-' . $sds_theme_options['color_scheme'], get_template_directory_uri() . $selected_color_scheme['stylesheet'], array( $selected_color_scheme['deps'] ) );
		}
	}

	// Web Fonts
	if ( function_exists( 'sds_web_fonts' ) && ! empty( $sds_theme_options['web_font'] ) ) {
		$web_fonts = sds_web_fonts();
		$protocol = is_ssl() ? 'https' : 'http';

		if ( ! empty( $sds_theme_options['web_font'] ) )
			wp_enqueue_style( 'sds-google-web-font', $protocol . '://fonts.googleapis.com/css?family=' . $sds_theme_options['web_font'] );
	}

	// Theme Option Fonts (Social Media)
	if ( ! empty( $sds_theme_options['social_media'] ) ) {
		$social_networks_active = false;

		foreach( $sds_theme_options['social_media'] as $network => $url )
			if ( ! empty( $url ) ) {
				$social_networks_active = true;
				break;
			}

		if ( $social_networks_active )
			wp_enqueue_style( 'font-awesome-css-min', get_template_directory_uri() . '/includes/css/font-awesome.min.css' );
	}

	// Comment Replies
	if ( is_singular() )
		wp_enqueue_script( 'comment-reply' );
}

/**
 * This function outputs necessary scripts/styles in the head section based on options (web font, custom scripts/styles).
 */
add_action( 'wp_head', 'sds_wp_head' );

function sds_wp_head() {
	global $sds_theme_options, $is_IE;

	// Web Fonts
	if ( function_exists( 'sds_web_fonts' ) && ! empty( $sds_theme_options['web_font'] ) ) :
		$web_fonts = sds_web_fonts();
		$selected_web_font = array_key_exists( $sds_theme_options['web_font'], $web_fonts ) ? $web_fonts[$sds_theme_options['web_font']] : false;

		if ( ! empty( $selected_web_font ) && isset( $selected_web_font['css'] ) ) :
		?>
			<style type="text/css">
				html, body {
					<?php echo $selected_web_font['css']; ?>
				}
			</style>
		<?php
		endif;
	endif;

	// HTML5 Shiv (IE only, conditionally for less than IE9)
	if ( $is_IE )
		echo '<!--[if lt IE 9]><script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script><![endif]-->';
}

/**
 * This function outputs the necessary CSS classes in the body_class() function based on content layout settings.
 */
add_filter( 'body_class', 'sds_body_class' );

function sds_body_class( $classes ) {
	global $sds_theme_options, $post;

	// If theme supports content layouts
	if ( function_exists( 'sds_content_layouts' ) ) {
		// If single page, determine if specific page template is set
		$wp_page_template = ( is_page() ) ? get_post_meta( $post->ID, '_wp_page_template', true ) : false;
		$sds_theme_options['page_template'] = $wp_page_template;

		// Global
		if ( ! empty( $sds_theme_options['content_layouts']['global'] ) ) {
			$sds_theme_options['body_class'] = $classes['sds-content-layout'] = $sds_theme_options['content_layouts']['global'];

			// Remove content layout styles if a page template is selected
			if ( ! empty( $wp_page_template ) && $wp_page_template !== 'default' ) {
				unset( $sds_theme_options['body_class'] );
				unset( $classes['sds-content-layout'] );
			}
		}

		// 404 Error
		if ( is_404() && ! empty( $sds_theme_options['content_layouts']['404'] ) )
			$sds_theme_options['body_class'] = $classes['sds-content-layout'] = $sds_theme_options['content_layouts']['404'];

		// Single Post
		if ( is_single() && ! empty( $sds_theme_options['content_layouts']['single'] ) )
			$sds_theme_options['body_class'] = $classes['sds-content-layout'] = $sds_theme_options['content_layouts']['single'];

		// Home (Blog)
		if ( is_home() && ! empty( $sds_theme_options['content_layouts']['home'] ) )
			$sds_theme_options['body_class'] = $classes['sds-content-layout'] = $sds_theme_options['content_layouts']['home'];

		// Single Page
		if ( is_page() && ! empty( $sds_theme_options['content_layouts']['page'] ) ) {
			// Add content layout styles only if a page template is not selected
			if( empty( $wp_page_template ) || $wp_page_template === 'default' )
				$sds_theme_options['body_class'] = $classes['sds-content-layout'] = $sds_theme_options['content_layouts']['page'];
		}

		// Front Page
		if ( is_front_page() && ! empty( $sds_theme_options['content_layouts']['front_page'] ) )
			$sds_theme_options['body_class'] = $classes['sds-content-layout'] = $sds_theme_options['content_layouts']['front_page'];

		// Archive
		if ( is_archive() && ! empty( $sds_theme_options['content_layouts']['archive'] ) )
			$sds_theme_options['body_class'] = $classes['sds-content-layout'] = $sds_theme_options['content_layouts']['archive'];

		// Category Archive
		if ( is_category() && ! empty( $sds_theme_options['content_layouts']['category'] ) )
			$sds_theme_options['body_class'] = $classes['sds-content-layout'] = $sds_theme_options['content_layouts']['category'];

		// Tag Archive
		if ( is_tag() && ! empty( $sds_theme_options['content_layouts']['tag'] ) )
			$sds_theme_options['body_class'] = $classes['sds-content-layout'] = $sds_theme_options['content_layouts']['tag'];
	}

	return $classes;
}

/**
 * This function configures/sets up theme options/features.
 */
add_action( 'after_setup_theme', 'sds_after_setup_theme' );

function sds_after_setup_theme() {
	// Enable Featured Images
	add_theme_support( 'post-thumbnails' );

	// Enable Automatic Feed Links
	add_theme_support( 'automatic-feed-links' );

	// Enable excerpts on Pages
	add_post_type_support( 'page', 'excerpt' );

	// Register WordPress Menus
	register_nav_menus( array(
		'top_nav' => __( 'Top Navigation', 'capture' ),
		'primary_nav' => __( 'Primary Navigation', 'capture' ),
		'footer_nav' => __( 'Footer Navigation', 'capture' )
	) );
}

/**
 * This function configures sidebars for use throughout the theme
 */
add_action( 'widgets_init', 'sds_widgets_init' );

function sds_widgets_init() {
	// Register SDS Social Media Widget (/includes/widget-social-media.php)
	register_widget( 'SDS_Social_Media_Widget' );

	// Primary sidebar
	register_sidebar( array(
		'name'          => __( 'Основной виджет', 'capture' ),
		'id'            => 'primary-sidebar',
		'description'   => __( 'Это основной виджет', 'capture' ),
		'before_widget' => '<section id="primary-sidebar-%1$s" class="widget primary-sidebar primary-sidebar-widget %2$s">',
		'after_widget'  => '<section class="clear"></section></section>',
		'before_title'  => '<h3 class="widgettitle widget-title primary-sidebar-widget-title">',
		'after_title'   => '</h3>',
	) );

	// Secondary sidebar
	register_sidebar( array(
		'name'          => __( 'Secondary Sidebar', 'capture' ),
		'id'            => 'secondary-sidebar',
		'description'   => __( 'Это вторичный виджет', 'capture' ),
		'before_widget' => '<section id="secondary-sidebar-%1$s" class="widget secondary-sidebar secondary-sidebar-widget %2$s">',
		'after_widget'  => '<section class="clear"></section></section>',
		'before_title'  => '<h3 class="widgettitle widget-title secondary-sidebar-widget-title">',
		'after_title'   => '</h3>',
	) );

	// Front Page Slider
	register_sidebar( array(
		'name'          => __( 'Front Page Slider', 'capture' ),
		'id'            => 'front-page-slider-sidebar',
		'description'   => __( '*This widget area is only displayed if a Front Page is selected via Settings > Reading in the Dashboard. Specifically formatted for Soliloquy or SlideDeck sliders.* This widget area is displayed above the content on the Front Page.', 'capture' ),
		'before_widget' => '<section id="front-page-slider-%1$s" class="widget front-page-slider front-page-slider-widget slider %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h3 class="widgettitle widget-title front-page-slider-title">',
		'after_title'   => '</h3>'
	) );

	// Front Page
	register_sidebar( array(
		'name'          => __( 'Front Page', 'capture' ),
		'id'            => 'front-page-sidebar',
		'description'   => __( '*This widget area is only displayed if a Front Page is selected via Settings > Reading in the Dashboard.* This widget area is displayed below the Front Page Slider on the Front Page and will replace the Front Page content.', 'capture' ),
		'before_widget' => '<section id="front-page-%1$s" class="widget front-page front-page-sidebar %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h3 class="widgettitle widget-title front-page-title">',
		'after_title'   => '</h3>'
	) );

	// Header Call To Action
	register_sidebar( array(
		'name'          => __( 'Header Call To Action', 'capture' ),
		'id'            => 'header-call-to-action-sidebar',
		'description'   => __( 'This widget area is used to display a call to action in the header', 'capture' ),
		'before_widget' => '<section id="header-call-to-action-%1$s" class="widget header-call-to-action-widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h3 class="widgettitle widget-title header-call-to-action-widget-title">',
		'after_title'   => '</h3>',
	) );

	// After Posts
	register_sidebar( array(
		'name'          => __( 'After Posts', 'capture' ),
		'id'            => 'after-posts-sidebar',
		'description'   => __( 'Виждет выводится после записей.', 'capture' ),
		'before_widget' => '<section id="after-posts-%1$s" class="widget after-posts after-posts-widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h3 class="widgettitle widget-title after-posts-title">',
		'after_title'   => '</h3>'
	) );

	// Footer
	register_sidebar( array(
		'name'          => __( 'Footer', 'capture' ),
		'id'            => 'footer-sidebar',
		'description'   => __( 'Этот виджет отображается в низу сайта.', 'capture' ),
		'before_widget' => '<section id="footer-widget-%1$s" class="widget footer-widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h3 class="widgettitle widget-title footer-widget-title">',
		'after_title'   => '</h3>'
	) );

	// Copyright
	register_sidebar( array(
		'name'          => __( 'Copyright Area', 'capture' ),
		'id'            => 'copyright-area-sidebar',
		'description'   => __( 'Этот виждет предназначен для текста в низу сайта.', 'capture' ),
		'before_widget' => '<section id="copyright-area-widget-%1$s" class="widget copyright-area copyright-area-widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h3 class="widgettitle widget-title copyright-area-widget-title">',
		'after_title'   => '</h3>',
	) );
}

/**
 * This function outputs the Primary Sidebar.
 */
function sds_primary_sidebar() {
	if ( is_active_sidebar( 'primary-sidebar' ) )
		dynamic_sidebar( 'primary-sidebar' );
}

/**
 * This function outputs the Secondary Sidebar.
 */
function sds_secondary_sidebar() {
	if ( is_active_sidebar( 'secondary-sidebar' ) )
		dynamic_sidebar( 'secondary-sidebar' );
}

/**
 * This function outputs the Front Page Slider Sidebar.
 */
function sds_front_page_slider_sidebar() {
	if ( is_active_sidebar( 'front-page-slider-sidebar' ) )
		dynamic_sidebar( 'front-page-slider-sidebar' );
}

/**
 * This function outputs the Header Call to Action Sidebar.
 */
function sds_header_call_to_action_sidebar() {
	if ( is_active_sidebar( 'header-call-to-action-sidebar' ) )
		dynamic_sidebar( 'header-call-to-action-sidebar' );
}

/**
 * This function outputs the After Posts Sidebar.
 */
function sds_after_posts_sidebar() {
	if ( is_active_sidebar( 'after-posts-sidebar' ) )
		dynamic_sidebar( 'after-posts-sidebar' );
}

/**
 * This function outputs the Footer Sidebar.
 */
function sds_footer_sidebar() {
	if ( is_active_sidebar( 'footer-sidebar' ) )
		dynamic_sidebar( 'footer-sidebar' );
}

/**
 * This function outputs the Copyright Area Sidebar.
 */
function sds_copyright_area_sidebar() {
	if ( is_active_sidebar( 'copyright-area-sidebar' ) )
		dynamic_sidebar( 'copyright-area-sidebar' );
}