<?php

namespace AppBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="model_fos_user")
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToMany(targetEntity="Workshop", inversedBy="users")
     * @ORM\JoinTable(name="model_users_workshops")
     */
    private $workshops;

    /**
     * @ORM\OneToMany(targetEntity="Order", mappedBy="user")
     */
    private $orders;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $image;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $phone;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $isModel;

    public function __construct()
    {
        parent::__construct();
        $this->workshops = new \Doctrine\Common\Collections\ArrayCollection();
        $this->isModel = false;
    }

    public function isModel()
    {
        return $this->isModel;
    }

    public function setIsModel($isModel)
    {
        $this->isModel = $isModel;
    }


    public function getId()
    {
        return $this->id;
    }

    /**
     * Add workshop
     *
     * @param \AppBundle\Entity\Workshop $workshop
     *
     * @return User
     */
    public function addWorkshop(\AppBundle\Entity\Workshop $workshop)
    {
        $this->workshops[] = $workshop;

        return $this;
    }

    /**
     * Remove workshop
     *
     * @param \AppBundle\Entity\Workshop $workshop
     */
    public function removeWorkshop(\AppBundle\Entity\Workshop $workshop)
    {
        $this->workshops->removeElement($workshop);
    }

    /**
     * Get workshops
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getWorkshops()
    {
        return $this->workshops;
    }

    /**
     * Set image
     *
     * @param string $image
     *
     * @return User
     */
    public function setImage($image)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * Get image
     *
     * @return string
     */
    public function getImage()
    {
        return $this->image;
    }

    public function setPhone($phone)
    {
        $this->phone = $phone;
    }

    public function getPhone()
    {
        return $this->phone;
    }
}
