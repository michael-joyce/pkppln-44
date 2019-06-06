pkppln2
=======

A Symfony project created on February 23, 2018, 2:00 pm.

Quality Tools
-------------

PHP Unit

`./vendor/bin/phpunit`

`./vendor/bin/phpunit --coverage-html=web/docs/coverage`

Sami

`sami -vv update --force sami.php`

PHP CS

`./vendor/bin/phpcs --report-xml=tmp.xml`

`saxon -xsl:phpcs.xsl -s:tmp.xml -o:web/docs/phpcs/index.html`

Sphinx

TBD
