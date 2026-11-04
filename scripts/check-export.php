<?php
if ( PHP_SAPI !== 'cli' ) { http_response_code( 404 ); exit; }
$root = dirname( __DIR__ ) . '/public'; $checked = 0;
function check_path( $url, $file ) {
    global $root, $checked;
    if ( ! str_starts_with( $url, '/' ) || str_starts_with( $url, '//' ) ) { return; }
    $path = rawurldecode( parse_url( $url, PHP_URL_PATH ) );
    $target = $root . $path;
    if ( is_dir( $target ) ) { $target .= '/index.html'; }
    if ( ! is_file( $target ) ) { throw new RuntimeException( "Broken local reference in $file: $url" ); }
    $checked++;
}
$pages = 0;
foreach ( new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $root, FilesystemIterator::SKIP_DOTS ) ) as $file ) {
    if ( 'html' !== $file->getExtension() ) { continue; }
    $pages++; $html = file_get_contents( $file->getPathname() );
    if ( preg_match( '/localhost|127\.0\.0\.1|_wpcf7|rest_route|wp-config\.php|<\?php/i', $html ) ) { throw new RuntimeException( 'Unsafe runtime reference: ' . $file ); }
    $doc = new DOMDocument(); libxml_use_internal_errors( true ); $doc->loadHTML( '<?xml encoding="UTF-8">' . $html ); libxml_clear_errors();
    $xpath = new DOMXPath( $doc );
    foreach ( $xpath->query( '//*[@href or @src or @srcset]' ) as $node ) {
        foreach ( array( 'href', 'src' ) as $attribute ) { if ( $node->hasAttribute( $attribute ) ) { check_path( $node->getAttribute( $attribute ), $file->getFilename() ); } }
        if ( $node->hasAttribute( 'srcset' ) ) { foreach ( explode( ',', $node->getAttribute( 'srcset' ) ) as $part ) { check_path( preg_split( '/\s+/', trim( $part ) )[0], $file->getFilename() ); } }
    }
    $forms = $xpath->query( '//form' );
    if ( str_contains( $file->getPathname(), DIRECTORY_SEPARATOR . 'contact' . DIRECTORY_SEPARATOR ) ) {
        if ( 1 !== $forms->length || 'hp-demo-form' !== $forms->item( 0 )->getAttribute( 'id' ) ) { throw new RuntimeException( 'Contact demo form missing/duplicated.' ); }
        if ( 5 !== $xpath->query( '//form//*[@required]' )->length ) { throw new RuntimeException( 'Five required fields expected.' ); }
    } elseif ( 0 !== $forms->length ) { throw new RuntimeException( 'Unexpected form outside Contact.' ); }
}
if ( 9 !== $pages ) { throw new RuntimeException( 'Expected 8 public pages plus 404.' ); }
echo "PASS: $pages HTML files and $checked internal link/image/script/style references. No localhost/PHP/CF7 runtime dependencies.\n";
