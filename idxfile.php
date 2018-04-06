<?php

function build(Idephix\Context $context)
{
    $context->local('mysql -hmysql -udev -pdev booking < /var/www/tests/fixtures/db.sql');
    $context->local('bin/console c:c');
    $context->test();
}

function test(Idephix\Context $context)
{
    $context->local('bin/phpunit -c ./', false, 300);
}
