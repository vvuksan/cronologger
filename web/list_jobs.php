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

if ( isset( $_GET['showday'] ) && $_GET['showday'] != "" ) {
    $unixtime = strtotime($_GET['showday']);
} else {
    $unixtime = mktime(0,0,0,date("m"), date("d"), date("Y"));
}

$time_prev = $unixtime - 86400;
$prev_timeperiod = date('Y-m-d', $time_prev);
$time_after = $unixtime + 86400;
$next_timeperiod = date('Y-m-d', $time_after);    

?>

<center><a href="#" onclick='getJobsListing("<?php print $_GET['search_type'] . "\",\"" . $prev_timeperiod ; ?>"); return false;'><?php print $prev_timeperiod; ?></a> <----</a> Go to  
----><a href="#" onclick='getJobsListing("<?php print $_GET['search_type'] . "\",\"" . $next_timeperiod; ?>"); return false;'><?php print $next_timeperiod; ?></a>

<?php
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

  if ( isset( $_GET['search_type'] ) && $_GET['search_type'] == "errors" ) {

    print "<p><h2>Displaying jobs that ran on " . date("Y-m-d", $unixtime) ." and had errors</h2><p>";

  }

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

    if ( isset( $_GET['search_type'] ) && $_GET['search_type'] == "errors" ) {
      if (! $row["value"]["_attachments"]["stderr"]["length"] > 0 ) {
	continue;
      }
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
  } // end of foreach ( $view["rows"] as $key => $row )

  print "</tbody></table>";

} else {

  print "<p class=nojobs>No jobs to display</p>";

}

?>
<script type="text/javascript" id="js">
    $(document).ready(function() {
        // call the tablesorter plugin
        $("table").tablesorter();
}); </script> 
