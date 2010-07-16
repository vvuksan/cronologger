<html>
<head>
<title>Cronologger : View</title>
<link rel="stylesheet" href="css/jq.css" type="text/css" media="print, projection, screen" /> 
<link rel="stylesheet" href="css/style.css" type="text/css" id="" media="print, projection, screen" /> 
<script language="javascript" type="text/javascript" src="js/jquery.min.js"></script> 
<script type="text/javascript" src="js/jquery.tablesorter.min.js"></script> 
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

// set a new connector to the CouchDB server
$client = new couchClient ('http://' . $couchdb_server . ':' . $couchdb_port, $couchdb_database );

// view fetching, using the view option limit
try {
    $end_time = time();
    $start_time = $end_time - 86400;
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

  print "<h2>Jobs that ran within last 24 hours</h2><p>
  <table cellspacing=1 class=tablesorter border=1>
  <thead>
  <tr><th>Start time</th><th>Job duration</th><th>Username</th><th>Hostname</th><th>Command</th></tr>
  </thead>
  <tbody>";

  foreach ( $view["rows"] as $key => $row ) {
    
    print "<tr><td>" . $row["value"]["time"] . "</td>" .
    "<td align=center>" . $row["value"]["job_duration"] . "</td>" .    
    "<td>" . $row["value"]["username"] . "</td>" .
    "<td>" . $row["value"]["hostname"] . "</td>" .
    "<td>" . $row["value"]["command_line"] . "</td>" .
    "</tr>\n";
  }

  print "</tbody></table>";

} else {

  print "No jobs running";

}

?>
<script type="text/javascript" id="js">
    $(document).ready(function() {
        // call the tablesorter plugin
        $("table").tablesorter();
}); </script> 

</body>
</html>