<?php

namespace App\Controller;

use App\Entity\Movie;
use App\Entity\Vote;
use Carbon\Carbon;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(ManagerRegistry $doctrine): Response
    {

        $movie_list = $doctrine->getRepository(Movie::class)->findAll();

        $votes = $doctrine->getRepository(Vote::class)->findAll();

        $i = 0;
        $j = 0;
            foreach ($movie_list as $k => $movie) {
                foreach ($votes as $key => $vote) {

                if ($movie->getID() == $vote->getMovie()->getID())
                {

                    if ($vote->getName() == 'Like') {
                        $i = $i + 1;
                    }

                    if ($vote->getName() == 'Dislike') {
                        $j = $j + 1;
                    }

                }
                    $list[$k] = [
                        'id' => $movie->getID(),
                        'title' => $movie->getTitle(),
                        'description' => $movie->getDescription(),
                        'likes' => $i,
                        'dislikes' => $j,
                        'user' => $movie->getUser(),
                        'created_at' => $movie->getCreatedAt(),
                    ];


            }
                $i = 0;
                $j = 0;
        }

        return $this->render('home/index.html.twig', [
            'movie_list' => $list,
        ]);
    }

    #[Route('/filters', name: 'movie_filters')]
    public function filters(ManagerRegistry $doctrine, Request $request): JsonResponse
    {

        $filter = ($request->query->has('filter')) ? $request->query->get('filter') : 'created_at';
        $sort_by = ($request->query->has('sort_by')) ? $request->query->get('sort_by') : 'ASC';

        $movie_list = $doctrine->getRepository(Movie::class)
            ->findBy(array(), [$filter => $sort_by]);

        $data = [];
        foreach ($movie_list as $movie) {
            $data[] = $movie->toArray();
        }

        return new JsonResponse($data);
    }

    #[Route('/positive_vote', name: 'positive_vote')]
    public function positiveVote(ManagerRegistry $doctrine, UserInterface $user): Response
    {
        $entityManager = $doctrine->getManager();
        $movie = $doctrine->getRepository(Movie::class)->find(array('id' => 1));

        $vote = new Vote();
        $vote->setMovie($movie);
        $vote->setName('Like');

        $entityManager->persist($vote);
        $entityManager->flush();

        return new Response('Saved new Vote with id ' . $vote->getId());
    }

    #[Route('/movie', name: 'create_movie')]
    public function createMovie(ManagerRegistry $doctrine, UserInterface $user): Response
    {
        $entityManager = $doctrine->getManager();

        $movie = new Movie();
        $movie->setTitle('Keyboard');
        $movie->setDescription('Ergonomic and stylish!');
        $movie->setCreatedAt(Carbon::now());
        $movie->setUser($user);

        // tell Doctrine you want to (eventually) save the Product (no queries yet)
        $entityManager->persist($movie);
        $entityManager->flush();

        return new Response('Saved new product with id ' . $movie->getId());
    }


}
