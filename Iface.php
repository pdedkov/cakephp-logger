<?php
namespace Logger;

interface Iface {
	/**
	 *
	 * Логируем
	 *
	 * @param mixed $message сообщение
	 * @param int $level уровень лога
	 * @param string $destination сущность в которую логировать
	 * @param string $dir пункт назначения
	 * @return bool
	 */
	public function log($message, $level, $destination, $dir);

	/**
	 * Чистка логов, удовлетворяющих определённым условия
	 * @return mixed
	 */
	public function expire($conditions = []);
}