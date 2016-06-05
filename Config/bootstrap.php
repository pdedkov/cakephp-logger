<?php
Configure::write('Logger.engine', 'File');

require  'Engine' . DS . Configure::read('Logger.engine') . DS . 'config.php';

Configure::write('Logger.expire', '-5 days');