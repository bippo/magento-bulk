#!/usr/bin/php
<?php
require_once 'lib/init.php';
require_once 'lib/attribute_functions.php';

$args = getopt('', array('code:', 'label:', 'opts:', 'normal', 'configurable', 'type:'));
if (empty($args) || empty($args['code']) || empty($args['label'])) {
	echo "Create an attribute\n";
	echo "Normal select attribute: attr-add.php --code CODE --label LABEL --type select [--normal] --opts [OPT,OPT,...]\n";
	echo "Configurable select attribute: attr-add.php --code CODE --label LABEL --type select --configurable --opts [OPT,OPT,...]\n";
	exit(1);
}

$code = $args['code'];
$label = $args['label'];
$type = $args['type'];
$configurable = isset($args['configurable']);
$opts = isset($args['opts']) ? split(',', $args['opts']) : array();

createSelectAttribute($code, $label, true, $opts);
