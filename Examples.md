# Select #

```
<?php
$q = new SelectQuery(array('my_table', 'another_table'));
$q->setWhere(new Condition('=', new Field('fk1', 0), new Field('id', 1)));

echo $q->sql();
```

will output:
```
SELECT t0.* FROM `my_table` as t0, `another_table` as t1 WHERE t0.`fk1` = t1.`id`
```