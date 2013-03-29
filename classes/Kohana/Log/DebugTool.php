<?php defined('SYSPATH') OR die('No direct script access.');

class Kohana_Log_DebugTool extends Log_Writer
{
	public static $logs = array();
	public function __construct() {}

	public function write(array $messages)
	{
		foreach ($messages as $message)
		{
			self::$logs[] = UTF8::str_ireplace(PHP_EOL, "<br>", $this->format_message($message));
		}
	}

}