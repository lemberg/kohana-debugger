<?php defined('SYSPATH') or die('No direct script access.');

if (Kohana::$config->load('debugger.auto_render') === TRUE)
{
	//register_shutdown_function(array('Debugger', 'render'));
}

if (Kohana::$config->load('debugger.panels.logs') === TRUE)
{
	Kohana::$log->attach(Log_Debugger::instance());
}
