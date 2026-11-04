<?php defined( 'ABSPATH' ) || exit; get_header(); ?>
<main id="main">
    <section class="hp-hero" aria-labelledby="hero-title">
        <div class="hp-hero-copy">
            <h1 id="hero-title"><?php echo nl2br( esc_html( hp_copy( 'hero_heading' ) ) ); ?></h1>
            <p><?php echo nl2br( esc_html( hp_copy( 'hero_intro' ) ) ); ?></p>
            <a class="hp-button" href="<?php echo esc_url( hp_page_url( 'buy' ) ); ?>"><?php echo esc_html( hp_copy( 'hero_button' ) ); ?></a>
        </div>
        <?php $hero_id = absint( hp_options()['hero_image_id'] ); ?>
        <?php if ( $hero_id && wp_attachment_is_image( $hero_id ) ) : ?>
            <?php echo wp_get_attachment_image( $hero_id, 'full', false, array( 'class' => 'hp-hero-image', 'loading' => 'eager', 'fetchpriority' => 'high' ) ); ?>
        <?php else : ?>
            <img class="hp-hero-image" src="<?php echo esc_url( get_template_directory_uri() . '/assets/harbor-pine-house.png' ); ?>" alt="Illustrated ivory cottage with a pine-green roof and a welcoming garden" width="1536" height="1024" fetchpriority="high">
        <?php endif; ?>
    </section>
    <div class="hp-content">
        <nav class="hp-pathways" aria-label="Find your next step">
            <?php
            $icons = array(
                'buy' => 'M3 12 12 3l9 9M5 10v11h5v-7h4v7h5V10',
                'sell' => 'M3 12 12 3h9v9l-9 9-9-9ZM16 7h.01',
                'relocate' => 'M19 10c0 5-7 12-7 12S5 15 5 10a7 7 0 1 1 14 0ZM15 10a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z',
            );
            foreach ( $icons as $role => $path ) : ?>
                <a class="hp-pathway" href="<?php echo esc_url( hp_page_url( $role ) ); ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.3" aria-hidden="true"><path d="<?php echo esc_attr( $path ); ?>"/></svg>
                    <span><strong><?php echo esc_html( hp_copy( $role . '_title' ) ); ?></strong><span><?php echo esc_html( hp_copy( $role . '_description' ) ); ?></span></span>
                    <span class="hp-arrow" aria-hidden="true">›</span>
                </a>
            <?php endforeach; ?>
        </nav>
        <section id="featured" class="hp-section" aria-labelledby="featured-title">
            <div class="hp-section-heading">
                <h2 id="featured-title"><?php echo esc_html( hp_copy( 'featured_heading' ) ); ?></h2>
                <a class="hp-text-link" href="<?php echo esc_url( hp_page_url( 'buy' ) ); ?>"><?php echo esc_html( hp_copy( 'featured_link_label' ) ); ?> <span aria-hidden="true">&rarr;</span></a>
            </div>
            <?php hp_property_grid( 3 ); ?>
        </section>
        <section class="hp-approach" aria-labelledby="approach-title">
            <div class="hp-approach-art">
                <?php $approach_image = absint( hp_options()['approach_image_id'] ); ?>
                <?php if ( $approach_image && wp_attachment_is_image( $approach_image ) ) : ?>
                    <?php echo wp_get_attachment_image( $approach_image, 'large', false, array( 'class' => 'hp-approach-image', 'loading' => 'lazy' ) ); ?>
                <?php else : ?>
                    <img class="hp-approach-image" src="<?php echo esc_url( get_template_directory_uri() . '/assets/harbor-pine-garden.png' ); ?>" alt="Illustrated garden path through an open green gate, with flowers and a cottage beyond" width="1536" height="1024" loading="lazy">
                <?php endif; ?>
            </div>
            <div class="hp-approach-copy">
                <p class="hp-eyebrow"><?php echo esc_html( hp_copy( 'approach_eyebrow' ) ); ?></p>
                <h2 id="approach-title"><?php echo esc_html( hp_copy( 'approach_heading' ) ); ?></h2>
                <p><?php echo nl2br( esc_html( hp_copy( 'approach_copy' ) ) ); ?></p>
                <a class="hp-text-link" href="<?php echo esc_url( hp_page_url( 'contact' ) ); ?>"><?php echo esc_html( hp_copy( 'approach_link_label' ) ); ?> <span aria-hidden="true">&rarr;</span></a>
            </div>
        </section>
        <section class="hp-next-steps" aria-labelledby="steps-title">
            <div class="hp-steps-heading"><p class="hp-eyebrow"><?php echo esc_html( hp_copy( 'steps_eyebrow' ) ); ?></p><h2 id="steps-title"><?php echo esc_html( hp_copy( 'steps_heading' ) ); ?></h2></div>
            <ol class="hp-steps-list">
                <?php for ( $step = 1; $step <= 3; $step++ ) : ?>
                    <li>
                        <span class="hp-step-number" aria-hidden="true"><?php echo esc_html( sprintf( '%02d', $step ) ); ?></span>
                        <h3><?php echo esc_html( hp_copy( 'step_' . $step . '_title' ) ); ?></h3>
                        <p><?php echo nl2br( esc_html( hp_copy( 'step_' . $step . '_copy' ) ) ); ?></p>
                    </li>
                <?php endfor; ?>
            </ol>
        </section>
        <section class="hp-home-closing" aria-labelledby="closing-title">
            <div><p class="hp-eyebrow"><?php echo esc_html( hp_copy( 'closing_eyebrow' ) ); ?></p><h2 id="closing-title"><?php echo esc_html( hp_copy( 'closing_heading' ) ); ?></h2><p class="hp-closing-copy"><?php echo nl2br( esc_html( hp_copy( 'closing_copy' ) ) ); ?></p></div>
            <a class="hp-button hp-button--ivory" href="<?php echo esc_url( hp_page_url( 'contact' ) ); ?>"><?php echo esc_html( hp_copy( 'closing_button' ) ); ?> <span aria-hidden="true">&rarr;</span></a>
        </section>
        <?php if ( ! hp_page_id( 'buy' ) || ! hp_page_id( 'sell' ) || ! hp_page_id( 'relocate' ) || ! hp_page_id( 'contact' ) ) : ?>
            <section id="site-setup" class="hp-empty hp-section" aria-label="Website setup">
                <p>We’re preparing the next chapter. More pages are coming soon.</p>
                <?php if ( current_user_can( 'manage_options' ) ) : ?><p><a href="<?php echo esc_url( admin_url( 'admin.php?page=harbor-pine' ) ); ?>">Assign your Buy, Sell, Relocate, and Contact pages in Harbor &amp; Pine settings →</a></p><?php endif; ?>
            </section>
        <?php endif; ?>
    </div>
</main>
<?php get_footer(); ?>
