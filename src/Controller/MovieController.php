<?php

namespace App\Controller;

use App\Entity\Movie;
use App\Entity\Review;
use App\Omdb\OmdbClient;
use App\Repository\MovieRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MovieController extends AbstractController
{
    /**
     * @Route("/movie/latest", name="movie_latest")
     */
    public function latest(MovieRepository $movieRepository): Response
    {
        $movies = $movieRepository->findAll();
        //dump($movies);

        return $this->render('movie/latest.html.twig', [
            'movies' => $movies,
        ]);
    }

    /**
     * @Route("/movie/search", name="movie_search")
     */
    public function search(OmdbClient $omdbClient, Request $request): Response
    {
        $keyword = $request->query->get('keyword', 'Sky');
        $movies = $omdbClient->requestAllBySearch($keyword);

        return $this->render('movie/search.html.twig', [
            'keyword' => $keyword,
            'movies' => $movies['Search'],
        ]);
    }

    /**
     * @Route("/movie", name="movie")
     */
    public function index(): Response
    {
        return $this->render('movie/index.html.twig', [
            'controller_name' => 'MovieController',
        ]);
    }

    #[Route("/movie/{id}", name: "movie_show", requirements: ['id' => '\d+'])]
    /**
     * @Route("/movie/{id}", name="movie_show", requirements={"id": "\d+"})
     */
    public function show(int $id): Response
    {
        // Fiche film $id
        //return new Response('Fiche film ' . $id);

        // {% for movie in movies %}
        return $this->render('movie/show.html.twig', []);
    }


    /**
     * @Route("/movie/{imdbId}/import", name="movie_import")
     */
    public function import(string $imdbId, OmdbClient $omdbClient, EntityManagerInterface $entityManager): Response
    {
        $movieInfo = $omdbClient->requestOneById($imdbId);
        $movie = Movie::fromApi($movieInfo);

        $entityManager->persist($movie);
        $entityManager->flush();

        // Quand l'import sera fait
        return $this->redirectToRoute('movie_latest');
    }

    /**
     * @Route("/movie/add_review/{userId}/{movieId}/{rating}", name="add_review")
     */
    public function addReview($userId, $movieId, $rating,
                              UserRepository $userRepository,
                              MovieRepository $movieRepository,
                                EntityManagerInterface $entityManager
    ): Response
    {
        $user = $userRepository->findOneBy(['id' => $userId]);
        $movie = $movieRepository->findOneBy(['id' => $movieId]);

        $review = new Review();
        $review
            ->setMovie($movie)
            ->setUser($user)
            ->setRating($rating)
        ;

        $entityManager->persist($review);
        $entityManager->flush();

        return $this->redirectToRoute('movie_show', ['id' => $movieId]);
    }
}
