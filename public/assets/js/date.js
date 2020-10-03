var dateApp = {

    init: function() {

        console.log('init js date');

        /********************************* AU CHARGEMENT DE LA PAGE *********************************/

        // Transforme le format des dates
        $(document).ready(function () {
            
            // Transforme le format des dates de recherche
            moment.locale('fr')

            var singleDate = $('.js-search-departureDate')
            $(singleDate).each(function() {
               $(this).text(moment($(this).text()).format('ddd D MMM'))
            })

            var returnDate = $('.js-search-returnDate')
            $(returnDate).each(function() {
               $(this).text(moment($(this).text()).format('ddd D MMM'))
            })

            var fullDate = $('.js-full-date')
            $(fullDate).each(function() {
                $(this).text(moment($(this).text()).format('ddd D MMM LT'))
            })

            var reviewDate = $('.js-review-date')
            $(reviewDate).each(function() {
                $(this).text(moment($(this).text()).format('ll'))
            })
            
        });
    },
};

// Chargement du DOM
$(dateApp.init);