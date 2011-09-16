<?php

namespace Admingenerator\GeneratorBundle\QueryFilter;

class PropelQueryFilter extends BaseQueryFilter
{

    public function addDefaultFilter($field, $value)
    {
        $this->query->filterBy($field, $value);
    }

    public function addBooleanFilter($field, $value)
    {
        if ("" !== $value) {
            $this->query->filterBy($field, $value);
        }
    }

    public function addVarcharFilter($field, $value)
    {
        $this->query->filterBy($field, '%'.$value.'%', \Criteria::LIKE);
    }

    public function addModelFilter($field, $value)
    {
        $method = 'filterBy'.$field;
        call_user_func_array(array($this->query, $method), array($value));
    }
    
    public function addCollectionFilter($field, $value)
    {
        if (!is_array($value)) {
            $value = array($value->getId());
        }

        if (strstr($field, '.')) {
            list($table, $field) = explode('.', $field);
        } else {
            $table = $field;
            $field = 'id';
        }

        $subquery = call_user_func_array(array($this->query, 'use'.$table.'Query'), array($table, \Criteria::INNER_JOIN));
        $subquery->filterBy($field, $value, \Criteria::IN)
                 ->endUse()
                 ->groupById();
    }

    public function addDateFilter($field, $value)
    {
        if (is_array($value)) {
            $filters = array();
            
            if ($value['from']) {
                $filters['min'] = $value['from']->format('Y-m-d');
            }

            if ($value['to']) {
                $filters['to'] = $value['to']->format('Y-m-d');
            }

            if (count($filters) > 0) {
                $method = 'filterBy'.$field;
                call_user_func_array(array($this->query, $method), array($filters));
            }  
            
        } elseif($value instanceof \DateTime) {
            $this->query->filterBy($field, $value->format('Y-m-d'));
        }
    }
}
