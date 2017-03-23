# opennlp

# English Coreference:
java -classpath \
"/usr/local/opennlp/output/opennlp-tools-1.3.0.jar:\
/usr/local/opennlp/lib/trove.jar:\
/usr/local/opennlp/lib/maxent-2.4.0.jar:\
/usr/local/opennlp/lib/jwnl-1.3.3.jar" \
opennlp.tools.lang.english.SentenceDetector \
 /usr/local/opennlp/models/english/sentdetect/EnglishSD.bin.gz < $1$2 | \
java -classpath \
"/usr/local/opennlp/output/opennlp-tools-1.3.0.jar:\
/usr/local/opennlp/lib/trove.jar:\
/usr/local/opennlp/lib/maxent-2.4.0.jar:\
/usr/local/opennlp/lib/jwnl-1.3.3.jar" \
opennlp.tools.lang.english.Tokenizer \
 /usr/local/opennlp/models/english/tokenize/EnglishTok.bin.gz | \
java -classpath \
"/usr/local/opennlp/output/opennlp-tools-1.3.0.jar:\
/usr/local/opennlp/lib/trove.jar:\
/usr/local/opennlp/lib/maxent-2.4.0.jar:\
/usr/local/opennlp/lib/jwnl-1.3.3.jar" \
 -Xmx350m opennlp.tools.lang.english.TreebankParser -d \
 /usr/local/opennlp/models/english/parser | \
java -classpath \
"/usr/local/opennlp/output/opennlp-tools-1.3.0.jar:\
/usr/local/opennlp/lib/trove.jar:\
/usr/local/opennlp/lib/maxent-2.4.0.jar:\
/usr/local/opennlp/lib/jwnl-1.3.3.jar" \
 -Xmx350m opennlp.tools.lang.english.NameFinder -parse \
 /usr/local/opennlp/models/english/namefind/*.bin.gz | \
java -classpath \
"/usr/local/opennlp/output/opennlp-tools-1.3.0.jar:\
/usr/local/opennlp/lib/trove.jar:\
/usr/local/opennlp/lib/maxent-2.4.0.jar:\
/usr/local/opennlp/lib/jwnl-1.3.3.jar" \
 -Xmx200m -DWNSEARCHDIR=$WNSEARCHDIR opennlp.tools.lang.english.TreebankLinker \
 /usr/local/opennlp/models/english/coref \
 > $1coref_$2
