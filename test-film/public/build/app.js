$(document).ready(function () {
    const autocompleteResults = $('.autocomplete-results');
    let typingTimer;
    const typingInterval = 300; // Intervalle de frappe pour limiter les requêtes
    let cache = {}; // Cache pour stocker les résultats de recherche
    let isFetchingAutocomplete = false; // Contrôle pour les requêtes `autocomplete`

    // Fonction pour afficher les détails du film et charger la vidéo dans la modale
    function showMovieDetails() {
        $(document).on('click', '.detailsFilm', function (e) {
            e.preventDefault();

            const name = $(this).attr('movie-name');
            const rate = $(this).attr('movie-rate');
            const videoKey = $(this).attr('movie-video-key');
            const videoUrl = `https://www.youtube.com/embed/${videoKey}`;
            const desc = $(this).attr('movie-desc');
            const count = $(this).attr('movie-count');

            $('#iframeVideo').attr('src', videoUrl);
            $('#iframeVideo').attr('title', name);
            $('#videoModalLabel').text(name);
            $('#movieDescription').text(desc);
            $('#movieRating').text(rate);
            $('#userCount').text(`pour ${count} utilisateurs`);
            $('#submitRating').data('movie-id', $(this).attr('movie-id'));

            $('#detailsModal').modal('show');
        });

        // Nettoyage de l'URL de la vidéo après la fermeture
        $('#detailsModal').on('hidden.bs.modal', function () {
            $('#iframeVideo').attr('src', '');
        });
    }

    // Initialisation de l'affichage des détails
    showMovieDetails();

    // Fonction de recherche avec cache et gestion des genres
    function getSearchAndGenres() {
        const searchText = $('.movieSearchInput').val().trim();
        const selectedGenres = $('.genreCheckbox:checked').map(function () {
            return parseInt($(this).val());
        }).get();

        const cacheKey = searchText + selectedGenres.join(',');
        if (cache[cacheKey]) {
            updateMovieList(cache[cacheKey]);
            return;
        }

        // Requête AJAX principale
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

        // Autocomplete (uniquement pour 3 caractères ou plus)
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
        showMovieDetails(); // Réattacher les événements de clic après la mise à jour
    }

    // Délai de recherche pour éviter les appels répétitifs
    $('.movieSearchInput').on('input', function () {
        clearTimeout(typingTimer);
        typingTimer = setTimeout(getSearchAndGenres, typingInterval);
    });

    $('.genreCheckbox').on('change', getSearchAndGenres);

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

    $('.movieSearchInputButton').on('click', function (e) {
        e.preventDefault();
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
    });


});
