#!/bin/bash
DATE=$(date +"%d-%b-%Y")
cd /etc
tar -zcvf backup-$DATE.tgz *
mv backup-$DATE.tgz /var/www/project.local
#50 10 * * * /var/www/project.local/tmp/task6.sh
