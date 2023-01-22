<?php

namespace App\Controller;

use App\Entity\Movie;
use App\Entity\User;
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

        $list = $this->GenerateList($movie_list, $votes);

        return $this->render('home/index.html.twig', [
            'movie_list' => $list,
        ]);
    }

    private function GenerateList($movie_list, $votes)
    {
        $i = 0; //for Likes
        $j = 0; //for Dislikes

        $list = [];
        foreach ($movie_list as $k => $movie) {
            foreach ($votes as $key => $vote) {

                if ($movie->getID() == $vote->getMovie()->getID()) {

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

        return $list;
    }

    #[Route('/filters', name: 'movie_filters')]
    public function filters(ManagerRegistry $doctrine, Request $request): JsonResponse
    {

        $filter = ($request->query->has('filter')) ? $request->query->get('filter') : 'created_at';

        $movie_list = $doctrine->getRepository(Movie::class)->findAll();
        $votes = $doctrine->getRepository(Vote::class)->findAll();

        $list = $this->GenerateList($movie_list, $votes);
        $data = [];
        foreach ($list as $key => $item) {
            $user = $doctrine->getRepository(User::class)->find($item['user']);

            $data[$key] = [
                'id' => $item['id'],
                'title' => $item['title'],
                'description' => $item['description'],
                'user_first_name' => $user->getFirstName(),
                'user_last_name' =>$user->getLastName(),
                'user_id' => $user->getId(),
                'created_at' => Carbon::parse($item['created_at'])->format('d/m/Y'),
                'likes' => $item['likes'],
                'dislikes' => $item['dislikes'],
                'same_user' => ($user->getId() == $this->getUser()->getID())? true : false
            ];
        }

        $key_array = array_column($data, $filter);
        array_multisort($key_array, SORT_DESC, $data);

        return new JsonResponse($data);
    }

    #[Route('/author/{user_id}', name: 'movies_by_user')]
    public function filterByUser($user_id,ManagerRegistry $doctrine): Response
    {

        $movieRepository = $doctrine->getRepository(Movie::class);
        $movies = $movieRepository->findBy(['user' => $user_id]);
        $votes = $doctrine->getRepository(Vote::class)->findAll();

        $list = $this->GenerateList($movies, $votes);
        return $this->render('home/index.html.twig', [
            'movie_list' => $list,
        ]);
    }

    #[Route('/positive_vote', name: 'positive_vote')]
    public function positiveVote(ManagerRegistry $doctrine, UserInterface $user): Response
    {
        $entityManager = $doctrine->getManager();

        $movie = $doctrine->getRepository(Movie::class)->find(array('id' => 1));

        $vote = new Vote();
        $vote->setMovie($movie);
        $vote->setName('Like');
        $vote->setUserId($user);

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
