<?php

namespace App\Tests\Controller;

use App\Controller\HomeController;
use App\Service\TmdbApiService;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class HomeControllerTest extends WebTestCase
{
    private $tmdbApiServiceMock;

    protected function setUp(): void
    {
        // Crée un mock pour TmdbApiService
        $this->tmdbApiServiceMock = $this->createMock(TmdbApiService::class);
    }

    public function testIndex()
    {
        // Simulation des données renvoyées par le service TmdbApiService
        $this->tmdbApiServiceMock->method('getTopRatedMovie')
            ->willReturn([
                'id' => 1,
                'title' => 'Best Movie',
                'poster_path' => '/path/to/poster.jpg',
            ]);

        $this->tmdbApiServiceMock->method('getGenres')
            ->willReturn([
                ['id' => 1, 'name' => 'Action'],
                ['id' => 2, 'name' => 'Comedy'],
            ]);

        $this->tmdbApiServiceMock->method('getPopularMovies')
            ->willReturn([
                [
                    'id' => 101,
                    'title' => 'Popular Movie 1',
                    'poster_path' => '/path/to/popular1.jpg',
                ],
                [
                    'id' => 102,
                    'title' => 'Popular Movie 2',
                    'poster_path' => '/path/to/popular2.jpg',
                ],
            ]);

        $this->tmdbApiServiceMock->method('getMovieVideos')
            ->willReturn([
                ['site' => 'YouTube', 'key' => 'video_key_123']
            ]);

        // Initialisation du client HTTP de Symfony
        $client = static::createClient();

        // Injection du mock de TmdbApiService dans le conteneur de services
        $client->getContainer()->set(TmdbApiService::class, $this->tmdbApiServiceMock);

        // Appel de la route /home
        $client->request('GET', '/home');

        // Vérification de la réponse
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        // Vérification de la présence des données dans la réponse
        $this->assertSelectorTextContains('h5', 'Best Movie');
        $this->assertSelectorTextContains('h4', 'Popular Movie 1');
    }
}
