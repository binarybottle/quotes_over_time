#!/usr/bin/python
"""
Find the person for each quote for each article
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

import os
import os.path
import re
from string import find
from string import strip
from string import split
from find_outer_parens import pullparen
from strip_opennlp_tags import striptags
pop_db = 1
if pop_db == 1:
	from settings/db import *

currentdir = os.curdir
outputdir = os.path.join( currentdir, 'StoreStories/' )
obj_quote = re.compile( r'"quote\d*' )
obj_num = re.compile( r'\d+' )
obj_VP = re.compile( r'\(VP' )
obj_empty = re.compile( r'\([A-Z]+\d*[\f|\n|\v|\r| |\t]+\)' )
obj_NP = re.compile( r'\(NP' )
obj_NPref = re.compile( r'NP#\d+' )
obj_prp = re.compile( r'NP#\d+[ ]+\(PRP' )
obj_det = re.compile( r'(the|an|a|he|she|they|we|you|dr.|mr.|ms.|mrs.)' , re.IGNORECASE )
obj_remove = re.compile( r'(\(|\)|\'|")' )

verbose = 1
write_output = 1
extract_VP = 1

# Loop through marked files.
directory_list = os.listdir(outputdir)  #directory_list = filter(os.path.isfile, directory_list)
#directory_list = ['coref_SP271990.htm']
#print directory_list

for filename in directory_list:
	if 'coref_' in filename and 'done_coref_' not in filename:

		if 'done_'+filename not in directory_list:

			f = open( outputdir+filename, 'rb' )
			file_contents = f.read()
			f.close()
			
			# Any quotes?
			iter_quote = obj_quote.finditer( file_contents )
			if iter_quote:
		
				# Create a list of numbers indicating parenthesis depth
				#   for each character in the file.
				#count_parens = 0
				#if count_parens == 1:
					#nparen = 0
					#nparens = []
					#for fchar in file_contents:
						#nparens.append( nparen )
						#if fchar == "(":		nparen = nparen + 1
						#elif fchar == ")":	nparen = nparen - 1
	
				# Loop through quotesperson = ''
				VP = ""
				NP = []
				NP_previous = []
				for match_quote in iter_quote:

					# Determine quoteID
					i1_quote = match_quote.start()
					quote_term = file_contents[ i1_quote : match_quote.end() ]
					quoteID = obj_num.findall( quote_term )
					quoteID = quoteID[0]
	
					# Initialize variables
					personID = 0
					nameID = 0
					person = ''
					person_info = ''
					i1 = None
					extract_NNP = 1
					loop = 0
					while loop == 0:
						
						# Extract a contiguous string containing the quote
						#   within the same pair of parentheses.
						i1_quote, i2_quote = pullparen( file_contents, i1_quote )
						quote_block = file_contents[ i1_quote : i2_quote+1 ]
	
						if find( quote_block, '(S (' ) > -1 or find( quote_block, '(TOP (' ) > -1:
							loop = 1
							if verbose == 2: print '\n', filename, '\n', quote_block
	
						# If enclosing parentheses for quote have been established
						if loop == 1:
	
							# Remove verb phrases (VP)
							remove_VP = 1
							if remove_VP == 1:
								iter_VP = obj_VP.finditer( quote_block )
								if iter_VP:
									for match_VP in iter_VP:
		
										# Remove if the VP string is not empty (after previous deletions)
										if quote_block[ match_VP.start() ] != " ":
		
											i1_VP, i2_VP = pullparen( quote_block, match_VP.start()+1 )
		
											if extract_VP == 1:
												VP = quote_block[ i1_VP : i2_VP ] # In case there is no NP
											
											spaces = (i2_VP-i1_VP+1)*' '	
		
											if i1_VP == 0 and i2_VP > 0:
												quote_block = spaces + quote_block[ i2_VP+1 : len(quote_block)+1 ]
											else:
												quote_block = quote_block[ 0 : i1_VP ] + spaces + quote_block[ i2_VP+1 : len(quote_block)+1 ]
	
							# Remove quotes
							remove_Q = 1
							if remove_Q == 1:
								iter_Q = obj_quote.finditer( quote_block )
								if iter_Q:
									for match_Q in iter_Q:
		
										# Remove if the Q string is not empty (after previous deletions)
										if quote_block[ match_Q.start() ] != " ":
		
											i1_Q, i2_Q = pullparen( quote_block, match_Q.start()+1 )
		
											spaces = (i2_Q-i1_Q+1)*' '
		
											if i1_Q == 0 and i2_Q > 0:
												quote_block = spaces + quote_block[ i2_Q+1 : len(quote_block)+1 ]
											else:
												quote_block = quote_block[ 0 : i1_Q ] + spaces + quote_block[ i2_Q+1 : len(quote_block)+1 ]
							# Remove empty parentheses (ex: (NP     ))
							remove_empty = 1
							if remove_empty == 1:
								iter_E = obj_empty.finditer( quote_block )
								if iter_E:
									for match_E in iter_E:
	
										# Remove if the Q string is not empty (after previous deletions)
										if quote_block[ match_E.start() ] != " ":
	
											i1_E, i2_E = pullparen( quote_block, match_E.start()+1 )
											spaces = (i2_E-i1_E+1)*' '
	
											if i1_E == 0 and i2_E > 0:
												quote_block = spaces + quote_block[ i2_E+1 : len(quote_block)+1 ]
											else:
												quote_block = quote_block[ 0 : i1_E ] + spaces + quote_block[ i2_E+1 : len(quote_block)+1 ]
												
							# Extract noun phrases (NP)
							iter_NP = obj_NP.finditer( quote_block )
							if iter_NP:
	
								# Store all NPs in a list
								NP = []
								for match_NP in iter_NP:
									i1_NP, i2_NP = pullparen( quote_block, match_NP.start()+1 )
									NP.append( quote_block[ i1_NP : i2_NP+1 ] )
	
							# Else if there are no noun phrases, use one in the VP or in the previous list
							if len(NP) == 0:
	
								if extract_VP == 1:
									if len(VP) > 0:
										
										# Extract noun phrases (NP) from the VP
										iter_NP2 = obj_NP.finditer( VP )
										if iter_NP2:
				
											# Loop through NPs
											NP2 = []
											for match_NP2 in iter_NP2:
												i1_NP2, i2_NP2 = pullparen( VP, match_NP2.start()+1 )
												testNP2 = VP[ i1_NP2 : i2_NP2+1 ]
				
												# Remove NP if it contains a quote
												if find( testNP2, '"quote' ) == -1:
													NP.append( VP[ i1_NP2 : i2_NP2+1 ] )
								if len(NP) == 0:
									NP = NP_previous
	
							# Store in case of later ambiguity
							NP_previous = NP
	
	
					# Continue if there is an NP
					if len(NP) > 0:
						NP = NP[0]
						iNNP = -1
						i1 = -1
						ipronoun = -1
						mref = obj_prp.search( NP );
						if mref:
							ipronoun = mref.start();
						iperson = find( NP, '(person' )
						
						if iperson > -1:
							i1 = iperson
						elif ipronoun > -1:
							i1 = ipronoun
						elif extract_NNP == 1:
							iNNP0 = find( NP, '(NNP' )
							if iNNP0 > -1:
								# If the NNP is not a location or date
								i1loc, i2loc = pullparen( NP, iNNP0 )
								NPloc = NP[ i1loc : i2loc+1 ]
								iloc = find( NPloc, '(location (NNP' )
								idate = find( NPloc, '(date (NNP' )
								if iloc == -1 and idate == -1:
									iNNP = iNNP0
									if iNNP > -1:
										i1 = iNNP
							#if i1 == -1:
								#i1 = find( NP, '(organization' )
	
						if i1 > -1 and len(NP) > 0:
	
							# 1. if i1 (person, organization, or NNP), look within for NP# or pullparen until NP# (<i1)
							# 2. if NP#, search for earliest coreference, then look for person, organization, or NNP
							# 3. if no NP#, use results before #1
							backstab=1
							if backstab==1:
								newi1 = 0
								
								# If NP# at the beginning of the present NP
								#NPrefs = obj_NPref.findall( NP )
								if ipronoun > -1:
									NPrefs = obj_NPref.findall( NP )
									mref = obj_NPref.search( NP )
									if mref.start() == 1:
										iNPref = find( file_contents, NPrefs[0] )
										i1_NPref, i2_NPref = pullparen( file_contents, iNPref )
										NP = file_contents[ i1_NPref : i2_NPref+1 ]
										i1 = 0
										newi1 = 1
								else:
											
									# Check for NP# within 1st enclosing parentheses
									i1outer1, i2outer1 = pullparen( NP, i1 )
									NPblock = NP[ i1outer1 : i2outer1+1 ]
									NPrefs = obj_NPref.findall( NPblock )
									if NPrefs:
										mref = obj_num.search( NPrefs[0] )
										if mref:
											if mref.start() < i1:
												NPref = NPrefs[0]
												iNPref = find( file_contents, NPref )
												i1_NPref, i2_NPref = pullparen( file_contents, iNPref )
												NP = file_contents[ i1_NPref : i2_NPref+1 ]
												i1 = 0
												newi1 = 1
									# Else check for NP# within 2nd enclosing parentheses
									else: 
										i1outer2, i2outer2 = pullparen( NP, i1outer1 )
										NPblock = NP[ i1outer2 : i2outer2+1 ]
										NPrefs = obj_NPref.findall( NPblock )
										if NPrefs:
											mref = obj_num.search( NPrefs[0] )
											if mref:
												if mref.start() < i1:
													NPref = NPrefs[0]
													iNPref = find( file_contents, NPref )
													i1_NPref, i2_NPref = pullparen( file_contents, iNPref )
													NP = file_contents[ i1_NPref : i2_NPref+1 ]
													i1 = 0
													newi1 = 1
								# Find earliest numbered reference; again find person, organization, or NNP
								if newi1 == 1:
									iperson = find( NP, '(person' )
									i1 = -1
									if iperson > -1:
										i1 = iperson
										
										# Find NNPs within person block
										i1per, i2per = pullparen( NP, iperson+1 )
										NPper = NP[ i1per : i2per+1 ]
										iNNP0 = find( NPper, '(NNP' )
										if iNNP0 > -1:
											iNNP = iNNP0
											i1 = iNNP
											NP = NPper
											
									elif extract_NNP == 1:
										iNNP0 = find( NP, '(NNP' )
										if iNNP0 > -1:
											# If the NNP is not a location or date
											i1loc, i2loc = pullparen( NP, iNNP0 )
											NPloc = NP[ i1loc : i2loc+1 ]
											iloc = find( NPloc, '(location (NNP' )
											idate = find( NPloc, '(date (NNP' )
											if iloc == -1 and idate == -1:
												iNNP = iNNP0
												if iNNP > -1:
													i1 = iNNP
										#if i1 == -1:
											#i1 = find( NP, '(organization' )

							if i1 > -1 and len(NP) > 0:

								if verbose == 2:  print '--NP--\n' + NP

								i1, i2 = pullparen( NP, i1+1 )
								reset_i2 = 0

								# Group together adjacent NNPs
								if iNNP > -1:
									istep = 1
									endloop = 0
									i1next, i2next = pullparen( NP, i1+1 )
									while endloop == 0:
										if len(NP) > i2next+istep:
											if NP[ i2next+istep ] == '(':
												if NP[ i2next+istep : i2next+istep+4 ] == '(NNP' or \
												NP[ i2next+istep : i2next+istep+3 ] == '(FW' or \
												NP[ i2next+istep : i2next+istep+3 ] == '(NN' or \
												NP[ i2next+istep : i2next+istep+3 ] == '(NP' or \
												NP[ i2next+istep : i2next+istep+7 ] == '(person' or \
												NP[ i2next+istep : i2next+istep+13 ] == '(organization' :
													i1next, i2next = pullparen( NP, i2next+istep+1 )
													i2 = i2next
													istep = 1
												else: endloop = 1
											else:
												istep = istep + 1
										else: endloop = 1

								# Search for earliest instance of name, then look for person, organization, or NNP
								backstab2=1
								if backstab2==1:
									# Extract any reference to a person, organization, or NNP
									person = NP[ i1 : i2+1 ]
									
									# Clean up results
									if len(person) > 0:
										person = striptags( person )
										person = obj_remove.sub( '', person )
										person = strip( person )
										mref = obj_det.search( person )
										if mref:
											if person == person[ mref.start() : mref.end() ]:
												person = ''
										if len(person) > 0:
											per_son = split(person)
											regname = per_son[0]
											for ips in range(len(per_son)-1):
												regname = regname+'.*'+per_son[ips+1]
											regname = obj_remove.sub( '', regname )
											obj_regname = re.compile( regname , re.IGNORECASE )
											mref = obj_regname.search( file_contents )
											if mref:
												i1 = mref.start()

												found_NP = 0
												while found_NP < 2:
													i1, i2 = pullparen( file_contents, i1 )
													if NP[i1:i1+3] == '(NP':
														found_NP = 2
													found_NP = found_NP + 1
												NP = file_contents[ i1 : i2+1 ]
												iperson = find( NP, '(person' )
												i1 = -1
												if iperson > -1:
													i1 = iperson
													
													# Find NNPs within person block
													i1per, i2per = pullparen( NP, iperson+1 )
													NPper = NP[ i1per : i2per+1 ]
													iNNP0 = find( NPper, '(NNP' )
													if iNNP0 > -1:
														iNNP = iNNP0
														i1 = iNNP
														NP = NPper
														
												elif extract_NNP == 1:
													iNNP0 = find( NP, '(NNP' )
													if iNNP0 > -1:
														# If the NNP is not a location or date
														i1loc, i2loc = pullparen( NP, iNNP0 )
														NPloc = NP[ i1loc : i2loc+1 ]
														iloc = find( NPloc, '(location (NNP' )
														idate = find( NPloc, '(date (NNP' )
														if iloc == -1 and idate == -1:
															iNNP = iNNP0
															if iNNP > -1:
																i1 = iNNP

												if i1 > -1 and len(NP) > 0:
													i1, i2 = pullparen( NP, i1+1 )
													# Group together adjacent NNPs
													if iNNP > -1:
														istep = 1
														endloop = 0
														reset_i2 = 0
														i1next, i2next = pullparen( NP, i1+1 )
														while endloop == 0:
															if len(NP) > i2next+istep:
																if NP[ i2next+istep ] == '(':
																	if NP[ i2next+istep : i2next+istep+4 ] == '(NNP' or \
																		NP[ i2next+istep : i2next+istep+3 ] == '(FW' or \
																		NP[ i2next+istep : i2next+istep+3 ] == '(NN' or \
																		NP[ i2next+istep : i2next+istep+3 ] == '(NP' or \
																		NP[ i2next+istep : i2next+istep+7 ] == '(person' or \
																		NP[ i2next+istep : i2next+istep+13 ] == '(organization' :
																		i1next, i2next = pullparen( NP, i2next+istep+1 )
																		i2 = i2next
																		reset_i2 = 1
																		istep = 1
																	else: endloop = 1
																else:
																	istep = istep + 1
															else: endloop = 1

								# Make sure that all parentheses are paired (and include i1 AND i2)
								if reset_i2 == 1:
									i10 = i1
									i1, i2 = pullparen( NP, i2-1 )
									if i10 < i1:
										endloop = 0
										while endloop == 0:
											i1, i2 = pullparen( NP, i1 )
											if i10 >= i1:
												endloop = 1

								if i1 > -1 and len(person) > 0:

									# Extract any reference to a person, organization, or NNP
									person = NP[ i1 : i2+1 ]
									
									# Extract any other information about the person
									# First remove person reference
									person_info = NP
									spaces = (i2-i1+1)*' '
									if i1 == 0:
										person_info = spaces + person_info[ i2+1 : len(person_info)+1 ]
									else:
										person_info = person_info[ 0 : i1 ] + spaces + person_info[ i2+1 : len(person_info)+1 ]
	
									# Clean up results
									if len(person) > 0:
										person = striptags( person )
										person = obj_remove.sub( '', person )
										person = strip( person )
										mref = obj_det.search( person )
										if mref:
											if person == person[ mref.start() : mref.end() ]:
												person = ''
										if verbose == 2: print 'person:        ', person
									if len(person_info) > 0:
										person_info = striptags( person_info )
										person_info = obj_remove.sub( '', person_info )
										person_info = strip( person_info )
										#if person_info == 'The' or person_info == 'the':
										#	person_info = ''
										mref = obj_det.search( person_info )
										if mref:
											if person_info == person_info[ mref.start() : mref.end() ]:
												person_info = ''
											
										if verbose == 2: print 'person_info:   ', person_info

									if len(person) > 0:

										# Insert person information in database
										if pop_db == 1:
											if len(person) > 0:
												
												# Determine the nameID and personID
												SQL  = 'SELECT * FROM names WHERE names.last = ' + repr(person)
												result = cursor.execute( SQL )
		
												# If there is no person information, compute maximum ID numbers
												if result == 0:
													# Retrieve the maximum nameID number
													SQL = 'SELECT * FROM names'
													cursor.execute( SQL )
													numrows = int(cursor.rowcount)
													nameID = str( numrows + 1 )
													# Retrieve the maximum personID number
													SQL = 'SELECT * FROM people'
													cursor.execute( SQL )
													numrows = int(cursor.rowcount)
													personID = str( numrows + 1 )
													# Retrieve the maximum infoID number
													SQL = 'SELECT * FROM info'
													cursor.execute( SQL )
													numrows = int(cursor.rowcount)
													infoID = str( numrows + 1 )
													
													if write_output == 1:
														# Insert person information in names, people, and info tables
														SQL = 'INSERT into names ( nameID, personID, last ) ' + \
														' values ( ' + nameID + ',' + personID + ',"' + person + '" )'
														cursor.execute( SQL ) 
														SQL = 'INSERT into people ( personID, nameID ) ' + \
														'values ( ' + personID + ',' + nameID + ' )'
														cursor.execute( SQL )
														if len(person_info) > 0:
															SQL = 'INSERT into info ( infoID, personID, info ) ' + \
															    'values ( ' + infoID + ',' + personID + ',"' + person_info + '" )'
															cursor.execute( SQL ) 
													
												# Else extract existing person information
												else: 
													row = cursor.fetchone()
													nameID = row[0]
													personID = row[1]
													if write_output == 1:
														if len(person_info) > 0:
															# Determine whether the same info about the person already exists in the database
															SQL  = 'SELECT info.info FROM info, names WHERE names.last = ' + repr(person) + \
															' AND names.personID = info.personID '
															' AND info.info != ' + person_info
															result = cursor.execute( SQL )
															if not result:
																# Insert person information in info table
																SQL = 'INSERT into info ( personID, info ) ' + \
																    'values ( ' + str(personID) + ',"' + person_info + '" )'
																cursor.execute( SQL ) 
		
												if verbose == 1:
													print filename
													print person
													if len(person_info) > 0:
														print person_info + '\n'
													else: 
														print '\n'
													
												# Insert person information in quotes table
												if write_output == 1:
													SQL = 'UPDATE quotes ' + \
														' SET personID = ' + str(personID) +  \
														' WHERE quoteID = ' + str(quoteID)
													cursor.execute( SQL )

		if write_output == 1:
			f = open( outputdir + 'done_' + filename, 'w' )
			f.write( 'done' )
			f.close()
