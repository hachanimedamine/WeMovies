$(document).ready(function () {
    // Fonction pour afficher les détails du film et charger la vidéo dans la modale
    function showMovieDetails() {
        $('.detailsFilm').off('click').on('click', function (e) {
            e.preventDefault();

            const name = $(this).attr('movie-name');
            const rate = $(this).attr('movie-rate');
            const videoKey = $(this).attr('movie-video-key');
            const videoUrl = `https://www.youtube.com/embed/${videoKey}`;
            const desc = $(this).attr('movie-desc');
            const count = $(this).attr('movie-count');

            // Debugging pour vérifier que les attributs sont bien présents
            console.log('Video Key:', videoKey);
            console.log('Video URL:', videoUrl);

            $('#iframeVideo').attr('src', videoUrl);
            $('#iframeVideo').attr('title', name);
            $('#videoModalLabel').text(name);
            $('#movieDescription').text(desc);
            $('#movieRating').text(rate);
            $('#userCount').text(`pour ${count} utilisateurs`);

            // Sauvegarder l'ID du film dans le bouton de soumission pour la notation
            $('#submitRating').data('movie-id', $(this).attr('movie-id'));

            $('#detailsModal').modal('show');
        });
    }

    // Appeler `showMovieDetails` au chargement initial de la page
    showMovieDetails();

    // Nettoyer l'URL de la vidéo lors de la fermeture de la modale
    $('#detailsModal').on('hidden.bs.modal', function () {
        $('#iframeVideo').attr('src', ''); // Arrête la vidéo en supprimant l'URL
    });

    // Fonction pour gérer la recherche et le filtrage par genres
    function getSearchAndGenres() {
        const searchText = $('.movieSearchInput').val();
        const selectedGenres = [];
        $('.genreCheckbox:checked').each(function() {
            selectedGenres.push(parseInt($(this).val()));
        });

        $.ajax({
            url: "/getList",
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ searchText: searchText, selectedGenres: selectedGenres }),
            success: function(response) {
                $('.movieList').html(response);

                // Ré-attacher les événements de clic aux nouveaux boutons après la mise à jour
                showMovieDetails();
            }
        });
    }

    // Déclencher le filtre de recherche et genres sur changement
    $('.movieSearchInput').on('input', getSearchAndGenres);
    $('.genreCheckbox').on('change', getSearchAndGenres);
});
