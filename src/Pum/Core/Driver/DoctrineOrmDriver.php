<?php

namespace Pum\Core\Driver;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use Pum\Core\Definition\Beam;
use Pum\Core\Definition\ObjectDefinition;
use Pum\Core\Definition\Project;
use Pum\Core\Exception\BeamNotFoundException;
use Pum\Core\Exception\DefinitionNotFoundException;
use Pum\Core\Exception\ProjectNotFoundException;

class DoctrineOrmDriver implements DriverInterface
{
    protected $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager  = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getBeamNames()
    {
        $result = array();

        $query = $this->getBeamRepository()
            ->createQueryBuilder('b')
            ->select('b.name AS name')
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
    public function getProjectNames()
    {
        $result = array();

        $query = $this->getProjectRepository()
            ->createQueryBuilder('p')
            ->select('p.name AS name')
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
    public function getBeam($name)
    {
        $beam = $this->getBeamRepository()->findOneBy(array('name' => $name));

        if (!$beam) {
            throw new BeamNotFoundException($name);
        }

        return $beam;
    }

    /**
     * {@inheritdoc}
     */
    public function saveBeam(Beam $beam)
    {
        $this->entityManager->persist($beam);
        $this->entityManager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function deleteBeam(Beam $beam)
    {
        $this->entityManager->remove($beam);
        $this->entityManager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getProject($name)
    {
        $beam = $this->getProjectRepository()->findOneBy(array('name' => $name));

        if (!$beam) {
            throw new ProjectNotFoundException($name);
        }

        return $beam;
    }

    /**
     * {@inheritdoc}
     */
    public function saveProject(Project $project)
    {
        $this->entityManager->persist($project);
        $this->entityManager->flush($project);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteProject(Project $project)
    {
        $this->entityManager->remove($project);
        $this->entityManager->flush($project);
    }

    /**
     * @return EntityRepository
     */
    private function getBeamRepository()
    {
        return $this->entityManager->getRepository('Pum\Core\Definition\Beam');
    }

    /**
     * @return EntityRepository
     */
    private function getProjectRepository()
    {
        return $this->entityManager->getRepository('Pum\Core\Definition\Project');
    }
}
