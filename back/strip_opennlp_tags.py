#!/usr/bin/python
"""
Remove opennlp annotation from a string.
For example, pairs of parentheses with capitalized tags adjacent to the open parentheses,
such as "(NNP " ... ")" and "(NP#12 " ... ")", as well as certain special cases would be removed,
including "(person " ... ")", "(organization " ... ")", "(money "...")", " (. .)", " (, ,)", "(" ")", "($ $)".

For example, the following subject string:

(NP (NN government) (NN spokesman) (person (NNP Surapon) (NNP Suebwonglee)))

would result in the following string:

government spokesman Surapon Suebwonglee

"""
import re
from string import find
from find_outer_parens import pullparen

obj_tag = re.compile( r'(\([A-Z]+#*\d*)|\(person|\(organization|\(money|\(location|\(date' ) #|\(. .\)') #|\(" "\)')
obj_punc = re.compile( r'(\(. .\)|\(, ,\)|\(`` "\)|\(\'\' "\)|\(\'\' \'\'\)|\(" \'\'\)|\(" "\)|\(; ;\)|\(; :\)|\(: ;\)|\($ $\)|\(-RRB- -RRB-\)|\(-LRB- -LRB-\)|-RRB-|-LRB-)' )
#obj_punc1 = re.compile( r'(\(. .\))' ); punc1 = '.'
#obj_punc2 = re.compile( r'(\(, ,\))' ); punc2 = ','
#obj_punc3 = re.compile( r'(\(; ;\))' ); punc3 = ';'
#obj_punc4 = re.compile( r'(\(: :\))' ); punc4 = ':'
#obj_punc5 = re.compile( r'(\(" "\))' ); punc5 = '"'
#obj_punc6 = re.compile( r'(\(`` "\))' ); punc6 = '"'
#obj_punc7 = re.compile( r'(\($ $\))' ); punc7 = '$'
obj_apos = re.compile( r"( ')")  #obj_apos1 = re.compile( r"( ')[a-z]")
obj_doublespace = re.compile( r'  ')

def striptags ( input_string ):

	iter_tag = obj_tag.finditer( input_string )
	if iter_tag:
	
		for match_tag in iter_tag:
			
			# If the input string is not empty (after previous deletions)
			if input_string[ match_tag.start() ] != " ":

				i1_paren, i2_paren = pullparen( input_string, match_tag.start()+1 )
				spaces = ( match_tag.end() - match_tag.start() + 1 )*' '

				if i1_paren == 0 and i2_paren > 0:
					input_string1 = spaces
					input_string2 = input_string[ match_tag.end()+1 : i2_paren ] + ' '
					input_string3 = input_string[ i2_paren+1 : len(input_string)+1 ] 
				else:
					input_string1 = input_string[ 0 : match_tag.start() ] + spaces
					input_string2 = input_string[ match_tag.end()+1 : i2_paren ] + ' '
					input_string3 = input_string[ i2_paren+1 : len(input_string)+1 ] 

				input_string  = input_string1 + input_string2 + input_string3

	#input_string = obj_punc1.sub( punc1, input_string )
	#input_string = obj_punc2.sub( punc2, input_string )
	#input_string = obj_punc3.sub( punc3, input_string )
	#input_string = obj_punc4.sub( punc4, input_string )
	#input_string = obj_punc5.sub( punc5, input_string )
	#input_string = obj_punc6.sub( punc6, input_string )
	#input_string = obj_punc7.sub( punc7, input_string )
	input_string = obj_punc.sub( "", input_string )

	loop = 0
	while loop == 0:

		if find( input_string, '  ') > -1:
			input_string = obj_doublespace.sub( ' ', input_string )
		else:
			break

	input_string = obj_apos.sub( "'", input_string )

	return input_string

#stripped=striptags( r'(NP#21 (NNP#31 (NN (person (NN hello))))(. .)(" ")) ')
#print stripped