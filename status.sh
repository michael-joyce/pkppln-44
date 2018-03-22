#!/bin/sh


./vendor/bin/phpcbf src/AppBundle/

./vendor/bin/phpcs --standard=phpcs.xml -n --report-xml=report.xml src/AppBundle/

java -cp ~/Classes/saxon9he.jar net.sf.saxon.Transform \
     -s:report.xml \
     -xsl:phpcs.xsl \
     -o:report.html
