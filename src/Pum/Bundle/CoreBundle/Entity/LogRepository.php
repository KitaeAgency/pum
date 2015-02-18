<?php

namespace Pum\Bundle\CoreBundle\Entity;

use Doctrine\ORM\EntityRepository;

class LogRepository extends EntityRepository
{
    const LOG_CLASS = 'Pum\Bundle\CoreBundle\Entity\Log';
    /**
     * {@inheritdoc}
     */
    public function count()
    {
        $count = $this->createQueryBuilder('l')->select('COUNT(l.id)')->getQuery()->getSingleScalarResult();

        return $count;
    }
}
