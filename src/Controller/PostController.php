<?php

namespace App\Controller;
session_start();

use App\Entity\Post;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PostController extends AbstractController
{
    /**
     * @Route("/post", name="post")
     */
    public function index()
    {
        $rep = $this->getDoctrine()->getRepository(Post::class);
        $posts = $rep->findAll();

        return $this->render('post/index.html.twig', [
            'posts' => $posts,
        ]);
    }

    /**
     * @Route("/post/{id}", name="post_info", requirements={"id"="\d+"})
     * @param $id
     * @return Response
     */
    public function get($id)
    {
        $rep = $this->getDoctrine()->getRepository(Post::class);
        $post = $rep->find($id);

        return $this->render('post/post.html.twig', [
            'id' => $post->getId(),
            'photo' => $post->getPhoto(),
            'text' => $post->getText(),
            'rating' => $post->getRating(),
            'canVote' => !isset($_SESSION["voted"]) || !in_array($id, $_SESSION["voted"]),
        ]);
    }

    /**
     * @Route("/post/new", name="post_new")
     */
    public function add()
    {
        return $this->render('post/new.html.twig', [
            'controller_name' => 'PostController',
        ]);
    }

    /**
     * @Route("/post/create", name="post_create")
     */
    public function create()
    {
        $entityManager = $this->getDoctrine()->getManager();

        $post = new Post();
        $post->setPhoto($_POST["photo"]);
        $post->setText($_POST["text"]);
        $post->setRating(500);

        $entityManager->persist($post);
        $entityManager->flush();

        return new Response('Saved new post with id '.$post->getId());

    }

    /**
     * @Route("/post/vote", name="post_vote")
     */
    public function vote()
    {
        $post_id = $_POST["id"];
        if(isset($_SESSION["voted"]) && in_array($post_id, $_SESSION["voted"])) {
            return new Response('Голосовать можно только один раз!');
        }
        $isLike = $_POST["type"] == "1";
        $diff = $isLike ? 1 : -1;

        $rep = $this->getDoctrine()->getRepository(Post::class);
        $entityManager = $this->getDoctrine()->getManager();
        $post = $rep->find($post_id);
        $post->setRating($post->getRating() + $diff);
        $entityManager->persist($post);
        $entityManager->flush();

        if(!isset($_SESSION["voted"])) {
            $_SESSION["voted"] = array();
        }
        $_SESSION["voted"][] = $post_id;

        return new Response('Спасибо за голос!');
    }
}
