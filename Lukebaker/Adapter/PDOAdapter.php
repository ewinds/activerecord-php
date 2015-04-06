<?php namespace Lukebaker\Adapter;

class PDOAdapter implements DatabaseAdapter {

  static function get_dbh($host="localhost", $port="3306", $db="", $user="", $password="", $driver="mysql") {
    $dbh = new \PDO("$driver:host=$host;port=$port;dbname=$db;charset=UTF8", $user, $password);
    $dbh->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    return $dbh;
  }

  static function query($query, $dbh=null) {
    return $dbh->query($query, \PDO::FETCH_ASSOC);
  }

  static function quote($string, $dbh=null, $type=null) {
    return $dbh->quote($string, $type);
  }

  static function last_insert_id($dbh=null, $resource=null) {
    return $dbh->lastInsertId($resource);
  }

}
