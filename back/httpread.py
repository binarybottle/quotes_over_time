#!/usr/bin/python
"""
Read the contents of a website.

Faster than:
   import urllib
   url_content = urllib.urlopen(url_string,'r').read()

"""

# <TakenFromFeedParser.py>
# timeoutsocket allows feedparser to time out rather than hang forever on ultra-slow servers.
# Python 2.3 now has this functionality available in the standard socket library, so under
# 2.3 you don't need to install anything.  But you probably should anyway, because the socket
# module is buggy and timeoutsocket is better.
#try:
#import timeoutsocket # http://www.timo-tasi.org/python/timeoutsocket.py
#timeoutsocket.setDefaultSocketTimeout(10)
#except ImportError:
import socket
#if hasattr(socket, 'setdefaulttimeout'): socket.setdefaulttimeout(10)
# </<TakenFromFeedParser.py>> 
socket.setdefaulttimeout(20) 

from httplib import HTTP
#import urllib #SLOW!

def httpread( url_base, url_tail ):

	url_content = ""
	
	h = HTTP( url_base )
	h.putrequest( 'GET', url_tail )
	h.putheader('Accept', 'text/html')
	h.putheader('Accept', 'text/plain')
	h.endheaders()
	errcode, errmsg, headers = h.getreply()
	if errcode == 200:
		f = h.getfile()
	url_content = f.read() # Print the raw HTML

	return url_content