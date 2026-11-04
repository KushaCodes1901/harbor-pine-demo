<?php defined( 'ABSPATH' ) || exit; get_header(); ?>
<main id="main" class="hp-content hp-section">
    <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
        <article class="hp-section"><h1><a href="<?php the_permalink(); ?>"><?php echo esc_html( get_the_title() ); ?></a></h1><?php the_content(); ?></article>
    <?php endwhile; the_posts_pagination(); else : ?>
        <h1>Nothing here just yet.</h1><p><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Find your way home →</a></p>
    <?php endif; ?>
</main>
<?php get_footer(); ?>
