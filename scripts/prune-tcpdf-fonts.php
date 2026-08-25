<?php
/**
 * Keep only core + DejaVu Sans font faces used by Gutena Forms PDF export.
 *
 * @package Gutena Forms
 */

$fonts_dir = dirname( __DIR__ ) . '/vendor/tecnickcom/tcpdf/fonts';

if ( ! is_dir( $fonts_dir ) ) {
	fwrite( STDERR, "TCPDF fonts directory not found; skipping prune.\n" );
	exit( 0 );
}

// TCPDF loads Helvetica by default; keep core Type1 defs + DejaVu Sans faces we use.
$keep_prefixes = array(
	'helvetica',
	'courier',
	'times',
	'symbol',
	'zapfdingbats',
	'dejavusans.',
	'dejavusansb.',
	'dejavusansi.',
	'dejavusansbi.',
);

$removed = 0;
foreach ( scandir( $fonts_dir ) as $entry ) {
	if ( '.' === $entry || '..' === $entry ) {
		continue;
	}

	$path = $fonts_dir . DIRECTORY_SEPARATOR . $entry;
	if ( is_dir( $path ) ) {
		continue;
	}

	$keep = false;
	foreach ( $keep_prefixes as $prefix ) {
		if ( 0 === strpos( $entry, $prefix ) ) {
			$keep = true;
			break;
		}
	}

	if ( $keep || 'readme.md' === strtolower( $entry ) ) {
		continue;
	}

	if ( @unlink( $path ) ) {
		++$removed;
	}
}

fwrite( STDOUT, "Pruned {$removed} unused TCPDF font files.\n" );
