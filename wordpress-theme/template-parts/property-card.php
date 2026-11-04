<?php defined( 'ABSPATH' ) || exit; ?>
<article class="hp-property-card">
    <a class="hp-card-link" href="<?php echo esc_url( get_permalink( get_the_ID() ) ); ?>">
        <?php hp_house_image( get_the_ID(), 'hp-card-image' ); ?>
        <div class="hp-card-copy">
            <h3><?php echo esc_html( get_the_title() ); ?></h3>
            <p class="hp-price"><?php echo esc_html( hp_price( get_the_ID() ) ); ?></p>
            <?php hp_facts( get_the_ID() ); ?>
        </div>
    </a>
</article>
