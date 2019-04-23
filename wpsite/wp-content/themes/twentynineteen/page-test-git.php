<?php
/*
*	Template Name: Test git Template
*/
get_header();
?>

	<section id="primary" class="content-area">
		<main id="main" class="site-main">
			<?php
            if(get_field('project_brief')) {
                echo 'Project : '.get_field('project_brief');
            }
            $image = get_field('project_image');
            if(!empty($image) ) {
                ?>
                <img src="<?php echo $image['url']; ?>" alt="<?php echo $image['alt']; ?>" />
                <?php
            }
			/* Start the Loop */
			while ( have_posts() ) :
				the_post();

				get_template_part( 'template-parts/content/content', 'page' );

				// If comments are open or we have at least one comment, load up the comment template.
				if ( comments_open() || get_comments_number() ) {
					comments_template();
				}

			endwhile; // End of the loop.
			?>

		</main><!-- #main -->
	</section><!-- #primary -->

<?php
get_footer();
