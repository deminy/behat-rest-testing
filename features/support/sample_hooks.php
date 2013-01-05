<?php
/**
 * @author   Demin Yin <deminy@deminy.net>
 * @license  MIT license
 */
use Behat\Behat\Event\FeatureEvent;

/**
 * Before entering the feature, we want to make sure data file is empty.
 *
 * @var Behat\Behat\Hook\Loader\ClosuredHookLoader $hooks
 */
$hooks->BeforeFeature(
	'',
	function(FeatureEvent $event) {
		$file = dirname(__FILE__) . '/../../www/employees.txt';
		file_put_contents($file, '');
	}
);

/**
 * After testing the feature, we want to make sure data file is empty.
 *
 * @var Behat\Behat\Hook\Loader\ClosuredHookLoader $hooks
 */
$hooks->AfterFeature(
	'',
	function(FeatureEvent $event) {
		$file = dirname(__FILE__) . '/../../www/employees.txt';
		file_put_contents($file, '');
	}
);
