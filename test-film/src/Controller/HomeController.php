<?php

namespace App\Controller;

use App\Service\MovieHtmlGenerator;
use App\Service\TmdbApiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/home', name: 'home')]
    public function index(TmdbApiService $tmdbApiService): Response
    {
        try {
            $bestMovie = $tmdbApiService->getTopRatedMovie();
            $allGenres = $tmdbApiService->getGenres();
            $allMovies = $tmdbApiService->getPopularMovies();

            $bestMovieVideoKey = $tmdbApiService->getFirstYouTubeVideoKey($bestMovie['id'] ?? 0);

            foreach ($allMovies as &$movie) {
                $movie['video_key'] = $tmdbApiService->getFirstYouTubeVideoKey($movie['id']);
            }

            return $this->render('home/index.html.twig', [
                'movies' => $allMovies,
                'genres' => $allGenres,
                'bestMovie' => $bestMovie,
                'bestMovieVideoKey' => $bestMovieVideoKey
            ]);

        } catch (\Exception $e) {
            return new Response('Erreur lors de la récupération des données : ' . $e->getMessage(), 500);
        }
    }



    #[Route('/getList', name: 'getList', methods: ['POST'])]
    public function getListData(Request $request, MovieHtmlGenerator $htmlGenerator, TmdbApiService $tmdbApiService): Response
    {
        $data = json_decode($request->getContent(), true);
        $genres = $data['selectedGenres'] ?? null;
        $nameField = $data['searchText'] ?? null;

        try {
            // Récupérer les films filtrés en fonction des genres et du champ de recherche
            $allMovies = $tmdbApiService->searchMovies($genres, $nameField);

            // Ajouter la clé `video_key` pour chaque film
            foreach ($allMovies as &$movie) {
                $movie['video_key'] = $tmdbApiService->getFirstYouTubeVideoKey($movie['id']);
            }

            // Générer le HTML avec les données des films (incluant `video_key`)
            $generateHtml = $htmlGenerator->generateMoviesHtml($allMovies);

            return new Response($generateHtml);

        } catch (\Exception $e) {
            return new Response('Erreur lors de la recherche des films : ' . $e->getMessage(), 500);
        }
    }

    #[Route('/get-session-rating', name: 'get_session_rating', methods: ['GET'])]
    public function getSessionRating(SessionInterface $session): JsonResponse
    {
        $rating = $session->get('movie_rating', null);
        return new JsonResponse(['rating' => $rating], 200);
    }
    #[Route('/autocomplete', name: 'autocomplete', methods: ['GET'])]
    public function autocomplete(Request $request, TmdbApiService $tmdbApiService, MovieHtmlGenerator $htmlGenerator): Response
    {
        $query = $request->query->get('query', '');

        // Si la requête est vide, on retourne une réponse vide pour éviter un traitement inutile
        if (empty($query)) {
            return new Response(''); // Réponse vide
        }

        try {
            // Récupération des films correspondant au terme de recherche
            $results = $tmdbApiService->autocompleteSearch($query);

            // Génération d'un HTML léger pour les suggestions d'autocomplétion
            $suggestionsHtml = '';
            foreach ($results as $movie) {
                $suggestionsHtml .= '<div class="autocomplete-item" data-movie-id="' . htmlspecialchars($movie['id']) . '">'
                    . htmlspecialchars($movie['title']) . '</div>';
            }

            return new Response($suggestionsHtml);

        } catch (\Exception $e) {
            return new Response('Erreur lors de l\'auto-complétion : ' . $e->getMessage(), 500);
        }
    }
    #[Route('/search-movie', name: 'search_movie', methods: ['POST'])]
    public function searchMovie(Request $request, TmdbApiService $tmdbApiService, MovieHtmlGenerator $htmlGenerator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $query = $data['query'] ?? '';

        if (empty($query)) {
            return new JsonResponse(['error' => 'Aucun terme de recherche fourni'], 400);
        }

        try {
            // Rechercher les films par titre
            $results = $tmdbApiService->searchMoviesByTitle($query);

            // Vérification des résultats
            if (empty($results)) {
                return new JsonResponse(['message' => 'Aucun film trouvé pour cette recherche.'], 404);
            }

            // Générer le HTML pour chaque film trouvé
            $htmlOutput = '';
            foreach ($results as $movie) {
                // Récupérer la clé vidéo pour chaque film
                $movie['video_key'] = $tmdbApiService->getFirstYouTubeVideoKey($movie['id']);
                // Générer le HTML pour le film et l'ajouter au résultat final
                $htmlOutput .= $htmlGenerator->generateSingleMovieHtml($movie);
            }

            return new JsonResponse(['html' => $htmlOutput]);

        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Erreur lors de la recherche du film : ' . $e->getMessage()], 500);
        }
    }

    /* #[Route('/submit-rating', name: 'submit_rating', methods: ['POST'])]
    public function submitRating(Request $request, SessionInterface $session): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['rating']) || empty($data['movie_id'])) {
            return new JsonResponse(['error' => 'Les champs rating et movie_id sont requis.'], 400);
        }

        $rating = $data['rating'];
        $movieId = $data['movie_id'];

        $session->set('movie_rating', ['idMovie' => $movieId, 'rating' => $rating]);

        return new JsonResponse(['message' => 'Rating submitted successfully!'], 201);
    }*/

}