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
<?php

function Error($mssg="It seems your values for property and/or value is incorrect. Bear in mind we only support Wikidata item (Q###) for value.") {
	?>
	<div style="padding:1em;width:50em;">
	<div class="ui negative message">
	  <div class="header">
	    That's bad!
	  </div>
	  <p>
<?php
	echo $mssg;
?>
	  </p>
	</div>
	</div>
	<?php
	die('ValueError');
}

function LabelGetter($ids, $lang="en") {
	$url = "https://www.wikidata.org/w/api.php?action=wbgetentities&ids=" . implode("|", $ids) . "&props=labels&format=json&languages=" . $lang;
	$res = json_decode(file_get_contents($url), true);
	$final_res = array();
	if (!isset($res["success"]) | $res["success"] !== 1) {
		return $ids;
	}
	foreach ($ids as $id) {
		if (isset($res["entities"][$id]["labels"][$lang]["value"])) {
			$final_res[] = $res["entities"][$id]["labels"][$lang]["value"];
		} else {
			$final_res[] = $id;
		}
	}
	return $final_res;
}
