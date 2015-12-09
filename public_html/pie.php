<?php // content="text/plain; charset=utf-8"
require_once ('jpgraph/jpgraph.php');
require_once ('jpgraph/jpgraph_pie.php');

// Some data
$data = explode('|', $_REQUEST['d']);
$legend = explode('|', $_REQUEST['l']);

// Create the Pie Graph.
$graph = new PieGraph(400,400);
$graph->SetShadow();

// Set A title for the plot
$graph->title->Set("Pie chart of values");
$graph->title->SetFont(FF_DV_SANSSERIF,FS_BOLD,14);
$graph->title->SetColor("brown");

// Create pie plot
$p1 = new PiePlot($data);
$p1->SetLegends($legend);
//$p1->SetSliceColors(array("red","blue","yellow","green"));
$p1->SetTheme("earth");

$p1->value->SetFont(FF_DV_SANSSERIF,FS_NORMAL,10);
// Set how many pixels each slice should explode
//$p1->Explode(array(0,15,15,25,15));


$graph->Add($p1);
$graph->Stroke();

?>


