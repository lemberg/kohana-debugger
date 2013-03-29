<?php defined('SYSPATH') or die('No direct script access.');

// Render Debug Toolbar on the end of application execution
if (Kohana::$config->load('debug_toolbar.auto_render') === TRUE)
{
	register_shutdown_function('Debugtoolbar::render');
}

if (Kohana::$config->load('debug_toolbar.panels.logs') === TRUE)
{
	Kohana::$log->attach(new Log_DebugTool());
}