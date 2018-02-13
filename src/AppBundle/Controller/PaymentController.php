<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Order;
use AppBundle\Entity\Workshop;
use JMS\Payment\CoreBundle\Form\ChoosePaymentMethodType;
use JMS\Payment\CoreBundle\PluginController\Result;
use JMS\Payment\CoreBundle\Plugin\Exception\ActionRequiredException;
use JMS\Payment\CoreBundle\Plugin\Exception\Action\VisitUrl;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class PaymentController extends Controller
{
    public function detailsAction(Request $request, $id)
    {
        $em       = $this->getDoctrine()->getManager();
        $user     = $this->get('security.token_storage')->getToken()->getUser();
        $workshop = $em->getRepository('AppBundle:Workshop')->find($id);
        $amount   = 6.00;
        $order    = new Order($amount, $user, $workshop);

        $config = [
            'paypal_express_checkout' => [
                'return_url' => $this->generateUrl('app_payment_success', [
                    'id' => $workshop->getId(),
                    'orderNumber' => $order->getOrderNumber()
                ]),
                'cancel_url' => $this->generateUrl('app_payment_cancel', [
                    'id' => $workshop->getId(),
                    'orderNumber' => $order->getOrderNumber()
                ]),
                'notify_url' => $this->generateUrl('app_payment_notify', [
                    'id' => $workshop->getId(),
                    'orderNumber' => $order->getOrderNumber()
                ]),
                'useraction' => 'commit',
            ],
        ];

        $form = $this->createForm(ChoosePaymentMethodType::class, null, [
            'amount'          => $amount,
            'currency'        => 'EUR',
            'predefined_data' => $config,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $ppc = $this->get('payment.plugin_controller');
            $ppc->createPaymentInstruction($instruction = $form->getData());
            $order->setPaymentInstruction($instruction);

            $em->persist($order);
            $em->flush($order);

            return new RedirectResponse($this->generateUrl('app_payment_complete', ['orderId' => $order->getId()]), 302);
        }

        return $this->render('payment/details.html.twig', [
            'form' => $form->createView(),
            'workshop' => $workshop
        ]);
    }


    public function completeAction(Request $request, $orderId)
    {
        $em = $this->getDoctrine()->getManager();
        $order = $em->getRepository('AppBundle:Order')->find($orderId);
        $ppc = $this->get('payment.plugin_controller');

        $instruction = $order->getPaymentInstruction();
        if (null === $pendingTransaction = $instruction->getPendingTransaction()) {
            $payment = $ppc->createPayment($instruction->getId(), $instruction->getAmount() - $instruction->getDepositedAmount());
        } else {
            $payment = $pendingTransaction->getPayment();
        }

        $result = $ppc->approveAndDeposit($payment->getId(), $payment->getTargetAmount());
        if (Result::STATUS_PENDING === $result->getStatus()) {
            $ex = $result->getPluginException();

            if ($ex instanceof ActionRequiredException) {
                $action = $ex->getAction();

                if ($action instanceof VisitUrl) {
                    return new RedirectResponse($action->getUrl());
                }

                throw $ex;
            }
        } else if (Result::STATUS_SUCCESS !== $result->getStatus()) {
            throw new \RuntimeException('Transaction was not successful: '.$result->getReasonCode());
        }

        // payment was successful, do something interesting with the order
    }
}
