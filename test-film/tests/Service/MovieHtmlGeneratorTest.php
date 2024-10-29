<?php

namespace App\Tests\Service;

use App\Service\MovieHtmlGenerator;
use PHPUnit\Framework\TestCase;

class MovieHtmlGeneratorTest extends TestCase
{
    public function testGenerateMoviesHtmlWithCompleteData()
    {
        $generator = new MovieHtmlGenerator();

        $movies = [
            [
                'poster_path' => 'test_poster.jpg',
                'original_title' => 'Original Title',
                'title' => 'Test Title',
                'overview' => 'This is a test movie overview.',
                'vote_average' => 8.5,
                'vote_count' => 100,
                'id' => 1,
            ]
        ];

        $html = $generator->generateMoviesHtml($movies);

        $this->assertStringContainsString('<div class="movie-card d-flex">', $html);
        $this->assertStringContainsString('<img src="https://image.tmdb.org/t/p/w500/test_poster.jpg"', $html);
        $this->assertStringContainsString('alt="Original Title"', $html);
        $this->assertStringContainsString('<h5>Test Title</h5>', $html);
        $this->assertStringContainsString('<p>This is a test movie overview.</p>', $html);
        $this->assertStringContainsString('<p class="star-rating">8.5', $html);
        $this->assertStringContainsString('<small>(100 votes)</small>', $html);
        $this->assertStringContainsString('movie-id="1"', $html);
    }

    public function testGenerateMoviesHtmlWithIncompleteData()
    {
        $generator = new MovieHtmlGenerator();

        $movies = [
            [
                'poster_path' => null,
                'original_title' => null,
                'title' => null,
                'overview' => null,
                'vote_average' => null,
                'vote_count' => null,
                'id' => null,
            ]
        ];

        $html = $generator->generateMoviesHtml($movies);

        $this->assertStringContainsString('<div class="movie-card d-flex">', $html);
        $this->assertStringContainsString('<img src="https://image.tmdb.org/t/p/w500/"', $html);
        $this->assertStringContainsString('alt="Titre inconnu"', $html);
        $this->assertStringContainsString('<h5>Titre inconnu</h5>', $html);
        $this->assertStringContainsString('<p>Description non disponible.</p>', $html);
        $this->assertStringContainsString('<p class="star-rating">0.0', $html);
        $this->assertStringContainsString('<small>(0 votes)</small>', $html);
        $this->assertStringContainsString('movie-id=""', $html);
    }
}
