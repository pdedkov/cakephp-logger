<?php
namespace Logger;

use Config\Singleton as Base;

/**
 * Класс для логирования каких-то действия, замер времени выполнения
 */
class Instance extends Base {
	/**
	 * набор констант для логирования с определением префикса
	 *
	 */
	const LOG_OK			= 1;
	const LOG_NOTICE		= 2;
	const LOG_ERROR			= 4;
	const LOG_CRITICAL		= 8;
	const LOG_INFO			= 16;
	const LOG_AHTUNG		= 32;
	const LOG_EXCEPTION		= 64;
	const LOG_DEBUG			= 128;
	const LOG_STAT			= 256;

	protected static $_logMessage = [
		self::LOG_OK			=> '[OK]',
		self::LOG_NOTICE		=> '[NOTICE]',
		self::LOG_ERROR			=> '[ERROR]',
		self::LOG_CRITICAL		=> '[CRITICAL]',
		self::LOG_INFO			=> '[INFO]',
		self::LOG_AHTUNG		=> '[AHTUNG]',
		self::LOG_EXCEPTION		=> '[EXCEPTION]',
		self::LOG_DEBUG			=> '[DEBUG]'
	];

	/**
	 * Массив таймеров, чтоб можно было мерять не одно действие, а много
	 *
	 * @var array
	 * @access private
	 */
	protected static $_timers = [];

	/**
	 * Время запуска для одного действия
	 * @var int
	 * @access private
	 */
	protected static $_startTime = null;


	protected static $_instance = null;

	protected static $_defaults = ['engine' => 'File'];

	/**
	 * Механизм логированиея
	 * @var  object
	 */
	protected $_Engine = null;

	protected function __construct($config = array()) {
		parent::__construct(__NAMESPACE__, $config);

		$class = "Logger\Engine\\{$this->_config['engine']}";

		if (!class_exists($class)) {
			throw new Exception('Неверный механизм логирования');
		}

		$this->_Engine = new $class(null, $this->_config[$this->_config['engine']]);
	}

	public static function getInstance() {
		if (is_null(static::$_instance)) {
			static::$_instance = new self();
		}

		return static::$_instance;
	}

	/**
	 * Статический метод для записи лога
	 *
	 * @param string $message сообщение
	 * @param const $level уровень ошибки
	 * @param string $file  файл в который пишем лог (добавляется дата и расширение .log)
	 *
	 * @return bool
	 */
	public static function log($message, $level = self::LOG_DEBUG, $destination = null, $dir = null) {
		$_this = self::getInstance();

		if (func_num_args() < 3 && func_num_args() > 1) {
			$destination = $level;
			$level = self::LOG_INFO;
		}


		if ($level == self::LOG_DEBUG && !\Configure::read('debug')) {
			return true;
		}

		if ($level == self::LOG_STAT) {
			$destination = "stat_" . $destination;
		}

		$level = isset(self::$_logMessage[$level]) ? self::$_logMessage[$level] : self::$_logMessage[self::LOG_INFO];

		return (bool)$_this->_Engine->log($message, $level, $destination, $dir);
	}

	/**
	 * Чистка устаревших логов
	 *
	 * @param [] $conditions условие чистки
	 * @return bool
	 */
	public static function expire($conditions = []) {
		$_this = self::getInstance();

		return (bool)$_this->_Engine->expire($conditions);
	}

	/**
	 * Засекает время
	 *
	 * @param string $title какое действие будем мерять
	 * @return bool
	 */
	public static function startTiming($title = null) {
		if (empty($title)) {
			self::$_startTime = microtime(true);
		} else {
			self::$_timers[$title] = microtime(true);
		}

		return true;
	}

	/**
	 * Записывает отмеренное время вместе с сообщением в файл
	 *
	 * @param string $message
	 * @param string $file
	 *
	 * @return bool
	 */
	public static function logTiming($message, $title = null, $file = 'performance') {
		if (
			(!self::$_startTime && empty($title))
			|| ((!empty($title) && !self::$_timers[$title]))) {
			trigger_error("No start time!", E_USER_WARNING);
			return false;
		}
		if (empty($title)) {
			$time = round(microtime(true) - self::$_startTime, 6);
			self::$_startTime = null;
		} else {
			$time = round(microtime(true) - self::$_timers[$title], 6);
			self::$_timers[$title] = null;
		}

		return self::log("[$time] $message", self::LOG_INFO, $file);
	}
}