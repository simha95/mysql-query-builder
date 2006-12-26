<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
require_once "..".DIRECTORY_SEPARATOR."autoload.php";

$q = new SelectQuery(array('my_table', 'another_table'));
$q->setWhere(new Condition('=', new Field('fk1', 0), new Field('id', 1)));

echo $q->sql()."\n";