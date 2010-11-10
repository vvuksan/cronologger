<?php

require_once("./config.default.php");
# If there are any overrides include them now
if ( ! is_readable('./config.php') ) {
    echo("<H2>WARNING: Configuration file config.php does not exist. Please
         notify your system administrator.</H2>");
} else
    include_once('./config.php');

header("Content-Type: text/plain");

$couch_url = 'http://' . $couchdb_server . ':' . $couchdb_port;
$couch_url_full = $couch_url . "/" . $couchdb_database;

if ( isset($_GET['docid']) && isset($_GET['output']) ) {
  $attachment_url = $couch_url_full . "/" . $_GET['docid'] . "/" . $_GET['output'];
  if ($fp = fopen($attachment_url, "r")) {
    while (!feof ($fp)) {
      $line = fgets ($fp, 1024);
      print $line;
    }
    fclose($fp);
  }
}
?>
