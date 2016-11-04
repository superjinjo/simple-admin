<?php

define('DB_HOST', 'localhost');
define('DB_NAME', 'boberdoo');
define('PDO_DSN', 'mysql:dbname='.DB_NAME.';host='.DB_HOST);

define('DB_USER', 'bob');
define('DB_PASS', 'erdoo');

function createPDO() {
    try {
        return new PDO(PDO_DSN, DB_USER, DB_PASS);
    } catch (PDOException $e) {
        return 'Connection failed: ' . $e->getMessage();
    }
}

