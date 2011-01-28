<?php
/*
 * Warp-style redirector.
 * Include file at the top of your index.php file to check for redirects and redirect if required
 * Redirects are simple URL -> URL redirects, all processed as simple 301s
 * Redirects are matched with and without the domain and with and without parameters
 */

/*
 * Step 1: Configure your redirector
 * Redirector expects to find a database table called g6_redirects in a MySQL database
 * Provide credentials for this database and alias the table, if necessary, in the parameters below
 * 
 * Structure for the table is as follows
 
	CREATE TABLE `g6_redirects` (
	  `fromurl` VARCHAR(2048)  NOT NULL,
	  `tourl` VARCHAR(2048)  NOT NULL
	)
	ENGINE = MyISAM;
 
 * 2048 is the maximum URL length supported by Internet Explorer
 * Do *NOT* include "http://" as a prefix to the URLs

 * When adding data to this table:
 * 1: fromurl should not have the http:// or https:// prefix
 * 2: torul should have the http:// or https:// prefix UNLESS you want relative redirects.
 */

$server = '';
$username = '';
$password = '';
$database = '';
$redirectstable = 'g6_redirects';

/* 
 * Step 2: The system connects to the database and looks for a redirect for the current URL
 * If, for any reason, we are unable to connect, we ignore this issue and simply exit.
 */

if ($db = mysql_connect($server,$username,$password)){
	mysql_select_db($database,$db);
	
	/*
	 * Step 3. Check for redirect of the whole URL or the URL without parameters
	 */
	$url = $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
	$domainlessurl = $_SERVER['REQUEST_URI'];
	
	if (strpos($url,'?') > 0){
		$pureurl = substr($url, 0, strpos($url,'?'));
		$puredomainlessurl = substr($domainlessurl, 0, strpos($domainlessurl,'?'));
	} else {
		$pureurl = '';
		$puredomainlessurl = '';
	}
	
	/*
	 * Step 4: Build the SQL Query and run it
	 */
	
	$redirectorSQL = 'SELECT tourl FROM ' . $redirectstable . ' WHERE fromurl = "' . $url . '" OR fromurl ="' . $domainlessurl . '" ';
	if (strlen($pureurl) > 0){ $redirectorSQL .= ' OR fromurl = "' . $pureurl . '" OR fromurl ="' . $puredomainlessurl . '"';}
	
	$redirects = mysql_query($redirectorSQL,$db);
	
	/*
	 * Step 5: Check result for redirect
	 * If a result is found, set the $newurl parameter ready for redirection
	 */
	if (mysql_num_rows($redirects) > 0){
		$newurl = mysql_result($redirects, 0);
	} else {
		$newurl = '';
	}
	
	/*
	 * Step 6: Close off DB connection cleanly
	 */
	mysql_close($db);
	
	/*
	 * Step 7: If there is a redirect, apply it now and exit processing
	 * We exit processing to prevent other scripts running and give a fast redirect if possible
	 */
	if (strlen($newurl) > 0){
		header('Location: ' . $newurl,TRUE,301);
		exit;
	}
	
}

?>
