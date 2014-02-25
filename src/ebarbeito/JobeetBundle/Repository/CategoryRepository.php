<?php

namespace ebarbeito\JobeetBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * CategoryRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class CategoryRepository extends EntityRepository {

  public function getWithJobs() {
    $query = $this->getEntityManager()
      ->createQuery('SELECT c FROM ebarbeitoJobeetBundle:Category c LEFT JOIN c.jobs j WHERE j.expires_at > :date AND j.is_activated = :activated')
      ->setParameter('date', date('Y-m-d H:i:s', time()))
      ->setParameter('activated', 1);

    return $query->getResult();
  }

}
