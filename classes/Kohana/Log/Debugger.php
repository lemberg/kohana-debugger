<?php defined('SYSPATH') OR die('No direct script access.');

class Kohana_Log_Debugger extends Log_Writer
{
	protected static $_instance = null;
	protected $logs = array();
	protected function __construct() {}
	protected function __clone() {}

	public static function instance()
	{
		if(is_null(Log_Debugger::$_instance))
		{
			Log_Debugger::$_instance = new Log_Debugger();
		}

		return Log_Debugger::$_instance;
	}

	public function write(array $messages)
	{
		foreach($messages as $message)
		{
			$this->logs[] = UTF8::str_ireplace(PHP_EOL, "<br>", $this->format_message($message));
		}
	}

	public function getLogs()
	{
		return $this->logs;
	}

}
