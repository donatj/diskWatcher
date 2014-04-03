#!/usr/bin/env php
<?php

require('vendor/autoload.php');

date_default_timezone_set(@date_default_timezone_get());

$config = parse_ini_file('config.ini', true);

$po = new donatj\Pushover($config['pushover']['api_key'], $config['pushover']['user_key']);

$flags      = new donatj\Flags();
$min        = & $flags->uint('min-free', null, 'Minimum amount free in gigs.');
$sleep      = & $flags->uint('sleep', 10, 'Seconds to sleep between disk checks.');
$note_sleep = & $flags->uint('note-sleep', 300, 'Seconds to sleep after a notification.');

try {
	$flags->parse();
	if(count($flags->args()) > 1) {
		throw new Exception('Too many arguments');
	}
} catch(Exception $e) {
	die($e->getMessage() . PHP_EOL . $flags->getDefaults() . PHP_EOL);
}

$path = $flags->arg(0) ?: '/';

$gig_bytes = 1073741824;
$min_bytes = $min * $gig_bytes;

while( true ) {
	$free = disk_free_space($path);

	$readable_gb = intval($free / $gig_bytes);
	if( $free < $min_bytes ) {
		$po->send('Under minimum space for "'. $path .'" on ' . gethostname() . '. at ' . $readable_gb, array( 'priority' => 1, 'sound' => 'updown' )) or die('Message Failed');
		echo $readable_gb . " - notified\n";
		sleep($note_sleep);
	} else {
		echo "\033[2K\0337";
		echo "{$readable_gb} GB\t" . date("Y-m-d H:i:s");
		sleep($sleep);
		echo "\0338";
	}
}

