#!/bin/sh
REPO="file:///home/pahomov/www/phpbee.org/svn/phpbee.org"
LASTFNAME="public_html/download/`ls -tr public_html/download/ | tail -n1`";
MTIME=`stat -n -f '%c' $LASTFNAME`
LASTDATE=`date -r $MTIME "+%Y-%m-%d"  `

FNAME="phpbee-`date "+%d%b%y"`.zip"
echo $FNAME


rm -fr build_test
mkdir build_test
cd build_test
svn export $REPO . --force
cp build.config.php config.php
chmod 777 config.php var
mv html/index_page_default.html html/index.html
mv html/404_default.html html/404.html
cp public_html/worker.php public_html/index.php
php public_html/install.php install_key=12345
cd tests

PHPUNIT=`phpunit run.php`
if [ "$?" -ne "0" ]
then	
	echo $PHPUNIT | mail -s 'phpbee build failed' alex@kochetov.com andrey.pahomov@gmail.com
	echo $PHPUNIT
	exit 1;
fi

cd ../..

rm -fr build
mkdir build
cd build
svn export $REPO . --force

svn log -r '{'$LASTDATE'}':'{'`date -v +1d "+%Y-%m-%d"`'}' --xml --verbose $REPO > Changelog.xml
xsltproc ../svn2cl.xsl Changelog.xml  > Changelog.txt
xsltproc ../svn2html.xsl Changelog.xml  > Changelog.html
mv default.config.php config.php
chmod 777 config.php var
mv html/index_page_default.html html/index.html
php phar.php
find . -name public_html -mindepth 2 -exec sh -c "L=\`dirname {}\`; mkdir -p public_html/\$L; cp -r {}/* public_html/\$L ; " \;
rm -fr libs
cp public_html/worker.php public_html/index.php
zip -r phpbee.zip config.php gs_libs.phar.gz html modules packages public_html Changelog.txt
zip phpbee.zip var
mv phpbee.zip ..
cd ..
cp build/Changelog.html public_html/download/Changelog-$FNAME.html
rm -fr build


cp phpbee.zip public_html/download/$FNAME
echo $FNAME > html/last_build.html

echo "Build $FNAME created and uploaded" 
echo "Build $FNAME created and uploaded" | mail -s 'phpbee build completed' alex@kochetov.com andrey.pahomov@gmail.com
