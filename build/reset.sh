#!/bin/sh

#
# This file is part of Totara LMS
#
# Copyright (C) 2010 onwards Totara Learning Solutions LTD
#
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 3 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
# @author Aaron Barnes <aaron.barnes@totaralms.com>
# @package totara
# @subpackage build
#
# This accepts three arguments
# 1/ The database server e.g. oak
# 2/ The database type e.g. postgres7
# 3/ The default language to use e.g. en

echo "Update Jenkins directory permissions"
# when Jenkins updates via apt it resets to 750 which, makes the webroot
# inaccessible. Resetting every build is overkill but does the job
chmod 755 /var/lib/jenkins

if [ -e config.php ]
then
    echo "Delete config.php"
    rm config.php
fi

DBNAME="jenkins-$JOB_NAME"
/var/lib/jenkins/internal-tools/testing/resetdb.sh $1 $2 $DBNAME

echo "Delete old moodledata"
sudo -u www-data php build/reset_cleanmoodledata.php
rmdir ../moodledata/

echo "Re-create moodledata"
mkdir ../moodledata
chmod 777 ../moodledata

echo "Allow creation of config.php"
chmod 777 .

# Copy vendor folder
echo "Copy vendor folder"
cp -r /usr/local/share/vendor26 vendor

echo "Initialize installation"

# Convert to type required by moodle cli install
if [ $2 = 'postgres7' ]
then
    DBTYPE='pgsql'
elif [ $2 = 'mysql' ]
then
    DBTYPE='mysqli'
elif [ $2 = 'mssql_n' ]
then
    DBTYPE='mssql'
else
    DBTYPE="$2"
fi

# If the language is set, use that language. Otherwise default to english.
if [ -n "$3" ]
then
    LANG="$3"
else
    LANG='en'
fi

sudo -u www-data php admin/cli/install.php \
    --chmod=755 \
    --lang=$LANG \
    --wwwroot="http://jobs.test.totaralms.com/$JOB_NAME" \
    --dataroot="/var/lib/jenkins/jobs/$JOB_NAME/moodledata" \
    --dbtype="$DBTYPE" \
    --dbname="$DBNAME" \
    --dbhost="$1" \
    --dbuser="jenkins" \
    --dbpass="password" \
    --prefix="tst_" \
    --fullname="$JOB_NAME" \
    --shortname="$JOB_NAME" \
    --adminuser=admin \
    --adminpass="passworD1!" \
    --non-interactive \
    --agree-license \
    --allow-unstable

echo "Appending test variables to config.php"
sudo chown jenkins config.php
echo >> config.php
echo "\$CFG->phpunit_prefix = 'unt_';" >> config.php;
echo "\$CFG->phpunit_dataroot = '/var/lib/jenkins/jobs/$JOB_NAME/phpunit_dataroot';" >> config.php;

echo "Initialize phpunit environment"
php admin/tool/phpunit/cli/init.php

