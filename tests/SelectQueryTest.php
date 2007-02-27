<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
require_once 'PHPUnit/Framework.php';
require_once "..".DIRECTORY_SEPARATOR."autoload.php";
 
class SelectQueryTest extends PHPUnit_Framework_TestCase
{
    public function testSelectAllFromOneTable()
    {
        $q = new SelectQuery(array('test'));

        $this->assertEquals('SELECT `t0`.* FROM `test` AS `t0`', $q->sql());
        $this->assertEquals(0, count($q->parameters()));
    }

    public function testSelectSomeFromOneTable()
    {
        $q = new SelectQuery(array('test'));
        $q->setWhere(new Condition('=', new Field('somefield'), 35));
        $q->setLimit(10, 2);

        $this->assertEquals('SELECT `t0`.* FROM `test` AS `t0` WHERE `t0`.`somefield` = :p1 LIMIT :p2 OFFSET :p3', $q->sql());

        $params = $q->parameters();
        $this->assertEquals(35, $params[':p1']);
        $this->assertEquals(10, $params[':p2']);
        $this->assertEquals(2, $params[':p3']);
    }

    public function testNestedConditions()
    {
        $q = new SelectQuery(array('test'));
        $q->setWhere(new AndOp(array(
            new Condition('>', new Field('id'), 12),
            new OrOp(array(
                new Condition('=', new Field('status'), 'demolished'),
                new NotOp(
                    new Condition('<', new Field('age'), 5)
                )
            ))
        )));

        $this->assertEquals('SELECT `t0`.* FROM `test` AS `t0` WHERE (`t0`.`id` > :p1 AND (`t0`.`status` = :p2 OR NOT (`t0`.`age` < :p3)))', $q->sql());
    }

    public function testNotOp()
    {
        try {
            new NotOp(array(
                new Condition('=', new Field('test'), 1),
                new Condition('=', new Field('test'), 2),
            ));
            fail(); // exception should happen
        } catch (InvalidArgumentException $e) {
        }
    }

    public function testInCondition()
    {
        $q = new SelectQuery(array('test'));
        $q->setWhere(new Condition('in', new Field('id'), array(1, 3, 5)));

        $this->assertEquals('SELECT `t0`.* FROM `test` AS `t0` WHERE `t0`.`id` IN (1, 3, 5)', $q->sql());
    }

    public function testSelectSpecificFields()
    {
        $q = new SelectQuery(array('test', 'test2'));
        $q->setSelect(array(new AllFields(), new Field('id', 1)));

        $this->assertEquals('SELECT `t0`.*, `t1`.`id` FROM `test` AS `t0`, `test2` AS `t1`', $q->sql());
    }

    public function testAlias()
    {
        $field1 = new Field('id', 0, 'test');

        $q = new SelectQuery(array('test', 'test2'));
        $q->setSelect(array($field1, new AllFields(1)));
        $q->setWhere(new Condition('=', $field1, '2'));

        $this->assertEquals('SELECT `t0`.`id` AS `test`, `t1`.* FROM `test` AS `t0`, `test2` AS `t1` WHERE `test` = :p1', $q->sql());
    }
}