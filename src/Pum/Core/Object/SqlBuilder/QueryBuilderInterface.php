<?php

namespace Pum\Core\Object\SqlBuilder;

/**
 * Interface for QueryBuilder.
 */
interface QueryBuilderInterface
{
    /**
     * set Object Factory
     */
    public function init();
    
    /**
     * set base object name (first function to call in order to init relations etcs)
     *
     * set encoding of return values (default utf8)
     *
     * @throws object not found
     */
    public function from($objectName);
    
    /**
     * set the selected fields
     *
     * $fields : array fields to select (relation is taken in account)
     *
     * $alias : array, key => field selected, value => alias
     *
     * $functions : array, key => field selected, value => function applied to the field
     *
     * @throws relation / field not found
     */
    public function select($fields, $alias, $functions);
    
    /**
     * set the join
     *
     * $left : object to get the relation from
     *
     * $right : relation of left
     *
     * $joinType : join type of the relation  default left join
     *
     * $restrictions : sql possibly added in ON clause
     *
     * @throws relation not found / left is not a relation of right
     */
    public function join($left, $right, $joinType = 'left join', $restrictions = array('query' => '', 'params' => array()));
    
    /**
     * set one restriction
     *
     * $field : field to restrict (relation is taken in account)
     *
     * $value : value of the the restriction
     *
     * $operator : operator of the restriction (default =)
     *
     * $additional : and or clause (default and) (priorities are not taken in account, you may use sqlRestriction for this)
     */
    public function restriction($field, $value, $operator = '=', $additional = 'and');
    
    /**
     * set sql restriction
     *
     * $sql : sql query
     *
     * $params : parameters
     */
    public function sqlRestriction($sql, $params = array());
    
    /**
     * set group by
     *
     * $fields : fields to group (alias or relations are possible)
     */
    public function groupBy($fields);
    
    /**
     * set having restriction
     *
     * $field : field to restrict (relation is taken in account)
     *
     * $value : value of the the restriction
     *
     * $operator : operator of the restriction (default =)
     *
     * $function : function applied to the field
     *
     * $additional : and or clause (default and) (priorities are not taken in account, you may use sqlRestriction for this)
     */
    public function having($field, $value, $operator, $function = '', $additional = 'and');
    
    /**
     * set having restriction
     *
     * $sql : sql query
     *
     * $params : query parameters
     */
    public function sqlHaving($sql, $params = array());
    
    /**
     * set limit and offset
     *
     * $limit : limit
     *
     * $offset : offset
     *
     */
    public function pagination($limit, $offset);
    
    /**
     * set order
     *
     * $fields : array fields to order
     *
     * $orders : array key => field, value => asc / desc
     *
     */
    public function order($fields, $orders);
    
    /**
     * execute query
     *
     * $debug : show query (true => parameters are replaced, 'dql' => parameteres are not replaced)
     *
     */
    public function execute($debug = false);
}
