<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Workshop;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Workshop controller.
 *
 */
class WorkshopController extends Controller
{
    public function unregisterAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $workshop = $em->getRepository('AppBundle:Workshop')->find($id);
        $user = $this->get('security.token_storage')->getToken()->getUser();
        if ($user->getWorkshops()->contains($workshop)) {
            $user->removeWorkshop($workshop);
            $em->persist($user);
            $em->flush();

            $this->redirect($this->generateUrl('app_workshop_register', ['id' => $workshop->getId()]));
        }

        return $this->render('workshop/register.html.twig', [
            'workshop' => $workshop
        ]);
    }
    public function registerAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $workshop = $em->getRepository('AppBundle:Workshop')->find($id);

        if ($request->query->has('register')) {
            $user = $this->get('security.token_storage')->getToken()->getUser();
            if (!$user->getWorkshops()->contains($workshop)) {
                $user->addWorkshop($workshop);
                $em->persist($workshop);
                $em->flush();
            }

            $this->redirect($this->generateUrl('app_workshop_register', ['id' => $workshop->getId()]));
        }

        return $this->render('workshop/register.html.twig', [
            'workshop' => $workshop
        ]);
    }

    /**
     * Lists all workshop entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $workshops = $em->getRepository('AppBundle:Workshop')->findAll();

        return $this->render('workshop/index.html.twig', array(
            'workshops' => $workshops,
        ));
    }

    /**
     * Creates a new workshop entity.
     *
     */
    public function newAction(Request $request)
    {
        $workshop = new Workshop();
        $form = $this->createForm('AppBundle\Form\WorkshopType', $workshop);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($workshop);
            $em->flush();

            return $this->redirectToRoute('workshop_show', array('id' => $workshop->getId()));
        }

        return $this->render('workshop/new.html.twig', array(
            'workshop' => $workshop,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a workshop entity.
     *
     */
    public function showAction(Workshop $workshop)
    {
        $deleteForm = $this->createDeleteForm($workshop);

        return $this->render('workshop/show.html.twig', array(
            'workshop' => $workshop,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing workshop entity.
     *
     */
    public function editAction(Request $request, Workshop $workshop)
    {
        $deleteForm = $this->createDeleteForm($workshop);
        $editForm = $this->createForm('AppBundle\Form\WorkshopType', $workshop);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('workshop_edit', array('id' => $workshop->getId()));
        }

        return $this->render('workshop/edit.html.twig', array(
            'workshop' => $workshop,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a workshop entity.
     *
     */
    public function deleteAction(Request $request, Workshop $workshop)
    {
        $form = $this->createDeleteForm($workshop);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($workshop);
            $em->flush();
        }

        return $this->redirectToRoute('workshop_index');
    }

    /**
     * Creates a form to delete a workshop entity.
     *
     * @param Workshop $workshop The workshop entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Workshop $workshop)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('workshop_delete', array('id' => $workshop->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
