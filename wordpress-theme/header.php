<?php defined( 'ABSPATH' ) || exit; ?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="hp-skip-link" href="#main">Skip to content</a>
<div class="hp-site">
    <header class="hp-header">
        <a class="hp-wordmark" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php hp_wordmark(); ?></a>
        <button class="hp-menu-toggle" type="button" aria-expanded="false" aria-controls="hp-nav" hidden>Menu <span aria-hidden="true">☰</span></button>
        <nav id="hp-nav" class="hp-nav" aria-label="Main navigation">
            <?php wp_nav_menu( array( 'theme_location' => 'primary', 'container' => false, 'menu_class' => 'hp-menu', 'fallback_cb' => 'hp_fallback_menu', 'depth' => 2 ) ); ?>
        </nav>
    </header>
