<?php

namespace DIA\TestBundle\Entity;

use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * DIA\TestBundle\Entity\User
 * @UniqueEntity(fields="username", message="Username taken")
 */
class User implements UserInterface {
    /**
     *
     *
     * @var integer $id
     */
    private $id;

    /**
     *
     *
     * @var string $username
     * @Assert\NotBlank(message="Please enter username")
     */
    private $username;

    /**
     *
     *
     * @var string $password
     * @Assert\NotBlank(message="Please enter password")
     */
    private $password;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set username
     *
     * @param string  $username
     */
    public function setUsername( $username ) {
        $this->username = $username;
    }

    /**
     * Get username
     *
     * @return string
     */
    public function getUsername() {
        return $this->username;
    }

    /**
     * Set password
     *
     * @param string  $password
     */
    public function setPassword( $password ) {
        $this->password = $password;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword() {
        return $this->password;
    }

    public function getRoles() {
        return array( $this->roles );
    }

    public function getSalt() {
        return null;
    }

    public function eraseCredentials() {

    }

    public function equals( UserInterface $user ) {
        return $user->getUsername() == $this->getUsername();
    }
    /**
     *
     *
     * @var string $roles
     */
    private $roles = 'ROLE_USER';


    /**
     * Set roles
     *
     * @param string  $roles
     */
    public function setRoles( $roles ) {
        $this->roles = $roles;
    }
    /**
     *
     *
     * @var string $first_name
     */
    private $first_name;

    /**
     *
     *
     * @var string $last_name
     */
    private $last_name;


    /**
     * Set first_name
     *
     * @param string  $firstName
     */
    public function setFirstName( $firstName ) {
        $this->first_name = $firstName;
    }

    /**
     * Get first_name
     *
     * @return string
     */
    public function getFirstName() {
        return $this->first_name;
    }

    /**
     * Set last_name
     *
     * @param string  $lastName
     */
    public function setLastName( $lastName ) {
        $this->last_name = $lastName;
    }

    /**
     * Get last_name
     *
     * @return string
     */
    public function getLastName() {
        return $this->last_name;
    }

    public function __construct() {
        $this->user_id = new \Doctrine\Common\Collections\ArrayCollection();
    }


    /**
     * Get user_id
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getUserId() {
        return $this->user_id;
    }

    /**
     *
     *
     * @var DIA\TestBundle\Entity\User
     */
    private $followedByMe;

    /**
     *
     *
     * @var DIA\TestBundle\Entity\User
     */
    private $myFollowers;


    /**
     * Add followedByMe
     *
     * @param DIA\TestBundle\Entity\User $followedByMe
     */
    public function addFollowedByMe( \DIA\TestBundle\Entity\User $followedByMe ) {
        $this->followedByMe[] = $followedByMe;
    }

    /**
     * Add myFollowers
     *
     * @param DIA\TestBundle\Entity\User $myFollowers
     */
    public function addMyFollower( \DIA\TestBundle\Entity\User $myFollowers ) {
        $myFollowers->addFollowedByMe($this);
        $this->myFollowers[] = $myFollowers;
    }

    /**
     * Get followedByMe
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getFollowedByMe($arrayOfIds = false) {
        if($arrayOfIds) {
            return $this->followedByMe->map(function( $obj ) { 
                return $obj->getId(); 
            })->toArray();
        } else return $this->followedByMe;
    }

    /**
     * Get myFollowers
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getMyFollowers($arrayOfIds = false) {
        if($arrayOfIds) {
            return $this->myFollowers->map(function( $obj ) { 
                return $obj->getId(); 
            })->toArray();
        } else return $this->myFollowers;
    }

    /**
     * Add followedByMe
     *
     * @param DIA\TestBundle\Entity\User $followedByMe
     */
    public function addUser(\DIA\TestBundle\Entity\User $followedByMe)
    {
        $this->followedByMe[] = $followedByMe;
    }
    /**
     * @var DIA\TestBundle\Entity\BlogPosts
     */
    private $posts;


    /**
     * Add posts
     *
     * @param DIA\TestBundle\Entity\BlogPosts $posts
     */
    public function addBlogPosts(\DIA\TestBundle\Entity\BlogPosts $posts)
    {
        $posts->setUser($this);
        $this->posts[] = $posts;
    }

    /**
     * Get posts
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getPosts()
    {
        return $this->posts;
    }

}