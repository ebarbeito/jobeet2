<?php

namespace ebarbeito\JobeetBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Category
 */
class Category {

  /**
   * @var integer
   */
  private $id;

  /**
   * @var string
   */
  private $name;

  /**
   * @var \Doctrine\Common\Collections\Collection
   */
  private $jobs;

  /**
   * @var \Doctrine\Common\Collections\Collection
   */
  private $affiliates;

  /**
   * Constructor
   */
  public function __construct() {
    $this->jobs = new \Doctrine\Common\Collections\ArrayCollection();
    $this->affiliates = new \Doctrine\Common\Collections\ArrayCollection();
  }

  /**
   * Get id
   *
   * @return integer 
   */
  public function getId() {
    return $this->id;
  }

  /**
   * Set name
   *
   * @param string $name
   * @return Category
   */
  public function setName($name) {
    $this->name = $name;

    return $this;
  }

  /**
   * Get name
   *
   * @return string 
   */
  public function getName() {
    return $this->name;
  }

  /**
   * Add jobs
   *
   * @param \ebarbeito\JobeetBundle\Entity\Job $jobs
   * @return Category
   */
  public function addJob(\ebarbeito\JobeetBundle\Entity\Job $jobs) {
    $this->jobs[] = $jobs;

    return $this;
  }

  /**
   * Remove jobs
   *
   * @param \ebarbeito\JobeetBundle\Entity\Job $jobs
   */
  public function removeJob(\ebarbeito\JobeetBundle\Entity\Job $jobs) {
    $this->jobs->removeElement($jobs);
  }

  /**
   * Get jobs
   *
   * @return \Doctrine\Common\Collections\Collection 
   */
  public function getJobs() {
    return $this->jobs;
  }

  /**
   * Add affiliates
   *
   * @param \ebarbeito\JobeetBundle\Entity\Affiliate $affiliates
   * @return Category
   */
  public function addAffiliate(\ebarbeito\JobeetBundle\Entity\Affiliate $affiliates) {
    $this->affiliates[] = $affiliates;

    return $this;
  }

  /**
   * Remove affiliates
   *
   * @param \ebarbeito\JobeetBundle\Entity\Affiliate $affiliates
   */
  public function removeAffiliate(\ebarbeito\JobeetBundle\Entity\Affiliate $affiliates) {
    $this->affiliates->removeElement($affiliates);
  }

  /**
   * Get affiliates
   *
   * @return \Doctrine\Common\Collections\Collection 
   */
  public function getAffiliates() {
    return $this->affiliates;
  }

  public function __toString() {
    return $this->getName() ? $this->getName() : "";
  }

}
