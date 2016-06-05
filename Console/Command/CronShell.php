<?php
class CronShell extends AppShell {
	public $uses = [];

	/**
	 * Чистка лишних логов
	 * @return void
	 */
	public function expire() {
		try {
			// считываем всю директорию логов
			return !Logger\Instance::expire(Configure::read('Logger.expire'));
		} catch (\Exception $e) {
			return 1;
		}
	}
}