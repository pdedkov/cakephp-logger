<?php
namespace Logger\Engine;

use Logger\Iface;

use Config\Object as Base;

class File extends Base implements Iface {
	protected static $_defaults = array(
		// путь к логам
		'dir' => TMP,
		// лог по умолчанию
		'default' => 'log'
	);

	/**
	 * (non-PHPdoc)
	 * @see app/Lib/Logger/Logger.Iface::log()
	 */
	public function log($message, $level, $destination, $dir) {
		// если имя файла не передано пишем в default-ный log
		if (empty($destination)) {
			$destination = $this->_config['default'];
		}

		// обрабатываем директории для логов
		$dir = $this->_config['dir'] . DS . (!empty($dir) ? $dir : date("Y-m-d"));

		if (!is_dir($dir)) {
			// пытаемся её создать
			$old = umask(0);
			if (!@mkdir($dir, 0777)) {
				return false;
			}
			umask($old);
		} elseif (!is_writable($dir)) {
			return false;
		}


		// формируем полный путь к файлу
		$path = $dir . DS . $destination . ".log";

		// преобразовываем сообщение
		if (!is_string($message)) {
			if (is_array($message)) {
				$message = print_r($message, true);
			} else {
				$message = var_export($message, true);
			}
		}

		$old = umask(0);
		if ((!file_exists($path)) || (is_writable($path))) {
			if ($fh = fopen($path, 'a')) {
				fprintf($fh, "%s: |%s| -> %s\n", $level, date("Y-m-d H:i:s"), $message);
				fclose($fh);
				umask($old);
			} else {
				return false;
			}
		} else {
			return false;
		}

		return true;
	}

	/**
	 * Чистка хранилища по условию
	 *
	 * @param array $conditions условие
	 *
	 * @return bool
	 */
	public function expire($conditions = []) {
		$dir = $this->_config['dir'] . DS;
		// обрабатываем директории для логов
		// переходив в нужную директорию
		chdir($dir);
		$entries = scandir($dir);

		\App::uses('Folder', 'Utility');
		$Dir = new \Folder();

		$Dir->cd($dir);

		foreach ($entries as $entry) {
			if (!is_dir($entry) || in_array($entry, ['.', '..']) || !is_writable($entry)) {
				continue;
			}

			if (strtotime($entry) < strtotime($conditions)) {
				$Dir->delete($entry);
			}
		}

		return true;
	}
}