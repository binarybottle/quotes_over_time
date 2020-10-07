<?php
include_once("../../db/qovert_db.php");
include_once("shared/header.php");

$first_timestamp = $_GET[first_timestamp];
$iday = $_GET[iday];
$topic_num = $_GET[topic_num];
$npeople=$_GET[npeople];
$personID1=$_GET[personID1];
$personID2=$_GET[personID2];
$personID3=$_GET[personID3];
$personID4=$_GET[personID4];
$personID5=$_GET[personID5];

   $time1 = 86400 * ( floor(($first_timestamp+72000)/86400) - 5/6 + $iday-1 );
   $time2 = 86400 * ( floor(($first_timestamp+72000)/86400) - 5/6 + $iday ) - 1;

   $time_parts = getdate($time2);
   $date       = $time_parts[mon].'/'.$time_parts[mday].'/'.$time_parts[year];
   //                  ' '.$time_parts[hours].':'.$time_parts[minutes].':'.$time_parts[seconds];


   echo '<div class= "stories_body">';
   echo '<h2>Stories on '.$date.'</h2>';
   echo '</div>';

   $SQL  = ' SELECT headline, abstract, source, link, timestamp
             FROM stories
             WHERE topicID='.$topic_num.
           ' AND timestamp BETWEEN '.$time1.' AND '.$time2;

   $result = mysqli_query ($link,$SQL)
               or die ('Query ' . $SQL . ' failed: ' . mysql_error ());

   if ($result) {

      $abstract = '';

      while($row = mysqli_fetch_row($result)) {

      //---------------
      // Format stories
      //---------------
         $time_parts = getdate($row[4]);
         $date       = $time_parts[mon].'/'.$time_parts[mday].'/'.$time_parts[year].
                       ' '.$time_parts[hours].':'.$time_parts[minutes].':'.$time_parts[seconds];

         $abstract .= '
         <div class= "stories_body">
         <span class="headline"><b>'.$row[0].'</b></span><br />
         <span class="date">'.$date.'</span>
         <span class="source">'.$row[2].'</span><br />
         <div class="story">
         </div><br /><div class="story">'.$row[1].'</div><br />
         </div><br />';
		 //<a href="'.$row[3].'"><div class="link">'.$row[3].'</div></a>
      }

   //----------------------------------------------------
   // Highlight quotes from each of the top-quoted people
   //----------------------------------------------------
      $abstract = str_replace("'","&apos;",$abstract);
      $abstract = str_replace("\n", "", $abstract);
      $abstract = trim(str_replace("\r", "", $abstract));

	  $SQL = "SELECT quote, quotes.personID
              FROM
                (SELECT * from stories where topicID=$topic_num 
                 AND timestamp BETWEEN $time1 AND $time2) stories2
              INNER JOIN
                quotes ON quotes.storyID=stories2.storyID ";
      $SQL.= "  WHERE (quotes.personID=$personID1
                   OR quotes.personID=$personID2
                   OR quotes.personID=$personID3
                   OR quotes.personID=$personID4
                   OR quotes.personID=$personID5)";
	  

      $result_quote = mysqli_query ($link,$SQL)
                or die ('Query ' . $SQL . ' failed: ' . mysql_error ());

      while($row2 = mysqli_fetch_row($result_quote)) {

         if (strlen($row2[0])>0) {

            $quote = $row2[0];
            $quote = str_replace("'","&apos;",$quote);
            $quote = str_replace("\n", "", $quote);

            $quote = trim(str_replace("\r", "", $quote));
            $quote = trim(preg_replace('#[.?!,;]?$#','',$quote));

            for ($iP=1; $iP<=$npeople; $iP+=1) {
	           if ($row2[1]==${'personID'.$iP}) {
			      $iperson = $iP;
                  break;
               }
            }

            $color_quote = '<span class="color'.$iperson.'"><span class="highlight">'.$quote.'</span></span>';

            $abstract = str_replace($quote,$color_quote,$abstract);

	     }
	  }
      echo $abstract;
   }

?>