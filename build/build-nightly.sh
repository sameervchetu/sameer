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
# This accepts two arguments
# 1/ The base url of the site being tested (including trailing slash)
# 2/ The cucumber test profile (see cucumber.yml)

# Step timer function
step_time () {
    if [ -z $TOTAL ]; then TOTAL=0; fi
    STEPTIME=$((`date +%s`-$TOTAL-$TIME))
    echo "STEP $1: Finished $STEPTIME Seconds ($(($STEPTIME/60)) Minutes)"
    TOTAL=$(($TOTAL+$STEPTIME))
}

TIME=`date +%s`
echo "STEP 1: Run php syntax check";
find . \( -path ./vendor -prune -o -name '*.php' -o -name '*.html' \) -print0 | xargs -0 -n1 -P4 ./build/lint.sh | grep -v "No syntax errors detected"
step_time "1"

echo "STEP 2: Generate some test users"
sudo -u www-data php build/generate_users.php
step_time "2"

echo "STEP 3: Run PHPUnit";
vendor/bin/phpunit --log-junit build/logs/xml/TEST-suite.xml
step_time "3"

echo "STEP 4: Run cucumber tests (disabled link checker tests)";
cucumber -p $2 --format junit --out build/logs/xml/
step_time "4"

if [ $2 = 'pgsql' ]
then
    echo "STEP 5: Generate database performance report"
    echo "Not currently enabled. Need to set up pgfouine to make this work"
#    mkdir -p build/logs/perf
#    cd build/logs/perf
#    # only process log info from the nightly database
#    sudo pgfouine -database t1-hudsontesting-nightly -file /var/log/postgresql/postgres.log -top 40 -report queries.html=overall,bytype,slowest,n-mosttime,n-mostfrequent,n-slowestaverage -report hourly.html=overall,hourly -report errors.html=overall,n-mostfrequenterrors -format html-with-graphs
#    cd -
    step_time "5"
fi

echo "STEP 6: Run link checker script as a learner";
sudo -u www-data php build/link_checker.php $1 learner 'passworD1!'
step_time "6"

echo "STEP 7: Run link checker script as an admin";
sudo -u www-data php build/link_checker.php $1 admin 'passworD1!'
step_time "7"

echo "Total Time was: $TOTAL Seconds ($(($TOTAL/60)) Minutes)"

