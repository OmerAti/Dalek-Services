<?php
/*				
//	(C) 2021 DalekIRC Services
\\				
//			pathweb.org
\\				
//	GNU GENERAL PUBLIC LICENSE
\\							v3
//				
\\				
//				
\\	Title:		SQL
//				
\\	Desc:		Provides basic essential client-side
//				SQL querying.
\\				
//				
\\				
//				
\\	Version:	1
//				
\\	Author:		Valware
//				
*/


class SQL {
	
	function __construct($ip,$user,$pass,$db){
		global $ip,$user,$pass,$db;
	}
	function query($query){
		global $sqlip,$sqluser,$sqlpass,$sqldb,$cf;
		$conn = mysqli_connect($sqlip,$sqluser,$sqlpass,$sqldb);
		if (!$conn) { return "ERROR"; }
		else {
			$result = mysqli_query($conn,$query);
			return $result;
		}
	}
	function user_insert($u){
		global $sqlip,$sqluser,$sqlpass,$sqldb,$cf;
		$conn = mysqli_connect($sqlip,$sqluser,$sqlpass,$sqldb);
		if (!$conn) { return "ERROR"; }
		else {
			$prep = $conn->prepare("INSERT INTO dalek_user (
				
				nick,
				timestamp,
				ident,
				realhost,
				account,
				UID,
				usermodes,
				cloak,
				ip,
				SID
			) VALUES (
				?,
				?,
				?,
				?,
				?,
				?,
				?,
				?,
				?,
				?
			)");
			$ip = inet_ntop(base64_decode($u['ipb64']));
			$prep->bind_param("ssssssssss",$u['nick'],$u['timestamp'],$u['ident'],$u['realhost'],$u['account'],$u['uid'],$u['usermodes'],$u['cloak'],$ip,$u['sid']);
			$prep->execute();
			$prep->close();
		}
	}
	function sid($u){
		global $sqlip,$sqluser,$sqlpass,$sqldb,$cf;
		$conn = mysqli_connect($sqlip,$sqluser,$sqlpass,$sqldb);
		if (!$conn) { return "ERROR"; }
		else {
			$prep = $conn->prepare("INSERT INTO dalek_server (
				
				servername,
				hops,
				sid,
				version
			) VALUES (
				?,
				?,
				?,
				?
			)");
			
			$prep->bind_param("ssss",$u['server'],$u['hops'],$u['sid'],$u['desc']);
			$prep->execute();
			$prep->close();
		}
	}
	function sjoin($u){
		global $sqlip,$sqluser,$sqlpass,$sqldb,$cf;
		$conn = mysqli_connect($sqlip,$sqluser,$sqlpass,$sqldb);
		if (!$conn) { return "ERROR"; }
		else {

			$prep = $conn->prepare("INSERT INTO dalek_channels (
				timestamp,
				channel,
				modes
			) VALUES (
				?,
				?,
				?
			)");
			
			$prep->bind_param("sss",$u['timestamp'],$u['channel'],$u['modes']);
			$prep->execute();
			$prep->close();
		}
	}
}
			

hook::func("preconnect", function($u){
	global $sql;
	
	$query = "CREATE TABLE IF NOT EXISTS dalek_user (
		id int NOT NULL AUTO_INCREMENT,
		nick varchar(255) NOT NULL,
		timestamp int NOT NULL,
		ident varchar(255) NOT NULL,
		realhost varchar(255) NOT NULL,
		UID varchar(255) NOT NULL,
		usermodes varchar(255),
		cloak varchar(255) NOT NULL,
		ip varchar(255) NOT NULL,
		account varchar(255),
		secure varchar(1),
		fingerprint varchar(255),
		SID varchar(3) NOT NULL,
		oper varchar(1),
		away varchar(1),
		awaymsg varchar(255),
		version varchar(255),		
		PRIMARY KEY (id)
	)";
	$sql::query($query);
	
	$query = "CREATE TABLE IF NOT EXISTS dalek_server (
		id int NOT NULL AUTO_INCREMENT,
		servername varchar(255),
		sid varchar(3) NOT NULL,
		linktime varchar(10),
		usermodes varchar(255),
		channelmodes varchar(255),
		hops varchar(4),
		version varchar(255),
		PRIMARY KEY (id)
	)";
	$sql::query($query);
	
	$query = "CREATE TABLE IF NOT EXISTS dalek_channels {
		id int NOT NULL AUTO_INCREMENT,
		timestamp int NOT NULL,
		channel varchar(255),
		modes varchar(255),
		topic varchar(255),
		PRIMARY KEY (id)
	)";
	$sql::query($query);
	$query = 	"TRUNCATE TABLE dalek_user";
	$sql::query($query);
	$query = 	"TRUNCATE TABLE dalek_channels";
	$sql::query($query);
	$query = 	"TRUNCATE TABLE dalek_server";
	$sql::query($query);
});

	

hook::func("UID", function($u){
	
	global $sql;
	
	$sql::user_insert($u);
	
});
	

hook::func("SID", function($u){
	
	global $sql;
	
	$sql::sid($u);
	
});

hook::func("SJOIN", function($u){
	global $sql;
	
	$sql::sjoin($u);
});


function find_person($person){
	
	global $sql,$ns;
	$query = "SELECT * FROM dalek_user WHERE nick = '$person'";
	$result = $sql::query($query);
	
	if (!$result){ goto uidcheck; }
	if (mysqli_num_rows($result) == 0){ goto uidcheck; }
	$row = mysqli_fetch_assoc($result);
	return $row;
	
	uidcheck:
	
	$query = "SELECT * FROM dalek_user WHERE UID = '$person'";
	$result = $sql::query($query);
	
	if (!$result){ return; }
	if (mysqli_num_rows($result) == 0){ return false; }
	$row = mysqli_fetch_assoc($result);
	mysqli_free_result($result);
	return $row;
}

function find_channel($channel){
	
	global $sql,$ns;
	$query = "SELECT * FROM dalek_channels WHERE channel = '$channel'";
	$result = $sql::query($query);
	if (!$result){ return false; }
	if (mysqli_num_rows($result) == 0){ return false; }
	$row = mysqli_fetch_assoc($result);
	mysqli_free_result($result);
	return $row;
}