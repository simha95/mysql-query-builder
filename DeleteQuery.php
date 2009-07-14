<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 enc=utf8: */
/**
 * @author Alexey Zakhlestin
 * @package mysql-query-builder
 **/
/*
    MySQL Query Builder
    Copyright © 2005-2009  Alexey Zakhlestin <indeyets@gmail.com>
    Copyright © 2005-2006  Konstantin Sedov <kostya.online@gmail.com>

    This library is free software; you can redistribute it and/or
    modify it under the terms of the GNU Lesser General Public
    License as published by the Free Software Foundation; either
    version 2.1 of the License, or (at your option) any later version.

    This library is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
    Lesser General Public License for more details.

    You should have received a copy of the GNU Lesser General Public
    License along with this library; if not, write to the Free Software
    Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
*/

/**
 * This class contains logic of "DELETE" queries
 *
 * @package mysql-query-builder
 * @author Alexey Zakhlestin
 */
class DeleteQuery extends BasicQuery
{
    private $del_limit = null;
    private $del_tables = null;

    public function __construct($tables, array $del_tables = array(0))
    {
        parent::__construct($tables);
        $this->del_tables = $del_tables;
    }

    /**
     * wrapper around BasicQuery::setOrderBy, which additionally checks if it is allowed, to apply order to the query. 
     * it is allowed only for single-table queries
     *
     * @param array $orderlist 
     * @param array $orderdirectionlist 
     * @return void
     * @throws LogicException
     */
    public function setOrderby(array $orderlist, array $orderdirectionlist = array())
    {
        if (count($this->from) != 1) {
            throw new LogicException("setOrderby is allowed only in single-table delete queries");
        }

        parent::setOrderby($orderlist, $orderdirectionlist);
    }

    /**
     * Sets maximum number of rows, the DELETE query will be applied to.
     * MySQL does not allow to specify offset, so, it is just a single number
     *
     * @param integer $limit 
     * @return void
     * @throws LogicException, InvalidArgumentException
     */
    public function setLimit($limit)
    {
        if (count($this->from) != 1) {
            throw new LogicException("setLimit is allowed only in single-table delete queries");
        }

        if (!is_numeric($limit) or $limit < 1)
            throw new InvalidArgumentException('positive number should be used as a limit');

        $this->del_limit = (string)$limit;
    }

    protected function getSql(array &$parameters)
    {
        $sql  = $this->getDelete($parameters);
        $sql .= $this->getUsing($parameters);
        $sql .= $this->getWhere($parameters);
        $sql .= $this->getOrderby($parameters);
        $sql .= $this->getLimit($parameters);

        if (count($this->from) == 1) {
            $sql = str_replace('`t0`', $this->from[0]->__toString(), $sql);
        }

        return $sql;
    }

    private function getDelete(&$parameters)
    {
        if (count($this->from) == 1)
            return 'DELETE FROM '.$this->from[0]->__toString();
        else {
            $sql = 'DELETE FROM';

            $first = true;
            foreach ($this->del_tables as $tbl) {
                if ($first) {
                    $first = false;
                } else {
                    $sql .= ',';
                }

                $sql .= ' `t'.$tbl.'`';
            }

            return $sql;
        }
    }

    protected function getUsing(&$parameters)
    {
        if (count($this->from) == 1)
            return '';

        $froms = array();
        for ($i = 0; $i < count($this->from); $i++) {
            $froms[] = $this->from[$i]->__toString().' AS `t'.$i.'`';
        }

        return " USING ".implode(", ", $froms);
    }

    protected function getLimit()
    {
        return (null == $this->del_limit) ? '' : ' LIMIT '.$this->del_limit;
    }
}
