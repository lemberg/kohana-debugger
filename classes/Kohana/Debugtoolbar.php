<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Kohana v3 Debug Toolbar
 *
 * @package Debug Toolbar
 * @author  Aaron Forsander <http://grimhappy.com/>
 * @author  Ivan Brotkin (BIakaVeron) <BIakaVeron@gmail.com>
 * @author  Sergei Gladkovskiy <smgladkovskiy@gmail.com>
 */
abstract class Kohana_Debugtoolbar {

	/**
	 * Queries container
	 *
	 * @var bool|array
	 */
	protected static $_queries = FALSE;

	/**
	 * Benchmarks container
	 *
	 * @var bool|array
	 */
	protected static $_benchmarks = FALSE;

	/**
	 * Custom tabs container
	 *
	 * @var array
	 */
	protected static $_custom_tabs = array();

	/**
	 * Can we render toolbar?
	 *
	 * @var bool
	 */
	protected static $_enabled = TRUE;

	/**
	 * Benchmark name
	 *
	 * @var string
	 */
	public static $benchmark_name = 'debug_toolbar';

	/**
	 * Renders the Debug Toolbar
	 *
	 * @static
	 * @return bool|string
	 */
	public static function render()
	{
		if ( ! self::is_enabled())
		{
			return FALSE;
		}
		
		$config = Kohana::$config->load('debug_toolbar');
		if (!$config->toolbar_enabled && !$config->plugin_enabled)
		{
			return false;
		}

		$token    = Profiler::start('custom', self::$benchmark_name);

		$template = new View('toolbar');

		
		
		$data = array();

		// Database panel
		if ($config->panels['database'] === TRUE)
		{
			$data['queries'] = self::get_queries();
			/*$template
				->set('queries', $queries['data'])
				->set('query_count', $queries['count'])
				->set('total_time', $queries['time'])
				->set('total_memory', $queries['memory']);*/
		}

		// Files panel
		if ($config->panels['files'] === TRUE)
		{
			$data['files'] = self::get_files();
			//$template->set('files', $files);
		}

		// Modules panel
		if ($config->panels['modules'] === TRUE)
		{
			$data['modules'] = self::get_modules();
			//$template->set('modules', self::get_modules());
		}

		// Routes panel
		if ($config->panels['routes'] === TRUE)
		{
			$data['routes'] = self::get_routes(Request::initial()->route());
			//$template->set('routes', self::get_routes());
		}

		// Custom data
		if ($config->panels['customs'] === TRUE)
		{
			$data['customs'] = self::get_customs();
			//$template->set('customs', $customs);
		}

		// Set alignment for toolbar
		switch ($config->toolbar["align"])
		{
			case 'right':
			case 'center':
			case 'left':
				$template->set('align', $config->toolbar["align"]);
				break;
			default:
				$template->set('align', 'left');
		}
		
		// Javascript for toolbar
		$template->set('scripts', file_get_contents(Kohana::find_file('views', 'toolbar', 'js')));

		// CSS for toolbar
		$styles = file_get_contents(Kohana::find_file('views', 'toolbar', 'css'));

		Profiler::stop($token);

		// Benchmarks panel
		if ($config->panels['benchmarks'] === TRUE)
		{
			$data['benchmarks'] = self::get_benchmarks();
			//$template->set('benchmarks', self::get_benchmarks());
		}
		
		if ($config->panels['vars'] === TRUE)
		{
			$data['vars']['post'] = isset($_POST) ? Debug::vars($_POST) : Debug::vars(array());
			$data['vars']['get'] = isset($_GET) ? Debug::vars($_GET) : Debug::vars(array());
			$data['vars']['files'] = isset($_FILES) ? Debug::vars($_FILES) : Debug::vars(array());
			$data['vars']['server'] = isset($_SERVER) ? Debug::vars($_SERVER) : Debug::vars(array());
			$data['vars']['cookie'] = isset($_COOKIE) ? Debug::vars($_COOKIE) : Debug::vars(array());
			$data['vars']['session'] = isset($_SESSION) ? Debug::vars($_SESSION) : Debug::vars(array());
		}
		
		if ($config->panels['logs'] === TRUE)
		{
			$data['logs'] = Log_DebugTool::$logs;
		}

		$template->set('styles', $styles);
		
		$template->set('data', $data);
		
		echo $template->render();
	}

	/**
	 * Adds custom data to render in a separate tab
	 *
	 * @param  string $tab_name
	 * @param  mixed  $data
	 *
	 * @return void
	 */
	public static function add_custom($tab_name, $data)
	{
		self::$_custom_tabs[$tab_name] = $data;
	}

	/**
	 * Get user vars
	 *
	 * @return array
	 */
	public static function get_customs()
	{
		$result = array();

		foreach (self::$_custom_tabs as $tab => $data)
		{
			if (is_array($data) OR is_object($data) OR is_bool($data))
			{
				$data = Debug::dump($data);
			}

			$result[$tab] = $data;
		}

		return $result;
	}

	/**
	 * Retrieves query benchmarks from Database
	 *
	 * @return  array
	 */
	public static function get_queries()
	{
		if (self::$_queries !== FALSE)
		{
			return self::$_queries;
		}

		$result = array();
		$count  = $time = $memory = 0;

		$groups = Profiler::groups();
		foreach (Database::$instances as $name => $db)
		{
			$group_name = 'database ('.strtolower($name).')';
			$group      = arr::get($groups, $group_name, FALSE);

			if ($group)
			{
				$sub_time = $sub_memory = $sub_count = 0;
				foreach ($group as $query => $tokens)
				{
					$sub_count += count($tokens);
					foreach ($tokens as $token)
					{
						$total           = Profiler::total($token);
						$sub_time       += $total[0];
						$sub_memory     += $total[1];
						$result[$name][] = array(
							'name'   => $query,
							'time'   => $total[0],
							'memory' => $total[1]
						);
					}
				}
				$count  += $sub_count;
				$time   += $sub_time;
				$memory += $sub_memory;

				$result[$name]['total'] = array($sub_count, $sub_time, $sub_memory);
			}
		}
		self::$_queries = array(
			'count'  => $count,
			'time'   => $time,
			'memory' => $memory,
			'data'   => $result
		);

		return self::$_queries;
	}

	/**
	 * Creates a formatted array of all Benchmarks
	 *
	 * @return array formatted benchmarks
	 */
	public static function get_benchmarks()
	{
		if (Kohana::$profiling == FALSE)
		{
			return array();
		}

		if (self::$_benchmarks !== FALSE)
		{
			return self::$_benchmarks;
		}

		$groups = Profiler::groups();
		$result = array();
		foreach (array_keys($groups) as $group)
		{
			if (strpos($group, 'database (') === FALSE)
			{
				foreach ($groups[$group] as $name => $marks)
				{
					$stats            = Profiler::stats($marks);
					$result[$group][] = array
					(
						'name'         => $name,
						'count'        => count($marks),
						'total_time'   => $stats['total']['time'],
						'avg_time'     => $stats['average']['time'],
						'total_memory' => $stats['total']['memory'],
						'avg_memory'   => $stats['average']['memory'],
					);
				}
			}
		}
		// add total stats
		$total                 = Profiler::application();
		$result['application'] = array
		(
			'count'        => 1,
			'total_time'   => $total['current']['time'],
			'avg_time'     => $total['average']['time'],
			'total_memory' => $total['current']['memory'],
			'avg_memory'   => $total['average']['memory'],

		);

		self::$_benchmarks = $result;

		return $result;
	}

	/**
	 * Get list of included files
	 *
	 * @return array file currently included by php
	 */
	public static function get_files()
	{
		$result = array();
		$result['list'] = array();
		
		$files = (array)get_included_files();
		sort($files);
		
		$total_size = 0;
		$total_lines = 0;

		foreach ($files as $file_name)
		{
			$size = filesize($file_name);
			$lines = count(file($file_name));

			$total_size += $size;
			$total_lines += $lines;
			$result['list'][] = array(
				'name' => $file_name,
				'size' => $size,
				'lines' => $lines,
			);
		}
		$result['total_size'] = $total_size;
		$result['total_lines'] = $total_lines;
		return $result;
	}

	/**
	 * Get module list
	 *
	 * @return array  module_name => module_path
	 */
	public static function get_modules()
	{
		$result = array();
		$result['list'] = array();
		$modules = Kohana::modules();

		foreach ($modules as $name => $path)
		{
			$result['list'][] = array(
				'name' => $name,
				'path' => $path,
				'abs_path' => realpath($path),
			);
		}
		return $result;
	}

	/**
	 * Returns all application routes
	 *
	 * @return array
	 */
	public static function get_routes($current_route)
	{
		$result = array();
		$result['list'] = array();
		$routes = Route::all();
		foreach($routes as $name => $route)
		{
			$result['list'][] = array(
				'current' => $route == $current_route? 1 : 0,
				'name' => $name,
			);
		}
		return $result;
	}



	/**
	 * Disable toolbar
	 * @static
	 */
	public static function disable()
	{
		self::$_enabled = FALSE;
	}

	/**
	 * Enable toolbar
	 * @static
	 */
	public static function enable()
	{
		self::$_enabled = TRUE;
	}

	/**
	 * Determines if all the conditions are correct to display the toolbar
	 * (pretty kludgy, I know)
	 *
	 * @static
	 * @return bool
	 */
	public static function is_enabled()
	{
		// disabled with Debugtoolbar::disable() call
		if (self::$_enabled === FALSE) {
			return FALSE;
		}

		$config = Kohana::$config->load('debug_toolbar');

		// Auto render if secret key isset
		if ($config->secret_key !== FALSE AND isset($_GET[$config->secret_key]))
		{
			return TRUE;
		}

		// Don't auto render when in PRODUCTION (this can obviously be
		// overridden by the above secret key)
		if (Kohana::$environment == Kohana::PRODUCTION)
		{
			return FALSE;
		}

		// Don't auto render toolbar for ajax requests
		if (Request::initial() === NULL OR Request::initial()->is_ajax())
		{
			return FALSE;
		}

		// Don't auto render toolbar for cli requests
		if (PHP_SAPI == 'cli')
		{
			return FALSE;
		}

		// Don't auto render toolbar if $_GET['debug'] = 'false'
		if (isset($_GET['debug']) AND strtolower($_GET['debug']) == 'false')
		{
			return FALSE;
		}

		return TRUE;
	}
}