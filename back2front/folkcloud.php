<?php
//
// folkcloud.php 
//
// Takes storyline data as input.
// From topic name, returns formatted data 
// of co-occurrences between quoted individuals in stories.
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
// License along with Mindboggle; if not, write to the
// Free Software Foundation, Inc.,
// 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
//``````````````````````````````````````````````````````````````````````````````

//``````````````````````````````````````````````````````````````````````````````
// Input
//``````````````````````````````````````````````````````````````````````````````
// Print results to standard output
   $stdout = 0;  
   if ($stdout==0) {
       $init_occurrences       = $_GET['init_occurrences']; 
       $grab_occurrences       = $_GET['grab_occurrences']; 
       $grab_occurrences_topic = $_GET['grab_occurrences_topic']; 
       $topic_num              = $_GET['topic_num']; 
       $npeople                = $_GET['npeople'];
   }
// overwritten if $stdout==0
   else {
       $init_occurrences       = 1;             // Grab occurrences from database
       $grab_occurrences       = 1;             // Grab occurrences from database
       $grab_occurrences_topic = 1;             // Grab occurrences from database
       $topic_num              = 4;             // Grab this numbered topic from database
       $npeople                = 50;
   }  

   $min_noccurrences         = 0;
   $min_noccurrences2        = 1;

   $remove_single_word_names = 1;
   
   $proc = 1;  // Format output to Processing-compatible string
               // the traditional delimiters   &  and  = 
               // have been replaced with     ~~~ and ```

//``````````````````````````````````````````````````````````````````````````````
// Connect to MySQL server
//``````````````````````````````````````````````````````````````````````````````
   include 'parameters_db.php';
   
   if ($init_occurrences==1) {
   
   //```````````````````````````````````````````````````````````````````````````
   // Initialize occurrences table
   //```````````````````````````````````````````````````````````````````````````
      $SQL  = ' drop table if exists `occurrences` ';
      mysql_query ($SQL) or die ('Query ' . $SQL . ' failed: ' . mysql_error ());

      $SQL  = ' CREATE table occurrences( ';
      $SQL .= ' SELECT occtable.personID, occtable.last, stories.storyID, stories.topicID, occcount.cnt ';
      $SQL .= ' from (SELECT people.personID, quotes.storyID as storyID1, names.last FROM `people` ';
      $SQL .=       ' inner join `names` on people.personID=names.personID ';
      $SQL .=       ' right join `quotes` on people.personID=quotes.personID ';
      $SQL .=       ' where people.personID>0 and (names.last like "%_ _%") ';
      $SQL .=       ' group by storyID, personID) ';
      $SQL .= ' as occtable ';
      $SQL .= ' left join (SELECT personID, count(*) as cnt ';
      $SQL .=            ' from (SELECT people.personID, quotes.storyID FROM `people` ';
      $SQL .=                  ' right join `quotes` on people.personID=quotes.personID ';
      $SQL .=                  ' where people.personID>0 group by storyID, people.personID) t1 ';
      $SQL .=            ' group by t1.personID) ';
      $SQL .=            ' as occcount on occtable.personID=occcount.personID ';
      $SQL .= ' left join `stories` on stories.storyID=occtable.storyID1 ';
      $SQL .= ' order by occtable.personID) ';
      mysql_query ($SQL) or die ('Query ' . $SQL . ' failed: ' . mysql_error ());

   //```````````````````````````````````````````````````````````````````````````
   // Initialize co-occurrences table
   //```````````````````````````````````````````````````````````````````````````
      $SQL  = ' drop table if exists `cooccurrences` ';
      mysql_query ($SQL) or die ('Query ' . $SQL . ' failed: ' . mysql_error ());

      $SQL  = ' CREATE table cooccurrences( ';
      $SQL .= ' SELECT t1.personID as person1, t1.storyID, t2.personID as person2, t1.topicID ';
      $SQL .= ' FROM (SELECT * from `occurrences` where cnt>' . $min_noccurrences . ') t1 ';
      $SQL .= ' inner join (SELECT * from `occurrences` where cnt>' . $min_noccurrences . ') t2 ';      $SQL .=             ' on t1.storyID=t2.storyID and t1.personID < t2.personID ';
      $SQL .= ' order by person1, person2) ';
      mysql_query ($SQL) or die ('Query ' . $SQL . ' failed: ' . mysql_error ());

   //```````````````````````````````````````````````````````````````````````````
   // Initialize co-occurrences table ACROSS TOPICS
   //```````````````````````````````````````````````````````````````````````````
      if ($grab_occurrences_topic==1) {
          $SQL  = ' drop table if exists `cooccurrences_topic` ';
          mysql_query ($SQL) or die ('Query ' . $SQL . ' failed: ' . mysql_error ());
    
          $SQL  = ' CREATE table cooccurrences_topic( ';
          $SQL .= ' SELECT t1.personID as person1, t2.personID as person2, t1.topicID ';
          $SQL .= ' FROM (SELECT * from `occurrences` where cnt>' . $min_noccurrences2 . ') t1 ';
          $SQL .= ' inner join (SELECT * from `occurrences` where cnt>' . $min_noccurrences2 . ') t2 ';          $SQL .=             ' on t1.topicID=t2.topicID and t1.personID < t2.personID ';
          $SQL .= ' order by person1, person2) ';
          mysql_query ($SQL) or die ('Query ' . $SQL . ' failed: ' . mysql_error ());
      }
   } // if ($init_occurrences==1) {

   if ($grab_occurrences==1) {
   
   //```````````````````````````````````````````````````````````````````````````
   // Grab occurrences (query database)
   //```````````````````````````````````````````````````````````````````````````
      $SQL  = ' drop table if exists `nothing` ';
      mysql_query ($SQL) or die ('Query ' . $SQL . ' failed: ' . mysql_error ());

      $SQL  = ' CREATE table nothing( ';
      $SQL .= ' SELECT DISTINCT last, cnt, personID FROM `occurrences` ';      $SQL .= ' WHERE topicID=' . $topic_num;      $SQL .= ' ORDER BY cnt DESC ';
      $SQL .= ' LIMIT 0 , ' . $npeople .')';      
      mysql_query ($SQL) or die ('Query ' . $SQL . ' failed: ' . mysql_error ());

      $SQL  = 'select * from nothing';
      $ans_occurrences = @ mysql_query ($SQL)
                     or die ('Query ' . $SQL . ' failed: ' . mysql_error ());
      $noccurrences = mysql_num_rows($ans_occurrences);
      //echo $noccurrences  . '<br>';

   //```````````````````````````````````````````````````````````````````````````
   // Grab co-occurrences (query database)
   //```````````````````````````````````````````````````````````````````````````
      $SQL  = ' SELECT cooccurrences.* FROM `cooccurrences` ';
      $SQL .= ' inner join `nothing` on nothing.personID=person1 ';
      $SQL .= ' inner join `nothing` nothing2 on nothing2.personID=person2 ';
      $SQL .= ' WHERE cooccurrences.topicID=' . $topic_num . ' ';      mysql_query ($SQL) or die ('Query ' . $SQL . ' failed: ' . mysql_error ());

      $ans_cooccurrences = mysql_query ($SQL)
                     or die ('Query ' . $SQL . ' failed: ' . mysql_error ());
      //$ncooccurrences = @ mysql_num_rows($ans_cooccurrences);
      //echo $ncooccurrences  . '<br>';

   //```````````````````````````````````````````````````````````````````````````
   // Grab co-occurrences ACROSS TOPICS (query database)
   //```````````````````````````````````````````````````````````````````````````
      if ($grab_occurrences_topic==1) {
      
          $SQL  = ' SELECT cooccurrences_topic.* FROM `cooccurrences_topic` ';
          $SQL .= ' inner join `nothing` on nothing.personID = person1 ';
          $SQL .= ' inner join `nothing` nothing2 on nothing2.personID = person2 ';
          mysql_query ($SQL) or die ('Query ' . $SQL . ' failed: ' . mysql_error ());
    
          $ans_cooccurrences_topic = mysql_query ($SQL)
                         or die ('Query ' . $SQL . ' failed: ' . mysql_error ());
          //$ncooccurrences_topic = @ mysql_num_rows($ans_cooccurrences_topic);
          //echo $ncooccurrences_topic  . '<br>';
      }
       
   //``````````````````````````````````````````````````````````````````````````````
   // Format occurrences
   //``````````````````````````````````````````````````````````````````````````````
      if ($proc==1) {
         $vars = 'noccurrences```' . $noccurrences . '~~~';
      }      
      $vars = '';
      $it=0;
      $P = array();      
      while($row = mysql_fetch_row($ans_occurrences)) {
         $P[$it] = $row[2];
         $it+=1;
         if ($proc==1) {
            $vars .= 'person' . $it . 'name```'        . $row[0] . '~~~';
            $vars .= 'person' . $it . 'occurrences```' . $row[1] . '~~~';
         }
      } 
      
   //``````````````````````````````````````````````````````````````````````````````
   // Format co-occurrences
   //``````````````````````````````````````````````````````````````````````````````
      if ($proc==1) {
         //$vars .= 'ncooccurrences```' . $ncooccurrences . '~~~';
         $vars .= 'cooccurrences```'; //  . '[';
      }

   // Create 2-D co-occurrence array
      $npeople2 = $noccurrences;
      $S = array();
      for ($ix=0; $ix<$npeople2; $ix+=1) {
         for ($iy=0; $iy<$npeople2; $iy+=1) {
            $S[$ix][$iy] = 0;
         }
      }      
      while($row = mysql_fetch_row($ans_cooccurrences)) {
        if (sizeof($row[0]) > 0) {
           $p1 = array_search($row[0],$P);           $p2 = array_search($row[2],$P);
           $S[$p1][$p2] = $S[$p1][$p2] + 1;
        }
      }

   // Convert each row of the 2-D co-occurrence array to a string
      for ($iy=0; $iy<$npeople2; $iy+=1) {

         if ($proc==1) {
            $row = $S[$iy];
            if ($iy>0) {
               $vars .= ',';
            }
            //$vars .= '[';
            for ($ix=0; $ix<$npeople2; $ix+=1) {

               $comma_row = $row[$ix];
               if ($ix>0) {
                  $vars .= ',';
               }
               $vars .= $row[$ix];
               //if ($row[$ix]>0) {echo '1';}
            }
            //$vars .= ']';

            //$comma_row = implode(",", $array);
            //$vars .= '[' . $comma_row . '],';
         }
      } 
      if ($proc==1) {
         //$vars .= ']~~~';
         $vars .= ',~~~';
      }

   //``````````````````````````````````````````````````````````````````````````````
   // Format co-occurrences ACROSS TOPICS
   //``````````````````````````````````````````````````````````````````````````````
      if ($grab_occurrences_topic==1) {
      
          if ($proc==1) {
             //$vars .= 'ncooccurrences_topic```' . $ncooccurrences_topic . '~~~';
             $vars .= 'cooccurrences_topic```'; //  . '[';
          }
    
       // Create 2-D co-occurrence array
          $npeople2 = $noccurrences;
          $S = array();
          for ($ix=0; $ix<$npeople2; $ix+=1) {
             for ($iy=0; $iy<$npeople2; $iy+=1) {
                $S[$ix][$iy] = 0;
             }
          }      
          while($row = mysql_fetch_row($ans_cooccurrences_topic)) {
            if (sizeof($row[0]) > 0) {
               $p1 = array_search($row[0],$P);               $p2 = array_search($row[1],$P);
               $S[$p1][$p2] = $S[$p1][$p2] + 1;
            }
          }
    
      // Convert each row of the 2-D co-occurrence array to a string
          for ($iy=0; $iy<$npeople2; $iy+=1) {
    
             if ($proc==1) {
                $row = $S[$iy];
    
                if ($iy>0) {
                   $vars .= ',';
                }
                //$vars .= '[';
                for ($ix=0; $ix<$npeople2; $ix+=1) {
    
                   $comma_row = $row[$ix];
                   if ($ix>0) {
                      $vars .= ',';
                   }
                   $vars .= $row[$ix];
                   //if ($row[$ix]>0) {echo '1';}
                }
                //$vars .= ']';
    
                //$comma_row = implode(",", $array);
                //$vars .= '[' . $comma_row . '],';
             }
          } 
          
          if ($proc==1) {
             //$vars .= ']~~~';
             $vars .= ',~~~';
          }
      }
      
   }    // if ($grab_occurrences==1) {
       
//``````````````````````````````````````````````````````````````````````````````
// Output
//``````````````````````````````````````````````````````````````````````````````
   if ($proc==1) {
      echo $vars;
   }

?>