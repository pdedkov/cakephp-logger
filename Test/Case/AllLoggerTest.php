<?php
/**
 * AllLoggerTest class
 *
 * Все тесты Logger-а, сейчас это скорее просто заглушка, т.к. Engine только File, но если что
 */
class AllLoggerTest extends PHPUnit_Framework_TestSuite {
	/**
	 * 	Метод сборки
	 */
	public static function suite() {
		$suite = new CakeTestSuite('All Logger tests');

		$suite->addTestFile(__DIR__ . DS . 'InstanceTest.php');

		return $suite;
	}
}