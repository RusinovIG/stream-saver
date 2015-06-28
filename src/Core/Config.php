<?php
/**
 * Created by PhpStorm.
 * User: Rusinov
 * Date: 28.06.15
 * Time: 13:21
 */

namespace Core;

class Config implements \ArrayAccess {

	/**
	 * Массив параметров
	 * @var array
	 */
	private $config = [
		'project_root' => '/home/garun/test/',
		'tmp_dir' => 'tmp/',

		// DB config
		'server_name' => 'localhost',
		'db_user' => 'test',
		'db_pass' => 'test',
		'db_name' => 'test',

		// Stream settings
		'stream_url' => 'rtmp://edge3.live.irib.ir/etv/irinn-3',
		'video_length' => '10', // Длительность видео-файлов в секундах
		'video_intersection_length' => '3', // Количество секунд до конца видео,
											// за которое нужно начать писать следующий файл
		'video_dir' => 'storage/video/',
		'video_format' => 'mp4'
	];

	/**
	 * @param mixed $offset
	 * @return boolean true on success or false on failure.
	 */
	public function offsetExists($offset) {
		return isset($this->config[$offset]);
	}

	/**
	 * @param mixed $offset
	 * @return mixed Can return all value types.
	 */
	public function offsetGet($offset) {
		return $this->config[$offset];
	}

	/**
	 * @param mixed $offset
	 * @param mixed $value
	 * @return void
	 */
	public function offsetSet($offset, $value) {
		$this->config[$offset] = $value;
	}

	/**
	 * @param mixed $offset
	 * @return void
	 */
	public function offsetUnset($offset) {
		unset($this->config[$offset]);
	}
}