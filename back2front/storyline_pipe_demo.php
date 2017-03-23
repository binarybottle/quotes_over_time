<?php
//
// storyline.php 
//
// From start and stop times, returns formatted data 
// within the intervening time window
//
//``````````````````````````````````````````````````````````````````````````````
// (c) 2006, @rno klein
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

//``````````````````````````````````````````````````````````````````````````````
// Input
//``````````````````````````````````````````````````````````````````````````````
// Print results to standard output
   $stdout = 1;  
   if ($stdout==0) {
       $topic_num          = $_GET['topic_num']; 
       $grab_stories       = $_GET['grab_stories'];       
       $grab_quotes        = $_GET['grab_quotes']; 
       $plot_nstories      = $_GET['plot_nstories'];       
       $plot_nquotes       = $_GET['plot_nquotes']; 
       $start_time         = $_GET['start_time']; 
       $stop_time          = $_GET['stop_time'];
       $ntimeslots_people  = $_GET['ntimeslots_people'];
       $ntimeslots_stories = $_GET['ntimeslots_stories'];
       $maxquotes          = $_GET['maxquotes'];
       $maxstories         = $_GET['maxstories'];
       $npeople            = $_GET['npeople'];
   }
// overwritten if $stdout==1
   else {
       $topic_num          = 1;             // Grab this numbered topic from database

       $grab_stories       = 1;             // Grab stories from database
       $grab_quotes        = 1;             // Grab quotes from database
       $plot_stories       = 1;             // Tally number of stories per day for lineplot
       $plot_nquotes       = 1;             // Tally number of quotes per person per day for lineplot

       $start_time         = '0000000001'; //'1155801600';  // Show data FROM this epoch time
       $stop_time          = '9999999999'; //'1157760000';  // Show data UNTIL this epoch time

       $ntimeslots_people  = 5;
       $ntimeslots_stories = 5;
       $npeople            = 5;
       $maxquotes          = 10;
       $maxstories         = 1;
   }  
   $show_all = 1; // Show quotes from entire history
   if ($show_all==1) { 
      $start_time          = '0000000000';  // Show data FROM this epoch time
      $stop_time           = '9999999999';  // Show data UNTIL this epoch time
   }
   
   $show_top = 1; // Show quotes only from the people of highest exposure
   
   $proc = 1;  // Format output to Processing-compatible string
               // the traditional delimiters   &  and  = 
               // have been replaced with     ~~~ and ```

//``````````````````````````````````````````````````````````````````````````````
// Compute timeslots
//``````````````````````````````````````````````````````````````````````````````
   if ($grab_stories==1) {
      $time_window_stories = ($stop_time-$start_time)/($ntimeslots_stories);
      $timeslots_stories = array();
      for ($it=0; $it<=$ntimeslots_stories; $it+=1) {
          $timeslots_stories[$it] = $start_time + ($it)*$time_window_stories;
      }
   }
   if ($grab_quotes==1) {
      $time_window_people = ($stop_time-$start_time)/($ntimeslots_people);
      $timeslots_people = array();
      for ($it=0; $it<=$ntimeslots_people; $it+=1) {
          $timeslots_people[$it] = $start_time + ($it)*$time_window_people;
      }
   }
   //print_r($timeslots_people); echo '<br>';
   //print_r($timeslots_stories); echo '<br>';

//``````````````````````````````````````````````````````````````````````````````
// Connect to MySQL server
//``````````````````````````````````````````````````````````````````````````````
   include 'parameters_db.php';
   
   $set_topic = 0;
      
//``````````````````````````````````````````````````````````````````````````````
// Grab stories (query database)
//``````````````````````````````````````````````````````````````````````````````
   if ($grab_stories==1) {

      $SQL  = ' SELECT * FROM stories ';
      $SQL .= " WHERE timestamp BETWEEN $start_time AND $stop_time "; 
      $SQL .= " AND topicID = $topic_num ";
      $SQL .= " AND headline!='' ";
      $SQL .= ' ORDER BY timestamp ';
      //$SQL .= " LIMIT $limit";

      $ans_stories = @ mysql_query ($SQL)
                     or die ('Query ' . $SQL . ' failed: ' . mysql_error ());

      //$nstories = mysql_num_rows($ans_stories);

   //``````````````````````````````````````````````````````````````````````````````
   // Format stories
   //``````````````````````````````````````````````````````````````````````````````
      if ($proc==1) {
         //$vars .= 'nstories```' . $nstories . '~~~';
         $vars .= 'ntimeslots_stories```' . $ntimeslots_stories . '~~~';
      }

      $S = array();
      for ($it=0; $it<$ntimeslots_stories; $it+=1) {
          $S[$it] = 0;
      }

      while($row = mysql_fetch_row($ans_stories)) {

        //print_r($row);

        for ($it=0; $it<$ntimeslots_stories; $it+=1) {

            $itd = $it + 1;
            if ( $row[3]>=$timeslots_stories[$it] && $row[3]<$timeslots_stories[$itd] ) {

                if ($S[$it] < $maxstories) {
                   $S[$it] = $S[$it]+1;

                   if ($proc==1) {
                      $vars .= 'time' . $itd . 'story' . $S[$it] . 'date```'     . $row[3] . '~~~';
                      $vars .= 'time' . $itd . 'story' . $S[$it] . 'headline```' . $row[4] . '~~~';
                      $vars .= 'time' . $itd . 'story' . $S[$it] . 'text```'     . $row[5] . '~~~';
                   }                  
                }
            }
        }
      } // while($row = mysql_fetch_row($ans_stories)) {
      if ($proc==1) {
         for ($it=0; $it<$ntimeslots_stories; $it+=1) {
             $itd = $it + 1;
             $vars .= 'time' . $itd . 'nstories```' . $S[$it] . '~~~';
         }
      }
      
   }    // if ($grab_stories==1) {

//``````````````````````````````````````````````````````````````````````````````
// Grab quotes (query MySQL database)
//``````````````````````````````````````````````````````````````````````````````
   if ($grab_quotes==1) {

   //```````````````````````````````````````````````````````````````````````````
   // Extract quotes and related data
   //```````````````````````````````````````````````````````````````````````````
        $SQL  = ' SELECT quotes2.quote, quotes2.timestamp, names2.last, topics2.topic, ';
        $SQL .= ' topics2.first_timestamp, topics2.numdays, topics2.daycounts ';
        $SQL .= " FROM (SELECT * FROM topics WHERE topicID=$topic_num) topics2 ";
        $SQL .= ' INNER JOIN stories ';
        $SQL .= ' ON topics2.topicID = stories.topicID ';
        
        $SQL .= ' INNER JOIN ';
        $SQL .= " (SELECT * FROM quotes WHERE quotes.timestamp BETWEEN $start_time AND $stop_time) quotes2 ";
        $SQL .= ' ON stories.storyID = quotes2.storyID ';
        $SQL .= ' RIGHT JOIN (';

        $SQL .= ' SELECT names.* ';
        $SQL .= ' FROM names inner join ( ';

        $SQL .= ' SELECT * ';
        $SQL .= ' FROM topics ';
        $SQL .= " WHERE topicID = $topic_num ";
        $SQL .= ' ) topics2 WHERE ( ';
        $SQL .= ' names.personID = topics2.personID1) ';
        $SQL .= ' OR ( names.personID = topics2.personID2 ) ';
        $SQL .= ' OR ( names.personID = topics2.personID3 ) ';
        $SQL .= ' OR ( names.personID = topics2.personID4 ) ';
        $SQL .= ' OR ( names.personID = topics2.personID5 ) ';
        $SQL .= ' ) names2 ON quotes2.personID = names2.personID ';
        $SQL .= ' ORDER BY names2.personID ';

      $ans_quotes = @ mysql_query ($SQL)
                    or die ('Query ' . $SQL . ' failed: ' . mysql_error ());
      $nquotes = mysql_num_rows($ans_quotes);

   //``````````````````````````````````````````````````````````````````````````````
   // Format quotes
   //``````````````````````````````````````````````````````````````````````````````
      $P = array();
      for ($ip=0; $ip<$ntimeslots_people; $ip+=1) {
          for ($it=0; $it<$ntimeslots_people; $it+=1) {
              $P[$ip][$it] = 0;
          }
      }

      $irow = 0;
      while($row = mysql_fetch_row($ans_quotes)) {

          //print_r($row);
         
      // Format a string for Processing (/Flash)
         if ($irow==0) {
            if ($proc==1) {
               $vars .= 'topic```'           . $row[3] . '~~~'; 
               $vars .= 'first_timestamp```' . $row[4] . '~~~';
               $vars .= 'numdays```'         . $row[5] . '~~~';
               $vars .= 'daycounts```'       . $row[6] . '~~~';
               $set_topic = 1;
               //$vars = 'topic```' . $row[4] . '~~~';            
               $vars .= 'ntimeslots_people```' . $ntimeslots_people . '~~~';               
               $vars .= 'npeople```' . $npeople . '~~~';               
            }                  
         }

         $person = $row[2];

         if ($irow==0) { 
            $ip=0;
            $ipd=1;
            $person_past = $person;

            if ($proc==1) {
               $vars .= 'person' . $ipd . 'name```' . $row[2] . '~~~';
            }
          }
          else {
            if (strcmp($person,$person_past)!=0) {
               $ip+=1;
               if ($ip>=$npeople) {
                  break;
               }
               $ipd+=1;
   
               if ($proc==1) {
                  $vars .= 'person' . $ipd . 'name```' . $row[2] . '~~~';
               }
            }
          }

          for ($it=0; $it<$ntimeslots_people; $it+=1) {

              $itd = $it + 1;

              if ( $row[1]>=$timeslots_people[$it] && $row[1]<$timeslots_people[$itd] ) {

                 if ($P[$ip][$it] < $maxquotes) {
                    $P[$ip][$it] = $P[$ip][$it]+1;

                    if ($proc==1) {
                       $vars .= 'person' . $ipd . 'time' . $itd . 'quote' . $P[$ip][$it] . 'timestamp```' . $row[1] . '~~~';
                       $vars .= 'person' . $ipd . 'time' . $itd . 'quote' . $P[$ip][$it] . '```' . $row[0] . '~~~';
                    }                  
                 }
              }         
          }
          $person_past = $person;
          $irow = $irow + 1;
          
      }  // while($row = mysql_fetch_row($ans_quotes)) {

      if ($proc==1) {
         for ($ip=0; $ip<$ntimeslots_people; $ip+=1) {
             if ($ip>=$npeople) {
                break;
             }
             $ipd = $ip + 1;
             for ($it=0; $it<$ntimeslots_people; $it+=1) {
                 $itd = $it + 1;
                 $vars .= 'person' . $ipd . 'time' . $itd . 'nquotes```' . $P[$ip][$it] . '~~~';
             }
         }
      }                            
   }     // if ($grab_quotes==1) {
   
//``````````````````````````````````````````````````````````````````````````````
// Tally number of stories per day for lineplot
//``````````````````````````````````````````````````````````````````````````````
   if ($plot_stories==1) {
   
      $SQL  = ' SELECT first_timestamp, numdays, daycounts, topic FROM topics ';
      $SQL .= " WHERE topicID = $topic_num ";
      $ans_topics = @ mysql_query ($SQL)
                     or die ('Query ' . $SQL . ' failed: ' . mysql_error ());
      $irow = 0;
      while($row = mysql_fetch_row($ans_topics)) {
         // print_r($row);
                   
          if ($proc==1) {
             if ($set_topic==0) {
                $vars .= 'topic```'           . $row[3] . '~~~';
                $vars .= 'first_timestamp```' . $row[0] . '~~~';
                $vars .= 'numdays```'         . $row[1] . '~~~';
                $vars .= 'daycounts```'       . $row[2] . '~~~';
                $set_topic = 1;        
             }
          }
      }
   }
      
//``````````````````````````````````````````````````````````````````````````````
// Tally number of stories per person per day (with quotes by that person)
//``````````````````````````````````````````````````````````````````````````````
   if ($plot_people==1) {

      $SQL = " SELECT * FROM topics WHERE topicID = $topic_num ";
      $ans_topics = @ mysql_query ($SQL)
                    or die ('Query ' . $SQL . ' failed: ' . mysql_error ());

      $irow = 0;
      while($row = mysql_fetch_row($ans_topics)) {
                  //print_r($row);
                 
           if ($proc==1) {
              if ($set_topic==0) {
                 $vars .= 'topic```'           . $row[1] . '~~~';          
                 $vars .= 'first_timestamp```' . $row[2] . '~~~';
                 $vars .= 'numdays```'         . $row[3] . '~~~';
                 $vars .= 'daycounts```'       . $row[4] . '~~~';
                 $set_topic = 1;        
              }
           }

           $iP3 = 0;

           for ($iP=1; $iP<=$npeople; $iP+=1) {                      
           
               if ($proc==1) {
                  $vars .= 'personID'           . $iP . '```' . $row[5+$iP2] . '~~~';
                  $vars .= 'daycounts_personID' . $iP . '```' . $row[6+$iP2] . '~~~';
               }               

               $iP3 = $iP3 + 2;
               
           }
      }
   }

   if ($set_topic==0) {

      $SQL = " SELECT topic, first_timestamp, numdays, daycounts FROM topics WHERE topicID = $topic_num ";
      $ans_topics = @ mysql_query ($SQL)
                    or die ('Query ' . $SQL . ' failed: ' . mysql_error ());
      $irow = 0;
      while($row = mysql_fetch_row($ans_topics)) {
           if ($proc==1) {
              $vars .= 'topic```'           . $row[0] . '~~~';          
              $vars .= 'first_timestamp```' . $row[1] . '~~~';
              $vars .= 'numdays```'         . $row[2] . '~~~';
              $vars .= 'daycounts```'       . $row[3] . '~~~';
           }
      }
   }
       
//``````````````````````````````````````````````````````````````````````````````
// Output
//``````````````````````````````````````````````````````````````````````````````
   if ($proc==1) {
      echo $vars;
   }

?>