<?php

namespace Pum\Bundle\CoreBundle\Entity;

use Doctrine\ORM\EntityRepository;

class LogTagRepository extends EntityRepository
{
    const LOG_TAG_CLASS = 'Pum\Bundle\CoreBundle\Entity\LogTag';
    /**
     * {@inheritdoc}
     */
    public function count()
    {
        $count = $this->createQueryBuilder('t')->select('COUNT(t.id)')->getQuery()->getSingleScalarResult();

        return $count;
    }
}
