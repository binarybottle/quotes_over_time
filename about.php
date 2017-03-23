<?php
include_once("shared/header.php");
//
// @rno klein, 2007
?>

<title>Quotes over Time</title>

<?php include_once("shared/banner.html"); ?>

<div class="about">

<div class="textblocks">

Quotes over Time tracks the top-quoted people from 

<A HREF="javascript:popUpFull('http://www.alertnet.org')">Reuters Alertnet News</a>

on a range of topics, and presents their quotes on a timeline. This demonstration automatically collected pilot data over the last few months of 2006.
Please click on the graphs and quotes to see full stories (requires that <b>popups</b> be turned on in your browser; the site has been optimized for the Firefox browser).

<br /><br />

<A HREF="javascript:popUpFull('http://www.binarybottle.com')">Arno Klein</a> initially developed Quotes over Time as a purely academic endeavor (all sources are cited). 
As it has garnered interest, we felt that it could serve as a generally useful resource, particularly for news organizations, policy analysts, and media analysts.

<br /><br />

<!--http://lingpipe-blog.com/2008/10/01/how-to-extract-quotes-from-the-news/-->


For more information, please contact arno[at]binarybottle.com.

<br /><br />
<br /><br />

Behind the scenes:

<br />

<ul>																		
  News stories are collected from 

<A HREF="javascript:popUpFull('http://www.alertnet.org')">Reuters Alertnet</a>

with 

<A HREF="javascript:popUpFull('https://github.com/binarybottle/quotes_over_time')">custom code</a> written in Python.

</ul>
<ul>
  The text is parsed to extract headlines, timestamps, sources, quotes, "coined expressions," names, and such (with more custom Python code)
  and after further processing these are stored in a MySQL database.
</ul>
<ul>
  Pronominal coreferencing (e.g., attributing "she said" to the appropriate individual) is performed with 

<A HREF="javascript:popUpFull('http://opennlp.sourceforge.net/')">opennlp</a>. 

a Java suite of programs for natural language processing.
</ul>
<ul>
  The front-end application is written in PHP, style sheets, and some javascript (popups and 

<A HREF="javascript:popUpFull('http://www.dynamicdrive.com/dynamicindex2/generaltick.htm')">fades</a>).
<br />																								   
The larger graphs are created in 

<A HREF="javascript:popUpFull('http://www.aditus.nu/jpgraph/')">JPGraph</a>, 
based on PHP's GD library, and the smaller graphs are created with a PHP implementation of Tufte's 

<A HREF="javascript:popUpFull('http://sparkline.org')">Sparklines</a>

.
</ul>

<br />
<ul>
<div class="credits">Concept, programming, and design: 
<A HREF="javascript:popUpFull('http://www.binarybottle.com')">Arno Klein</a><br />
Thanks to Jamie Smith for partnering in the conceptual development,
and for SQL programming.
</div>
</ul>

</div>

<? include_once("shared/footer.php"); ?>

</div>
