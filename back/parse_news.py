#!/usr/bin/python
"""Markup files by running opennlp.sh on a list of files,
which in turn runs the opennlp natural language processing software
"""

import os
import os.path
currentdir = os.curdir
outputdir = os.path.join( currentdir, 'StoreStories/' )
verbose = 1

# Loop through unmarked files.
directory_list = os.listdir(outputdir)  #directory_list = filter(os.path.isfile, directory_list)
for filename in directory_list:
   if '.htm' in filename and 'coref_' not in filename:
      if not os.path.exists( outputdir + 'coref_' + filename ):

         # Run opennlp for each file
         cmd = './opennlp.sh ' + outputdir + ' ' + filename

         if verbose == 1:
            print cmd

         os.system( cmd )
