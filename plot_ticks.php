<?php
/*
 * Sparkline PHP Graphing Library
 * Copyright 2004 James Byers <jbyers@users.sf.net>
 * http://sparkline.org
 *
 * Sparkline is distributed under a BSD License.  See LICENSE for details.
 *
 * $Id: filled.php,v 1.2 2005/06/02 21:00:32 jbyers Exp $
 *
 * filled shows deficit data in a simulated filled-line mode
 *              
 */

           $topic_num=$_GET[topic_num];
		   $start_day=$_GET[start_day];
		   $stop_day=$_GET[stop_day];
		   $iperson=$_GET[iperson];
           $barheight=$_GET[barheight];
           $barwidth=$_GET[barwidth];
           $barcolor=$_GET[barcolor];

//////////////////////////////////////////////////////////////////////////////
// build sparkline using standard flow:
//   construct, set, render, output
//
require_once('thirdparty/sparkline-php-0.2/lib/Sparkline_Bar_with_offset.php');
include_once("../../db/qovert_db.php");

// Retrieve data from database
   $person_index[0] = 6;
   $person_index[1] = 8;
   $person_index[2] = 10;
   $person_index[3] = 12;
   $person_index[4] = 14;
   $iP = $person_index[$iperson-1];

   $SQL  = ' SELECT * FROM topics_view ';
   $SQL .= " WHERE topicID = $topic_num";
   $result = mysqli_query ($link,$SQL)
               or die ('Query ' . $SQL . ' failed: ' . mysql_error ());
   $row = mysqli_fetch_row($result);

   $icount=0;
   $rowexplode = explode(',',$row[$iP]);
   for ($rcount=$start_day; $rcount<=$stop_day; $rcount+=1) {
      if ($rowexplode[$rcount] > 0) {  $data[$icount] = 1;
      } else {                         $data[$icount] = 0;
      }
      $icount+=1;
   }

   $barspacing = $barwidth * 0.25;
   $barwidth   = $barwidth * 0.75;

   $sparkline = new Sparkline_Bar();
   $sparkline->SetDebugLevel(DEBUG_NONE);
   $sparkline->SetBarWidth($barwidth);
   $sparkline->SetBarSpacing($barspacing);
   $sparkline->SetBarColorDefault($barcolor);

   $j = 0;
   for($i = 0; $i < sizeof($data); $i++) {

     $sparkline->SetData($j++, $data[$i]);

   }

   $sparkline->Render($barheight); // height only for Sparkline_Bar
   $sparkline->Output();

?>
