<?php

namespace Pum\Core\Driver;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Pum\Core\Definition\ObjectDefinition;
use Pum\Core\Exception\DefinitionNotFoundException;

class DoctrineOrmDriver implements DriverInterface
{
    protected $entityManager;
    protected $repositoryName;

    public function __construct(EntityManager $entityManager, $repositoryName = 'Pum\Core\Definition\ObjectDefinition')
    {
        $this->entityManager  = $entityManager;
        $this->repositoryName = $repositoryName;
    }

    /**
     * {@inheritdoc}
     */
    public function getAllDefinitionNames()
    {
        $result = array();

        $query = $this->getRepository()
            ->createQueryBuilder('a')
            ->select('a.name AS name')
            ->getQuery()
        ;

        $result = array();
        foreach ($query->execute() as $entry) {
            $result[] = $entry['name'];
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefinition($name)
    {
        if ($result = $this->getRepository()->findOneByName($name)) {
            return $result;
        }

        $available = $this->getAllDefinitionNames();

        throw new DefinitionNotFoundException($name, $available);
    }

    /**
     * {@inheritdoc}
     */
    public function save(ObjectDefinition $definition)
    {
        $this->entityManager->persist($definition);
        $this->entityManager->flush($definition);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(ObjectDefinition $definition)
    {
        $this->entityManager->remove($definition);
        $this->entityManager->flush($definition);
    }

    /**
     * @return EntityRepository
     */
    private function getRepository()
    {
        return $this->entityManager->getRepository($this->repositoryName);
    }
}
