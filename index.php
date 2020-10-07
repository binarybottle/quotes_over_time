<?php
include_once("../../db/qovert_db.php");
include_once("shared/header.php");
include_once("shared/banner.html");
//
// @rno klein, 2007

//``````````````````````````````````````````````````````````````````````````````
// (c) 2007, @rno klein
//
// This file is part of Storyline.
//
// Storyline is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version
// 2 of the License, or (at your option) any later version.
//
// Storyline is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty
// of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
// See the GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public
// License along with Storyline; if not, write to the
// Free Software Foundation, Inc.,
// 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
//``````````````````````````````````````````````````````````````````````````````

   echo '<title>QoverT.info: Quotes over Time</title>';

//------
// Input
//------
   $ntimeslots         = 4;
   $npeople            = 5;
   $quote_length       = 100;
   $fadetext           = 1;
   $pause_in_ms        = 5000;

   $plot = $_GET['plot'];
   if (!isset($plot)) {
      $plot = 'lines';
      $plot = 'steps';
      $plot = 'bars';
   }

   $grab_quotes        = 1;             // Grab quotes from database
// BAR PLOTS BROKEN WITH PHP UPGRADE!
   $plot_stories       = 0;             // Tally number of stories per day for plot
   $plot_people        = 0;             // Add lineplots above barplots for peoples' quotes
                                        // NOTE: heights not by #stories -- just for debugging
//-------------
// Topic number
//-------------
// Access stored topic number if changing date range
   $topic_num_new = $_POST['topic_num_new'];
   if (empty($topic_num_new)) { 
      $sql = 'SELECT topic_num_new FROM temp';
      $result_stored = mysqli_query($link,$sql) or die (mysql_error());
      $topic_num_new = mysqli_fetch_row($result_stored);      
      $topic_num_new = $topic_num_new[0];
// Store topic number
   } else {
      $SQL = "UPDATE temp SET topic_num_new=$topic_num_new";
      mysqli_query($link,$SQL) or die (mysql_error()); 
   }
   $topic_num = $topic_num_new;

//----------------------------------
// Access information on given topic
//----------------------------------
   $SQL = " SELECT *
            FROM topics_view
            WHERE topicID = $topic_num ";
   $ans_topic = mysqli_query($link,$SQL) or die ('Query ' . $SQL . ' failed: ' . mysql_error ());
   $row_topic  = mysqli_fetch_array($ans_topic);

   $first_timestamp     = $row_topic[first_timestamp];
   $numdays             = $row_topic[numdays];
   $personID1           = $row_topic[personID1];
   $personID2           = $row_topic[personID2];
   $personID3           = $row_topic[personID3];
   $personID4           = $row_topic[personID4];
   $personID5           = $row_topic[personID5];
   $daycounts           = $row_topic[daycounts];
   $daycounts_personID1 = $row_topic[daycounts_personID1];
   $daycounts_personID2 = $row_topic[daycounts_personID2];
   $daycounts_personID3 = $row_topic[daycounts_personID3];
   $daycounts_personID4 = $row_topic[daycounts_personID4];
   $daycounts_personID5 = $row_topic[daycounts_personID5];
   $daycounts           = explode(',',$daycounts);
   $daycounts1          = explode(',',$daycounts_personID1);
   $daycounts2          = explode(',',$daycounts_personID2);
   $daycounts3          = explode(',',$daycounts_personID3);
   $daycounts4          = explode(',',$daycounts_personID4);
   $daycounts5          = explode(',',$daycounts_personID5);

//----------------------------------------------------------------
// Find date range containing quotes by top quoted, timeslot width
// (unless date range selected)
//----------------------------------------------------------------
   if (empty($start_day) && empty($stop_day)) {

      $start_day = 0;
      $stop_day  = 0;
      $d  = 0;
      for ( $iP = 0; $iP < $numdays; $iP += 1) {
          $p[$iP] = $daycounts1[$iP]+$daycounts2[$iP]+$daycounts3[$iP]+$daycounts4[$iP]+$daycounts5[$iP];
          if ($p[$iP]>0 && $d==0) {
             $start_day = $iP;
             $d=1;
          }
          if ($p[$iP]>0) {
             $stop_day = $iP;
          }
      }
   }

//------------------------
// Start & stop timestamps
//------------------------
   $start_time = 86400 * ( floor(($first_timestamp+72000)/86400) - 5/6 + $start_day );
   $stop_time  = 86400 * ( floor(($first_timestamp+72000)/86400) - 5/6 + $stop_day  ) - 1;
   $timeslot   = ($stop_time-$start_time)/($ntimeslots);

//-----------
// Topic form
//-----------
   echo '<div class="topic_form">';
   echo '<form method="post" action="index.php" enctype="multipart/form-data">';
   echo '<input type="submit" value="Search" class="topic_button" />';
   echo '<select name="topic_num_new" class="topic_form_select">';

// Populate dropdown lists (all topics)
   $SQL = "SELECT topicID, topic FROM topics_view WHERE numdays>1 ORDER BY topic";
   $ans_topics = mysqli_query($link,$SQL) or die (mysql_error());                                                                       
   while($row = mysqli_fetch_array($ans_topics)) {
       if ($row[topicID]==$topic_num) {
                $selected = 'selected="yes"';  
       } else { $selected = '';  
       }        
       echo '<option value="'.$row[topicID].'" '.$selected.'>'.$row[topic].'</option>';
   }
   echo '</select>';
   echo '<input type="hidden" value="'.$topic_num_new.'" />';
   echo '</form>';
   echo '<span id="about"><a href="./about.php">about</a></span>';
   echo '</div>';

//----------
// Time form
//----------
//   echo '<div class="range_form">';
//   echo '<form method="get" action="index.php?start_day='.$start_day.'&stop_day='.$stop_day.
//        '" enctype="multipart/form-data">';
//
// Time: from
//   echo '<select name="start_day" class="start_time_form">';
//   for ( $iday = 1; $iday <= $numdays; $iday += 1) {

//      $time_from  = 86400 * ( floor(($first_timestamp+72000)/86400) - 5/6 + $iday-1 );
//      $time_parts = getdate($time_from);
//      $date       = $time_parts[mon].'/'.$time_parts[mday].'/'.$time_parts[year];                 
//      if ($start_time==($time_from)) {
//         $selected = 'selected="yes"';  
//      } else { $selected = '';  
//      }
//      echo '<option value="'.$iday.'" '.$selected.'>'.$date.'</option>';
//   }
//   echo '</select>';

// Time: to
//   echo '<select name="stop_day" class="stop_time_form">';
//   for ( $iday = 1; $iday <= $numdays; $iday += 1) {
//      $time_to    = 86400 * ( floor(($first_timestamp+72000)/86400) - 5/6 + $iday-1 );
//      $time_parts = getdate($time_to);
//      $date       = $time_parts[mon].'/'.$time_parts[mday].'/'.$time_parts[year];
//
//      if ($stop_time==$time_to - 1) {
//		echo '<br>'.$stop_time.' '.$time_to.'<br>';
//               $selected = 'selected="yes"';  
//      } else { $selected = '';  
//      }
//      echo '<option value="'.$iday.'" '.$selected.'>'.$date.'</option>';
//   }
//   echo '</select>';
//   echo '<input type="submit" value="Range" class="range_button" />';
//   echo '<input type="submit" value="Range" class="range_button2" />';
//   echo '</form>';
//   echo '</div>';

//``````````````````````````````````````````````````````````````````````````````
// Begin formatting
//``````````````````````````````````````````````````````````````````````````````
   echo '<div class="main">';

//``````````````````````````````````````````````````````````````````````````````
// Grab quotes (query MySQL database)
//``````````````````````````````````````````````````````````````````````````````
   if ($grab_quotes==1) {

   //```````````````````````````````````````````````````````````````````````````
   // Extract quotes and related data from each of the top-quoted people
   //```````````````````````````````````````````````````````````````````````````
      echo '<div class="quotes_section">';
      echo '<div class="quote_title">Quotes</div>'; 
      echo '<div class= "names">';

      for ( $iperson = 1; $iperson <= $npeople; $iperson += 1) {

         $SQL  = ' SELECT names.title, names.first, names.middle, names.last, names.suffix
                   FROM (SELECT * FROM topics_view WHERE topicID='.$topic_num.') topics2
                   INNER JOIN names ON names.personID = topics2.personID'.$iperson;

         $ans_name = @ mysqli_query($link,$SQL)
                       or die ('Query ' . $SQL . ' failed: ' . mysql_error ());
         $row_names = mysqli_fetch_array($ans_name);
            
         $name='';
         if ($row_names[2]!='0') {
            $name .= $row_names[2].' ';
         }
         if ($row_names[3]!='0') {
            $name .= ' '.$row_names[3].' ';
         }
         if ($row_names[4]!='0') {
            $name .= ' '.$row_names[4].' ';
         }
         if ($row_names[5]!='0') {
            $name .= ' '.$row_names[5].' ';
         }
         if ($row_names[6]!='0') {
            $name .= ' '.$row_names[6].' ';
         }
         $name = trim(str_replace('  ',' ',$name));

         echo '<div class="person'.$iperson.'">'.$name.'</div>';
		 //echo '<div class="person'.$iperson.'"><span class="color'.$iperson.'">'.$name.'</span></div>';
      }
      echo '</div>';

   //```````````````````````````````````````````````````````````````````````````
   // Extract quotes and related data from each of the top-quoted people
   //```````````````````````````````````````````````````````````````````````````
	  $SQL = "SELECT quotes.*,
              (floor((quotes.timestamp-$start_time)/$timeslot)+1) AS timeslot
              FROM
                (SELECT * from stories where topicID=$topic_num 
                 AND timestamp BETWEEN $start_time AND $stop_time) stories2
              INNER JOIN
                quotes ON quotes.storyID=stories2.storyID ";
      $SQL.= "  WHERE quotes.personID=$personID1
                   OR quotes.personID=$personID2
                   OR quotes.personID=$personID3
                   OR quotes.personID=$personID4
                   OR quotes.personID=$personID5";
      $result = @ mysqli_query($link,$SQL)
                or die ('Query ' . $SQL . ' failed: ' . mysql_error ());

   //``````````````````````````````````````````````````````````````````````````````
   // Store quotes prior to display
   //``````````````````````````````````````````````````````````````````````````````
      $Q = array();
      while($row = mysqli_fetch_array($result)) {

         if (strlen($row[quote])>0) {
            for ($irow=1; $irow<=$npeople; $irow+=1) {
	           if ($row[personID]==${'personID'.$irow}) {
			      $iperson = $irow;
                  break;
               }
            }
            if (strlen($row[quote])>$quote_length) {
                      $ellipsis = '...';
            } else {  $ellipsis = '';
            }                  
            // popup(this, 'windowname')
            $quote = str_replace("'","&apos;",$row[quote]);
	        $quote = str_replace("\n", "", $quote);
			$quote = trim(str_replace("\r", "", $quote));
			//			$quote = trim(preg_replace('/[.!?,;]/', '', $quote));
			//          $quote = trim(preg_replace('#(,)[\s]?$#','',$quote));

            if (strlen($quote)>0) {
               $quote_string = '<a href="quote_popup.php?storyID='.$row[storyID].
                               '&quoteID='.$row[quoteID].'&iperson='.$iperson.'&date='.$date.
                               '" onClick="return popup(this)"><span class="quote"><span class="color'.$iperson.'">&#8220;'
                               .substr($quote,0,$quote_length).$ellipsis.
                               '&#8221;</span></span></a>';

               $time_parts = getdate($row[timestamp]);
               $date = $time_parts[mon].'/'.$time_parts[mday].'/'.$time_parts[year];                 
               if (strlen($date)>0) {
                  $quote_string .= '<span class="date_quote"><span class="color'.$iperson.'"> '.$date.'</span></span>';
               }
               $countQ = count( $Q[$iperson][$row[timeslot]] );
               $Q[$iperson][$row[timeslot]][$countQ] = $quote_string;
            }
         }
      }

   //---------------------------------------
   // Display quotes per person per timeslot
   //---------------------------------------
      echo '<div class= "quotes">';

      $uniqID = 1;
      for ($iP=1; $iP<=$npeople; $iP+=1) {
         for ($iT=1; $iT<=$ntimeslots; $iT+=1) {
            $uniqID += 1;
            $iQ = 0;  //for ($iQ=1; $iQ<=5; $iQ+=1) {
            if (strlen($Q[$iP][$iT][$iQ])>0) {
               echo '<div class= "person'.$iP.'">';
               echo '<div class= "quote'.$iT.'">';

            //--------------------------------------------
            // Display first quote per person per timeslot
            //--------------------------------------------
               if ($fadetext==0) {
                  echo $Q[$iP][$iT][$iQ].'<br>';
               }
            //--------------------------------------------------
            // Display all quotes per person per timeslot (fade)
            //--------------------------------------------------
               else {
                  $countQ = count( $Q[$iP][$iT] );

// @rno: FIX FADE!!!
                  if ($countQ==1) {  
//                  if ($countQ>0) {  

                     $quote = '<span class="singlequote">'.$Q[$iP][$iT][0].'</span>';
                     echo $quote.'<br /><span class="singlequote_number">1 of '.$countQ.'</span>';

                  } else {

                     echo '<script type="text/javascript">';
                     echo '  var tickercontent=new Array('.($countQ-1).')';
                     echo '</script>';
                     for ($x=0; $x<$countQ; $x+=1) {
                        $quote = $Q[$iP][$iT][$x];
                        echo '<script type="text/javascript">
                               tickercontent['.$x.']=\''.$quote.'<br /><span class="quote_number">'.($x+1).' of '.$countQ.'</span>\'
                              </script>';
                     }
            
                     echo '<script type="text/javascript">
                            //new domticker(name_of_message_array, CSS_ID, CSS_classname, 
                            new domticker(tickercontent, "dummyID'.$uniqID.'", "someclass", '.$pause_in_ms.', "fadeit")
                           </script>
                          ';
                  }
                  echo '</div>';      
                  echo '</div>';      
               }
	        }
		 }
	  }
      echo '</div>';
      echo '</div>';
   }                         // if ($grab_quotes==1) {
   
//``````````````````````````````````````````````````````````````````````````````
// Plot a graph with the number of stories per day and stories with quotes
//``````````````````````````````````````````````````````````````````````````````
   if ($plot_stories==1) {

      $tickheight_nav  = 4;
      $tickheight_main = 5;
      $tickcolors[0]   = 'purple';
      $tickcolors[1]   = 'green';
      $tickcolors[2]   = 'black';
      $tickcolors[3]   = 'red';
      $tickcolors[4]   = 'blue';

      $width           = 800;
      $margin_left     = 30;
      $margin_top      = 10;
      $margin_bottom   = 10;
      $width_map       = $width - $margin_left;

      $barcolor1       = 'gray';
      $barcolor2       = 'darkgray';
      $axiscolor       = 'darkgray';
      $barcolor_main   = 'darkgray';
      $axiscolor_main  = 'black';
      $lineweight      = 0;

      $height_nav      = 60;
      $height_main     = 100;

      $column_width_nav = $width_map/$numdays;

      echo '<div class="stories_section">Stories</div>';

   //---------------------------------------------------
   // Image map to make clickable sections of plot (top)
   //---------------------------------------------------
      $map_string_nav = '';
      for ( $iP = 1; $iP <= $npeople; $iP += 1) {
          ${'map_string_ticks_nav'.$iP} = '';
      }

      for ( $iday = 1; $iday <= $numdays; $iday += 1) {

        if ($daycounts[$iday-1] > 0) {

           $x1 = ($iday-1) * $column_width_nav + $margin_left;
           $x2 = $x1       + $column_width_nav;

           $link_string = 'stories_popup.php?topic_num='.$topic_num.
		                  '&first_timestamp='.$first_timestamp.'&iday='.$iday.
                          '&personID1='.$personID1.'&personID2='.$personID2.
                          '&personID3='.$personID3.'&personID4='.$personID4.
                          '&personID5='.$personID5.'&npeople='.$npeople;

           $map_string_nav .= '<AREA HREF="javascript:popUp(\''.$link_string.'\')" ALT="Stories" '.
				 ' COORDS="'.$x1.','.$margin_top.','.$x2.','.$height_nav.'" SHAPE=RECT>';

           for ( $iP = 1; $iP <= $npeople; $iP += 1) {
              ${'map_string_ticks_nav'.$iP} .= '<AREA HREF="javascript:popUp(\''.$link_string.'\')" ALT="Stories" '.
                ' COORDS="'.($x1-$margin_left).',0,'.($x2-$margin_left).','.$tickheight_nav.'" SHAPE=RECT>';
           }
        }
      }
      echo '<MAP NAME="map_nav">';
      echo $map_string_nav;
      echo '</MAP>';
      for ( $iP = 1; $iP <= $npeople; $iP += 1) {
         echo '<MAP NAME="map_ticks_nav'.$iP.'">';
         echo ${'map_string_ticks_nav'.$iP};
         echo '</MAP>';
      }

   //----------------------------------------------------
   // Image map to make clickable sections of plot (main)
   //----------------------------------------------------
      $map_string_main = '';
      for ( $iP = 1; $iP <= $npeople; $iP += 1) {
          ${'map_string_ticks_main'.$iP} = '';
      }

      $numdays_zoom = $stop_day - $start_day + 1;
      $column_width_main = $width_map/$numdays_zoom;

      for ( $iday = 1; $iday <= $numdays_zoom; $iday += 1) {

        if ($daycounts[$start_day+$iday-1]>0) {

           $x1 = ($iday-1) * $column_width_main + $margin_left;
           $x2 = $x1       + $column_width_main;
 
           $nday = $iday + $start_day;  

           $link_string = 'stories_popup.php?topic_num='.$topic_num.
		                  '&first_timestamp='.$first_timestamp.'&iday='.$nday.
                          '&personID1='.$personID1.'&personID2='.$personID2.
                          '&personID3='.$personID3.'&personID4='.$personID4.
                          '&personID5='.$personID5.'&npeople='.$npeople;

           $map_string_main .= '<AREA HREF="javascript:popUp(\''.$link_string.'\')" ALT="Stories" '.
                ' COORDS="'.$x1.','.$margin_top.','.$x2.','.$height_main.'" SHAPE=RECT>';

           for ( $iP = 1; $iP <= $npeople; $iP += 1) {
              ${'map_string_ticks_main'.$iP} .= '<AREA HREF="javascript:popUp(\''.$link_string.'\')" ALT="Stories" '.
                ' COORDS="'.($x1-$margin_left).',0,'.($x2-$margin_left).','.$tickheight_main.'" SHAPE=RECT>';
           }
        }
      }
      echo '<MAP NAME="map_main">';
      echo $map_string_main;
      echo '</MAP>';
      for ( $iP = 1; $iP <= $npeople; $iP += 1) {
         echo '<MAP NAME="map_ticks_main'.$iP.'">';
         echo ${'map_string_ticks_main'.$iP};
         echo '</MAP>';
      }

   //-----------------------------
   // Run JPGraph plotting program
   //-----------------------------
      echo '<img src="plot_'.$plot.'.php'.
           '?plot='.$plot.
           '&topic_num='.$topic_num.
		   '&start_day=0'.
           '&stop_day='.($numdays-1).
           '&start_day_select='.$start_day.
           '&stop_day_select='.$stop_day.
           '&height='.$height_nav.
           '&width='.$width.
           '&margin_left='.$margin_left.
           '&margin_right=0'.
           '&margin_top='.$margin_top.
           '&margin_bottom='.$margin_bottom.
           '&barcolor='.$barcolor1.
           '&barcolor2='.$barcolor2.
           '&axiscolor='.$axiscolor.
           '&lineweight='.$lineweight.
           '&plot_zero='.$margin_bottom.
		   '&plot_people='.$plot_people.'" class="plot_nav" border="0" '.
           'USEMAP="#map_nav">';

      echo '<img src="plot_'.$plot.'.php'.
           '?plot='.$plot.
           '&topic_num='.$topic_num.
           '&start_day='.$start_day.
           '&stop_day='.$stop_day.
           '&height='.$height_main.
           '&width='.$width.
           '&margin_left='.$margin_left.
           '&margin_right=0'.
           '&margin_top='.$margin_top.
           '&margin_bottom='.$margin_bottom.
           '&barcolor='.$barcolor_main.
           '&axiscolor='.$axiscolor_main.
           '&lineweight='.$lineweight.
           '&plot_zero='.$margin_bottom.
           '&plot_people='.$plot_people.'" class="plot_main" border="0" '.
           'USEMAP="#map_main">';

      $tickwidth_nav   = $column_width_nav;
      $tickwidth_main  = $column_width_main;

      echo '<div class="plot_ticks_nav">';
      for ($iP=1; $iP<=$npeople; $iP+=1) {
         echo '<img src="plot_ticks.php'.
              '?topic_num='.$topic_num.
              '&start_day=0'.
              '&stop_day='.($numdays-1).
              '&iperson='.$iP.     
              '&barheight='.$tickheight_nav.
              '&barwidth='.$tickwidth_nav.
              '&barcolor='.$tickcolors[$iP-1].'" border="0" '.
              'USEMAP="#map_ticks_nav'.$iP.'">';
      }
      echo '</div>';

      echo '<div class="plot_ticks_main">';
      for ($iP=1; $iP<=$npeople; $iP+=1) {
         echo '<img src="plot_ticks.php'.
              '?topic_num='.$topic_num.
              '&start_day='.$start_day.
              '&stop_day='.$stop_day.
              '&iperson='.$iP.     
              '&barheight='.$tickheight_main.
              '&barwidth='.$tickwidth_main.
              '&barcolor='.$tickcolors[$iP-1].'" border="0" '.
              'USEMAP="#map_ticks_main'.$iP.'">';
      }
      echo '</div>';

   // Add labels: nav
      $time_parts = getdate($first_timestamp);
      $date1 = $time_parts[mon].'/'.$time_parts[mday].'/'.$time_parts[year];
      echo '<span class="date_start_nav">'.$date1.'</span>';
      $time_parts = getdate($first_timestamp + (($numdays-1) * 86400) - 14400);
      $date2 = $time_parts[mon].'/'.$time_parts[mday].'/'.$time_parts[year];
      echo "<span class='date_end_nav'>$date2</span>";

      if ($numdays>1) {       $days_str      = ' days';
      } else {                $days_str      = ' day';
      }
      if ($numdays_zoom>1) {  $days_zoom_str = ' days (zoom)';
      } else {                $days_zoom_str = ' day';
      }

   // Add number of days: nav
      echo "<span class='date_days_nav'>- $numdays$days_str -</span>";
   // Add number of days: main
      echo "<span class='date_days_main'>- ".($stop_day-$start_day+1)."$days_zoom_str -</span>";

   } else {
      
     echo '<div class="stories_section">';
     echo '<font color="red">[NOTE: Unfortunately, changes in the PHP language since 2007 have broken interactive bar plot and story context popup functionality on this site.]</font>';
     echo '</div>';

   }

   echo '</div>';
   echo '</div>';

   include_once("shared/footer.php");

?>
