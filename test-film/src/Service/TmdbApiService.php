<?php

namespace App\Service;

use GuzzleHttp\Client;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Serializer\SerializerInterface;

class TmdbApiService
{
    private Client $client;
    private SerializerInterface $serializer;

    public function __construct(ParameterBagInterface $params, SerializerInterface $serializer)
    {
        $this->client = new Client([
            'base_uri' => 'https://api.themoviedb.org/3/',
            'headers' => [
                'Authorization' => 'Bearer ' . $params->get('TMDB_BEARER_TOKEN'),
                'Accept' => 'application/json',
            ]
        ]);
        $this->serializer = $serializer;
    }

    public function autocompleteSearch(string $query): array
    {
        $response = $this->client->request('GET', 'search/movie', [
            'query' => [
                'query' => $query,
                'include_adult' => 'false',
                'language' => 'en-US',

            ]
        ]);

        $data = json_decode($response->getBody()->getContents(), true);

        return $data['results'] ?? [];
    }

    /**
     * Sérialise les données en JSON.
     *
     * @param mixed $data
     * @return string
     */
    public function serializeData($data): string
    {
        return $this->serializer->serialize($data, 'json');
    }

    public function getMovieVideos(int $movieId, string $language = 'en-US'): array
    {
        return $this->fetchData("movie/{$movieId}/videos", ['language' => $language])['results'] ?? [];
    }


    public function getFirstYouTubeVideoKey(int $movieId): ?string
    {
        $videos = $this->fetchData("movie/{$movieId}/videos", ['language' => 'en-US'])['results'] ?? [];
        foreach ($videos as $video) {
            if ($video['site'] === 'YouTube') {
                return $video['key'];
            }
        }
        return null;
    }

    private function fetchData(string $endpoint, array $queryParams = []): array
    {
        $response = $this->client->request('GET', $endpoint, ['query' => $queryParams]);
        return json_decode($response->getBody()->getContents(), true);
    }

    public function getTopRatedMovie(): array
    {
        return $this->fetchData('movie/top_rated', ['language' => 'fr-FR'])['results'][0] ?? [];
    }

    public function getGenres(): array
    {
        return $this->fetchData('genre/movie/list', ['language' => 'fr'])['genres'] ?? [];
    }

    public function getPopularMovies(): array
    {
        return $this->fetchData('discover/movie', [
            'include_adult' => 'false',
            'include_video' => 'false',
            'language' => 'fr-FR',
            'sort_by' => 'popularity.desc',
        ])['results'] ?? [];
    }

    public function searchMovies(?array $genres = null, ?string $nameField = null): array
    {
        $queryParams = [
            'include_adult' => 'false',
            'include_video' => 'false',
            'language' => 'en-US',
            'sort_by' => 'popularity.desc',
        ];

        if (!empty($genres)) {
            $queryParams['with_genres'] = implode(',', $genres);
        }
        if (!empty($nameField)) {
            $queryParams['query'] = $nameField;
        }

        return $this->fetchData('discover/movie', $queryParams)['results'] ?? [];
    }
    public function searchMoviesByTitle(string $title): array
    {
        try {
            $response = $this->client->request('GET', 'search/movie', [
                'query' => [
                    'query' => $title,
                    'language' => 'fr-FR',
                    'include_adult' => 'false'
                ]
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            return $data['results'] ?? [];
        } catch (\Exception $e) {
            // Log ou message d'erreur
            throw new \RuntimeException('Erreur lors de la recherche du film : ' . $e->getMessage());
        }
    }

    public function generateSingleMovieHtml(array $movie): string
    {
        return sprintf(
            '<div class="movie-item">
            <h3>%s</h3>
            <p>Note : %s</p>
            <p>%s</p>
            <iframe width="560" height="315" src="https://www.youtube.com/embed/%s" frameborder="0" allowfullscreen></iframe>
         </div>',
            htmlspecialchars($movie['title']),
            htmlspecialchars($movie['vote_average']),
            htmlspecialchars($movie['overview']),
            htmlspecialchars($movie['video_key'])
        );
    }

}
