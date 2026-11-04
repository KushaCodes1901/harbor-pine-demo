<?php defined( 'ABSPATH' ) || exit; get_header(); ?>
<main id="main" class="hp-detail-wrap">
    <a class="hp-back" href="<?php echo esc_url( hp_page_url( 'buy' ) ); ?>">← Back to properties</a>
    <?php while ( have_posts() ) : the_post(); ?>
        <article class="hp-detail">
            <?php hp_house_image( get_the_ID(), 'hp-detail-image' ); ?>
            <div class="hp-detail-copy">
                <h1><?php echo esc_html( get_the_title() ); ?></h1>
                <p class="hp-price"><?php echo esc_html( hp_price( get_the_ID() ) ); ?></p>
                <?php $address = get_post_meta( get_the_ID(), '_hp_address', true ); if ( $address ) : ?><p class="hp-address"><?php echo esc_html( $address ); ?></p><?php endif; ?>
                <?php hp_facts( get_the_ID() ); ?>
                <div class="hp-page-copy hp-description"><?php the_content(); ?></div>
                <a class="hp-button" href="<?php echo esc_url( hp_inquiry_url( get_the_ID() ) ); ?>">Inquire about this property</a>
                <p class="hp-small">Fictional listing for a portfolio demo.</p>
            </div>
        </article>
    <?php endwhile; ?>
</main>
<?php get_footer(); ?>
