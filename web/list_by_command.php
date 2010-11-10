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

?>

<?php
// view fetching, using the view option limit
try {
    if ( isset( $_GET['command'])) {
      $client->key($_GET['command']);
    }

    $view = $client->limit(100)->asArray()->getView('cronview','by_commandline');    
} catch (Exception $e) {
    echo "ERROR while getting view contents: ".$e->getMessage()."<BR>\n";
}

##############################################################
# Did we get any response for the key we were looking for
##############################################################
if ( sizeof($view["rows"]) > 0 ) {

  print "
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

    "<td> <a href='list_by_command.php?command=" . $row['value']['command_line'] . "'>" . $row["value"]["command_line"] . "</a></td>" .
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
