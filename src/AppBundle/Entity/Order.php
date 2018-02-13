<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Payment\CoreBundle\Entity\PaymentInstruction;

/**
 * @ORM\Entity
 * @ORM\Table(name="model_order")
 */
class Order
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\OneToOne(targetEntity="JMS\Payment\CoreBundle\Entity\PaymentInstruction")
     */
    private $paymentInstruction;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="orders")
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="Workshop", inversedBy="orders")
     */
    private $workshop;

    /**
     * @ORM\Column(type="string", unique = true)
     */
    private $orderNumber;

    /**
     * @ORM\Column(type="decimal", precision = 2)
     */
    private $amount;

    public function __construct($amount, User $user, Workshop $workshop)
    {
        $this->amount = $amount;
        $this->user = $user;
        $this->workshop = $workshop;

        $this->orderNumber = mt_rand();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getOrderNumber()
    {
        return $this->orderNumber;
    }

    public function getAmount()
    {
        return $this->amount;
    }

    public function getPaymentInstruction()
    {
        return $this->paymentInstruction;
    }

    public function setPaymentInstruction(PaymentInstruction $instruction)
    {
        $this->paymentInstruction = $instruction;
    }
}
