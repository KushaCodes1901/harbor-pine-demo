<?php defined( 'ABSPATH' ) || exit; get_header(); ?>
<main id="main" class="hp-content hp-section">
    <?php while ( have_posts() ) : the_post(); ?>
        <article class="hp-page-intro"><p class="hp-eyebrow">A thoughtful, personal approach</p><h1><?php echo esc_html( get_the_title() ); ?></h1><div class="hp-page-copy"><?php the_content(); ?></div>
            <a class="hp-button" href="<?php echo esc_url( hp_inquiry_url() ); ?>">Contact Harbor &amp; Pine</a>
        </article>
    <?php endwhile; ?>
</main>
<?php get_footer(); ?>
