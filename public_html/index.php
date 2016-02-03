<?php
require_once('header.php');

function fixer($number) {
	return number_format((float)$number, 3, '.', '');
};

error_reporting(E_ERROR|E_CORE_ERROR|E_COMPILE_ERROR); // E_ALL|
ini_set('display_errors', 'On');
$dbmycnf = parse_ini_file("../replica.my.cnf");
$dbuser = $dbmycnf['user'];
$dbpass = $dbmycnf['password'];
unset($dbmycnf);
$dbhost = "tools-db";
$dbname = "s52781__wd_p";
$db = new PDO('mysql:host='.$dbhost.';dbname='.$dbname.';charset=utf8', $dbuser, $dbpass);
?>
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
if (!isset($_REQUEST['p'])) { ?>
<div style="padding:1em;width:60em;">
<div class="ui message">
  <div class="header">
    Let's start!
  </div>
  <p>Hey! Please determine what property-value pair you want to see. If you want to get result for all values of your property, leave value part blank.<br /><br />
You can give only a property and it gives you the four most
used values + analyze of sources and quality in overall (<a href="http://tools.wmflabs.org/wd-analyst/index.php?p=P31">Run the query</a>)<br />
You also can give a property and multiple values you want. <a href="http://tools.wmflabs.org/wd-analyst/index.php?p=P27&q=Q30|Q183">For example</a> in case you want to compare P27:Q183 (Country of citizenship: Germany) and P27:Q30 (US) you should use "Q30|Q183".</p>
</div>
<form class="ui form" action="index.php" method="get">
  <div class="fields">
    <div class="field">
      <label>Property</label>
      <input name="p" placeholder="e.g. P31" type="text">
    </div>
    <div class="field">
      <label>Value</label>
      <input name="q" placeholder="e.g. Q5" type="text">
    </div>
  </div>
<button class="ui button" type="submit">Run the query</button>
</div>
</div>
<?php
} else {
	$p = ucfirst($_REQUEST['p']);
	if (!isset($_REQUEST['limit']) | !$_REQUEST['limit']) {
		$_REQUEST['limit'] = 5;
	};
	if (!is_numeric($_REQUEST['limit'])) {
		Error("It seems limit is not set correctly");
	};
	$limit = $_REQUEST['limit'] + 0;
	if ($limit > 50 ) {
		Error("Maximum value for limit is 50");
	};
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
			Error();
		};
		$n_q[] = $q + 0;
	};
	if (!is_numeric($p)) {
		Error();
	};
	$p = $p + 0;
	if ($n_q[0] === 0) {
		$sql = "SELECT * FROM property WHERE property = " . $p . " ORDER BY no_uniq_items DESC LIMIT " . $limit;
	} else {
		$sql = "SELECT * FROM property WHERE property = " . $p . " and value IN (0,".implode(',',$n_q).") ORDER BY no_uniq_items DESC";
	};
	$result = $db->query($sql);
	$result = $result->fetchAll();
	if (!$result) {
		Error("It seems your we can't find anything from our database. Bear in mind we only support Wikidata item (Q###) for value.");
	};
?>
<div style="padding:2em;">
<div class="ui positive message" style="width:50em;">
  <div class="header">
    Results are ready!
  </div>
  <p>This table gives you data and charts.<br>Last update: 2015-12-06<br />First row is about the property and all values.</p>
</div>
<table class="ui selectable celled table">
  <thead>
    <tr><th>Property</th>
    <th>Value</th>
    <th>Number of statements</th>
    <th>Number of items</th>
    <th>Number of unsourced statements</th>
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
	$labels_old = array();
	foreach ($result as $row) {
		if ($row[1] == 0) {
			$labels_old[] = "P" . $row[0];
		} else {
			$labels_old[] = "Q" . $row[1];
		};
	};
	$labels = array();
	$label_res = LabelGetter($labels_old);
	for($i = 0, $size = count($labels_old); $i < $size; ++$i) {
	    $labels[$labels_old[$i]] = $label_res[$i];
	}
	foreach ($result as $row) {
		if ($row[1] == 0) {
			$row[1] = "\n<div class=\"ui ribbon label\">All values</div>\n";
			$row[0] = "<a href=\"https://wikidata.org/wiki/Property:P" . $row[0] . "\">". $labels["P" . $row[0]] . "</a>";
		} else {
			$row[1] = "Q" . $row[1];
			$row[0] = $labels["P" . $row[0]];
			$sum_pie = $sum_pie + (int)$row[3];
			$pie_data[] = $row[3];
			$pie_data2[] = $labels[$row[1]];
			$row[1] = "<a href=\"https://wikidata.org/wiki/" . $row[1] . "\">". $labels[$row[1]] . "</a>";
		};
		$d = array($row[0], $row[1], number_format($row[2]), number_format($row[3]), fixer($row[4] / $row[2]), fixer($row[5] / $row[2]), fixer($row[6] / $row[2]), fixer($row[7] / $row[2]), fixer($row[8] / $row[2]), fixer($row[9] / $row[2]), fixer($row[10] / $row[2]), number_format($row[11]), intval(($row[11] * 100) / $row[2]));
		$bar_data[] = implode(',', array(fixer($row[4] / $row[2]), fixer($row[5] / $row[2]), fixer($row[6] / $row[2]), fixer($row[7] / $row[2])));
		$bar_data2[] = implode(',', array(fixer($row[8] / $row[2]), fixer($row[9] / $row[2]), fixer($row[10] / $row[2])));
		echo "<tr>\n<td>{$d[0]}</td>\n<td>{$d[1]}</td><td>{$d[2]}</td><td>{$d[3]}</td><td>{$d[11]} ($d[12]%)</td><td>{$d[4]}</td><td>{$d[5]}</td><td>{$d[6]}</td><td>{$d[7]}</td><td>{$d[8]}</td><td>{$d[9]}</td><td>{$d[10]}</td></tr>";
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
require_once('footer.php');
?>
