<?php
/** Settings API screen; no options or pages are created until the user saves. */
defined( 'ABSPATH' ) || exit;

function hp_option_fields() {
    return array(
        'logo_id' => array( 'Branding', 'Logo', 'image', 0 ),
        'hero_heading' => array( 'Homepage', 'Hero heading (line breaks allowed)', 'textarea', "Find your\nplace." ),
        'hero_intro' => array( 'Homepage', 'Hero introduction', 'textarea', 'Homes in great communities, with a thoughtful, personal approach.' ),
        'hero_image_id' => array( 'Homepage', 'Hero image', 'image', 0 ),
        'hero_button' => array( 'Homepage', 'Hero button label', 'text', 'Explore properties' ),
        'buy_title' => array( 'Homepage', 'Buy tile title', 'text', 'Buy' ),
        'buy_description' => array( 'Homepage', 'Buy tile description', 'textarea', 'Find a home that fits your life.' ),
        'sell_title' => array( 'Homepage', 'Sell tile title', 'text', 'Sell' ),
        'sell_description' => array( 'Homepage', 'Sell tile description', 'textarea', 'Get thoughtful guidance and strong exposure.' ),
        'relocate_title' => array( 'Homepage', 'Relocate tile title', 'text', 'Relocate' ),
        'relocate_description' => array( 'Homepage', 'Relocate tile description', 'textarea', 'Make a smooth move to a new community.' ),
        'featured_heading' => array( 'Homepage', 'Featured Homes heading', 'text', 'Featured homes' ),
        'featured_link_label' => array( 'Homepage', 'View all homes link label', 'text', 'View all homes' ),
        'approach_eyebrow' => array( 'Homepage', 'Introduction small heading', 'text', 'Rooted in what matters' ),
        'approach_heading' => array( 'Homepage', 'Introduction heading', 'text', 'A home is more than an address.' ),
        'approach_copy' => array( 'Homepage', 'Introduction copy', 'textarea', 'It’s the morning light in your kitchen. A favorite walk around the neighborhood. A little room for the life you want to build. At Harbor & Pine, finding a place starts with understanding what makes it feel like yours.' ),
        'approach_image_id' => array( 'Homepage', 'Introduction garden image', 'image', 0 ),
        'approach_link_label' => array( 'Homepage', 'Introduction Contact link label', 'text', 'Tell us what home means to you' ),
        'steps_eyebrow' => array( 'Homepage', 'Steps small heading', 'text', 'A little clarity, every step' ),
        'steps_heading' => array( 'Homepage', 'Steps heading', 'text', 'Your next chapter, step by step.' ),
        'step_1_title' => array( 'Homepage', 'Step 1 title', 'text', 'Share what matters' ),
        'step_1_copy' => array( 'Homepage', 'Step 1 copy', 'textarea', 'Start with your everyday life, your hopes, and the things you wouldn’t want to compromise on.' ),
        'step_2_title' => array( 'Homepage', 'Step 2 title', 'text', 'Explore your options' ),
        'step_2_copy' => array( 'Homepage', 'Step 2 copy', 'textarea', 'Take a closer look at the homes, spaces, and neighborhood details that feel like a good fit.' ),
        'step_3_title' => array( 'Homepage', 'Step 3 title', 'text', 'Plan your move' ),
        'step_3_copy' => array( 'Homepage', 'Step 3 copy', 'textarea', 'Bring the possibilities together and map out a thoughtful next step, at a pace that feels right.' ),
        'closing_eyebrow' => array( 'Homepage', 'Closing section small heading', 'text', 'Good things start with a conversation' ),
        'closing_heading' => array( 'Homepage', 'Closing section heading', 'text', 'Let’s find your place.' ),
        'closing_copy' => array( 'Homepage', 'Closing section copy', 'textarea', 'A first home, a fresh start, or a new neighborhood. Tell us a little about the next chapter you have in mind.' ),
        'closing_button' => array( 'Homepage', 'Closing Contact button label', 'text', 'Start a conversation' ),
        'buy_page' => array( 'Homepage', 'Buy page', 'page', 0 ),
        'sell_page' => array( 'Homepage', 'Sell page', 'page', 0 ),
        'relocate_page' => array( 'Homepage', 'Relocate page', 'page', 0 ),
        'contact_page' => array( 'Contact', 'Contact page', 'page', 0 ),
        'email' => array( 'Contact', 'Public business email', 'email', '' ),
        'phone' => array( 'Contact', 'Public business phone', 'text', '' ),
        'address' => array( 'Contact', 'Address or service area', 'textarea', '' ),
    );
}

function hp_options() {
    $values = array();
    foreach ( hp_option_fields() as $key => $field ) { $values[ $key ] = $field[3]; }
    // Read values from the previous theme version without modifying its options.
    $old = get_option( 'hp_settings', array() );
    if ( is_array( $old ) ) { $values = array_replace( $values, array_intersect_key( $old, $values ) ); }
    $home = 'page' === get_option( 'show_on_front' ) ? absint( get_option( 'page_on_front' ) ) : 0;
    if ( $home ) {
        foreach ( hp_option_fields() as $key => $field ) {
            if ( 'Homepage' === $field[0] && in_array( $field[2], array( 'text', 'textarea' ), true ) ) {
                $legacy = get_post_meta( $home, '_hp_home_' . $key, true );
                if ( is_string( $legacy ) && '' !== $legacy ) { $values[ $key ] = $legacy; }
            }
        }
        $values['hero_image_id'] = get_post_thumbnail_id( $home );
    }
    $saved = get_option( 'hp_options', array() );
    return array_replace( $values, is_array( $saved ) ? array_intersect_key( $saved, $values ) : array() );
}

function hp_copy( $key ) {
    $options = hp_options();
    $fields = hp_option_fields();
    return is_string( $options[ $key ] ) && '' !== trim( $options[ $key ] ) ? $options[ $key ] : $fields[ $key ][3];
}

function hp_option_id( $value ) {
    $raw = is_scalar( $value ) ? (string) $value : '';
    return strlen( $raw ) <= 10 && preg_match( '/^[1-9]\d*$/D', $raw ) ? (int) $raw : 0;
}

function hp_valid_page( $id ) {
    $id = hp_option_id( $id );
    return $id && 'page' === get_post_type( $id ) && 'publish' === get_post_status( $id )
        && $id !== (int) get_option( 'page_on_front' ) && $id !== (int) get_option( 'page_for_posts' ) ? $id : 0;
}

function hp_page_id( $role ) {
    $options = hp_options();
    return isset( $options[ $role . '_page' ] ) ? hp_valid_page( $options[ $role . '_page' ] ) : 0;
}

function hp_page_url( $role ) {
    $id = hp_page_id( $role );
    return $id ? get_permalink( $id ) : home_url( '/' ) . ( 'buy' === $role ? '#featured' : '#site-setup' );
}

function hp_inquiry_url( $id = 0 ) {
    $url = hp_page_url( 'contact' );
    if ( ! hp_page_id( 'contact' ) ) { return $url; }
    return ( $id ? add_query_arg( 'property_id', absint( $id ), $url ) : $url ) . '#contact';
}

function hp_sanitize_options( $input ) {
    $input = is_array( $input ) ? $input : array();
    $clean = array(); $used_pages = array();
    foreach ( hp_option_fields() as $key => $field ) {
        $raw = isset( $input[ $key ] ) && is_scalar( $input[ $key ] ) ? (string) $input[ $key ] : '';
        if ( 'page' === $field[2] ) {
            $id = hp_valid_page( $raw );
            if ( ( '' !== $raw && '0' !== $raw && ! $id ) || ( $id && in_array( $id, $used_pages, true ) ) ) {
                add_settings_error( 'hp_options', 'hp_' . $key, $field[1] . ': choose a separate published page, other than Home or the posts page.' );
                $id = 0;
            }
            if ( $id ) { $used_pages[] = $id; }
            $clean[ $key ] = $id;
        } elseif ( 'image' === $field[2] ) {
            $id = hp_option_id( $raw );
            $clean[ $key ] = $id && 'attachment' === get_post_type( $id ) && wp_attachment_is_image( $id ) ? $id : 0;
            if ( $id && ! $clean[ $key ] ) { add_settings_error( 'hp_options', 'hp_' . $key, $field[1] . ': choose an image from Media Library.' ); }
        } elseif ( 'email' === $field[2] ) {
            $clean[ $key ] = is_email( trim( $raw ) ) ? sanitize_email( $raw ) : '';
            if ( '' !== trim( $raw ) && '' === $clean[ $key ] ) { add_settings_error( 'hp_options', 'hp_email', 'Enter a valid public email address.' ); }
        } else {
            $clean[ $key ] = 'textarea' === $field[2] ? sanitize_textarea_field( $raw ) : sanitize_text_field( $raw );
        }
    }
    return $clean;
}

function hp_register_options() {
    register_setting( 'hp_options_group', 'hp_options', array( 'type' => 'array', 'sanitize_callback' => 'hp_sanitize_options' ) );
    foreach ( array( 'Branding', 'Homepage', 'Contact' ) as $section ) {
        add_settings_section( 'hp_' . strtolower( $section ), $section, function () use ( $section ) {
            if ( 'Contact' === $section ) { echo '<p>Public details are for display only. Configure the mail recipient and demo mode in Contact Form 7.</p>'; }
        }, 'harbor-pine' );
    }
    foreach ( hp_option_fields() as $key => $field ) {
        add_settings_field( 'hp_' . $key, $field[1], 'hp_option_control', 'harbor-pine', 'hp_' . strtolower( $field[0] ), array( 'key' => $key, 'field' => $field, 'label_for' => 'hp-' . $key ) );
    }
}
add_action( 'admin_init', 'hp_register_options' );

function hp_option_control( $args ) {
    $key = $args['key']; $field = $args['field']; $value = hp_options()[ $key ];
    $name = 'hp_options[' . $key . ']';
    if ( 'page' === $field[2] ) {
        wp_dropdown_pages( array( 'name' => $name, 'id' => 'hp-' . $key, 'selected' => $value, 'show_option_none' => '— Select a published page —', 'option_none_value' => 0, 'post_status' => 'publish', 'exclude' => implode( ',', array_filter( array( get_option( 'page_on_front' ), get_option( 'page_for_posts' ) ) ) ) ) );
    } elseif ( 'image' === $field[2] ) {
        echo '<div class="hp-media-control"><input type="hidden" id="hp-' . esc_attr( $key ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '"><div class="hp-media-preview">';
        if ( $value ) { echo wp_get_attachment_image( $value, 'thumbnail' ); }
        echo '</div><button class="button hp-media-select" type="button">Choose image</button> <button class="button hp-media-remove" type="button">Remove image</button></div>';
    } elseif ( 'textarea' === $field[2] ) {
        echo '<textarea class="large-text" rows="3" id="hp-' . esc_attr( $key ) . '" name="' . esc_attr( $name ) . '">' . esc_textarea( $value ) . '</textarea>';
    } else {
        echo '<input class="regular-text" type="' . esc_attr( $field[2] ) . '" id="hp-' . esc_attr( $key ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '">';
    }
}

add_action( 'admin_menu', function () {
    add_menu_page( 'Harbor & Pine', 'Harbor & Pine', 'manage_options', 'harbor-pine', function () {
        if ( ! current_user_can( 'manage_options' ) ) { return; }
        echo '<div class="wrap"><h1>Harbor &amp; Pine</h1><p>Choose pages after publishing them. Blank homepage copy uses the original design text.</p>';
        settings_errors( 'hp_options' );
        echo '<form method="post" action="options.php">';
        settings_fields( 'hp_options_group' ); do_settings_sections( 'harbor-pine' ); submit_button();
        echo '</form></div>';
    }, 'dashicons-admin-home', 30 );
} );
add_action( 'admin_enqueue_scripts', function ( $hook ) {
    if ( 'toplevel_page_harbor-pine' !== $hook ) { return; }
    wp_enqueue_media();
    wp_enqueue_script( 'hp-admin-media', get_template_directory_uri() . '/assets/admin.js', array( 'media-views' ), filemtime( get_template_directory() . '/assets/admin.js' ), true );
    wp_add_inline_style( 'common', '.hp-media-preview img{display:block;max-width:180px;height:auto;margin:0 0 12px}' );
} );
