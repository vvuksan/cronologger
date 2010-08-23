<html>
<head>
<title>Cronologger : Only Show Errors</title>
<link rel="stylesheet" href="css/jq.css" type="text/css" media="print, projection, screen" /> 
<link rel="stylesheet" href="css/style.css" type="text/css" id="" media="print, projection, screen" /> 
<script language="javascript" type="text/javascript" src="js/jquery.min.js"></script> 
<script type="text/javascript" src="js/jquery.tablesorter.min.js"></script> 
<style>
td.centered {
	text-align: center;
}
</style>

</head>
<body>
<?php

require_once("./config.default.php");
# If there are any overrides include them now
if ( ! is_readable('./config.php') ) {
    echo("<H2>WARNING: Configuration file config.php does not exist. Please
         notify your system administrator.</H2>");
} else
    include_once('./config.php');

require_once 'lib/couch.php';
require_once 'lib/couchClient.php';
require_once 'lib/couchDocument.php';

$couch_url = 'http://' . $couchdb_server . ':' . $couchdb_port;
$couch_url_full = $couch_url . "/" . $couchdb_database;

// set a new connector to the CouchDB server
$client = new couchClient ($couch_url , $couchdb_database);

if ( isset( $_GET['showday'] )) {
    $unixtime = strtotime($_GET['showday']);
} else {
    $unixtime = mktime(0,0,0,date("m"), date("d"), date("Y"));
}

$time_prev = $unixtime - 86400;
$prev_timeperiod = date('Y-m-d', $time_prev);
$time_after = $unixtime + 86400;
$next_timeperiod = date('Y-m-d', $time_after);    

?>

<center><a href=?showday=<?php print $prev_timeperiod .'>' . $prev_timeperiod; ?> <----</a> Go to  
<a href=?showday=<?php print $next_timeperiod . ">----> " . $next_timeperiod ;?></a>

<?
// view fetching, using the view option limit
try {
    $start_time = $unixtime;
    $end_time = $start_time + 86400;
    $client->startkey($start_time);    
    $client->endkey($end_time);
    $view = $client->limit(100)->asArray()->getView('cronview','by_unixtime');    
} catch (Exception $e) {
    echo "ERROR while getting view contents: ".$e->getMessage()."<BR>\n";
}

##############################################################
# Did we get any response for the key we were looking for
##############################################################
if ( sizeof($view["rows"]) > 0 ) {

  print "<p><h2>Displaying jobs that ran on " . date("Y-m-d", $unixtime) ." and had errors</h2><p>
  <table cellspacing=1 class=tablesorter border=1>
  <thead>
  <tr><th>Start time</th><th>Job duration</th><th>Return code</th>
  <th>Username</th><th>Hostname</th><th>Command</th>
  <th>StdOut (Bytes)</th><th>StdErr (Bytes)</th>
  </tr>
  </thead>
  <tbody>";

  foreach ( $view["rows"] as $key => $row ) {
    if (! $row["value"]["_attachments"]["stderr"]["length"] > 0 ) {
       continue;
    }
    
    $docid = $row["value"]["_id"];
    print "<tr><td>" . $row["value"]["time"] . "</td>" .
    "<td align=center>" . $row["value"]["job_duration"] . "</td>" .
    "<td align=center>" . $row["value"]["return_code"] . "</td>" .    
    "<td class=centered>" . $row["value"]["username"] . "</td>" .
    "<td class=centered>" . $row["value"]["hostname"] . "</td>" .
    "<td>" . $row["value"]["command_line"] . "</td>" .
    "<td class=centered><a href=get_attachment.php?docid=" . $docid . "&output=stdout>" . $row["value"]["_attachments"]["stdout"]["length"] . "</a></td>" .
    "<td class=centered><a href=get_attachment.php?docid=" . $docid . "&output=stderr>" . $row["value"]["_attachments"]["stderr"]["length"] . "</a></td>" .
    "</tr>\n";
  }

  print "</tbody></table>";

} else {

  print "<p class=nojobs>No jobs running</p>";

}

?>
<script type="text/javascript" id="js">
    $(document).ready(function() {
        // call the tablesorter plugin
        $("table").tablesorter();
}); </script> 

</body>
</html>
