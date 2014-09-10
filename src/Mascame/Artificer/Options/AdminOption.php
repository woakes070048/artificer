<?php namespace Mascame\Artificer\Options;

class AdminOption extends Option {

	public static $key = 'admin';

	public static function get($key = null)
	{
		return Option::get(self::$key . '.' . $key);
	}

	public static function has($key = '')
	{
		return Option::has(self::$key . '.' . $key);
	}

	public static function all($key = null)
	{
		if (!$key) {
			$key = self::$key;
		}

		return Option::get($key);
	}
}