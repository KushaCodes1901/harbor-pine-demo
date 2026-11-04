<?php
/** Template Name: Contact Page */
defined( 'ABSPATH' ) || exit; get_header();
?>
<main id="main" class="hp-content">
    <?php while ( have_posts() ) : the_post(); ?>
        <section id="contact" class="hp-contact hp-section" aria-labelledby="contact-title">
            <div class="hp-contact-intro">
                <p class="hp-eyebrow">Let’s find your place</p><h1 id="contact-title"><?php echo esc_html( get_the_title() ); ?></h1>
                <div class="hp-page-copy"><?php the_content(); ?></div>
                <?php $details = hp_options(); ?>
                <?php if ( $details['email'] || $details['phone'] || $details['address'] ) : ?>
                    <dl class="hp-business-details">
                        <?php if ( $details['email'] ) : ?><dt>Email</dt><dd><a href="<?php echo esc_url( 'mailto:' . $details['email'] ); ?>"><?php echo esc_html( $details['email'] ); ?></a></dd><?php endif; ?>
                        <?php if ( $details['phone'] ) : ?><dt>Phone</dt><dd><a href="<?php echo esc_url( 'tel:' . preg_replace( '/[^0-9+]/', '', $details['phone'] ) ); ?>"><?php echo esc_html( $details['phone'] ); ?></a></dd><?php endif; ?>
                        <?php if ( $details['address'] ) : ?><dt>Address / service area</dt><dd><?php echo nl2br( esc_html( $details['address'] ) ); ?></dd><?php endif; ?>
                    </dl>
                <?php endif; ?>
            </div>
            <div class="hp-form-panel">
                <h2>Property inquiry</h2>
                <p class="hp-demo-note">Portfolio demo. Use sample details. No email will be sent.</p>
                <?php $property = hp_selected_property(); ?>
                <?php if ( $property ) : ?><p class="hp-selected-property">Inquiring about: <strong><?php echo esc_html( get_the_title( $property ) ); ?></strong><br><a href="<?php echo esc_url( get_permalink() . '#contact' ); ?>">Make a general inquiry instead</a></p><?php endif; ?>
                <?php if ( shortcode_exists( 'contact-form-7' ) ) : ?>
                    <?php echo do_shortcode( '[contact-form-7 id="17b8878" title="Contact Form"]' ); ?>
                <?php else : ?><p>The demo inquiry form is currently unavailable.</p><?php endif; ?>
            </div>
        </section>
    <?php endwhile; ?>
</main>
<?php get_footer(); ?>
