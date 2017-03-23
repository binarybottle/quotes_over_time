#!/usr/bin/python
"""
Extract the contents and enclosing parentheses of a subject string, given an index.

For the following subject string:
(TOP (S (NP (NN "quote22)) (, ,) ('' ") 
            (NP (NN government) (NN spokesman) (person (NNP Surapon) (NNP Suebwonglee))) 
	    (VP (VBD told) (NP (DT a) (NN news) (NN conference))) (. .)) )
the input argument 17 refers to the 18th character (quote22) and would result in the string: (NN "quote22)
and using this result (12) as an input argument would result in: (NP (NN "quote22))
and so forth...

"""

def pullparen ( input_string, string_index ):

	# Determine parenthesis depth for each charactery
	# in the file from the index character
	paren_num = 0
	index_start = string_index
	if input_string[index_start] == ")":
		paren_num = paren_num = -1
	loop = 0
	while loop == 0:
		if index_start == 0:
			break
		index_start = index_start - 1
		if input_string[index_start] == "(":
			paren_num = paren_num + 1
			if paren_num == 1:
				break
		elif input_string[index_start] == ")":
			paren_num = paren_num - 1

	paren_num = 0
	index_end = string_index
	if input_string[index_end] == "(":
		paren_num = paren_num = -1
	while loop == 0:
		if index_end == len( input_string )-1:
			break
		index_end = index_end + 1
		if input_string[index_end] == ")":
			paren_num = paren_num + 1
			if paren_num == 1:
				break
		if input_string[index_end] == "(":
			paren_num = paren_num - 1

	return index_start, index_end

#s='0( ((1(2)) 3 ) 4) 5'
#a,b = pullparen( s, 9 )
#print a
#print b