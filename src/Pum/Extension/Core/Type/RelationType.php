
    public function mapRelation(array $relation)
    {
        switch ($relation['type']) {
            case Relation::ONE_TO_MANY:
                if (null === $relation['toName']) {
                    # http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/association-mapping.html#one-to-many-unidirectional-with-join-table
                    $this->mapManyToMany(array(
                        'fieldName'    => $relation['fromName'],
                        'targetEntity' => $relation['toClass'],
                        'joinTable' => array(
                            'name'   => $relation['tableName'],
                            'joinColumns' => array(array('name' => $relation['from'].'_id', 'referencedColumnName' => 'id')),
                            'inverseJoinColumns' => array(array('name' => $relation['to'].'_id', 'referencedColumnName' => 'id', 'unique' => true)),
                        )
                    ));
                } else {
                    $this->mapOneToMany(array(
                        'fieldName'    => $relation['fromName'],
                        'targetEntity' => $relation['toClass'],
                        'mappedBy'    => $relation['toName'],
                    ));
                }

                break;

            case Relation::MANY_TO_ONE:
                $this->mapManyToOne(array(
                    'fieldName'    => $relation['fromName'],
                    'targetEntity' => $relation['toClass'],
                    'joinColumns' => array(
                        array('name' => $relation['fromName'].'_id', 'referencedColumnName' => 'id')
                    )
                ));

                break;
        }
    }
