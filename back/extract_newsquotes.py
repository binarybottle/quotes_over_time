#!/usr/bin/python
"""
Spider news sites and populate a MySQL database
"""
#``````````````````````````````````````````````````````````````````````````````
# (c) 2006, @rno klein
#
# This file is part of Storyline.
#
# Storyline is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License
# as published by the Free Software Foundation; either version
# 2 of the License, or (at your option) any later version.
#
# Storyline is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty
# of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
# See the GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public
# License along with Mindboggle; if not, write to the
# Free Software Foundation, Inc.,
# 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
#``````````````````````````````````````````````````````````````````````````````

# Input
# NOTE: one section tailored to the news source is preceded by: "TAILORED TO SOURCE"

import re
from calendar import timegm
from pairtags import *
import string
import urlparse
from httpread import *
from params_extract_newsquotes import *

# Include source information
if source_ID == 2:
	from params_alertnet import *
# Log into MySQL server
if pop_mysql == 1:
	from settings/db import *

# Initialize variables
obj_url = re.compile( url_regexp,  re.IGNORECASE | re.MULTILINE )
obj_headline = re.compile( headline_regexp, re.IGNORECASE | re.MULTILINE )
obj_datestring = re.compile( datestring_regexp, re.IGNORECASE | re.MULTILINE )
#obj_article = re.compile( article_regexp, re.IGNORECASE | re.MULTILINE )
obj_articlestart = re.compile( article_start_regexp, re.IGNORECASE | re.MULTILINE )
obj_articleend = re.compile( article_end_regexp, re.IGNORECASE | re.MULTILINE )
obj_store = re.compile( store_regexp, re.IGNORECASE | re.MULTILINE )
obj_quote = re.compile( quote_regexp, re.IGNORECASE | re.MULTILINE )
obj_puncs = re.compile( puncs_regexp, re.IGNORECASE | re.MULTILINE )
obj_quotedelim = re.compile( quote_delim_regexp, re.IGNORECASE | re.MULTILINE )
obj_commaquote = re.compile( comma_quote_regexp, re.IGNORECASE | re.MULTILINE )
obj_tags = re.compile( r'(<.*?>|\&nbsp;)' )
obj_period_tags = re.compile( r'\.<.*?>' )
remove_tags = 1
if remove_tags == 1:
	obj_pTITLEptags = re.compile( r'((<p>|</p>|<p></p>)\s*[^a-z]+\s*(<p>|</p>|<p></p>)|(<P>|</P>|<P></P>)\s*[^a-z]+\s*(<P>|</P>|<P></P>))' )
	obj_popentags = re.compile( r'<p>', re.IGNORECASE | re.MULTILINE )
	obj_pclosetags = re.compile( r'</p>', re.IGNORECASE | re.MULTILINE )
	obj_dummy1 = re.compile( r'P1P1P1' )
	obj_dummy2 = re.compile( r'P2P2P2' )

refill_topics_table = 0 # debugging

def main( source_ID, pop_mysql, pop_quotes ): 
	
	# Begin program
	# Loop through topics
	# - Some topics hang if run with brute_all=1 (4, 7, 18, 28, 29, etc.)
	# - Doesn't stall if selected topics -- others commented out in parameters file (September, 2006)
	itopic = 1
	iemergency = 1
	
	if topic_num > 0:
		ntopics = 1
	else:
		ntopics = size_topics;
	
	# Find missing stories
	#iit= 1
	#while iit <= 3210:
		#if pop_mysql == 1:
			#SQL  = 'SELECT * FROM stories WHERE storyID = ' + str(iit)
			#result = cursor.execute( SQL )
			#if result == 0:   
				#print itopic, iit, result
		#iit= iit+1
	#print 'DONE!'
	
	while itopic <= ntopics:

		if topic_num > 0:
			itopic = topic_num
			if itopic > len(topics):
			    iemergency = topic_num

		topicID = str( itopic )
		if verbose==1:
			print topicID + ' of ' + str(ntopics) + ' topics'

		# Loop through page numbers, each with maximum url_page_incr links per page
		url_page_num = 0
		continue_loop = 1

		while continue_loop == 1:
	
			if test_n_urls > 0:
				continue_loop = 0
	
			if test_n_pages > 0:
				if url_page_num >= test_n_pages*url_page_incr:
					continue_loop = 0
	
			# TAILORED TO SOURCE
			# Construct page url with source-dependent information
			if source_ID == 2:
	
		                ntopics_topics = size_topics_topics;
				if itopic <= ntopics_topics and iemergency == 1:
			
						topic      = topics[itopic-1][0]
						emergency  = ''
						topic_name = topics[itopic-1][1]
				else:
					if itopic+iemergency <= ntopics:
						topic      = ''
						emergency  = emergencies[iemergency-1][0]
						topic_name = emergencies[iemergency-1][1]

				main_url_tail = '/thenews/newsdesk/index.htm?gofilter=Filter' + \
					'&period=' + num_days + \
					'&fb_emergencycodes=' + emergency  + \
					'&fb_countrycodes=' + \
					'&fb_sourcecodes=' + \
					'&fb_topiccodes=' + topic + \
					'&startdate=' + date_today + \
					'&enddate=' + date_today + \
					'&limit=10' + \
					'&rt=1' + \
					'&start=' + str(url_page_num)

			main_url = domain_string + main_url_tail

			# Insert the topicID into the database (first determine if the topicID exists)
			if pop_mysql == 1:
				SQL  = 'SELECT * FROM topics WHERE topicID = ' + topicID
				result = cursor.execute( SQL )
				if result == 0:   
					SQL = 'INSERT into topics ( topicID, topic ) ' + \
					'values ( ' + topicID + ',' + repr(topic_name) + ' )'
					cursor.execute( SQL )
	
			# Extract text from main site containing URLs of referral sites
			main_content = httpread( domain_string, main_url_tail )
			#main_content = urllib.urlopen(url,'r').read()
			if refill_topics_table == 1:
				main_content = ""
				continue_loop = 0

			if main_content != "":
				
				if verbose == 1:
					print main_url

				# Extract unique URLs (from surrounding text on main site)
				iterator = obj_url.finditer( main_content )
				if iterator:
					
					url_found = 0
					url_in_database = 0
					stored_urls = []
					for match in iterator:
						url_found = 1

						# Prepend the url string, then parse the string into a url base and tail
						url_string = main_content[ match.start() : match.end() ]

						debug_local = 0
						if debug_local == 1:
							url_string = "/thenews/newsdesk/N12358263.htm"

						if url_append_pre != 0:
							url_string = url_append_pre + url_string
						url_tuple = urlparse.urlparse( url_string )
						url_base = url_tuple[1]
						url_tail = urlparse.urljoin( url_tuple[2], url_tuple[3] )
						url_tail = urlparse.urljoin( url_tail, url_tuple[4] )
						url_tail = urlparse.urljoin( url_tail, url_tuple[5] )

						# Determine if the story already exists in the database
						url_in_database = 0
						if pop_mysql == 1:
							if debug_local == 0:
									
								SQL = 'SELECT link FROM stories WHERE link = "' + url_string + '"'
								result = cursor.execute(SQL)
								# If the URL is stored in the database
								if result > 0:
									url_in_database = 1
									if brute_all == 0:
										#if url_string not in stored_urls:
										#	stored_urls.append( url_string )
										#else:
										continue_loop = 0
										#break

						if url_in_database == 0:
							
							# Extract referral site content from each URL
							if verbose == 1:
								print '          ' + url_string
							
							url_content = httpread( url_base, url_tail )
							#url_content = urllib.urlopen( url_string, 'r' ).read()
							#if verbose == 1:
							#	print '          ' + url_content

							if url_content != "":
								#Initialize variables
								headline = ''
								datestring = ''
								article = ''
								filename = ''

								# Extract headline from referral site
								# Remove <tags> and preceding space
								m = obj_headline.search( url_content )
								if m:
									headline = url_content[ m.start() : m.end() ]
									headline = pairtags( headline )
									headline = obj_tags.sub( '', headline )
									headline = headline.strip()
		
								# Extract date and time
								# Remove <tags> and surrounding space
								m = obj_datestring.search( url_content )
								if m:
									datestring = url_content[ m.start() : m.end() ]
									datestring = pairtags( datestring )
									datestring = obj_tags.sub( '', datestring )
									datestring = datestring.strip()
		
								# Extract article and abstract (beginning of article) from referral site
								article = url_content
								m = obj_pTITLEptags.search( article )
								article = obj_pTITLEptags.sub( '', article )
								
								extended_match = 1
								if extended_match == 1:
									#iterator = obj_articlestart.finditer( url_content )
									#for match in iterator:
									#	matchstart = match.start()
									article_rev = article[::-1]
									m = obj_articlestart.search( article_rev )
									if m:
										matchstart_rev = m.start()
										article_rev = article_rev[ 0 : matchstart_rev ]
										article = article_rev[::-1]

									m = obj_articleend.search( article )
									if m:
										matchend = m.start()
										article = article[ 0 : matchend ]
									
								# Single regexp to extract article -- @rno: FIX: MULTILINE not working...
								else:
									m = obj_article.search( article )
									if m:
										article = article[ m.start() : m.end() ]
								# Remove <tags> and preceding space
								if remove_tags == 1:
									article = pairtags( article )
									article = obj_popentags.sub( 'P1P1P1', article )
									article = obj_pclosetags.sub( 'P2P2P2', article )
									article = obj_period_tags.sub( '. ', article )
									article = obj_tags.sub( '', article )
									article = obj_dummy1.sub( '<p>', article )
									article = obj_dummy2.sub( '</p>', article )
									#article = stripper.strip( article )
									article  = article.strip()
									#abstract = substr(article, 0, abstract_length);
								if verbose == 1 and debug_local == 1:
									print '          ' + article

								# TAILORED TO SOURCE
								# IF datestring = '19 Jun 2006 22:41:46 GMT'
								#
								# unix_timestamp will give you the time in seconds since 
								# '1970-01-01 00:00:00' GMT as an unsigned integer
								# Converting DMYHMS to Epoch Seconds
								# To work with Epoch Seconds, you need to use the time module
								if len(datestring) > 0:
									ds = datestring.split()
									if len(ds) > 3:
										dstime = ds[3].split(':')
									# year-month-day-hour-minute-second-weekday(0-6,0=mon)-julianday(1-366)-dstflag(0)
									#momentTuple = (1976, 8, 10, 11, 11, 0, 1, 223, 0)
									hardcode_month = 1
									if hardcode_month == 1:
										if ds[1]=='Jan': month = 1
										elif ds[1]=='Feb': month = 2
										elif ds[1]=='Mar': month = 3
										elif ds[1]=='Apr': month = 4
										elif ds[1]=='May': month = 5
										elif ds[1]=='Jun': month = 6
										elif ds[1]=='Jul': month = 7
										elif ds[1]=='Aug': month = 8
										elif ds[1]=='Sep': month = 9
										elif ds[1]=='Oct': month = 10
										elif ds[1]=='Nov': month = 11
										elif ds[1]=='Dec': month = 12
									else:
										month = ds[1]
									if len(ds) == 3:										
										momentTuple = ( int(ds[2]), month, 0, 0, 0, 0, 0, 0 )
									else:
										momentTuple = ( int(ds[2]), month, int(ds[0]), int(dstime[0]), int(dstime[1]), int(dstime[2]), 0, 0, 0 )
									timestamp = timegm( momentTuple )
								else: 
									timestamp = 0
									
								# Retrieve the maximum storyID number
								if pop_mysql == 1:
									SQL = 'SELECT * FROM stories'
									cursor.execute( SQL )
									numrows = int(cursor.rowcount)
									storyID = str( numrows + 1 )

								# Extract quotes from and markup article
								if pop_quotes == 1:
									offset_shift = 0

									# Extract quotes and coined expressions from each story
									article_marked = article

									#if verbose == 1: 
										#print article_marked, '\n'

									iterator = obj_quote.finditer( article )
									for match in iterator:
										offset = match.start()

										quote = article[ match.start() : match.end() ]

										if store_article == 1:
											quoteS = obj_quotedelim.sub( ' ', quote )
											quoteS = obj_period_tags.sub( '. ', quoteS )
											quoteS = obj_tags.sub( ' ', quoteS )
											quoteS = quoteS.strip()
										if pop_mysql == 1:
											quoteM = quote
											quoteM = obj_commaquote.sub( ' ', quoteM )
											quoteM = obj_quotedelim.sub( ' ', quoteM )
											quoteM = obj_period_tags.sub( '. ', quoteM )
											quoteM = obj_tags.sub( ' ', quoteM )
											list_of_words = string.split( quoteM )
										elif store_article == 1:
											list_of_words = string.split( quoteS )

										num_words = len( list_of_words )
										# If there is no punctuation before the closing quote, and
										# if there are few words (optional),
										# save string enclosed by quotation marks as a coined expression.
										isquote = 0
										haspunc = 0
										m = obj_puncs.search( quote )
										if m:
											haspunc = 1
										if ( haspunc > 0 and num_words > min_words_quote ) or num_words > min_words_quote:
											isquote = 1

										# Insert quotes in database
										if pop_mysql == 1:

											if isquote == 1:
												SQL = 'INSERT into quotes (storyID, quote, timestamp) ' + \
												' values ( ' + storyID + ',' + repr(quoteM) + ',' + str(timestamp) + ' )'
												cursor.execute( SQL ) 
											# Coined phrases (unconnected to a particular speaker)
											else:
												SQL = 'INSERT into coined ( storyID, coined ) ' + \
												' values ( ' + storyID + ',' + repr(quoteM) + ' )'
												cursor.execute( SQL )
		
										# Insert quote references in article before saving the article as a file
										if store_article == 1:

											if haspunc > 0:
												qpunc = quoteS[-1]

											if isquote == 1:
												SQL = 'SELECT * FROM quotes'
												cursor.execute( SQL )
												numrows = int( cursor.rowcount )
												quoteid = str( numrows )

												if haspunc > 0:
													article_marked = article_marked[ 0 : offset+offset_shift ] + "\"quote" + quoteid + qpunc + "\"" + article_marked[ offset+offset_shift+len(quote) : len(article_marked) ]
													offset_shift = offset_shift - len(quote) + len(quoteid) + 8
												else:
													article_marked = article_marked[ 0 : offset+offset_shift ] + "\"quote" + quoteid + "\"" + article_marked[ offset+offset_shift+len(quote) : len(article_marked) ]
													offset_shift = offset_shift - len(quote) + len(quoteid) + 7
											else:
												SQL = 'SELECT * FROM coined'
												cursor.execute( SQL )
												numrows = int( cursor.rowcount )
												coinid = str( numrows )

												if haspunc > 0:
													article_marked = article_marked[ 0 : offset+offset_shift ] + "\"coin" + coinid + qpunc + "\"" + article_marked[ offset+offset_shift+len(quote)+1 : len(article_marked) ]
													offset_shift = offset_shift - len(quote)-1 + len(coinid) + 7
												else:
													article_marked = article_marked[ 0 : offset+offset_shift ] + "\"coin" + coinid + "\"" + article_marked[ offset+offset_shift+len(quote)+1 : len(article_marked) ]
													offset_shift = offset_shift - len(quote)-1 + len(coinid) + 6
								
								#if verbose == 1: 
									#print article_marked, '\n'

								# Save the article as a file for further processing
								if store_article == 1:
									if remove_tags == 1:
										article_marked = obj_period_tags.sub( '. ', article_marked )
										article_marked = obj_tags.sub( ' ', article_marked )
									filename = url_string
									filename = filename.split('/')
									filename = filename[ len(filename)-1 ]
									f=open( store_dir + filename, 'w' )
									f.write( article_marked )
									f.close()

								#print article_marked

								# Populate the storyline MySQL database
								if pop_mysql == 1:
									# Insert story into the database (placeID removed)
									if remove_tags == 1:
										article = obj_period_tags.sub( '. ', article )
										article = obj_tags.sub( ' ', article )
									article = obj_quotedelim.sub( '"', article )
									
									SQL = 'INSERT into stories ' + \
										'(storyID,topicID,timestamp,headline,abstract,source,link,filename)' + \
										' VALUES ( ' + storyID + ',' + topicID + ',' + str(timestamp) + ',' + repr(headline) + ',' + \
										repr(article) + ',' + repr(source_name) + ',' + repr(url_string) + ',' + repr(filename) + ' )'
									#print SQL
									cursor.execute( SQL )
						#else:
						#	if brute_all==0:
						#		continue_loop = 0
					if url_found==0:
						continue_loop = 0
					#if url_in_database==1 and brute_all==0:
					#	continue_loop = 0
				else:
					continue_loop = 0
			else:
				continue_loop = 0
			
			url_page_num = url_page_num + url_page_incr

		itopic = itopic + 1
		if itopic > len(topics):
			iemergency = iemergency + 1

if run_profiler == 1:
	import profile
	import pstats
	profile.run( 'import grabnews; grabnews.main( source_ID, pop_mysql, pop_quotes )', 'profile.tmp' )
	p = pstats.Stats('profile.tmp')
	p.sort_stats('cumulative').print_stats(25)

else: main( source_ID, pop_mysql, pop_quotes )



