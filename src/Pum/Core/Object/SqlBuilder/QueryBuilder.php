<?php

namespace Pum\Core\Object\SqlBuilder;

use Pum\Bundle\CoreBundle\PumContext;
use Pum\Core\ObjectFactory;

class QueryBuilder implements QueryBuilderInterface
{
    /**
     * @var PumContext
     */
    private $context;
    
    /**
     * @var pdo
     */
    private $pdo;
    
    /**
     * @var tables
     */
    private $tables;
    
    /**
     * @var query
     */
    private $query;
    
    /**
     * @var objectFactory
     */
    private $objectFactory;
    
    /**
     * @var fromTable
     */
    private $fromTable;
    
    /**
     * @var allowedFunctions
     */
    private $allowedFunctions;

    public function __construct(PumContext $context, ObjectFactory $objectFactory)
    {
        $this->context          = $context;
        $this->objectFactory    = $objectFactory;

        $this->init();

        $this->tables           = null;

        $this->allowedFunctions = array(
            'avg',
            'ceil',
            'floor',
            'count',
            'length',
            'lower',
            'max',
            'min',
            'round',
            'sum',
            'upper',
        );
        
        $db        = $this->context->getContainer()->getParameter('database_name');
        $host      = $this->context->getContainer()->getParameter('database_host');
        $user      = $this->context->getContainer()->getParameter('database_user');
        $pwd       = $this->context->getContainer()->getParameter('database_password');
        
        $this->pdo = new \PDO('mysql:host='.$host.';dbname='.$db.';charset=utf8', $user, $pwd);
        $this->pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_OBJ);
    }

    public function init()
    {
        $this->query = array(
            'params' => array(),
            'alias'  => array(),
            'orders' => array(),
            'groups' => array(),
        );

        return $this;
    }

    public function select($fields = array(), $alias = array(), $functions = array())
    {
        if (!is_array($fields)) {
            $fields = array($fields);
        }
        
        foreach ($fields as $field) {
            if ($searchField = $this->fieldExists($field)) {
                $parent = $searchField['parent'];
                $fieldName  = $searchField['field'];
                
                if (isset($functions[$field]) && in_array($functions[$field], $this->allowedFunctions)) {
                    
                    $this->query['selects'][] = strtoupper($functions[$field]).'('.$parent.'.'.$fieldName.')';
                }
                else {
                    $this->query['selects'][] = $parent.'.'.$fieldName;
                }
                
                if (isset($alias[$field])) {
                    $this->query['selects'][count($this->query['selects'])-1] .= ' as '.$alias[$field];
                    $this->query['alias'][$alias[$field]] = $field;
                }
            }
        }

        return $this;
    }

    public function from($objectName)
    {
        //reinit
        $this->init();

        $this->generateDefinition($objectName);
        $this->fromTable = $objectName;
        
        $this->query['from'] = 'obj__'.$this->context->getProjectName().'__'.$objectName.' '.$objectName;

        return $this;
    }

    public function generateDefinition($objectName, $parent = null)
    {
        if (!$parent) {
            $parent = $objectName;
        }
        
        if (!isset($this->tables[$parent])) {
            if ($this->tables[$parent]['fields'] = $this->objectFactory->getProject($this->context->getProjectName())->getObject($objectName)->getFields()) {
                $this->tables[$parent]['object_type'] = $objectName;
            }
            else {
                throw new \Exception('Object '.$objectName.' not Found');
            }
        }

        return $this;
    }

    public function getRelationDefinition($relation, $parent)
    {
        if (!isset($this->tables[$parent])) {
            return false;
        }
        
        if ($this->join($parent, $relation)) {
            return true;
        }
        
        return false;
    }

    public function fieldExists($field)
    {
        $parent = null;
        
        $fromTable   = $this->fromTable;
        $parent      = $fromTable;
        $relations   = explode('.', $field);
        $field       = $relations[count($relations)-1];
        unset($relations[count($relations)-1]);
        
        foreach ($relations as $key => $relation) {
            if ($key > 0) {
                $parent = $fromTable.'_'.implode('_', array_slice($relations, 0, $key));
            }
            
            if (!$this->getRelationDefinition($relation, $parent)) {
                throw new \Exception('Relation '.$parent.' - '.$relation.' not Found');
            }
        }
        
        $parent = $fromTable;
        if (!empty($relations)) {
            $parent .= '_'.implode('_', $relations);
        }
        
        if (!isset($this->tables[$parent]) || (!isset($this->tables[$parent]['fields'][$field]) && trim($field) != '*' && trim($field) != 'id')) {
            throw new \Exception('Field '.$parent.' '.$field.' not Found');
        }
        
        return array('parent' => $parent, 'field' => $field);
    }

    public function pagination($limit = null, $offset = null)
    {
        if ($limit && is_numeric($limit)) {
            $this->query['limit'] = $limit;
        }
        
        if ($offset && is_numeric($offset)) {
            $this->query['offset'] = $offset;
        }

        return $this;
    }

    public function join($left, $right, $joinType = 'left join', $restrictions = array('query' => '', 'params' => array()))
    {
        $inner = '';
        if ($left) {
            $inner = '_';
        }
        
        if (!isset($this->tables[$left]['fields'][$right])) {
            throw new \Exception('Relation '.$left.' '.$right.' not Found');
        }
        
        if ($this->tables[$left]['fields'][$right]->getType() != 'relation') {
            throw new \Exception($right.' is not a relation of '.$left);
        }
        
        if (!isset($this->tables[$left.$inner.$right])) {
            $typeOptions = $this->tables[$left]['fields'][$right]->getTypeOptions();
            $this->generateDefinition($typeOptions['target'], $left.$inner.$right);
            
            $leftJoin = $left;
            if (!$left) {
                $letJoin = $this->fromTable;
            }
            
            $joinRestriction = '';
            if (isset($restrictions['query']) && $restrictions['query'] != '') {
                $joinRestriction = ' AND '.$this->getSqlRestrictionQuery($restrictions['query']);
                
                if (!empty($restrictions['params'])) {
                    $this->query['params'] = array_merge($this->query['params'], $restrictions['params']);
                }
            }
            
            switch ($typeOptions['type']) {
                default :
                    $on = '';
                    
                    $inverseRelation = ($typeOptions['inversed_by']) ? $typeOptions['inversed_by'].'_id' : 'id';
                    
                    $on .= $leftJoin.'.id = '.$left.$inner.$right.'.'.$inverseRelation;
                    
                    $this->query['joins'][] = strtoupper($joinType).' obj__'.$this->context->getProjectName().'__'.$typeOptions['target'].' '.$left.$inner.$right.' ON ('.$on.$joinRestriction.')';
                break;
                
                case 'many-to-one' :
                    $on = '';
                    
                    $inverseRelation = 'id';
                    
                    $on .= $leftJoin.'.'.$this->tables[$left]['fields'][$right]->getName().'_id = '.$left.$inner.$right.'.'.$inverseRelation;
                    
                    $this->query['joins'][] = strtoupper($joinType).' obj__'.$this->context->getProjectName().'__'.$typeOptions['target'].' '.$left.$inner.$right.' ON ('.$on.$joinRestriction.')';
                break;
                
                case 'many-to-many' :
                    $leftAssoc  = ($typeOptions['owning']) ? $this->tables[$leftJoin]['object_type'] : $typeOptions['target'];
                    $rightAssoc = ($typeOptions['owning']) ? $right : $typeOptions['inversed_by'];
                    
                    $onAssoc    = $leftJoin.'.id = '.$leftJoin.'_assoc_'.$leftAssoc.'_'.$rightAssoc.'.'.$this->tables[$leftJoin]['object_type'].'_id';
                    $assocTable = strtoupper($joinType).' obj__'.$this->context->getProjectName().'__assoc__'.$leftAssoc.'__'.$rightAssoc.' '.$leftJoin.'_assoc_'.$leftAssoc.'_'.$rightAssoc.' ON ('.$onAssoc.')';
                    $this->query['joins'][] = $assocTable;
                    
                    
                    $on = '';
                    $inverseRelation = 'id';
                    $on .= $leftJoin.'_assoc_'.$leftAssoc.'_'.$rightAssoc.'.'.$typeOptions['target'].'_id = '.$left.$inner.$right.'.'.$inverseRelation;
                    $this->query['joins'][] = strtoupper($joinType).' obj__'.$this->context->getProjectName().'__'.$typeOptions['target'].' '.$left.$inner.$right.' ON ('.$on.$joinRestriction.')';
                break;
            }
        }
        
        return $this;
    }

    public function restriction($field, $value, $operator = '=', $additional = 'and')
    {
        if ($searchField = $this->fieldExists($field)) {
            $parent = $searchField['parent'];
            $field  = $searchField['field'];
            
            if (is_array($value)) {
                $operator = 'IN';
            }
        
            $restriction = strtoupper($additional).' ';
            if (!isset($this->query['restrictions'])) {
                $this->query['restrictions'] = array();
                $restriction = 'WHERE ';
            }
            
            $count = count($this->query['restrictions']);
            
            if (isset($this->query['havings'])) {
                $count += count($this->query['havings']);
            }
            
            if (is_array($value)) {
                $in_query = '';
                foreach($value as $key_v => $value_v) {
                    $in_query = $in_query . ':'.$field.'_' . $count . '_' . $key_v . ', ';
                }
                $in_query = trim(substr($in_query, 0, -2));
            }
            
            if (isset($in_query)) {
                $restriction .= $parent.'.'.$field.' '.strtoupper($operator).' ('.$in_query.')';
                
                foreach ($value as $key => $val) {
                    $this->query['params'][':'.$field.'_' . $count . '_' . $key] = $value[$key];
                }
            }
            else {
                $restriction .= $parent.'.'.$field.' '.strtoupper($operator).' :'.$field.'_'.$count;
                $this->query['params'][':'.$field.'_'.$count] = $value;
            }
            
            $this->query['restrictions'][] = $restriction;
        }

        return $this;
    }

    public function sqlRestriction($sql, $params = array())
    {
        preg_match_all('#\{(([a-zA-Z0-9_]+?\.)*([a-zA-Z0-9_]+?))\}#', $sql, $matches);
        
        if (isset($matches[1]) && !empty($matches[1])) {
            foreach ($matches[1] as $key => $field) {
                if ($searchField = $this->fieldExists($field)) {
                    $parent = $searchField['parent'];
                    $field  = $searchField['field'];
                    
                    $sql = str_replace($matches[0][$key], $parent.'.'.$field, $sql);
                }
            }
        }
        
        if (!isset($this->query['restrictions'])) {
            $this->query['restrictions'] = array();
            $sql = 'WHERE '.$sql;
        }
        
        if (!empty($params)) {
            $this->query['params'] = array_merge($this->query['params'], $params);
        }
        
        $this->query['restrictions'][] = $sql;

        return $this;
    }

    public function getSqlRestrictionQuery($sql)
    {
        preg_match_all('#\{(([a-zA-Z0-9_]+?\.)*([a-zA-Z0-9_]+?))\}#', $sql, $matches);
        
        if (isset($matches[1]) && !empty($matches[1])) {
            foreach ($matches[1] as $key => $field) {
                if ($searchField = $this->fieldExists($field)) {
                    $parent = $searchField['parent'];
                    $field  = $searchField['field'];
                    
                    $sql = str_replace($matches[0][$key], $parent.'.'.$field, $sql);
                }
            }
        }
        
        return $sql;
    }

    public function groupBy($fields = array())
    {
        if (!is_array($fields)) {
            $fields = array($fields);
        }
            
        foreach ($fields as $field) {
            $canGroup = false;
            if (array_search($field, $this->query['alias']) !== false) {
                $canGroup = true;
            }
            elseif ($searchField = $this->fieldExists($field)) {
                $field = $searchField['parent'].'.'.$searchField['field'];
                $canGroup = true;
            }
            
            if ($canGroup) {
                $this->query['groups'][] = $field;
            }
        }

        return $this;
    }

    public function having($field, $value, $operator = '=', $function = '', $additional = 'and')
    {
        $canHave = false;
        
        if (isset($this->query['alias'][$field])) {
            $canHave = true;
            $parent = '';
        }
        elseif ($searchField = $this->fieldExists($field)) {
            $parent  = $searchField['parent'].'.';
            $field   = $searchField['field'];
            $canHave = true;
        }
        
        if ($canHave) {
            if (is_array($value)) {
                $operator = 'IN';
            }
        
            $restriction = strtoupper($additional).' ';
            if (!isset($this->query['havings'])) {
                $this->query['havings'] = array();
                $restriction = 'HAVING ';
            }
            
            $count = count($this->query['havings']);
            
            if (isset($this->query['restrictions'])) {
                $count += count($this->query['restrictions']);
            }
            
            if (is_array($value)) {
                $in_query = '';
                foreach($value as $key_v => $value_v) {
                    $in_query = $in_query . ':'.$field.'_' . $count . '_' . $key_v . ', ';
                }
                $in_query = trim(substr($in_query, 0, -2));
            }
            
            if (isset($in_query)) {
                $havingField = $parent.$field;
                if ($function != '' && in_array($function, $this->allowedFunctions)) {
                    
                    $havingField = strtoupper($function).'('.$havingField.')';
                }
                $restriction .= $havingField.' '.strtoupper($operator).' ('.$in_query.')';
                
                foreach ($value as $key => $val) {
                    $this->query['params'][':'.$field.'_' . $count . '_' . $key] = $value[$key];
                }
            }
            else {
                $havingField = $parent.$field;
                if ($function != '' && in_array($function, $this->allowedFunctions)) {
                    
                    $havingField = strtoupper($function).'('.$havingField.')';
                }
                $restriction .= $havingField.' '.strtoupper($operator).' :'.$field.'_'.$count;
                
                $this->query['params'][':'.$field.'_'.$count] = $value;
            }
            
            $this->query['havings'][] = $restriction;
        }

        return $this;
    }

    public function sqlHaving($sql, $params = array())
    {
        preg_match_all('#\{(([a-zA-Z0-9_]+?\.)*([a-zA-Z0-9_]+?))\}#', $sql, $matches);
        
        if (isset($matches[1]) && !empty($matches[1])) {
            foreach ($matches[1] as $key => $field) {
                if (isset($this->query['alias'][$field])) {
                    $sql = str_replace($matches[0][$key], $field, $sql);
                }
                elseif ($searchField = $this->fieldExists($field)) {
                    $parent = $searchField['parent'];
                    $field  = $searchField['field'];
                    
                    $sql = str_replace($matches[0][$key], $parent.'.'.$field, $sql);
                }
            }
        }
        
        if (!empty($params)) {
            $this->query['params'] = array_merge($this->query['params'], $params);
        }
        
        if (!isset($this->query['havings'])) {
            $this->query['havings'] = array();
            $sql = 'HAVING '.$sql;
        }
        
        $this->query['havings'][] = $sql;

        return $this;
    }

    public function order($fields = array(), $orders = array())
    {
        if (!is_array($fields)) {
            $fields = array($fields);
        }
        
        if (!is_array($orders)) {
            $orders = array($orders);
        }
        
        foreach ($fields as $key => $field) {
            $canOrder = false;
            if (isset($this->query['alias'][$field]) !== false) {
                $canOrder = true;
            }
            elseif ($searchField = $this->fieldExists($field)) {
                $field = $searchField['parent'].'.'.$searchField['field'];
                $canOrder = true;
            }
            
            if ($canOrder) {
                $orderBy = $field;
                $order   = 'ASC';
                
                if (isset($orders[$key])) {
                    $order = strtoupper($orders[$key]);
                }
                
                $orderBy .= ' '.$order;
                
                $this->query['orders'][] = $orderBy;
            }
        }

        return $this;
    }

    public function getQuery()
    {
        if (!isset($this->query['selects'])) {
            $this->query['selects'][] = $this->fromTable.'.*';
        }
        
        $query = '';
        
        $query .= 'SELECT '.implode(', ', $this->query['selects']);
        $query .= chr(13).' FROM '.$this->query['from'];
        
        if (isset($this->query['joins'])) {
            $query .= chr(13).' '.implode(chr(13).' ', $this->query['joins']);
        }
        
        if (isset($this->query['restrictions'])) {
            $query .= chr(13).' '.implode(chr(13).' ', $this->query['restrictions']);
        }
        
        if (!empty($this->query['groups'])) {
            $query .= chr(13).' GROUP BY '.implode(', '.chr(13), $this->query['groups']);
        }
        
        if (isset($this->query['havings'])) {
            $query .= chr(13).' '.implode(chr(13).' ', $this->query['havings']);
        }
        
        if (!empty($this->query['orders'])) {
            $order = chr(13).' ORDER BY '.implode(', ', $this->query['orders']);
            $query .= $order;
        }
        
        if (isset($this->query['limit'])) {
            $query .= chr(13).' LIMIT '.$this->query['limit'];
            
            if (isset($this->query['offset'])) {
                $query .= ', '.$this->query['offset'];
            }
        }
        
        return $query;
    }

    public function execute($debug = false)
    {
        $query = $this->pdo->prepare($this->getQuery());
        $query->execute($this->query['params']);
        
        if ($debug) {
            if ($debug === 'dql') {
                echo '<pre style="color:green">'.$this->getQuery().'</pre>';
                echo '<pre style="color:purple">Params :</pre>';
                
                foreach ($this->query['params'] as $key => $value) {
                    print_r ('<pre style="color:purple">'.$key.' -> '.$value.'</pre>');
                }
            }
            else {
                $returnQuery = $this->getQuery();
                
                foreach ($this->query['params'] as $key => $value) {
                    $returnQuery = str_replace($key, '"'.$value.'"', $returnQuery);
                }
                
                echo '<pre style="color:green">'.$returnQuery.'</pre>';
            }
        }
        
        return $query->fetchAll();
    }
}
