<?php defined('SYSPATH') or die('No direct script access.');

return array(
	/*
	 * If true, the debug toolbar will be automagically displayed
	 * NOTE: if IN_PRODUCTION is set to TRUE, the toolbar will
	 * not automatically render, even if auto_render is TRUE
	 */
	'auto_render'    => Kohana::$environment > Kohana::PRODUCTION,

	'toolbar' => array(
		'enabled'    => FALSE,
		'minimized'  => FALSE,
		'align'      => 'right' // right, left or center
	),

	'plugin' => array(
		'enabled'    => TRUE
	),

	/*
	 * Enable or disable specific panels
	 */
	'panels' => array(
		'benchmarks' => TRUE,
		'database'   => TRUE,
		'vars'       => TRUE,
		'ajax'       => TRUE,
		'files'      => TRUE,
		'modules'    => TRUE,
		'routes'     => TRUE,
		'customs'    => TRUE,
		'logs'       => TRUE
	),

	/*
	 * Secret Key
	 */
	'secret_key'     => FALSE
);
