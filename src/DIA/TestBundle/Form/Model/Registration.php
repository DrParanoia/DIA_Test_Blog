<?php

namespace DIA\TestBundle\Form\Model;

use Symfony\Component\Validator\Constraints as Assert;

use DIA\TestBundle\Entity\User;

class Registration {
    /**
     *
     *
     * @Assert\Type(type="DIA\TestBundle\Entity\User")
     */
    protected $user;


    public function setUser( User $user ) {
        $this->user = $user;
    }

    public function getUser() {
        return $this->user;
    }

}

?>
