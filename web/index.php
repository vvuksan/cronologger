<HTML>
<HEAD>
<META http-equiv="Content-type" content="text/html; charset=utf-8">
<SCRIPT TYPE="text/javascript" SRC="js/jquery-1.4.4.min.js"></script>
<script type="text/javascript" src="js/jquery-ui-1.8.6.custom.min.js"></script>
<script type="text/javascript" src="js/jquery.tablesorter.min.js"></script>
<link type="text/css" href="css/flick/jquery-ui-1.8.6.custom.css" rel="stylesheet" />
<link rel="stylesheet" href="css/jq.css" type="text/css" media="print, projection, screen" /> 
<link rel="stylesheet" href="css/style.css" type="text/css" id="" media="print, projection, screen" /> 
<script>
  $(function() {
    $( "#tabs" ).tabs();
  });

function getJobsListing(search_type,showday) {
  if ( search_type == "errors" ){
    targetdiv = "#tabs-jobs-with-errors";
  } else {
    targetdiv = "#tabs-job-log";
  }
  $.get('list_jobs.php', "search_type=" + search_type + "&showday=" + showday, function(data) {
    $(targetdiv).html('<img src="img/spinner.gif">');
    $(targetdiv).html(data);
    $("table").tablesorter();
  });
}

function getCurrentlyRunning() {
  $.get('list_running_jobs.php', "" , function(data) {
    $("#tabs-currently-running").html('<img src="img/spinner.gif">');
    $("#tabs-currently-running").html(data);
    $("table").tablesorter();
  });
}
</script>
<style>
td.centered {
	text-align: center;
}
</style>
</head>
<body>
<style type="text/css">
    body{ font: 75% "Trebuchet MS", sans-serif; margin: 5px;}
</style>
<div id="tabs">
    <ul>
	<li><a href="#tabs-jobs-with-errors">Jobs With Errors</a></li>
	<li><a onclick='getJobsListing("all", "");' href="#tabs-job-log">Job Log</a></li>
	<li><a onclick="getCurrentlyRunning();" href="#tabs-currently-running">Currently Running</a></li>
    </ul>

<div id="tabs-jobs-with-errors">
<img src="img/spinner.gif">
</div>

<div id="tabs-job-log">

</div>

<div id="tabs-currently-running">
<img src="img/spinner.gif">
</div>
</div>

<script>
getJobsListing("errors","#tabs-jobs-with-errors","");
</script>

</body>
</html>