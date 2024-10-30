$(document).ready(function () {
    const autocompleteResults = $('.autocomplete-results');
    let typingTimer;
    const typingInterval = 300; // Délai de frappe pour limiter les requêtes
    let cache = {}; // Cache pour stocker les résultats de recherche
    let isFetchingAutocomplete = false; // Contrôle pour les requêtes d'auto-complétion

    // Fonction pour afficher les détails du film dans une modale
    function showMovieDetails() {
        $(document).on('click', '.detailsFilm', function (e) {
            e.preventDefault();

            // Récupération des attributs du film
            const name = $(this).attr('movie-name');
            const rate = $(this).attr('movie-rate');
            const videoKey = $(this).attr('movie-video-key');
            const videoUrl = `https://www.youtube.com/embed/${videoKey}`;
            const desc = $(this).attr('movie-desc');
            const count = $(this).attr('movie-count');

            // Affichage des détails du film
            $('#iframeVideo').attr('src', videoUrl).attr('title', name);
            $('#videoModalLabel').text(name);
            $('#movieDescription').text(desc);
            $('#movieRating').text(rate);
            $('#userCount').text(`pour ${count} utilisateurs`);
            $('#submitRating').data('movie-id', $(this).attr('movie-id'));
            $('#detailsModal').modal('show');
        });

        // Nettoyage de l'URL de la vidéo à la fermeture de la modale
        $('#detailsModal').on('hidden.bs.modal', function () {
            $('#iframeVideo').attr('src', '');
        });
    }

    // Initialisation des détails des films
    showMovieDetails();

    // Fonction principale pour la recherche avec cache et gestion des genres
    function getSearchAndGenres() {
        const searchText = $('.movieSearchInput').val().trim();
        const selectedGenres = $('.genreCheckbox:checked').map(function () {
            return parseInt($(this).val());
        }).get();

        const cacheKey = searchText + selectedGenres.join(',');

        // Vérifie le cache pour les résultats de recherche existants
        if (cache[cacheKey]) {
            updateMovieList(cache[cacheKey]);
            return;
        }

        // Requête AJAX pour la recherche
        $.ajax({
            url: "/getList",
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ searchText, selectedGenres }),
            success: function (response) {
                cache[cacheKey] = response; // Stockage dans le cache
                updateMovieList(response);
            }
        });

        // Requête AJAX pour l'auto-complétion (dès le 1er caractère)
        if (searchText.length >= 1 && !isFetchingAutocomplete) {
            isFetchingAutocomplete = true;
            $.ajax({
                url: `/autocomplete?query=${encodeURIComponent(searchText)}`,
                type: 'GET',
                success: function (html) {
                    autocompleteResults.html(html).show();
                },
                complete: function () {
                    isFetchingAutocomplete = false;
                }
            });
        } else {
            autocompleteResults.hide();
        }
    }

    // Mise à jour de la liste des films
    function updateMovieList(html) {
        $('.movieList').html(html);
        showMovieDetails(); // Réattacher les événements après mise à jour
    }

    // Délai de recherche pour éviter les appels répétitifs
    $('.movieSearchInput').on('input', function () {
        clearTimeout(typingTimer);
        typingTimer = setTimeout(getSearchAndGenres, typingInterval);
    });

    // Gestion de la touche "Entrée" pour la recherche
    $('.movieSearchInput').on('keypress', function (e) {
        if (e.which === 13) { // Code pour la touche "Entrée"
            e.preventDefault();
            performSearch();
        }
    });

    // Clic sur le bouton de recherche
    $('.movieSearchInputButton').on('click', function (e) {
        e.preventDefault();
        performSearch();
    });

    // Fonction de recherche (utilisée pour le bouton et la touche "Entrée")
    function performSearch() {
        const query = $('.movieSearchInput').val().trim();
        if (query.length > 0) {
            $.ajax({
                url: '/search-movie',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({ query: query }),
                success: function (response) {
                    if (response.html) {
                        $('.movieList').html(response.html);
                    } else if (response.message) {
                        $('.movieList').html('<p>' + response.message + '</p>');
                    }
                },
                error: function () {
                    $('.movieList').html('<p>Erreur lors de la recherche du film.</p>');
                }
            });
        }
    }

    // Gestion du clic sur une suggestion d'auto-complétion
    $(document).on('click', '.autocomplete-item', function () {
        $('.movieSearchInput').val($(this).text());
        autocompleteResults.hide();
        getSearchAndGenres();
    });

    // Cacher les suggestions d'auto-complétion si l'utilisateur clique ailleurs
    $(document).on('click', function (event) {
        if (!$(event.target).closest('.search-box').length) {
            autocompleteResults.hide();
        }
    });

    // Gestion des genres
    $('.genreCheckbox').on('change', getSearchAndGenres);
});
