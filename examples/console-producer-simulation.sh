#!/bin/sh
#php7 console-producer.php --debug 0 --simulate 1 --keyPrefix "mk" --keyDistribution $2 --numMessages $1 --expMinMs 5000000 --expMaxMs 15000000 --sleepUs $3
php7 console-producer.php --debug 0 --simulate 1 --keyPrefix "mk" --keyDistribution $2 --numMessages $1 --expMinMs 5000000 --expMaxMs 5000000 --sleepUs $3
#php7 console-producer.php --debug 0 --simulate 1 --keyPrefix "mk" --keyDistribution $2 --numMessages $1 --expMinMs 1000 --expMaxMs 1000000 --sleepUs $3

