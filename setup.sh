#!/bin/sh

mysqld
mysql -u dev -p dev booking < /var/www/tests/fixtures/db.sql
