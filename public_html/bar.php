<?php // content="text/plain; charset=utf-8"
require_once ('jpgraph/jpgraph.php');
require_once ('jpgraph/jpgraph_bar.php');
 
$data = explode('|', $_REQUEST['d']);
$colors = array("orange", "blue", "green", "brown", "red", "yellow", "black");
 
// Create the graph. These two calls are always required
$graph = new Graph(600,500);
$graph->SetScale("textlin");
 
$graph->SetShadow();
//$graph->img->SetMargin(40,30,20,40);
 
if (isset($_REQUEST['xaxis'])) {
        $graph->xaxis->SetTickLabels(explode('|', $_REQUEST['xaxis']));
}
if (isset($_REQUEST['legend'])) {
//	print_r($_REQUEST['legend']);
	$graph->legend->SetPos(0.5,0.97,'center','bottom');
        $graph->legend->SetLayout(10);
        $graph->legend->SetFillColor('white');
        $graph->legend->SetFont(FF_DV_SANSSERIF,FS_NORMAL,10);
};
$bars = array();
for($i = 0; $i < count($data); ++$i) {
	$bar = new BarPlot(explode(',', $data[$i]));
	$bar->SetFillColor($colors[$i]);
	if (isset($_REQUEST['legend'])) {
        	$bar->SetLegend(explode('|', $_REQUEST['legend'])[$i]);
	};
	$bars[] = $bar;
};

// Create the grouped bar plot
$gbplot = new GroupBarPlot($bars);
//if (isset($_REQUEST['legend'])) {
//	$gbplot->SetLegends(explode('|', $_REQUEST['legend']));
//};
// ...and add it to the graPH
$gbplot->SetWidth(0.6);
$graph->Add($gbplot);
 
$graph->title->Set($_REQUEST['title']);
$graph->xaxis->title->Set($_REQUEST['x']);
$graph->yaxis->title->Set($_REQUEST['y']);

$graph->title->SetFont(FF_DV_SANSSERIF,FS_BOLD);
$graph->yaxis->title->SetFont(FF_DV_SANSSERIF,FS_BOLD);
$graph->xaxis->title->SetFont(FF_DV_SANSSERIF,FS_BOLD);
 
// Display the graph
$graph->Stroke();
?>
