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

    /**
     * Static caches.
     */
    protected $beamNames    = null;
    protected $beams        = array();
    protected $projectNames = null;
    protected $projects = array();

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager  = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getBeamNames()
    {
        if (null === $this->beamNames) {
            $query = $this->getBeamRepository()
                ->createQueryBuilder('b')
                ->select('b.name AS name')
                ->getQuery()
            ;

            $this->beamNames = array();
            foreach ($query->execute() as $entry) {
                $this->beamNames[] = $entry['name'];
            }
        }

        return $this->beamNames;
    }

    /**
     * {@inheritdoc}
     */
    public function getProjectNames()
    {
        if (null === $this->projectNames) {
            $query = $this->getProjectRepository()
                ->createQueryBuilder('p')
                ->select('p.name AS name')
                ->getQuery()
            ;

            $this->projectNames = array();
            foreach ($query->execute() as $entry) {
                $this->projectNames[] = $entry['name'];
            }
        }

        return $this->projectNames;
    }

    /**
     * {@inheritdoc}
     */
    public function getBeam($name)
    {
        if (isset($this->beams[$name])) {
            return $this->entityManager->merge($this->beams[$name]);
        }

        $beam = $this->getBeamRepository()->findOneBy(array('name' => $name));

        if (!$beam) {
            throw new BeamNotFoundException($name);
        }

        return $this->beams[$name] = $beam;
    }

    /**
     * {@inheritdoc}
     */
    public function saveBeam(Beam $beam)
    {
        $this->beams[$beam->getName()] = $beam;

        $this->entityManager->transactional(function ($em) use ($beam) {
            $em->persist($beam);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function deleteBeam(Beam $beam)
    {
        unset($this->beams[$beam->getName()]);

        $this->entityManager->transactional(function ($em) use ($beam) {
            $em->remove($beam);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function getProject($name)
    {
        if (isset($this->projects[$name])) {
            return $this->entityManager->merge($this->projects[$name]);
        }

        $project = $this->getProjectRepository()->createQueryBuilder('p')
            ->select('p, b, o, r, f')
            ->leftJoin('p.beams', 'b')
            ->leftJoin('b.objects', 'o')
            ->leftJoin('b.relations', 'r')
            ->leftJoin('o.fields', 'f')
            ->where('p.name = :name')
            ->setParameter('name', $name)
            ->getQuery()
            ->getOneOrNullResult()
        ;

        if (!$project) {
            throw new ProjectNotFoundException($name);
        }

        return $this->projects[$name] = $project;
    }

    /**
     * {@inheritdoc}
     */
    public function saveProject(Project $project)
    {
        $this->projects[$project->getName()] = $project;

        $this->entityManager->transactional(function ($em) use ($project) {
            $em->persist($project);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function deleteProject(Project $project)
    {
        unset($this->projects[$project->getName()]);

        $this->entityManager->transactional(function ($em) use ($project) {
            $em->remove($project);
        });
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
