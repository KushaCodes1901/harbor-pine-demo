<?php
/** Template Name: Buy Page */
defined( 'ABSPATH' ) || exit; get_header();
?>
<main id="main" class="hp-content hp-section">
    <?php while ( have_posts() ) : the_post(); ?>
        <header class="hp-page-intro"><p class="hp-eyebrow">Find your next chapter</p><h1><?php echo esc_html( get_the_title() ); ?></h1><div class="hp-page-copy"><?php the_content(); ?></div></header>
    <?php endwhile; ?>
    <?php hp_property_grid( -1 ); ?>
</main>
<?php get_footer(); ?>
