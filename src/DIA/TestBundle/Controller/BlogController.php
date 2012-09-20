<?php

namespace DIA\TestBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use DIA\TestBundle\Entity\BlogPosts;
use Symfony\Component\HttpFoundation\Response;

class BlogController extends Controller {

	function __construct() {

	}

	public function testAction() {
		$response = array();

	  $request = $this->getRequest();
	  $session = $request->getSession();
	  $security = $this->get('security.context');

	  $em = $this->getDoctrine()->getEntityManager();
	  $post = $em->getRepository('DIATestBundle:BlogPosts')->findOneById(1);

	  $reply = $post->getReplies();

    $test = $reply->map(function( $obj ) { 
        return $obj->getId(); 
    })->toArray();

	  $response['test'] = $test;


		return new Response(json_encode($response));
	}

	public function rePostAction() {
		$response = Array();
		$response['status'] = false;

		$em = $this->getDoctrine()->getEntityManager();
	  $security = $this->get('security.context');
	  $token = $security->getToken();
	  $user = $token->getUser();

		$em = $this->getDoctrine()->getEntityManager();

	  $original_id = $this->get('request')->get('original_id');		

	  $original = $em->getRepository('DIATestBundle:BlogPosts')->findOneById($original_id);

		$repost = new BlogPosts();
		$repost->setBody("");

	  $user->addBlogPosts($repost);
	  $original->addRepost($repost);

	  $em->persist($user);
	  $em->flush();

	  $response['date'] = Date("d.m.Y H:i");
	  $response['status'] = true;

		return new Response(json_encode($response));
	}

	public function deletePostAction() {
		$response = array();

	  $request = $this->getRequest();
	  $session = $request->getSession();
	  $security = $this->get('security.context');
	  $token = $security->getToken();

	  $em = $this->getDoctrine()->getEntityManager();

		$postId = $this->get('request')->get('id');

	  $user = $token->getUser();
	  $post = $em->getRepository('DIATestBundle:BlogPosts')->findOneById($postId);

	  $response['test'] = $postId;
	  $response['test2'] = $post->getId();

	 	$post->deleteReposts();
	 	$post->deleteReplies();

	  $user->getPosts()->removeElement($post);

	  $em->persist($post);
	  $em->persist($user);
	  $em->flush();

		return new Response(json_encode($response));
	}

	public function mainAction() {
		$response = array();

	  $request = $this->getRequest();
	  $session = $request->getSession();
	  $security = $this->get('security.context');

	  $locale = $session->getLocale();

	  $token = $security->getToken();
	  $roles = $token->getRoles();
	  $role = $roles[0]->getRole();

	  $em = $this->getDoctrine()->getEntityManager();

  	$user = $token->getUser();

	  $response['role'] = print_r($role, true);
	  $response['locale'] = $locale;
	  $response['userId'] = $user->getId();
	  $response['blogOwnerId'] = $response['userId'];

	  //$posts = $em->getRepository('DIATestBundle:BlogPosts')->findByUsers();

		$qb = $em->getRepository('DIATestBundle:BlogPosts')->createQueryBuilder('b');
	  if($followedByMe = $user->getFollowedByMe(true)) {

		  $posts = $qb->where($qb->expr()->in('b.user', $followedByMe))
			  ->orWhere('b.user = :my_id')->setParameter('my_id', $user->getId())
			  ->orderBy('b.created_at', 'DESC')
		    ->getQuery()
		    ->getResult();
		  $count = count($posts);
	  } else {
		  $posts = $qb->where('b.user = :id')->setParameter('id', $user->getId())
			  ->orderBy('b.created_at', 'DESC')
		    ->getQuery()
		    ->getResult();
		  $count = count($posts);
	  }

    $response['blog'] = $posts;
    $response['blogCount'] = $count;
    $response['composeMessage'] = true;

    $response['blogHeader'] = $this->get('translator')->trans("My blog");

		return $this->render('DIATestBundle:Blog:blog.html.twig', $response);
	}

	public function getBlogAction($userId = 0) {
		$response = array();

	  $security = $this->get('security.context');
	  $token = $security->getToken();
	  $me = $token->getUser();

	  $response['userId'] = $me->getId();

	  $em = $this->getDoctrine()->getEntityManager();

  	$user = $em->getRepository('DIATestBundle:User')->findOneById($userId);

 		if(!$user) return $this->redirect($this->generateUrl('blog_main'));

 		$response['blogOwnerId'] = $userId;

	  $qb = $em->getRepository('DIATestBundle:BlogPosts')->createQueryBuilder('b');

	  $posts = $qb->where('b.user = :id')->setParameter('id', $user->getId())
		  ->orderBy('b.created_at', 'DESC')
	    ->getQuery()
	    ->getResult();

	  $count = count($posts);

    $response['blog'] = $posts;
    $response['blogCount'] = $count;

    $response['blogHeader'] = $this->get('translator')->trans("%username%'s blog", array('%username%' => $user->getUsername() ));

		return $this->render('DIATestBundle:Blog:blog.html.twig', $response);
	}

	public function getConvAction($id) {
		$response = array();

		$em = $this->getDoctrine()->getEntityManager();
		$post = $em->getRepository('DIATestBundle:BlogPosts')->findOneById($id);

		$replies = $post->getReplies();

    $replyArray = $replies->map(function( $obj ) { 
      return array(
      	'id'						=> $obj->getId(),
      	'body'					=> $obj->getBody(),
      	'userId'				=> $obj->getUser()->getId(),
      	'username'			=> $obj->getUser()->getUsername(),
      	'created'				=> $obj->getCreatedat()->format("d.m.Y H:i")
      );
    })->toArray();

    $replyArray[] = array(
    	'id'						=> $post->getId(),
    	'body'					=> $post->getBody(),
    	'userId'				=> $post->getUser()->getId(),
    	'username'			=> $post->getUser()->getUsername(),
    	'created'				=> $post->getCreatedat()->format("d.m.Y H:i")
    );

    $response['replies'] = $replyArray;

		return new Response(json_encode($response));
	}

	public function iFollowAction() {
		$response = array();

	  $security = $this->get('security.context');
	  $token = $security->getToken();
	  $user = $token->getUser();

	  $followers = $user->getMyFollowers(true);

	  $iFollow = $user->getFollowedByMe();

	  $response['users'] = $iFollow;
	  $response['myFollowers'] = $followers;

		return $this->render('DIATestBundle:Blog:iFollow.html.twig', $response);
	}

	public function makePostAction() {
		$response = Array();
		$response['status'] = false;

		$em = $this->getDoctrine()->getEntityManager();
	  $security = $this->get('security.context');
	  $token = $security->getToken();
	  $user = $token->getUser();

	  $body = trim($this->get('request')->get('body'));

	  if(!empty($body)) {
			$post = new BlogPosts();
			$post->setBody($body);
		  $user->addBlogPosts($post);

		  $em->persist($user);
		  $em->flush();

		  $response['date'] = Date("d.m.Y H:i");
		  $response['status'] = true;
		}

		return new Response(json_encode($response));	
	}

	public function replyToPostAction() {
		$response = Array();
		$response['status'] = false;

		$em = $this->getDoctrine()->getEntityManager();
	  $security = $this->get('security.context');
	  $token = $security->getToken();
	  $user = $token->getUser();

		$em = $this->getDoctrine()->getEntityManager();

	  $original_id = $this->get('request')->get('original_id');		

	  $original = $em->getRepository('DIATestBundle:BlogPosts')->findOneById($original_id);

	  $body = trim($this->get('request')->get('body'));

	  if(!empty($body)) {
			$reply = new BlogPosts();
			$reply->setBody($body);

		  $user->addBlogPosts($reply);
		  $original->addBlogPosts($reply);

		  $em->persist($user);
		  $em->flush();

		  $response['date'] = Date("d.m.Y H:i");
		  $response['status'] = true;
		}

		return new Response(json_encode($response));	
	}

	public function myFollowersAction() {
		$response = array();

	  $security = $this->get('security.context');
	  $token = $security->getToken();
	  $user = $token->getUser();

	  $followers = $user->getMyFollowers();

	  $iFollow = $user->getFollowedByMe(true);

	  $response['users'] = $followers;
	  $response['iFollow'] = $iFollow;

		return $this->render('DIATestBundle:Blog:myFollowers.html.twig', $response);
	}

	public function settingsAction() {
		return $this->render('DIATestBundle:Blog:settings.html.twig');
	}

	public function changeFollowStatusAction() {
		$response = Array();
		$response['status'] = false;

		$security = $this->get('security.context');
		$em = $this->getDoctrine()->getEntityManager();

	  $token = $security->getToken();
	  $me = $token->getUser();

	  $id = trim($this->get('request')->get('id'));
	  $status = trim($this->get('request')->get('status'));

	  if($status == 'no') {

	  	if($user = $this->getDoctrine()->getRepository('DIATestBundle:User')->findOneById($id)) {

			  $me->addFollowedByMe($user);

			  $em->persist($me);
			  $em->flush();

			  $response['following'] = true;
			  $response['status'] = true;
			}
	  } else {
	  	if($user = $this->getDoctrine()->getRepository('DIATestBundle:User')->findOneById($id)) {
			  $me->getFollowedByMe()->removeElement($user);

			  $em->persist($me);
			  $em->flush();

			  $response['following'] = false;
			  $response['status'] = true;
			}
	  }

	  return new Response(json_encode($response));

	}

	public function searchGetUsersAction() {
		$response = Array();

		$security = $this->get('security.context');
	  $token = $security->getToken();
	  $me = $token->getUser();

		$value = trim($this->get('request')->get('value'));

		if(!empty($value)) {
			$em = $this->getDoctrine()->getEntityManager();
			$query = $em->createQuery('SELECT u FROM DIATestBundle:User u WHERE (u.username LIKE :value OR u.first_name LIKE :value OR u.last_name LIKE :value) AND u.id <> :my_id')
				->setParameter('value', '%'.$this->get('request')->get('value').'%')
				->setParameter('my_id', $me->getId());
			$users = $query->getResult();
		}

		$response['users'] = array();
		if(isset($users)) {
			$followedByMe = $me->getFollowedByMe();
			$myFollowers = $me->getMyFollowers();
			foreach($users as $user) {
				$index = count($response['users']);
				$response['users'][$index]['id'] = $user->getId();
				$response['users'][$index]['username'] = $user->getUsername();
				$response['users'][$index]['first_name'] = $user->getFirstname();
				$response['users'][$index]['last_name'] = $user->getLastname();

				$response['users'][$index]['followed'] = false;
				$response['users'][$index]['follower'] = false;

				if($followedByMe->contains($user)) $response['users'][$index]['followed'] = true;
				if($myFollowers->contains($user)) $response['users'][$index]['follower'] = true;

				$response['users'][$index]['profileImage'] = '';
			}
		}

		return new Response(json_encode($response));
	}

	public function getMustacheTemplateAction($template) {
		$response = Array();

		$security = $this->get('security.context');
	  $token = $security->getToken();
	  $me = $token->getUser();

	  $response['userId'] = $me->getId();

		return $this->render('::mustacheTemplates/'.$template.'.html.twig');
	}

	public function searchUsersAction() {
		return $this->render('DIATestBundle:Blog:searchUsers.html.twig');
	}

}
