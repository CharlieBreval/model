<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Workshop;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;

/**
 * Workshop controller.
 *
 */
class WorkshopController extends Controller
{
    /**
     * @return Response
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
     * @param  Request $request
     * @return Response
     */
    public function newAction(Request $request)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $workshop = new Workshop();
        $workshop->setStart(new \DateTime());
        $workshop->setEnd(new \DateTime());
        $workshop->setDescription('Séance de modèle vivant');
        $workshop->setCreatedBy($user);

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
     * @param  Request  $request
     * @param  Workshop $workshop
     * @return Response
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
     * Suppression d'un seance de modele
     *
     * @param  Request  $request
     * @param  Workshop $workshop
     * @return RedirectResponse
     */
    public function deleteAction(Request $request, Workshop $workshop)
    {
        $form = $this->createDeleteForm($workshop);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($workshop->getUsers() as $user) {
                $body = "<html><body><br>La séance vient d'être annulée pour cause de manque de personne ou modèle.<br> Veuillez nous excuser pour la gêne occasionée !</body></html>";
                $message = \Swift_Message::newInstance()
                    ->setSubject('Annulation séance modèle vivant du '.$workshop->getStart()->format('d-m-Y'))
                    ->setFrom('bonjour@charliebreval.com')
                    ->setTo($user->getEmail())
                    ->setBody($body,'text/html')
                ;

                $this->get('mailer')->send($message);
            }
            $em = $this->getDoctrine()->getManager();
            $em->remove($workshop);
            $em->flush();
        }

        return $this->redirectToRoute('workshop_index');
    }

    /**
     * @param  Request $request
     * @param  integer  $id
     * @return Response
     */
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

    /**
     * @param  Request $request
     * @param  integer  $id
     * @return Response
     */
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
     * Creates a form to delete a workshop entity.
     *
     * @param Workshop $workshop The workshop entity
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Workshop $workshop)
    {
        return $this->createFormBuilder($workshop)
            ->setAction($this->generateUrl('workshop_delete', ['id' => $workshop->getId()]))
            ->setMethod('DELETE')
            ->add('delete', SubmitType::class, [
                'attr' => [
                    'class' => 'btn btn-danger'
                ]
            ])
            ->getForm();
    }
}
