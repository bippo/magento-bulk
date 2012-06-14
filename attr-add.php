#!/usr/bin/php
<?php
require_once 'lib/init.php';
require_once 'lib/attribute_functions.php';

$args = getopt('', array('code:', 'label:', 'opts:', 'datatype:', 'normal', 'configurable', 'type:'));
if (empty($args) || empty($args['code']) || empty($args['label'])) {
	echo "1. Create a data attribute\n";
	echo "   Normal data attribute: attr-add.php --code CODE --label LABEL --type data --datatype DATATYPE [--normal]\n";
	echo "   Configurable data attribute: attr-add.php --code CODE --label LABEL --type data --datatype DATATYPE [--configurable]\n";
	echo "   Data types: varchar, int, decimal, datetime, text\n";
	echo "2. Create a select attribute\n";
	echo "   Normal select attribute: attr-add.php --code CODE --label LABEL --type select [--normal] --opts [OPT,OPT,...]\n";
	echo "   Configurable select attribute: attr-add.php --code CODE --label LABEL --type select --configurable --opts [OPT,OPT,...]\n";
	exit(1);
}

$code = $args['code'];
$label = $args['label'];
$type = $args['type'];
$datatype = isset($args['datatype']) ? $args['datatype'] : null;
$configurable = isset($args['configurable']);
$options = isset($args['opts']) ? split(',', $args['opts']) : array();

switch ($type) {
	case 'data':
		createDataAttribute($code, $label, $datatype, $configurable);
		break;
	case 'select':
		createSelectAttribute($code, $label, $configurable, $options);
		break;
	default:
		throw new Exception("Unknown attribute type: $type");
}
