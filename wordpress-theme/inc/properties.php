<?php
defined( 'ABSPATH' ) || exit;
function hp_register_properties() {
    register_post_type( 'hp_property', array(
        'labels' => array( 'name' => 'Properties', 'singular_name' => 'Property', 'add_new_item' => 'Add new property', 'edit_item' => 'Edit property' ),
        'public' => true, 'show_in_rest' => false, 'menu_icon' => 'dashicons-admin-home',
        'supports' => array( 'title', 'editor', 'thumbnail' ),
        'rewrite' => array( 'slug' => 'homes', 'with_front' => false ), 'has_archive' => false,
    ) );
}
add_action( 'init', 'hp_register_properties' );
add_action( 'after_switch_theme', function () { hp_register_properties(); flush_rewrite_rules(); } );

function hp_property_fields() {
    return array( 'address' => array( 'Address', 'text', '' ), 'price' => array( 'Price in USD (numbers only)', 'number', '1' ), 'beds' => array( 'Beds', 'number', '1' ), 'baths' => array( 'Baths', 'number', '0.5' ) );
}
add_action( 'add_meta_boxes', function () { add_meta_box( 'hp-property-details', 'Property details', 'hp_property_meta_box', 'hp_property', 'normal', 'high' ); } );
function hp_property_meta_box( $post ) {
    wp_nonce_field( 'hp_save_property', 'hp_property_nonce' );
    foreach ( hp_property_fields() as $key => $field ) {
        echo '<p><label for="hp-' . esc_attr( $key ) . '"><strong>' . esc_html( $field[0] ) . '</strong></label><br><input class="widefat" id="hp-' . esc_attr( $key ) . '" name="hp_' . esc_attr( $key ) . '" type="' . esc_attr( $field[1] ) . '" value="' . esc_attr( get_post_meta( $post->ID, '_hp_' . $key, true ) ) . '"';
        if ( 'number' === $field[1] ) { echo ' min="0" step="' . esc_attr( $field[2] ) . '"'; }
        echo '></p>';
    }
}
function hp_save_property( $id ) {
    if ( wp_is_post_revision( $id ) || ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $id ) ) { return; }
    if ( ! isset( $_POST['hp_property_nonce'] ) || ! is_string( $_POST['hp_property_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['hp_property_nonce'] ) ), 'hp_save_property' ) ) { return; }
    foreach ( hp_property_fields() as $key => $field ) {
        if ( ! isset( $_POST[ 'hp_' . $key ] ) || ! is_string( $_POST[ 'hp_' . $key ] ) ) { continue; }
        $value = trim( wp_unslash( $_POST[ 'hp_' . $key ] ) );
        if ( '' === $value ) { delete_post_meta( $id, '_hp_' . $key ); continue; }
        if ( 'address' === $key ) { $value = sanitize_text_field( $value ); }
        else {
            if ( ! preg_match( '/^\d+(?:\.\d+)?$/D', $value ) || ! is_finite( (float) $value ) || (float) $value > 1000000000 ) { continue; }
            $value = 'baths' === $key ? round( (float) $value * 2 ) / 2 : (int) $value;
        }
        update_post_meta( $id, '_hp_' . $key, $value );
    }
}
add_action( 'save_post_hp_property', 'hp_save_property' );
function hp_price( $id ) {
    $value = get_post_meta( $id, '_hp_price', true );
    return '' === $value ? 'Price on request' : '$' . number_format_i18n( (float) $value );
}
function hp_house_image( $id, $class ) {
    if ( has_post_thumbnail( $id ) ) { echo get_the_post_thumbnail( $id, 'large', array( 'class' => $class ) ); }
    else { echo '<img src="' . esc_url( get_template_directory_uri() . '/assets/harbor-pine-house.png' ) . '" alt="Illustrated placeholder cottage in a leafy garden" class="' . esc_attr( $class ) . '" width="1536" height="1024" loading="lazy">'; }
}
function hp_facts( $id ) {
    echo '<ul class="hp-facts" aria-label="Property features">';
    foreach ( array( 'beds', 'baths' ) as $key ) {
        $value = get_post_meta( $id, '_hp_' . $key, true );
        if ( '' !== $value ) { echo '<li>' . esc_html( $value . ' ' . $key ) . '</li>'; }
    }
    echo '</ul>';
}
function hp_selected_property() {
    $raw = isset( $_GET['property_id'] ) && is_string( $_GET['property_id'] ) ? wp_unslash( $_GET['property_id'] ) : '';
    if ( ! preg_match( '/^[1-9]\d*$/D', $raw ) || strlen( $raw ) > 10 ) { return null; }
    $post = get_post( (int) $raw );
    return $post && 'hp_property' === $post->post_type && 'publish' === $post->post_status ? $post : null;
}
function hp_property_grid( $limit = 3 ) {
    $homes = new WP_Query( array( 'post_type' => 'hp_property', 'post_status' => 'publish', 'posts_per_page' => $limit, 'orderby' => array( 'date' => 'DESC', 'ID' => 'DESC' ), 'no_found_rows' => true ) );
    if ( $homes->have_posts() ) {
        echo '<div class="hp-property-grid">';
        while ( $homes->have_posts() ) { $homes->the_post(); get_template_part( 'template-parts/property-card' ); }
        echo '</div>';
    } else {
        echo '<div class="hp-empty"><h3>A little room for possibility.</h3><p>Our first homes are coming soon. We look forward to helping you find your place.</p>';
        if ( current_user_can( 'edit_posts' ) ) { echo '<a href="' . esc_url( admin_url( 'post-new.php?post_type=hp_property' ) ) . '">Add your first property in WP Admin &rarr;</a>'; }
        echo '</div>';
    }
    wp_reset_postdata();
}
