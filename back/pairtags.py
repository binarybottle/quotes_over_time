#!/usr/bin/python
" Close open tags at the beginning and end of a string."

def pairtags( input_tag_string ):
	iopentag = input_tag_string.find('<')
	iclosetag = input_tag_string.find('>')
	if iclosetag < iopentag:
		input_tag_string = '<' + input_tag_string
	iopentag = input_tag_string.rfind('<')
	iclosetag = input_tag_string.rfind('>')
	if iopentag > iclosetag:
		input_tag_string = input_tag_string + '>'
	return input_tag_string
