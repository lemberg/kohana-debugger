<?php defined('SYSPATH') OR die('No direct script access.');

/*
 * This code allows to send custom x-kohana-debugger header
 * with all the debug info at the and of the request.
 */
class Response extends Kohana_Response
{
	public function send_headers($replace = FALSE, $callback = NULL)
	{
		$this->headers('x-kohana-debugger', json_encode(Debugger::data()));
		return parent::send_headers($replace, $callback);
	}
}
