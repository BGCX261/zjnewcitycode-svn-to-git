<?php

if(!defined('SABLOG_ROOT')) {
	exit('Access Denied');
}

class DB_MySQL  {

	var $querycount = 0;
	var $link;

	function connect($servername, $dbusername, $dbpassword, $dbname, $usepconnect=0) {
		$func = $usepconnect ? 'mysql_pconnect' : 'mysql_connect';
		if(!$this->link = @$func($servername, $dbusername, $dbpassword, 1)) {
			$this->halt('Can not connect to MySQL server');
		}

		if($this->version() > '4.1') {
			mysql_query("SET character_set_connection=utf8, character_set_results=utf8, character_set_client=binary", $this->link);
			if($this->version() > '5.0.1') {
				mysql_query("SET sql_mode=''", $this->link);
			}
		}
		$dbname && mysql_select_db($dbname, $this->link);
	}


	function geterrdesc() {
		return (($this->link) ? mysql_error($this->link) : mysql_error());
	}

	function geterrno() {
		return intval(($this->link) ? mysql_errno($this->link) : mysql_errno());
	}

	function insert_id() {
		return ($id = mysql_insert_id($this->link)) >= 0 ? $id : $this->result($this->query("SELECT last_insert_id()"), 0);
	}

	function fetch_array($query, $result_type = MYSQL_ASSOC) {
		return mysql_fetch_array($query, $result_type);
	}

	function query($sql, $type = '') {
		//echo "<div style=\"text-align: left;\">".htmlspecialchars($sql)."</div>";
		/*
		遇到问题时用这个来检查SQL执行语句
		writefile('sqlquerylog.txt', $sql."\n", 'a');
		*/
		$func = $type == 'UNBUFFERED' && @function_exists('mysql_unbuffered_query') ?
			'mysql_unbuffered_query' : 'mysql_query';
		if(!($query = $func($sql)) && $type != 'SILENT') {
			$this->halt('MySQL Query Error', $sql);
		}
		$this->querycount++;
		return $query;
	}
	
	function unbuffered_query($sql) {
		$query = $this->query($sql, 'UNBUFFERED');
		return $query;
	}

	function select_db($dbname) {
		return mysql_select_db($dbname, $this->link);
	}

	function fetch_row($query) {
		$query = mysql_fetch_row($query);
		return $query;
	}

	function fetch_one_array($query) {
		$result = $this->query($query);
		$record = $this->fetch_array($result);
		return $record;
	}

	function num_rows($query) {
		$query = mysql_num_rows($query);
		return $query;
	}

	function num_fields($query) {
		return mysql_num_fields($query);
	}
	
	function result($query, $row) {
		$query = @mysql_result($query, $row);
		return $query;
	}
	
	function free_result($query) {
		$query = mysql_free_result($query);
		return $query;
	}

	function version() {
		return mysql_get_server_info($this->link);
	}

	function close() {
		return mysql_close($this->link);
	}

	function halt($msg ='', $sql=''){
		global $php_self,$timestamp,$onlineip,$action,$job,$sax_uid,$sax_group;

		if ($sql) {
			$sqlcontent = "<?PHP exit('Access Denied'); ?>\t$timestamp\t$onlineip\t".basename($php_self).($job ? '/job='.$job : '').($action ? '/action='.$action : '')."\t".htmlspecialchars($this->geterrdesc())."\t".str_replace(array("\r", "\n", "\t"), array(' ', ' ', ' '), trim(htmlspecialchars($sql)))."\n";
			writefile(SABLOG_ROOT.'data/log/dberrorlog.php', $sqlcontent, 'a');
		}

		$message = "<html>\n<head>\n";
		$message .= "<meta content=\"text/html; charset=utf-8\" http-equiv=\"Content-Type\">\n";
		$message .= "<style type=\"text/css\">\n";
		$message .=  "body,p,pre {\n";
		$message .=  "font:12px Verdana;\n";
		$message .=  "}\n";
		$message .=  "</style>\n";
		$message .= "</head>\n";
		$message .= "<body bgcolor=\"#FFFFFF\" text=\"#000000\" link=\"#006699\" vlink=\"#5493B4\">\n";

		$message .= "<p><strong>".htmlspecialchars($msg)."</strong></p>\n";
		if ($sax_uid && $sax_group == 1) {
			$message .= "<strong>Mysql error description</strong>: ".htmlspecialchars($this->geterrdesc())."\n<br />";
			$message .= "<strong>Mysql error number</strong>: ".$this->geterrno()."\n<br />";
		}
		$message .= "<strong>Date</strong>: ".date("Y-m-d @ H:i")."\n<br />";
		$message .= "<strong>Script</strong>: http://".$_SERVER['HTTP_HOST'].getenv("REQUEST_URI")."\n<br />";

		$message .= "</body>\n</html>";
		echo $message;
		exit;
	}
}
?>