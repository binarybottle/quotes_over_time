<?php
include_once("../../db/qovert_db.php");
include_once("shared/header.php");

$storyID = $_GET[storyID];
$quoteID = $_GET[quoteID];
$iperson = $_GET[iperson];
$date    = $_GET[date];

// $SQL  = ' SELECT quote, quotes.quoteID, quotes.storyID, quotes.timestamp, headline, abstract, source, link
   $SQL  = ' SELECT quote, headline, abstract, source, link, quotes.timestamp
             FROM quotes, stories
             WHERE quotes.storyID='.$storyID.'
             AND stories.storyID='.$storyID.'
             AND quoteID='.$quoteID;

   $result = mysqli_query ($SQL)
               or die ('Query ' . $SQL . ' failed: ' . mysql_error ());
   $row = mysqli_fetch_row($result);
   $quote = str_replace("'","&apos;",$row[0]);
   $quote = str_replace("\n", "", $quote);
   $quote = trim(str_replace("\r", "", $quote));
   $quote = trim(preg_replace('#[.?!,;]?$#','',$quote));

   $abstract = str_replace("'","&apos;",$row[2]);
   $abstract = str_replace("\n", "", $abstract);
   $abstract = trim(str_replace("\r", "", $abstract));

   echo '<div class= "story_body">';

/*
   echo 'QUOTE: '.$quote.'<br><br>ABSTRACT: '.$abstract.'<br>';
   echo 'quoteID: '.$row[1].'<br>';
   echo 'storyID: '.$row[2].'<br>';
   echo 'timestamp: '.$row[3].'<br><br>';
*/

//-------------
// Format story
//-------------
   $color_quote = '<span class="color'.$iperson.'"><span class="highlight">'.$quote.'</span></span>';

   $timestamp  = $row[5];
   $time_parts = getdate($timestamp);
   $date       = $time_parts[mon].'/'.$time_parts[mday].'/'.$time_parts[year].
                 ' '.$time_parts[hours].':'.$time_parts[minutes].':'.$time_parts[seconds];

   echo '<span class="headline"><b>'.$row[1].'</b></span><br />';
   echo '<span class="date">'.$date.'</span>
         <span class="source">'.$row[3].'</span><br /><br />';
   echo "<div class='story'>".str_replace($quote,$color_quote,$abstract)."</div><br />";
// echo '<a href="'.$row[4].'"><div class="link">'.$row[4].'</div></a>';

   echo '</div>';       


?>