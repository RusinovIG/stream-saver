<?php
/**
 * Created by PhpStorm.
 * User: Rusinov
 * Date: 30.06.15
 * Time: 0:08
 */

namespace Core;

/**
 * Обертка для работы с БД
 */
class DB {

	/**
	 * @var \PDO
	 */
	private $pdo;

	/**
	 * @param $serverName
	 * @param $userName
	 * @param $password
	 * @param $dbName
	 */
	public function __construct(
		$serverName,
		$userName,
		$password,
		$dbName
	) {
		try {
			$this->pdo = new \PDO('mysql:host=' . $serverName . ';dbname=' . $dbName, $userName, $password);
		} catch (\PDOException $e) {
			die($e->getMessage());
		}
	}

	/**
	 * @param $sql
	 * @return \PDOStatement
	 */
	public function select($sql) {
		$res = $this->pdo->query($sql);
		return $res;
	}

	/**
	 * @param $sql
	 * @param int $params
	 * @return \PDOStatement
	 */
	public function execute($sql, $params = 0) {
		$statement = $this->pdo->prepare($sql);
		if ($params == 0)  {
			$statement->execute();
		} else {
			$statement->execute($params);
		}
		return $statement;
	}

	/**
	 * @param $sql
	 * @param int $params
	 * @return mixed
	 */
	public function fetch($sql, $params = 0) {
		$statement = $this->execute($sql, $params);
		return $statement->fetch(\PDO::FETCH_ASSOC);
	}

	/**
	 * @param $sql
	 * @param int $params
	 * @return array
	 */
	public function fetch_all($sql, $params = 0) {
		$statement = $this->execute($sql, $params);
		return $statement->fetchALL(\PDO::FETCH_ASSOC);
	}

	/**
	 * @param $table
	 * @param $data
	 * @return string
	 */
	public function insertArray($table, $data) {
		$fields = "";
		$fieldsValues = "";
		foreach (array_keys($data) as $key) {
			$fields .= (($fields == "") ? '' : ',') . $key;
			$fieldsValues .= (($fieldsValues == "") ? ':' : ',:') . $key;
		}
		$query = "INSERT " . $table . " (" . $fields . ") VALUES (" . $fieldsValues . ")";
		$this->execute($query, $data);
		return $this->pdo->lastInsertId();
	}
}