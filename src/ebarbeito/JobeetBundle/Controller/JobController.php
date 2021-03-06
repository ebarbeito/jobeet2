<?php

namespace ebarbeito\JobeetBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use ebarbeito\JobeetBundle\Entity\Job;
use ebarbeito\JobeetBundle\Form\JobType;

/**
 * Job controller.
 */
class JobController extends Controller {

  /**
   * Lists all Job entities.
   *
   */
  public function indexAction() {
    $em = $this->getDoctrine()->getManager();
    $categories = $em->getRepository('ebarbeitoJobeetBundle:Category')->getWithJobs();

    foreach ($categories as $category) {
      $category->setActiveJobs($em->getRepository('ebarbeitoJobeetBundle:Job')->getActiveJobs($category->getId(), $this->container->getParameter('max_jobs_on_homepage')));
      $category->setMoreJobs($em->getRepository('ebarbeitoJobeetBundle:Job')->countActiveJobs($category->getId()) - $this->container->getParameter('max_jobs_on_homepage'));
    }

    return $this->render('ebarbeitoJobeetBundle:Job:index.html.twig', array(
      'categories' => $categories
    ));
  }
  
  /**
   * Creates a new Job entity.
   *
   */
  public function createAction(Request $request) {
    $entity = new Job();
    $form = $this->createForm(new JobType(), $entity);
    $form->bind($request);
    if ($form->isValid()) {
      $em = $this->getDoctrine()->getManager();
      $em->persist($entity);
      $em->flush();

      return $this->redirect($this->generateUrl('ebarbeito_job_preview', array(
        'company' => $entity->getCompanySlug(),
        'location' => $entity->getLocationSlug(),
        'token' => $entity->getToken(),
        'position' => $entity->getPositionSlug()
      )));
    }

    return $this->render('ebarbeitoJobeetBundle:Job:new.html.twig', array(
      'entity' => $entity,
      'form' => $form->createView(),
    ));
  }

  /**
   * Creates a form to create a Job entity.
   *
   * @param Job $entity The entity
   *
   * @return \Symfony\Component\Form\Form The form
   */
  private function createCreateForm(Job $entity) {
    $form = $this->createForm(new JobType(), $entity, array(
      'action' => $this->generateUrl('ebarbeito_job_create'),
      'method' => 'POST',
    ));

    $form->add('submit', 'submit', array('label' => 'Create'));

    return $form;
  }

  /**
   * Displays a form to create a new Job entity.
   *
   */
  public function newAction() {
    $entity = new Job();
    $entity->setType('full-time');
    $form = $this->createForm(new JobType(), $entity);

    return $this->render('ebarbeitoJobeetBundle:Job:new.html.twig', array(
      'entity' => $entity,
      'form' => $form->createView(),
    ));
  }

  /**
   * Finds and displays a Job entity.
   *
   */
  public function showAction($id) {
    $em = $this->getDoctrine()->getManager();
    $entity = $em->getRepository('ebarbeitoJobeetBundle:Job')->getActiveJob($id);

    if (!$entity) {
      // throwable url: /app_dev.php/job/sensio-labs/paris-france/ID/web-developer-expired
      throw $this->createNotFoundException('Unable to find Job entity.');
    }

    $deleteForm = $this->createDeleteForm($id);

    return $this->render('ebarbeitoJobeetBundle:Job:show.html.twig', array(
      'entity' => $entity,
      'delete_form' => $deleteForm->createView(),));
  }
  
  /**
   * Find and previews a Job entity
   * 
   */
  public function previewAction($token) {
    $em = $this->getDoctrine()->getManager();
    $entity = $em->getRepository('ebarbeitoJobeetBundle:Job')->findOneByToken($token);

    if (!$entity) {
      throw $this->createNotFoundException('Unable to find Job entity.');
    }

    $deleteForm = $this->createDeleteForm($entity->getToken());
    $publishForm = $this->createPublishForm($entity->getToken());

    return $this->render('ebarbeitoJobeetBundle:Job:show.html.twig', array(
      'entity' => $entity,
      'delete_form' => $deleteForm->createView(),
      'publish_form' => $publishForm->createView(),
    ));
  }
  
  /**
   * 
   * 
   */
  public function publishAction(Request $request, $token) {
    $form = $this->createPublishForm($token);
    $form->bind($request);

    if ($form->isValid()) {
      $em = $this->getDoctrine()->getManager();
      $entity = $em->getRepository('ebarbeitoJobeetBundle:Job')->findOneByToken($token);

      if (!$entity) {
        throw $this->createNotFoundException('Unable to find Job entity.');
      }

      $entity->publish();
      $em->persist($entity);
      $em->flush();

      $this->get('session')->getFlashBag()->add('notice', 'Your job is now online for 30 days.');
    }

    return $this->redirect($this->generateUrl('ebarbeito_job_preview', array(
      'company' => $entity->getCompanySlug(),
      'location' => $entity->getLocationSlug(),
      'token' => $entity->getToken(),
      'position' => $entity->getPositionSlug()
    )));
  }

  /**
   * Displays a form to edit an existing Job entity.
   *
   */
  public function editAction($token) {
    $em = $this->getDoctrine()->getManager();
    $entity = $em->getRepository('ebarbeitoJobeetBundle:Job')->findOneByToken($token);

    if (!$entity) {
      throw $this->createNotFoundException('Unable to find Job entity.');
    }

    $editForm = $this->createForm(new JobType(), $entity);
    $deleteForm = $this->createDeleteForm($token);

    return $this->render('ebarbeitoJobeetBundle:Job:edit.html.twig', array(
      'entity' => $entity,
      'edit_form' => $editForm->createView(),
      'delete_form' => $deleteForm->createView(),
    ));
  }
  
  /**
   * Creates a form to publish a Job entity
   * @param type $token
   * @return \Symfony\Component\Form\Form The form
   */
  private function createPublishForm($token) {
    return $this->createFormBuilder(array('token' => $token))
      ->add('token', 'hidden')
      ->getForm();
  }

  /**
   * Creates a form to edit a Job entity.
   *
   * @param Job $entity The entity
   *
   * @return \Symfony\Component\Form\Form The form
   */
  private function createEditForm(Job $entity) {
    $form = $this->createForm(new JobType(), $entity, array(
      'action' => $this->generateUrl('ebarbeito_job_update', array('id' => $entity->getId())),
      'method' => 'PUT',
    ));

    $form->add('submit', 'submit', array('label' => 'Update'));

    return $form;
  }

  /**
   * Edits an existing Job entity.
   *
   */
  public function updateAction(Request $request, $token) {
    $em = $this->getDoctrine()->getManager();
    $entity = $em->getRepository('ebarbeitoJobeetBundle:Job')->findOneByToken($token);

    if (!$entity) {
      throw $this->createNotFoundException('Unable to find Job entity.');
    }

    $editForm = $this->createForm(new JobType(), $entity);
    $deleteForm = $this->createDeleteForm($token);

    $editForm->bind($request);
    if ($editForm->isValid()) {
      $em->persist($entity);
      $em->flush();

      return $this->redirect($this->generateUrl('ebarbeito_job_preview', array(
        'company' => $entity->getCompanySlug(),
        'location' => $entity->getLocationSlug(),
        'token' => $entity->getToken(), 
        'position' => $entity->getPositionSlug()
      )));
    }

    return $this->render('ebarbeitoJobeetBundle:Job:edit.html.twig', array(
      'entity' => $entity,
      'edit_form' => $editForm->createView(),
      'delete_form' => $deleteForm->createView(),
    ));
  }

  /**
   * Deletes a Job entity.
   *
   */
  public function deleteAction(Request $request, $token) {
    $form = $this->createDeleteForm($token);
    $form->bind($request);

    if ($form->isValid()) {
      $em = $this->getDoctrine()->getManager();
      $entity = $em->getRepository('ebarbeitoJobeetBundle:Job')->findOneByToken($token);

      if (!$entity) {
        throw $this->createNotFoundException('Unable to find Job entity.');
      }

      $em->remove($entity);
      $em->flush();
    }

    return $this->redirect($this->generateUrl('ebarbeito_job'));
  }

  /**
   * Creates a form to delete a Job entity by token.
   *
   * @param mixed $token The entity token
   *
   * @return \Symfony\Component\Form\Form The form
   */
  private function createDeleteForm($token) {
    return $this->createFormBuilder(array('token' => $token))
      ->add('token', 'hidden')
      ->getForm();
  }

}
