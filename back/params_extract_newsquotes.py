#!/usr/bin/python
"""Parameters for grabnews.py"""

source_ID = 2  # Source 2 = Reuters alertnet
pop_mysql = 1  # Populate MySQL database
pop_quotes = 1  # Extract quotes and put in database
run_profiler = 0  # Profile python code for debugging
verbose = 1 #  Print information to standard output for debugging
store_dir = './StoreStories/'         # Directory in which articles are stored  

brute_all = 0  # Continue spidering even after finding a link already in the db (set topic_num)
test_n_pages = 0  # Restrict number of pages per topic (else set to zero)
test_n_urls = 0  # Restrict number of urls in first page of each topic (else set to zero)
topic_num = 0  # Restrict to given topic number (else set to zero)

