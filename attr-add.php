#!/usr/bin/php
<?php
require_once 'lib/init.php';
require_once 'lib/attribute_functions.php';

$args = getopt('', array('code:', 'label:', 'opts:', 'datatype:', 'normal', 'configurable', 'type:'));
if (empty($args) || empty($args['code']) || empty($args['label'])) {
	echo "1. Create a data attribute\n";
	echo "   Normal data attribute: attr-add.php --code CODE --label LABEL --type data --datatype DATATYPE [--normal]\n";
	echo "   Configurable data attribute: attr-add.php --code CODE --label LABEL --type data --datatype DATATYPE [--configurable]\n";
	echo "   Data types: varchar, int, decimal, datetime, text, currency\n";
	echo "2. Create a select attribute\n";
	echo "   Normal select attribute: attr-add.php --code CODE --label LABEL --type select [--normal] --opts [OPT,OPT,...]\n";
	echo "   Configurable select attribute: attr-add.php --code CODE --label LABEL --type select --configurable --opts [OPT,OPT,...]\n";
	exit(1);
}

$code = $args['code'];
$label = $args['label'];
$type = $args['type'];
$configurable = isset($args['configurable']);

switch ($type) {
	case 'data':
		$datatype = isset($args['datatype']) ? $args['datatype'] : null;
		$backendTypes = array(
			'string'	=> 'varchar',
			'varchar'	=> 'varchar',
			'int'		=> 'int',
			'decimal'	=> 'decimal',
			'double'	=> 'decimal',
			'datetime'	=> 'datetime',
			'text'		=> 'text',
			'currency'	=> 'decimal');
		$frontendInputs = array(
			'string'	=> 'text',
			'varchar'	=> 'text',
			'int'		=> 'text',
			'decimal'	=> 'text',
			'double'	=> 'text',
			'datetime'	=> 'date',
			'text'		=> 'textarea',
			'currency'	=> 'price');
		createDataAttribute($code, $label,
			$backendTypes[$datatype], $frontendInputs[$datatype],
			$configurable);
		break;
	case 'select':
		$options = isset($args['opts']) ? split(',', $args['opts']) : array();
		createSelectAttribute($code, $label, $configurable, $options);
		break;
	default:
		throw new Exception("Unknown attribute type: $type");
}
