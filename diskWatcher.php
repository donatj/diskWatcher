#!/usr/bin/env php
<?php

require('vendor/autoload.php');

$config = parse_ini_file('config.ini', true);

$po = new donatj\Pushover($config['pushover']['api_key'], $config['pushover']['user_key']);

$flags      = new donatj\Flags();
$min        = & $flags->uint('min-free', null, 'Minimum amount free in gigs.');
$path       = & $flags->string('path', '/', 'Path to watch disk space of.');
$sleep      = & $flags->uint('sleep', 10, 'Seconds to sleep between disk checks.');
$note_sleep = & $flags->uint('note-sleep', 300, 'Seconds to sleep after a notification.');

try {
	$flags->parse();
} catch(Exception $e) {
	die($e->getMessage() . PHP_EOL . $flags->getDefaults() . PHP_EOL);
}

$gig_bytes = 1073741824;
$min_bytes = $min * $gig_bytes;

while( true ) {
	$free = disk_free_space($path);

	if( $free < $min_bytes ) {
		$po->send('Under minimum gigs on ' . gethostname(), array( 'priority' => 1, 'sound' => 'updown' )) or die('Message Failed');
		echo "notified\n";
		sleep($note_sleep);
	} else {
		echo intval($free / $gig_bytes) . ' ';
		sleep($sleep);
	}
}

