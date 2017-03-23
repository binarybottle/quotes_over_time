<?php
include_once ("thirdparty/jpgraph-2.1.4/src/jpgraph.php");
include_once ("thirdparty/jpgraph-2.1.4/src/jpgraph_bar.php");
include_once ("thirdparty/jpgraph-2.1.4/src/jpgraph_line.php");
include_once ("shared/db.php");

           $topic_num=$_GET[topic_num];
		   $start_day=$_GET[start_day];
		   $stop_day=$_GET[stop_day];
           $start_day_select=$_GET[start_day_select];
           $stop_day_select=$_GET[stop_day_select];
           $height=$_GET[height];
           $width=$_GET[width];
           $margin_left=$_GET[margin_left];
           $margin_right=$_GET[margin_right];
           $margin_top=$_GET[margin_top];
           $margin_bottom=$_GET[margin_bottom];
           $barcolor=$_GET[barcolor];
           $barcolor2=$_GET[barcolor2];
           $axiscolor=$_GET[axiscolor];
           $lineweight=$_GET[lineweight];
           $plot_zero=$_GET[plot_zero];
		   $plot_people=$_GET[plot_people];

$barwidth = 0.75;

// Retrieve data from database
   $SQL  = ' SELECT * FROM topics_view ';
   $SQL .= " WHERE topicID = $topic_num";
   $result = @ mysql_query ($SQL)
               or die ('Query ' . $SQL . ' failed: ' . mysql_error ());
   $row = mysql_fetch_row($result);

// Whole range:
   $icount=0;
   $rowexplode = explode(',',$row[4]);
   for ($rcount=$start_day; $rcount<=$stop_day; $rcount+=1) {
      $cdata[$icount] = $rowexplode[$rcount]; $icount+=1;
   }

// Selected range:
   if (!empty($barcolor2)) {
      $icount=0;
      for ($rcount=$start_day; $rcount<=$stop_day; $rcount+=1) {
         if ($rcount>=$start_day_select && $rcount<=$stop_day_select) {
            $cdataselect[$icount] = $rowexplode[$rcount];
         } else {
            $cdataselect[$icount] = 0; 
         }
         $icount+=1;
      }
   }

   if ($plot_people==1) {
      $icount=0;
      $rowexplode = explode(',',$row[6]);
      for ($rcount=$start_day; $rcount<=$stop_day; $rcount+=1) {
         $cdata1[$icount] = $rowexplode[$rcount]; $icount+=1;
      }
      $icount=0;
      $rowexplode = explode(',',$row[8]);
      for ($rcount=$start_day; $rcount<=$stop_day; $rcount+=1) {
         $cdata2[$icount] = $rowexplode[$rcount]; $icount+=1;
      }
      $icount=0;
      $rowexplode = explode(',',$row[10]);
      for ($rcount=$start_day; $rcount<=$stop_day; $rcount+=1) {
         $cdata3[$icount] = $rowexplode[$rcount]; $icount+=1;
      }
      $icount=0;
      $rowexplode = explode(',',$row[12]);
      for ($rcount=$start_day; $rcount<=$stop_day; $rcount+=1) {
         $cdata4[$icount] = $rowexplode[$rcount]; $icount+=1;
      }
      $icount=0;
      $rowexplode = explode(',',$row[14]);
      for ($rcount=$start_day; $rcount<=$stop_day; $rcount+=1) {
         $cdata5[$icount] = $rowexplode[$rcount]; $icount+=1;
      }
   }

   if ($cdata[0]>0) {

// Create the graph. These two calls are always required 
   $graph  = new Graph($width, $height, "auto");  
   $graph->SetScale( "textlin");
   $graph->xaxis->Hide();
   $graph->xaxis->HideLine(); 
   $graph->xaxis->HideTicks(); 
   $graph->SetAxisStyle(AXSTYLE_BOXOUT);
   $graph->img->SetMargin($margin_left,$margin_right,$margin_top,$margin_bottom);
   $graph->SetMarginColor("#EEEED1"); 
   if ($plot_zero>0) {
      $sline  = new PlotLine (HORIZONTAL,0, $axiscolor,$lineweight); 
      $graph->Add( $sline);
   }

   $graph->SetFrame(true,'#EEEED1');
   $graph->SetBackgroundGradient('#EEEED1','#EEEED1:2',GRAD_HOR,BGRAD_PLOT);

// First create the individual plots 
   $p0 = new BarPlot( $cdata); 
   $p0->SetWidth($barwidth);
   if (!empty($barcolor2)) {
      $p0select = new BarPlot( $cdataselect); 
      $p0select->SetWidth($barwidth);
   }
   if ($plot_people==1) {
      $p1 = new LinePlot( $cdata1); 
      $p2 = new LinePlot( $cdata2); 
      $p3 = new LinePlot( $cdata3); 
      $p4 = new LinePlot( $cdata4); 
      $p5 = new LinePlot( $cdata5); 
// Center the line plot in the center of the bars
   $p1->SetBarCenter();
   $p2->SetBarCenter();
   $p3->SetBarCenter();
   $p4->SetBarCenter();
   $p5->SetBarCenter();

   }

/*
   $p0->SetStepStyle();
   if (!empty($barcolor2)) {
      $p0select->SetStepStyle();
   }
   if ($plot_people==1) {
	       $p1->SetStepStyle();
	       $p2->SetStepStyle();
	       $p3->SetStepStyle();
	       $p4->SetStepStyle();
	       $p5->SetStepStyle();
   }
*/

   if ($plot_people==1) {
      $p1->SetWeight($lineweight);
      $p2->SetWeight($lineweight);
      $p3->SetWeight($lineweight);
      $p4->SetWeight($lineweight);
      $p5->SetWeight($lineweight);
   }

   $p0->SetColor($barcolor); 
   if (!empty($barcolor2)) {
      $p0select->SetColor($barcolor2); 
   }
   if ($plot_people==1) {
      $p1->SetColor("red");
      $p2->SetColor("brown"); 
      $p3->SetColor("green"); 
      $p4->SetColor("blue"); 
      $p5->SetColor("purple"); 
   }

// Add band
//   $band = new PlotBand(HORIZONTAL,BAND_RDIAG,0,"max","green");
//   $band->ShowFrame(false);
//   $graph->AddBand($band);
// Add line
//   $sline  = new PlotLine (VERTICAL,$orig_start_day, "black",2); 
//   $graph->Add( $sline);
//   $sline  = new PlotLine (VERTICAL,$orig_stop_day, "black",2); 
//   $graph->Add( $sline);

   $p0->SetFillColor($barcolor); 
   if (!empty($barcolor2)) {
      $p0select->SetFillColor($barcolor2); 
   }

   $graph->Add($p0);
   if (!empty($barcolor2)) {
      $graph->Add($p0select);
   }


   $accumulate = 0;
   if ($accumulate==1) {
   // Add the plots together to form an accumulated plot 
      if ($plot_people==1) {
         $p1->SetFillColor("red");
         $p2->SetFillColor("brown"); 
         $p3->SetFillColor("green"); 
         $p4->SetFillColor("blue"); 
         $p5->SetFillColor("purple"); 
	     $ap = new AccLinePlot(array($p1,$p2,$p3,$p4,$p5)); 
         $graph->Add($ap);
      }

   } else {
      if ($plot_people==1) {
      $graph->Add($p1);
      $graph->Add($p2);
      $graph->Add($p3);
      $graph->Add($p4);
      $graph->Add($p5);
      }
   }

// Display the graph 
   $graph->Stroke(); 

   }

?> 
