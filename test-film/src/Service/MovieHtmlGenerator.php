<?php

namespace App\Service;

class MovieHtmlGenerator
{
    // Méthode d'origine pour générer la liste complète des films
    public function generateMoviesHtml($movies): string
    {
        $htmlOutput = '<div class="col-md-9 movieList">';
        foreach ($movies as $movie) {
            $posterPath = isset($movie['poster_path']) ? htmlspecialchars($movie['poster_path']) : '';
            $title = isset($movie['title']) ? htmlspecialchars($movie['title']) : 'Titre inconnu';
            $overview = isset($movie['overview']) ? htmlspecialchars($movie['overview']) : 'Description non disponible.';
            $voteAverage = isset($movie['vote_average']) ? number_format((float)$movie['vote_average'], 1, '.', '') : '0.0';
            $voteCount = isset($movie['vote_count']) ? (int)$movie['vote_count'] : 0;
            $id = isset($movie['id']) ? (int)$movie['id'] : '';
            $videoKey = isset($movie['video_key']) ? htmlspecialchars($movie['video_key']) : '';

            $htmlOutput .= '<div class="movie-card d-flex" style="flex-basis: 100%; flex-grow: 1;">';
            $htmlOutput .= '<img src="https://image.tmdb.org/t/p/w500/' . $posterPath . '" alt="' . $title . '" class="movie-img" style="width: 100px; height: auto; border-radius: 5px; margin-right: 15px;">';
            $htmlOutput .= '<div class="movie-info">';
            $htmlOutput .= '<h5>' . $title . '</h5>';
            $htmlOutput .= '<p>' . $overview . '</p>';
            $htmlOutput .= '<p class="star-rating" style="color: #ffc107; font-size: 12px;">' . $voteAverage;

            for ($i = 1; $i <= 5; $i++) {
                $htmlOutput .= '<i class="fa fa-star"' . ($i < $voteAverage / 2 ? ' style="color: blue;"' : '') . '></i>';
            }

            $htmlOutput .= '<small>(' . $voteCount . ' votes)</small>';
            $htmlOutput .= '</p>';

            $htmlOutput .= '<a href="#" ';
            $htmlOutput .= 'movie-desc="' . $overview . '" ';
            $htmlOutput .= 'movie-image="' . $posterPath . '" ';
            $htmlOutput .= 'movie-rate="' . $voteAverage . '" ';
            $htmlOutput .= 'movie-name="' . $title . '" ';
            $htmlOutput .= 'movie-id="' . $id . '" ';
            $htmlOutput .= 'movie-count="' . $voteCount . '" ';
            $htmlOutput .= 'movie-video-key="' . $videoKey . '" ';
            $htmlOutput .= 'class="btn btn-primary btn-details detailsFilm" style="padding: 6px 12px; font-size: 12px; border-radius: 4px;">Lire le détails</a>';

            $htmlOutput .= '</div></div>';
        }
        $htmlOutput .= '</div>';

        return $htmlOutput;
    }

    public function generateSingleMovieHtml(array $movie): string
    {
        $posterPath = isset($movie['poster_path']) ? htmlspecialchars($movie['poster_path']) : '';
        $title = isset($movie['title']) ? htmlspecialchars($movie['title']) : 'Titre inconnu';
        $overview = isset($movie['overview']) ? htmlspecialchars($movie['overview']) : 'Description non disponible.';
        $voteAverage = isset($movie['vote_average']) ? number_format((float)$movie['vote_average'], 1, '.', '') : '0.0';
        $voteCount = isset($movie['vote_count']) ? (int)$movie['vote_count'] : 0;
        $id = isset($movie['id']) ? (int)$movie['id'] : '';
        $videoKey = isset($movie['video_key']) ? htmlspecialchars($movie['video_key']) : '';

        $htmlOutput = '<div class="movie-item" style="text-align: center; max-width: 600px; margin: 0 auto;">';
        $htmlOutput .= '<h3>' . $title . '</h3>';
        $htmlOutput .= '<p>Note : ' . $voteAverage . '</p>';
        $htmlOutput .= '<p>' . $overview . '</p>';
        $htmlOutput .= '<iframe width="100%" height="315" src="https://www.youtube.com/embed/' . $videoKey . '" frameborder="0" allowfullscreen style="border-radius: 8px; margin-top: 10px;"></iframe>';
        $htmlOutput .= '</div>';

        return $htmlOutput;
    }
}
