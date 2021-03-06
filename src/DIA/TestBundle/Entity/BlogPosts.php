<?php

namespace DIA\TestBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * DIA\TestBundle\Entity\BlogPosts
 */
class BlogPosts
{
    /**
     * @var integer $id
     */
    private $id;

    /**
     * @var string $body
     */
    private $body;

    /**
     * @var string $title
     */
    private $title;

    /**
     * @var timestamp $created_at
     */
    private $created_at;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set body
     *
     * @param string $body
     */
    public function setBody($body)
    {
        $this->body = $body;
    }

    /**
     * Get body
     *
     * @return string 
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Set title
     *
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set created_at
     *
     * @param timestamp $createdAt
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->created_at = $createdAt;
    }

    /**
     * Get created_at
     *
     * @return timestamp 
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }
    public function __construct()
    {
        $this->user_id = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * @ORM\PrePersist
     */
    public function setCreatedAtValue()
    {
        $this->created_at = new \DateTime();
    }
    /**
     * @var DIA\TestBundle\Entity\User
     */
    private $user;


    /**
     * Set user
     *
     * @param DIA\TestBundle\Entity\User $user
     */
    public function setUser(\DIA\TestBundle\Entity\User $user)
    {
        $this->user = $user;
    }

    /**
     * Get user
     *
     * @return DIA\TestBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }


    /**
     * @var DIA\TestBundle\Entity\BlogPosts
     */
    private $replies;

    /**
     * @var DIA\TestBundle\Entity\BlogPosts
     */
    private $replyTo;


    /**
     * Add replies
     *
     * @param DIA\TestBundle\Entity\BlogPosts $replies
     */
    public function addBlogPosts(\DIA\TestBundle\Entity\BlogPosts $replies)
    {
        $this->replies[] = $replies;
    }

    /**
     * Get replies
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getReplies()
    {
        return $this->replies;
    }

    /**
     * Get replyTo
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getReplyTo()
    {
        return $this->replyTo;
    }
    /**
     * @var DIA\TestBundle\Entity\BlogPosts
     */
    private $reposts;

    /**
     * @var DIA\TestBundle\Entity\BlogPosts
     */
    private $originalPost;


    /**
     * Add reposts
     *
     * @param DIA\TestBundle\Entity\BlogPosts $replies
     */
    public function addRepost(\DIA\TestBundle\Entity\BlogPosts $reposts)
    {
        $this->reposts[] = $reposts;
    }


    /**
     * Get reposts
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getReposts()
    {
        return $this->reposts;
    }

    public function isRepostedBy($userId) {
        $reposts = $this->reposts;
        foreach($reposts as $repost) {
            if($repost->getUser()->getId() == $userId) return true;
        }
        return false;
    }

    public function deleteReposts() {
        $reposts = $this->getReposts();
        foreach($reposts as $repost) {
            $repost->getUser()->getPosts()->removeElement($repost);
        }
    }

    public function deleteReplies() {
        $replies = $this->getReplies();
        foreach($replies as $reply) {
            $reply->getUser()->getPosts()->removeElement($reply);
        }
    }

    /**
     * Get originalPost
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getOriginalPost()
    {
        return $this->originalPost;
    }

    /**
     * Set replies
     *
     * @param DIA\TestBundle\Entity\BlogPosts $replies
     */
    public function setReplies(\DIA\TestBundle\Entity\BlogPosts $replies)
    {
        $this->replies = $replies;
    }

    /**
     * Set reposts
     *
     * @param DIA\TestBundle\Entity\BlogPosts $reposts
     */
    public function setReposts(\DIA\TestBundle\Entity\BlogPosts $reposts)
    {
        $this->reposts = $reposts;
    }
}