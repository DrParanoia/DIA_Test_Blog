<?php

namespace DIA\TestBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use DIA\TestBundle\Entity\BlogPosts;
use Symfony\Component\HttpFoundation\Response;

class BlogController extends Controller {

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

	 	$post->deleteReposts();
	 	$post->deleteReplies();

	  $user->getPosts()->removeElement($post);

	  $em->persist($post);
	  $em->persist($user);
	  $em->flush();

		return new Response(json_encode($response));
	}

	public function mainAction($userId = 0) {
		$response = array();

	  $request = $this->getRequest();
	  $session = $request->getSession();
	  $security = $this->get('security.context');
	  $token = $security->getToken();

	  $em = $this->getDoctrine()->getEntityManager();
  	$user = $token->getUser();

 	  $response['userId'] = $user->getId();

  	$blogUser = $em->getRepository('DIATestBundle:User')->findOneById($userId);
 		if($blogUser) {
 	  	$foundBlog = true;
 	  	$response['blogHeader'] = $this->get('translator')->trans("%username%'s blog", array('%username%' => $blogUser->getUsername() ));
 		} else {
 	 		$blogUser = $user;
 			$foundBlog = false;
 			$response['blogHeader'] = $this->get('translator')->trans("My blog");
    	$response['composeMessage'] = true;
 		}

	  $response['blogOwnerId'] = $blogUser->getId();

	  if(($followedByMe = $user->getFollowedByMe(true)) && !$foundBlog) {
			$query = $em->createQuery('
				SELECT p, rl, rl_to, rp, rp_or 
					FROM DIATestBundle:BlogPosts p 
					LEFT JOIN p.replies rl 
					LEFT JOIN p.replyTo rl_to 
					LEFT JOIN p.reposts rp 
					LEFT JOIN p.originalPost rp_or 
				WHERE p.user = :id 
				OR p.user IN (:followed)
				ORDER BY p.created_at DESC
			')->setParameter('followed', implode(",", $followedByMe));
	  } else {
			$query = $em->createQuery('
				SELECT p, rl, rl_to, rp, rp_or 
					FROM DIATestBundle:BlogPosts p 
					LEFT JOIN p.replies rl 
					LEFT JOIN p.replyTo rl_to 
					LEFT JOIN p.reposts rp 
					LEFT JOIN p.originalPost rp_or 
				WHERE p.user = :id 
				ORDER BY p.created_at DESC
			');
	  }
	  $query->setParameter('id', $blogUser->getId());
		$response['blog'] = $posts = $query->getResult();
	  $response['blogCount'] = $count = count($posts);

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
