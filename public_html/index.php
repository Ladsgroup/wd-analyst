<head>

<title>Wikidata Analyst</title>

<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="Keywords" content="wikidata,analysis,quality">
<meta name="Description" content="A tool to give better insight to people who want to improve or examine quality of Wikidata">

<link rel="stylesheet" type="text/css" href="semantic/dist/semantic.min.css"><script src="semantic/dist/semantic.min.js"></script>
<body>
  <div class="main nav">
    <div class="ui inverted main menu">
      <div class="container">
        <div class="left menu">
          <div class="title item">
            <b>Wikidata Analyst</b>
          </div><a href="/wd-analyst/index.php" class="launch item">Home</a>
		<a href="/" class="launch item">Labs</a>
        </div>

        <div class="right menu">
          <a href="/wd-analyst/about.php" class="item">About</a>
        </div>
      </div>
    </div>
  </div>

<div class="ui tabular menu" style="padding:10px 10px 0px 10px;">
  <a class="item active">
    Property-based analysis
  </a>
  <a class="item" href="sitelink.php">
    Sitelink-based analysis
  </a>
  <a class="item" href="ref.php">
    Reference-based analysis
  </a>
</div>

<?php
error_reporting(E_ERROR|E_CORE_ERROR|E_COMPILE_ERROR); // E_ALL|
ini_set('display_errors', 'On');
$dbmycnf = parse_ini_file("../replica.my.cnf");
$dbuser = $dbmycnf['user'];
$dbpass = $dbmycnf['password'];
unset($dbmycnf);
$dbhost = "tools-db";
$dbname = "s52781__wd_p";
function fixer($number) {
	return number_format((float)$number, 3, '.', '');
};
$db = new PDO('mysql:host='.$dbhost.';dbname='.$dbname.';charset=utf8', $dbuser, $dbpass);
if (!isset($_REQUEST['p'])) { ?>
<div style="padding:1em;width:50em;">
<div class="ui message">
  <div class="header">
    Let's start!
  </div>
  <p>Hey! Please determine what property-value pair you want to see. If you want to get result for all values of your property leave value part blank</p>
</div>
<form class="ui form" action="index.php" method="get">
  <div class="fields">
    <div class="field">
      <label>Property</label>
      <input name="p" placeholder="P31" type="text">
    </div>
    <div class="field">
      <label>Value</label>
      <input name="q" placeholder="Q5" type="text">
    </div>
  </div>
<button class="ui button" type="submit">Run the query</button>
</div>
</div>
<?php
} else {
	$p = ucfirst($_REQUEST['p']);
	if (!isset($_REQUEST['q']) | !$_REQUEST['q']) {
		$_REQUEST['q'] = 'Q0';
	};
	$qs = explode('|', $_REQUEST['q']);
	$q = $qs[0];
	if (substr($p, 0, 1) === 'P') { $p = substr($p, 1); }
	$n_q = array();
	foreach ($qs as $q) {
		$q = ucfirst($q);
		if (substr($q, 0, 1) === 'Q') { $q = substr($q, 1); }
		if (!is_numeric($q)) {
?>
<div style="padding:1em;width:50em;">
<div class="ui negative message">
  <div class="header">
    Thata's bad!
  </div>
  <p>It seems your values for property and/or value is incorrect. Bear in mind we only support Wikidata item (Q###) for value.</p>
</div>
</div>
<?php
		die('ValueError');
		};
		$n_q[] = $q + 0;
	};
	if (!is_numeric($p)) {
?>
<div style="padding:1em;width:50em;">
<div class="ui negative message">
  <div class="header">
    Thata's bad!
  </div>
  <p>It seems your values for property and/or value is incorrect. Bear in mind we only support Wikidata item (Q###) for value.</p>
</div>
</div>
<?php
	die('ValueError');
	};
	$p = $p + 0;
	if ($n_q[0] === 0) {
		$sql = "SELECT * FROM property WHERE property = " . $p . " ORDER BY no_uniq_items DESC LIMIT 5";
	} else {
		$sql = "SELECT * FROM property WHERE property = " . $p . " and value IN (0,".implode(',',$n_q).") ORDER BY no_uniq_items DESC";
	};
	$result = $db->query($sql);
	$result = $result->fetchAll();
	if (!$result) {
?>
<div style="padding:1em;width:50em;">
<div class="ui negative message">
  <div class="header">
    Thata's bad!
  </div>
  <p>It seems your we can't find anything from our database. Bear in mind we only support Wikidata item (Q###) for value.</p>
</div>
</div>
<?php
	die('ValueError');
	};
?>
<div style="padding:2em;">
<div class="ui positive message" style="width:50em;">
  <div class="header">
    Results are ready!
  </div>
  <p>This table gives you the data and charts are here because they are cool!<br>Last update: 2015-11-30</p>
</div>
<table class="ui celled table">
  <thead>
    <tr><th>Property</th>
    <th>Value</th>
    <th>Number of statements</th>
    <th>Number of items</th>
    <th>Ave. labels</th>
    <th>Ave. sitelinks</th>
    <th>Ave. descriptions</th>
    <th>Ave. claims</th>
    <th>Ave. qualifiers</th>
    <th>Ave. references</th>
    <th>Ave. Wiki refs.</th>
  </tr></thead>
  <tbody>
<?php
	$pie_data = array();
	$pie_data2 = array();
	$first_row = $result[0][3];
	$sum_pie = 0;
	$bar_data = array();
	$bar_data2 = array();
	foreach ($result as $row) {
		if ($row[1] == 0) {
			$row[1] = "\n<div class=\"ui ribbon label\">All values</div>\n";
			$row[0] = "<a href=\"https://wikidata.org/wiki/P" . $row[0] . "\">P". $row[0] . "</a>";
		} else {
			$row[1] = "Q" . $row[1];
			$row[0] = "P" . $row[0];
			$sum_pie = $sum_pie + (int)$row[3];
			$pie_data[] = $row[3];
			$pie_data2[] = $row[1];
			$row[1] = "<a href=\"https://wikidata.org/wiki/" . $row[1] . "\">". $row[1] . "</a>";
		};
		$d = array($row[0], $row[1], $row[2], $row[3], fixer($row[4] / $row[2]), fixer($row[5] / $row[2]), fixer($row[6] / $row[2]), fixer($row[7] / $row[2]), fixer($row[8] / $row[2]), fixer($row[9] / $row[2]), fixer($row[10] / $row[2]));
		$bar_data[] = implode(',', array(fixer($row[4] / $row[2]), fixer($row[5] / $row[2]), fixer($row[6] / $row[2]), fixer($row[7] / $row[2])));
		$bar_data2[] = implode(',', array(fixer($row[8] / $row[2]), fixer($row[9] / $row[2]), fixer($row[10] / $row[2])));
		echo "<tr>\n<td>{$d[0]}</td>\n<td>{$d[1]}</td><td>{$d[2]}</td><td>{$d[3]}</td><td>{$d[4]}</td><td>{$d[5]}</td><td>{$d[6]}</td><td>{$d[7]}</td><td>{$d[8]}</td><td>{$d[9]}</td><td>{$d[10]}</td></tr>";
	};
echo "</tbody>\n</table>";
$pie_data[] = $first_row - $sum_pie;
$legend = array('All');
$legend = array_merge($legend, $pie_data2);
$pie_data2[] = "Other";
$pie_data = implode("|", $pie_data);
$pie_data2 = implode("|", $pie_data2);
$bar_data = implode("|", $bar_data);
$bar_data2 = implode("|", $bar_data2);
$legend = implode("|", $legend);
echo "<div class=\"ui large rounded bordered images\">";
echo "<img src=\"pie.php?d=$pie_data&l=$pie_data2\">";
echo "<img src=\"bar.php?d=$bar_data&title=Average data per statement&x=&y=&xaxis=label|sitelink|desc.|claim&legend=$legend\">";
echo "<img src=\"bar.php?d=$bar_data2&title=Average data per claim&x=&y=&xaxis=qualifier|ref.|wiki ref.&legend=$legend\">";
echo "</div></div>";
};
?>
