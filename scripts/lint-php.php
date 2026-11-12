<?php
/** Syntax-only checks for tracked source; no application files are included. */
if (PHP_SAPI !== 'cli') {
	header('HTTP/1.1 403 Forbidden');
	exit;
}
if (PHP_MAJOR_VERSION !== 7 || PHP_MINOR_VERSION !== 3) {
	fwrite(STDERR, "Run this check with PHP CLI 7.3. Current: ".PHP_VERSION."\n");
	exit(2);
}
$root = dirname(__DIR__);
$descriptors = array(0 => array('pipe', 'r'), 1 => array('pipe', 'w'), 2 => array('pipe', 'w'));
$process = proc_open('git ls-files -z -- "*.php"', $descriptors, $pipes, $root);
if (!is_resource($process)) {
	fwrite(STDERR, "Cannot run Git.\n");
	exit(2);
}
fclose($pipes[0]);
$listing = stream_get_contents($pipes[1]);
$gitError = stream_get_contents($pipes[2]);
fclose($pipes[1]);
fclose($pipes[2]);
if (proc_close($process) !== 0 || $listing === '') {
	fwrite(STDERR, "Cannot list tracked PHP files.\n");
	exit(2);
}
$files = array_filter(explode("\0", $listing), 'strlen');
$failed = 0;
foreach ($files as $file) {
	$command = escapeshellarg(PHP_BINARY).' -n -d short_open_tag=1 -l '.escapeshellarg($root.'/'.$file);
	$process = proc_open($command, $descriptors, $pipes, $root);
	if (!is_resource($process)) {
		fwrite(STDERR, "Cannot start PHP lint: ".$file."\n");
		exit(2);
	}
	fclose($pipes[0]);
	$output = stream_get_contents($pipes[1]);
	$error = stream_get_contents($pipes[2]);
	fclose($pipes[1]);
	fclose($pipes[2]);
	if (proc_close($process) !== 0) {
		$failed++;
		fwrite(STDERR, "PHP_SYNTAX_ERROR: ".$file."\n".$output.$error);
	}
}
if ($failed) {
	fwrite(STDERR, "PHP_SYNTAX_FAILED: ".$failed." file(s).\n");
	exit(1);
}
echo 'PHP_SYNTAX_OK: '.count($files).' tracked PHP files; PHP '.PHP_VERSION.".\n";
