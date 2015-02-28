<?php
include "../vendor/autoload.php";

use TomVerran\DataDog\Parser;
use TomVerran\Stats\Storage\Database\MysqlConfiguration;
use TomVerran\Stats\Storage\DatabaseStorage;

/**
 * This tiny entirely procedural application consumes JSON from DataDog and stores it in a database.
 * Note that currently there is no authentication so anyone can push stats to this URL.
 */
//DataDog posts the JSON as the raw request body...
$input = stream_get_contents( fopen( 'php://input', 'rb' ) );
if ( isset( $_SERVER['HTTP_CONTENT_ENCODING'] ) && $_SERVER['HTTP_CONTENT_ENCODING'] == 'deflate' ) {
    $input = gzinflate( substr( $input ,2,-4 ) );
}

$parser = new Parser;
$request = $parser->parse( $input );

$configuration = new MysqlConfiguration;
$configuration->setDatabase( 'metrics' )
              ->setHost( 'localhost' )
              ->setUsername( 'root' )
              ->setPassword( '' );

$storage = new DatabaseStorage( $configuration );
foreach ( $request->getMetrics() as $metric ) {
    $storage->store( $metric );
}