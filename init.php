<?php defined('SYSPATH') or die('No direct script access.');

if (Kohana::$config->load('debugger.panels.logs') === TRUE)
{
	Kohana::$log->attach(Log_Debugger::instance());
}
