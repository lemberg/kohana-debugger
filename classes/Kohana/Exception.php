<?php defined('SYSPATH') OR die('No direct script access.');

class Kohana_Exception extends Kohana_Kohana_Exception
{
	public static function response(Exception $e)
	{
		$response = parent::response($e);
		if (Kohana::$config->load('debug_toolbar.auto_render') === TRUE)
		{
			$response->body($response->body() . Debugtoolbar::render());
		}
		return $response;
	}
}