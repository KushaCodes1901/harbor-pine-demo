<?php
/** Rebuild a static portfolio from the running local WordPress site. CLI only. */
if ( PHP_SAPI !== 'cli' ) { http_response_code( 404 ); exit; }
$wp_root = realpath( $argv[1] ?? dirname( __DIR__, 2 ) );
if ( ! $wp_root || ! is_file( $wp_root . '/wp-load.php' ) ) { throw new RuntimeException( 'Supply the local WordPress root.' ); }
require $wp_root . '/wp-load.php';
$release = dirname( __DIR__ );
$public = $release . '/public';
if ( ! is_dir( $public ) ) { mkdir( $public, 0777, true ); }
$home = home_url( '/' );
$origin = wp_parse_url( $home, PHP_URL_SCHEME ) . '://' . wp_parse_url( $home, PHP_URL_HOST );
$base_path = wp_parse_url( $home, PHP_URL_PATH );
$copied = array();

function hp_export_write( $path, $content ) {
    if ( ! is_dir( dirname( $path ) ) ) { mkdir( dirname( $path ), 0777, true ); }
    file_put_contents( $path, $content );
}
function hp_export_asset( $url ) {
    global $home, $origin, $base_path, $wp_root, $public, $copied;
    $url = html_entity_decode( $url, ENT_QUOTES | ENT_HTML5 );
    if ( str_starts_with( $url, 'data:' ) || str_starts_with( $url, '#' ) ) { return $url; }
    if ( str_starts_with( $url, '//' ) ) { $url = 'http:' . $url; }
    if ( str_starts_with( $url, '/' ) ) { $url = $origin . $url; }
    if ( ! str_starts_with( $url, $home ) ) { return $url; }
    $path = rawurldecode( substr( wp_parse_url( $url, PHP_URL_PATH ), strlen( $base_path ) ) );
    $source = realpath( $wp_root . '/' . $path );
    $root = str_replace( '\\', '/', $wp_root ) . '/';
    if ( ! $source || ! str_starts_with( str_replace( '\\', '/', $source ), $root ) || ! is_file( $source ) ) { throw new RuntimeException( 'Missing asset: ' . $path ); }
    $allowed = array( 'css', 'js', 'png', 'jpg', 'jpeg', 'gif', 'webp', 'svg', 'avif', 'ico', 'woff', 'woff2', 'ttf', 'otf' );
    if ( ! in_array( strtolower( pathinfo( $source, PATHINFO_EXTENSION ) ), $allowed, true ) ) { throw new RuntimeException( 'Refusing non-public asset: ' . $path ); }
    $destination = '/assets/' . $path;
    if ( ! isset( $copied[ $path ] ) ) {
        $copied[ $path ] = true;
        $content = file_get_contents( $source );
        if ( 'css' === strtolower( pathinfo( $source, PATHINFO_EXTENSION ) ) ) {
            $content = preg_replace_callback( '~url\(\s*([\'"]?)(.*?)\1\s*\)~', function ( $m ) use ( $url ) {
                $value = $m[2];
                if ( ! preg_match( '~^(?:[a-z]+:|/|#)~i', $value ) ) { $value = WP_Http::make_absolute_url( $value, $url ); }
                return 'url("' . hp_export_asset( $value ) . '")';
            }, $content );
        }
        hp_export_write( $public . $destination, $content );
    }
    return $destination . ( wp_parse_url( $url, PHP_URL_FRAGMENT ) ? '#' . wp_parse_url( $url, PHP_URL_FRAGMENT ) : '' );
}
function hp_export_link( $url ) {
    global $home, $base_path;
    if ( str_starts_with( $url, $home ) ) { return '/' . substr( $url, strlen( $home ) ); }
    if ( str_starts_with( $url, $base_path ) ) { return '/' . substr( $url, strlen( $base_path ) ); }
    return $url;
}
function hp_export_page( $url, $destination, $contact = false ) {
    global $public, $home;
    $response = wp_remote_get( $url, array( 'timeout' => 30 ) );
    if ( is_wp_error( $response ) ) { throw new RuntimeException( $response->get_error_message() ); }
    $status = wp_remote_retrieve_response_code( $response );
    if ( 200 !== $status && '404.html' !== $destination ) { throw new RuntimeException( 'HTTP ' . $status . ': ' . $url ); }
    $doc = new DOMDocument( '1.0', 'UTF-8' );
    libxml_use_internal_errors( true ); $doc->loadHTML( '<?xml encoding="UTF-8">' . wp_remote_retrieve_body( $response ) ); libxml_clear_errors();
    $xpath = new DOMXPath( $doc );
    foreach ( iterator_to_array( $xpath->query( '//script | //link[not(@rel="stylesheet") and not(@rel="icon") and not(@rel="apple-touch-icon")] | //meta[@name="generator"] | //div[@id="wpadminbar"]' ) ) as $node ) { $node->parentNode->removeChild( $node ); }
    if ( $contact ) {
        $panel = $xpath->query( '//div[contains(concat(" ",normalize-space(@class)," ")," hp-form-panel ")]' )->item( 0 );
        if ( ! $panel ) { throw new RuntimeException( 'Contact panel not found.' ); }
        while ( $panel->firstChild ) { $panel->removeChild( $panel->firstChild ); }
        $fragment = $doc->createDocumentFragment();
        $fragment->appendXML( '<h2>Property inquiry</h2><p class="hp-demo-note">Portfolio demo. Use sample details. No email is sent and no inquiry data is stored.</p><p class="hp-selected-property" id="hp-selected-property" hidden="hidden"></p><form id="hp-demo-form" aria-label="Demo property inquiry"><div class="hp-form-grid"><label class="hp-form-field">First name<input name="first-name" autocomplete="given-name" maxlength="80" required="required" /></label><label class="hp-form-field">Last name<input name="last-name" autocomplete="family-name" maxlength="80" required="required" /></label><label class="hp-form-field">Email<input type="email" name="email" autocomplete="email" maxlength="254" required="required" /></label><label class="hp-form-field">Phone<input type="tel" name="phone" autocomplete="tel" maxlength="40" required="required" /></label><label class="hp-form-field hp-form-field--full">Message<textarea name="message" rows="5" maxlength="1500" required="required"></textarea></label><div class="hp-form-actions hp-form-field--full"><button class="hp-button" type="button" id="hp-demo-submit">Test inquiry</button></div></div><p id="hp-demo-response" role="status" aria-live="polite" tabindex="-1" hidden="hidden"></p></form><noscript><p>Enable JavaScript to try this browser-only demo form. No details are submitted.</p></noscript>' );
        $panel->appendChild( $fragment );
    }
    foreach ( $xpath->query( '//*[@href or @src or @srcset or @poster]' ) as $node ) {
        foreach ( array( 'href', 'src', 'poster' ) as $attribute ) {
            if ( ! $node->hasAttribute( $attribute ) ) { continue; }
            $value = $node->getAttribute( $attribute );
            $asset = 'a' !== $node->tagName && ( 'src' === $attribute || 'poster' === $attribute || 'link' === $node->tagName );
            $node->setAttribute( $attribute, $asset ? hp_export_asset( $value ) : hp_export_link( $value ) );
        }
        if ( $node->hasAttribute( 'srcset' ) ) {
            $parts = array();
            foreach ( explode( ',', $node->getAttribute( 'srcset' ) ) as $part ) {
                $tokens = preg_split( '/\s+/', trim( $part ), 2 );
                $parts[] = hp_export_asset( $tokens[0] ) . ( isset( $tokens[1] ) ? ' ' . $tokens[1] : '' );
            }
            $node->setAttribute( 'srcset', implode( ', ', $parts ) );
        }
    }
    foreach ( $xpath->query( '//style | //*[@style]' ) as $node ) {
        if ( 'style' === $node->tagName ) { $node->nodeValue = str_replace( $home, '/', $node->nodeValue ); }
        elseif ( $node->hasAttribute( 'style' ) ) { $node->setAttribute( 'style', str_replace( $home, '/', $node->getAttribute( 'style' ) ) ); }
    }
    $body = $doc->getElementsByTagName( 'body' )->item( 0 );
    foreach ( array( '/assets/theme.js', '/assets/demo.js' ) as $script ) {
        $element = $doc->createElement( 'script' ); $element->setAttribute( 'src', $script ); $element->setAttribute( 'defer', 'defer' ); $body->appendChild( $element );
    }
    $css = $doc->createElement( 'link' ); $css->setAttribute( 'rel', 'stylesheet' ); $css->setAttribute( 'href', '/assets/demo.css' ); $doc->getElementsByTagName( 'head' )->item( 0 )->appendChild( $css );
    $html = preg_replace( '/<\?xml[^>]*>/', '', $doc->saveHTML() );
    if ( str_contains( $html, 'localhost' ) || str_contains( $html, '127.0.0.1' ) ) { throw new RuntimeException( 'Localhost reference remains: ' . $destination ); }
    hp_export_write( $public . '/' . $destination, $html );
    echo 'Exported ' . $destination . PHP_EOL;
}

$routes = array( home_url( '/' ) => 'index.html' );
foreach ( array( 'buy', 'sell', 'relocate', 'contact' ) as $role ) {
    $id = hp_page_id( $role );
    if ( ! $id ) { throw new RuntimeException( 'Assign the ' . $role . ' page in WordPress first.' ); }
    $url = get_permalink( $id ); $path = trim( substr( wp_parse_url( $url, PHP_URL_PATH ), strlen( $base_path ) ), '/' );
    $routes[ $url ] = $path . '/index.html';
}
$properties = array();
foreach ( get_posts( array( 'post_type' => 'hp_property', 'post_status' => 'publish', 'numberposts' => -1 ) ) as $property ) {
    $url = get_permalink( $property->ID ); $path = trim( substr( wp_parse_url( $url, PHP_URL_PATH ), strlen( $base_path ) ), '/' );
    $routes[ $url ] = $path . '/index.html'; $properties[ $property->ID ] = $property->post_title;
}
foreach ( $routes as $url => $destination ) { hp_export_page( $url, $destination, $url === hp_page_url( 'contact' ) ); }
hp_export_page( home_url( '/hp-static-missing-page-938764/' ), '404.html' );
hp_export_write( $public . '/assets/properties.json', wp_json_encode( (object) $properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE ) );
hp_export_write( $public . '/assets/theme.js', file_get_contents( get_template_directory() . '/assets/theme.js' ) );
foreach ( array( 'demo.js', 'demo.css' ) as $file ) { hp_export_write( $public . '/assets/' . $file, file_get_contents( $release . '/scripts/' . $file ) ); }
hp_export_write( $public . '/robots.txt', "User-agent: *\nAllow: /\n" );
// Keep the portfolio's theme source synchronized with the exported snapshot.
$theme_root = get_template_directory();
foreach ( new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $theme_root, FilesystemIterator::SKIP_DOTS ) ) as $file ) {
    $relative = str_replace( '\\', '/', substr( $file->getPathname(), strlen( $theme_root ) + 1 ) );
    if ( str_starts_with( $relative, 'tests/' ) || str_starts_with( $relative, '.' ) ) { continue; }
    if ( in_array( strtolower( $file->getExtension() ), array( 'php', 'css', 'js', 'png', 'jpg', 'jpeg', 'webp', 'svg', 'md' ), true ) ) {
        hp_export_write( $release . '/wordpress-theme/' . $relative, file_get_contents( $file->getPathname() ) );
    }
}
echo 'Export complete: ' . count( $routes ) . ' pages, a 404 page, and ' . count( $copied ) . ' local assets.' . PHP_EOL;
