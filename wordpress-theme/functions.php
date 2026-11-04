<?php
defined( 'ABSPATH' ) || exit;
require_once get_template_directory() . '/inc/options.php';
require_once get_template_directory() . '/inc/properties.php';

add_action( 'after_setup_theme', function () {
    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'html5', array( 'gallery', 'caption', 'style', 'script' ) );
    register_nav_menus( array( 'primary' => 'Primary Menu' ) );
} );
add_action( 'wp_enqueue_scripts', function () {
    wp_enqueue_style( 'harbor-pine', get_stylesheet_uri(), array(), filemtime( get_template_directory() . '/style.css' ) );
    wp_enqueue_script( 'harbor-pine', get_template_directory_uri() . '/assets/theme.js', array(), filemtime( get_template_directory() . '/assets/theme.js' ), true );
} );
add_filter( 'use_block_editor_for_post_type', function ( $use, $type ) {
    return in_array( $type, array( 'page', 'hp_property' ), true ) ? false : $use;
}, 10, 2 );

function hp_wordmark() {
    $logo = hp_options()['logo_id'];
    $image = $logo ? wp_get_attachment_image( $logo, 'medium', false, array( 'class' => 'hp-logo', 'alt' => 'Harbor & Pine' ) ) : '';
    echo $image ? $image : 'Harbor &amp; Pine';
}

function hp_fallback_menu() {
    echo '<ul class="hp-menu">';
    foreach ( array( 'buy' => 'Buy', 'sell' => 'Sell', 'relocate' => 'Relocate', 'contact' => 'Contact' ) as $role => $label ) {
        echo '<li><a href="' . esc_url( hp_page_url( $role ) ) . '">' . esc_html( $label ) . '</a></li>';
    }
    echo '</ul>';
}

// Short custom menu URLs stay valid from any detail or content page.
add_filter( 'nav_menu_link_attributes', function ( $attributes, $item, $args ) {
    if ( 'primary' === $args->theme_location && isset( $attributes['href'] ) ) {
        $roles = array( '#featured' => 'buy', '#buy' => 'buy', '#sell' => 'sell', '#relocate' => 'relocate', '#contact' => 'contact' );
        if ( isset( $roles[ $attributes['href'] ] ) ) {
            $attributes['href'] = hp_page_url( $roles[ $attributes['href'] ] );
        }
    }
    return $attributes;
}, 10, 3 );

add_filter( 'template_include', function ( $template ) {
    if ( is_front_page() ) { return $template; }
    if ( hp_page_id( 'buy' ) && is_page( hp_page_id( 'buy' ) ) ) {
        return get_template_directory() . '/page-buy.php';
    }
    if ( hp_page_id( 'contact' ) && is_page( hp_page_id( 'contact' ) ) ) {
        return get_template_directory() . '/page-contact.php';
    }
    return $template;
} );

// Keep CF7's validation/submission; clarify only this form's demo success response.
add_filter( 'wpcf7_display_message', function ( $message, $status ) {
    $form = WPCF7_ContactForm::get_current();
    return $form && '17b8878' === $form->hash() && $form->in_demo_mode() && 'mail_sent_ok' === $status
        ? 'Test inquiry complete. This is a portfolio demo; no email was sent.' : $message;
}, 10, 2 );
