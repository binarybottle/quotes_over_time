#!/usr/bin/python
"""
Tally appearances, the number of quotes per person.
Top talkers are stored in the Storyline database, topics table.
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
from params_extract_newsquotes import *
from numpy import *

# Include source information
if source_ID == 2:
	from params_alertnet import *
# Log into MySQL server
from db import *

def main( ):
	
	# Begin program
	tally_nstories = 1
	tally_topquoted = 1
	tally_nappearances = 1

	limit_npeople = 5
	min_number_of_names = 1
	
	# Loop through topics
	itopic = 1
	ntopics = size_topics
	while itopic <= ntopics:
	
		topicID = str( itopic )

		# Tally the number of stories per day
		if tally_nstories == 1:

			SQL = ' SELECT timestamp FROM stories ' + \
			' WHERE topicID=' + topicID + \
			' ORDER BY timestamp '
			result = cursor.execute(SQL)
			if result > -1: 
				row = cursor.fetchall()

				if len(row) > 0:
					first_timestamp = row[0][0]

					day_numbers = floor((array(row)+72000.)/86400.) - 5/6.
					day_numbers = list( day_numbers )

					# Fill array with day counts
					maxday = max(max(day_numbers))
					minday = min(min(day_numbers))
					ndays = maxday - minday + 1

					day_counts = zeros([ndays, 1])
			
					for iday in range(len(day_numbers)):
						if day_numbers[iday][0] >= minday:
							day_counts[day_numbers[iday][0]-minday] = day_numbers.count(day_numbers[iday][0])
					day_counts_string = ''
					for iday in range(len(day_counts)):
						if len(day_counts_string) == 0:
							day_counts_string = repr(day_counts[iday][0])
						else:
							day_counts_string = day_counts_string + ',' + repr(day_counts[iday][0])

				else: 
					first_timestamp = 0
					day_counts = []
					day_counts_string = ''
					
				SQL = 'UPDATE topics_view SET first_timestamp=\'' + str(first_timestamp) + '\'' + \
				' WHERE topicID=' + topicID
				cursor.execute( SQL )

				SQL = 'UPDATE topics_view SET numdays=\'' + str(len(day_counts)) + '\'' + \
				' WHERE topicID=' + topicID
				cursor.execute( SQL )

				SQL = 'UPDATE topics_view SET daycounts=\'' + day_counts_string + '\'' + \
				' WHERE topicID=' + topicID
				cursor.execute( SQL )

		# Find top quoted people
		if tally_topquoted == 1:
			
			SQL  = 'SELECT quotes2.personID, COUNT(DISTINCT quotes2.timestamp) AS nb ' + \
			     ' FROM (SELECT * FROM quotes WHERE quotes.personID>0) quotes2 ' + \
			     ' INNER JOIN (SELECT * FROM stories WHERE stories.topicID=' + topicID + ') stories2 ' + \
			     ' ON stories2.storyID=quotes2.storyID ' + \
			     ' GROUP BY quotes2.personID ORDER BY nb DESC ' #LIMIT ' + str(limit_npeople)
			result = cursor.execute(SQL)
			if result > 0:
				row = cursor.fetchall()
				nrows = len(row) #min(limit_npeople, len(row))
				i_update = 0
				irow = 0
				while irow < nrows:
					
					#print row
					personID = row[irow][0]
					appearances = row[irow][1]
					irow += 1

					SQL = 'SELECT last FROM names WHERE personID=' + str(personID)
					cursor.execute( SQL )
					if result > -1:
						row_pID = cursor.fetchall()
						name_list = row_pID[0][0].split()
						number_of_names = len(name_list)

						if number_of_names > min_number_of_names:
							i_update += 1
							if i_update > limit_npeople:
								break
							SQL = 'UPDATE topics_view SET personID' + str(i_update) + '=' + str(personID) + \
							' WHERE topicID=' + topicID
							cursor.execute( SQL )
							#print SQL
							#print itopic, appearances
							#print name_list

		# Tally the number of stories per day per (top-quoted) person
		if tally_nappearances == 1:
			
			for iP in range(limit_npeople):

				SQL = ' SELECT quotes.timestamp ' + \
				' FROM (SELECT * FROM topics_view WHERE topicID=' + topicID + ') topics2 ' + \
				' INNER JOIN stories ' + \
				' ON topics2.topicID=stories.topicID ' + \
				' INNER JOIN quotes ' + \
				' ON stories.storyID=quotes.storyID ' + \
				' AND quotes.personID=topics2.personID' + str(iP+1) + \
				' ORDER BY quotes.timestamp '

				result = cursor.execute(SQL)
				if result > -1:
					row = cursor.fetchall()
					day_numbers = floor((array(row)+72000.)/86400.) - 5/6.
					day_numbers = list( day_numbers )
					
					# Fill array with day counts (array created above for all stories on the topic)
					day_counts = zeros([ndays, 1])
					for iday in range(len(day_numbers)):
						if day_numbers[iday][0] >= minday:
							day_counts[day_numbers[iday][0]-minday] = day_numbers.count(day_numbers[iday][0])

					day_counts_string = ''
					for iday in range(len(day_counts)):
						if len(day_counts_string) == 0:
							day_counts_string = repr(day_counts[iday][0])
						else:
							day_counts_string = day_counts_string + ',' + repr(day_counts[iday][0])

					SQL = 'UPDATE topics_view SET daycounts_personID' + str(iP+1) + '=\'' + day_counts_string + '\'' + \
					' WHERE topicID=' + topicID
					cursor.execute( SQL )

		itopic = itopic + 1

main( )
