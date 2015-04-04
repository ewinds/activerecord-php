<?php namespace Lukebaker;

/**
*	The database manager class.
*/
class Manager
{
	/**
	 * The current globally used instance.
	 *
	 * @var object
	 */
	protected static $instance;

	/**
	 * The database config.
	 *
	 * @var array
	 */
	protected $config = [
		'AR_ADAPTER' => 'MySQL', // could be 'PDO'
		'AR_DRIVER' =>  'mysql',
		'AR_HOST' =>    'localhost',
		'AR_PORT' =>    '3306',
		'AR_DB' =>      'activerecord',
		'AR_USER' =>    'activerecord',
		'AR_PASS' =>    'gafUthzeed5',
		'AR_PREFIX' => 'prefix_',
		/* used in generate.php to determine which tables we want models for
		  remove or unset if all tables in a db are wanted */
		'AR_TABLES' => [
		  'posts',
		  'comments',
		  'slugs',
		  'categories',
		  'categorizations',
		  'authors',
		]
	];

	/**
	 * Make this capsule instance available globally.
	 *
	 * @return void
	 */
	public function setAsGlobal()
	{
		static::$instance = $this;
	}

	/**
	 * Set the database config infomation.
	 *
	 * @return void
	 */
	public function config(array $config)
	{
		$this->config = array_merge($this->config, $config);
	}

	/**
	 * Get this global database config.
	 *
	 * @return array
	 */
	public static function getConfig()
	{
		return static::$instance->config;
	}
}
