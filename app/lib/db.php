<?php

// MySQL connection setting

define('DBHOST', 'localhost');
define('DBUSER', 'root');
define('DBPASS', 'abrari');
define('DBNAME', 'fonetik');
define('DBPREFIX', '');

class mysqlDB {

    var $dbconn;
    var $queries = array();
    var $num_queries = 0;
    var $num_rows;

    function dberror($msg) {
	exit("<h1>$msg</h1>");
    }

    function query_error() {
	exit("Terjadi kesalahan saat menyimpan data. \n\n" . "Pesan kesalahan : " . mysql_error($this->dbconn));
    }

    function connect() {	
	$this->dbconn = @mysql_connect(DBHOST, DBUSER, DBPASS);
	if ($this->dbconn === false) $this->dberror('Koneksi database gagal!');
	@mysql_select_db(DBNAME, $this->dbconn) or $this->dberror('Akses database gagal!');
    }

    function query($query) {
	$res = @mysql_query($query, $this->dbconn);
	if ($res === false) $this->query_error();
	else {
	    $this->queries[] = $query;
	    $this->num_queries++;
	    return $res;
	}
    }

    function get_result($query, $as = 'row') {		// no auto-transpose
	$result = $this->query($query);
	$num_rows = @mysql_num_rows($result);
	$num_fields = @mysql_num_fields($result);

	$resultarray = array();

	$this->num_rows = $num_rows;

	if ($num_rows > 0) {
	    while ($row = @call_user_func("mysql_fetch_$as", $result)) {
		if ($num_fields == 1)
		    $resultarray[] = $row[0];
		else
		    $resultarray[] = $row;
	    }
	}
	
	return $resultarray;

    }

    function get_data($table, $column, $row, $value) {
	$data = $this->get_result("SELECT `$column` FROM `$table` WHERE `$row` = '$value'");
	return $data;
    }

}

