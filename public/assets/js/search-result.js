var app = {

    init: function() {

        console.log('init');

        /********************************* AU CHARGEMENT DE LA PAGE *********************************/

        // Variables globales pour la pagination des trajets
        var reachedMaxDDayCityDeparture = false;
        var startDDayCityDeparture = 0;

        var reachedMaxDDaySpotArrival = false;
        var startDDaySpotArrival = 0;

        var reachedMaxDDayPrice = false;
        var startDDayPrice = 0;

        var reachedMaxOtherDayDate = false;
        var startOtherDayDate = 0;

        var reachedMaxOtherDayCityDeparture = false;
        var startOtherDayCityDeparture = 0;

        var reachedMaxOtherDaySpotArrival = false;
        var startOtherDaySpotArrival = 0;

        var reachedMaxOtherDayPrice = false;
        var startOtherDayPrice = 0;

        var limit = 3;

        // Affiche les premiers trajets créés au chargement de la page
        $(document).ready(function () {
            // Transforme le format des dates de recherche
            moment.locale('fr')

            var departureDateFormat = moment($('.js-search-departureDate').text()).format('ddd D MMM')
            $('.js-search-departureDate').text(departureDateFormat)

            var returnDateFormat = moment($('.js-search-returnDate').text()).format('ddd D MMM')
            $('.js-search-returnDate').text(returnDateFormat)

            // Charge la bonne div selon qu'il y ait des résultats ou non
            dDay = $('#dDay-container')
            if (dDay.length > 0) {
                getDDayCityDeparture();
            } else {
                getOtherDayDate();
            }
        });
        
        // Affiche les trajets D DAY
        function getDDayCityDeparture() {
            if (reachedMaxDDayCityDeparture) {
                return;
            }
            searchId = $('#js-search-id').attr("data-id")
            // Reqûete AJAX
            $.ajax({
                url: 'http://localhost/RameneTaPlanche/symfony/public/search/result/' + searchId + '/dDay/cityDeparture',
                method: 'GET',
                dataType: 'json',
                data: {
                    getData: 1,
                    startDDayCityDeparture: startDDayCityDeparture,
                    limit: limit,
                },
                // Si la requête aboutit
                success: function(rideCollection) {
                    console.log("dDay DepartureCity ")
                    // On remove les autres résultats qui ont pu être affichés
                    $('.load-dDay-spotArrival, .load-dDay-price').remove();
                    // On remove les boutons "voir plus de résultats" qui ont déjà pu être affichés
                    $("#js-more-dDay-cityDeparture, #js-more-dDay-spotArrival, #js-more-dDay-price").remove()
                    // Puis on ajoute la classe active sur le menu
                    $(".dDay-cityDeparture").removeClass("btn-filter")
                    $(".dDay-cityDeparture").addClass("active-filter")
                    // Et on enlève les autres classes actives s'il y en a eu
                    $(".dDay-spotArrival, .dDay-price").removeClass("active-filter")
                    $(".dDay-spotArrival, .dDay-price").addClass("btn-filter")
                    $('.js-edit-remove').remove()
                    // On affiche les résultats AJAX demandés
                    for (rideIndex in rideCollection.rides) {
                        var currentRide = rideCollection.rides[rideIndex];
                        var returnDate = currentRide.returnDate;
                        // On conditionne l'affichage si c'est un résultat aller
                        if (returnDate == '1900-01-01T00:00:00+00:00') {
                            // On génère un nouveau trajet avec la méthode generateSingleRideElement
                            var rideElement = app.generateSingleRideElement(currentRide);
                            // Et on ajoute ce nouvel élement de type <template> dans le container prévu à cet effet dans le DOM
                            // Cette étape est très importante : si on l'oublie, le navigateur ne pourra pas l'afficher
                            $('#dDay-container').append(rideElement);
                        // Ou un résultat retour
                        } else {
                            // On génère un nouveau trajet avec la méthode generateReturnRideElement
                            var rideElement = app.generateReturnRideElement(currentRide);
                            // Et on ajoute ce nouvel élement de type <template> dans le container prévu à cet effet dans le DOM
                            $('#dDay-container').append(rideElement);
                        }
                    }
                    // Incrémente le startDDayCityDeparture de 3 pour aller chercher les 3 résultats suivants
                    startDDayCityDeparture += limit
                    // Check si le nombre de trajets total est inférieur à la pagination de 3 pour proposer de modifier la recherche après le dernier trajet
                    if (rideCollection.ridesTotal.length <= 3) {
                        $('#dDay-container').append($('#js-edit-search-dDay').contents().clone())
                    }
                    // Ajoute les nouveaux trajets trouvés au DOM dans la bonne div
                    $("#dDay-container").append(rideCollection.rides);
                    var dDay = $('.load-dDay-cityDeparture')
                    if (dDay.length > 0) {
                        var DOMRides = $('.load-dDay-cityDeparture').length
                        if (rideCollection.ridesTotal.length > DOMRides) {
                            var moreRides= $('<div class="text-center"><input id="js-more-dDay-cityDeparture" class="btn-lightgreen" type="button" value="Voir plus de résultats"/>');
                            $("#dDay-container").append(moreRides);
                        }
                        if (rideCollection.ridesTotal.length > 3) {
                            if (rideCollection.ridesTotal.length == DOMRides) {
                                $('#dDay-container').append($('#js-edit-search-dDay').contents().clone())
                            }
                        }
                    }
                },
                //Si error, cela veut dire que le user n'est plus connecté
                error: function() {
                    // On le redirige donc vers la page de connexion
                    window.location.replace("http://localhost/RameneTaPlanche/symfony/public/connexion");
                },
            });
        };

        // Affiche les trajets OTHER DAY
        function getOtherDayDate() {
            if (reachedMaxOtherDayDate) {
                return;
            }
            searchId = $('#js-search-id').attr("data-id")
            // Reqûete AJAX
            $.ajax({
                url: 'http://localhost/RameneTaPlanche/symfony/public/search/result/' + searchId + '/otherDay/date',
                method: 'GET',
                dataType: 'json',
                data: {
                    getData: 1,
                    startOtherDayDate: startOtherDayDate,
                    limit: limit,
                },
                // Si la requête aboutit
                success: function(rideCollection) {
                    console.log("otherDay Date ")
                    // On remove les autres résultats qui ont pu être affichés
                    $('.load-otherDay-cityDeparture, .load-otherDay-spotArrival, .load-otherDay-price').remove();
                    // On remove les boutons "voir plus de résultats" qui ont déjà pu être affichés
                    $("#js-more-otherDay-cityDeparture, #js-more-otherDay-spotArrival, #js-more-otherDay-price, #js-more-otherDay-date").remove()
                    // Puis on ajoute la classe active sur le menu
                    $(".otherDay-date").removeClass("btn-filter")
                    $(".otherDay-date").addClass("active-filter")
                    // Et on enlève les autres classes actives s'il y en a eu
                    $(".otherDay-cityDeparture, .otherDay-spotArrival, .otherDay-price").removeClass("active-filter")
                    $(".otherDay-cityDeparture, .otherDay-spotArrival, .otherDay-price").addClass("btn-filter")
                    $('.js-edit-remove').remove()
                    // On affiche les résultats AJAX demandés
                    console.log(rideCollection)
                    for (rideIndex in rideCollection.rides) {
                        var currentRide = rideCollection.rides[rideIndex];
                        var returnDate = currentRide.returnDate;
                        // On conditionne l'affichage si c'est un résultat aller
                        if (returnDate == '1900-01-01T00:00:00+00:00') {
                            // On génère un nouveau trajet avec la méthode generateSingleRideElement
                            var rideElement = app.generateSingleRideElement(currentRide);
                            // Et on ajoute ce nouvel élement de type <template> dans le container prévu à cet effet dans le DOM
                            // Cette étape est très importante : si on l'oublie, le navigateur ne pourra pas l'afficher
                            $('#otherDay-container').append(rideElement);
                        // Ou un résultat retour
                        } else {
                            // On génère un nouveau trajet avec la méthode generateReturnRideElement
                            var rideElement = app.generateReturnRideElement(currentRide);
                            // Et on ajoute ce nouvel élement de type <template> dans le container prévu à cet effet dans le DOM
                            $('#otherDay-container').append(rideElement);
                        }
                    }
                    // Incrémente le startOtherDayDate de 3 pour aller chercher les 3 résultats suivants
                    startOtherDayDate += limit
                    // Check si le nombre de trajets total est inférieur à la pagination de 3 pour proposer de modifier la recherche après le dernier trajet
                    if (rideCollection.ridesTotal.length <= 3) {
                        $('#otherDay-container').append($('#js-edit-search-otherDay').contents().clone())
                    }
                    // Ajoute les nouveaux trajets trouvés au DOM dans la bonne div
                    $("#otherDay-container").append(rideCollection.rides);
                    // Bouton plus de trajets
                    var otherDay = $('.load-otherDay-date')
                    if (otherDay.length > 0) {
                        var DOMRides = $('.load-otherDay-date').length
                        if (rideCollection.ridesTotal.length > DOMRides) {
                            var moreRides= $('<div class="text-center"><input id="js-more-otherDay-date" class="btn-lightgreen" type="button" value="Voir plus de résultats"/>');
                            $("#otherDay-container").append(moreRides);
                        }
                        if (rideCollection.ridesTotal.length > 3) {
                            if (rideCollection.ridesTotal.length == DOMRides) {
                                $('#otherDay-container').append($('#js-edit-search-otherDay').contents().clone())
                            }
                        }
                    }
                },
                //Si error, cela veut dire que le user n'est plus connecté
                error: function() {
                    // On le redirige donc vers la page de connexion
                    window.location.replace("http://localhost/RameneTaPlanche/symfony/public/connexion");
                },
            });
        };

        /********************************* SI CLIC SUR VOIR PLUS *********************************/

        $(document).on('click','#js-more-dDay-cityDeparture',function(){
            getDDayCityDeparture();   
        }),
        $(document).on('click','#js-more-dDay-spotArrival',function(){
            getOtherRides(event);   
        }),
        $(document).on('click','#js-more-dDay-price',function(){
            getOtherRides(event);   
        }),
        $(document).on('click','#js-more-otherDay-date',function(){
            getOtherDayDate();   
        }),
        $(document).on('click','#js-more-otherDay-cityDeparture',function(){
            getOtherRides(event);   
        }),
        $(document).on('click','#js-more-otherDay-spotArrival',function(){
            getOtherRides(event);   
        }),
        $(document).on('click','#js-more-otherDay-price',function(){
            getOtherRides(event);   
        }),

        /********************************* SI CLIC SUR AUTRES TRAJETS *********************************/

        $(".js-other-rides").click(function(event) {
            // Pour chaque clic, check son currentTarget, et exécute la méthode qui correspond
            $(event.currentTarget).each(function() {
                // Si le user clique sur un bouton qui a déjà la classe active, sors de là pour éviter une nouvelle requête ajax inutile
                if ($(event.currentTarget).hasClass("active-filter")) {
                    return;   
                }        
                //Sinon traite la demande
                else {
                    if ($(event.currentTarget).hasClass("dDay-spotArrival")) {
                        reachedMaxDDaySpotArrival = false;
                        startDDaySpotArrival = 0;
                        getOtherRides(event);
                    } else if ($(event.currentTarget).hasClass("dDay-price")) {
                        reachedMaxDDayPrice = false;
                        startDDayPrice = 0;
                        getOtherRides(event);
                    } else if ($(event.currentTarget).hasClass("dDay-cityDeparture")) {
                        reachedMaxDDayCityDeparture = false;
                        startDDayCityDeparture = 0;
                        getDDayCityDeparture();   
                    } else if ($(event.currentTarget).hasClass("otherDay-date")) {
                        reachedMaxOtherDayDate = false;
                        startOtherDayDate = 0;
                        getOtherDayDate();
                    } else if ($(event.currentTarget).hasClass("otherDay-cityDeparture")) {
                        reachedMaxOtherDayCityDeparture = false;
                        startOtherDayCityDeparture = 0;
                        getOtherRides(event);
                    } else if ($(event.currentTarget).hasClass("otherDay-spotArrival")) {
                        reachedMaxOtherDaySpotArrival = false;
                        startOtherDaySpotArrival = 0;
                        getOtherRides(event); 
                    } else {
                        reachedMaxOtherDayPrice = false;
                        startOtherDayPrice = 0;
                        getOtherRides(event);
                    }
                }
            })
        });

        function getOtherRides(event) {

            /* DDay SpotArrival */
            if ($(event.currentTarget).hasClass("dDay-spotArrival") || $(event.target).attr("id") == "js-more-dDay-spotArrival") {
                $('.load-dDay-cityDeparture, .load-dDay-price').remove();
                if (reachedMaxDDaySpotArrival) {
                    return;
                }
                searchId = $('#js-search-id').attr("data-id")
                // Reqûete AJAX
                $.ajax({
                    url: 'http://localhost/RameneTaPlanche/symfony/public/search/result/' + searchId + '/dDay/spotArrival',
                    method: 'GET',
                    dataType: 'json',
                    data: {
                        getData: 1,
                        startDDaySpotArrival: startDDaySpotArrival,
                        limit: limit,
                    },
                    success: function(rideCollection) {
                        console.log("dDay SpotArrival")
                        $("#js-more-dDay-cityDeparture, #js-more-dDay-spotArrival, #js-more-dDay-price").remove()
                        $(".dDay-spotArrival").removeClass("btn-filter")
                        $(".dDay-spotArrival").addClass("active-filter")
                        $(".dDay-cityDeparture, .dDay-price").removeClass("active-filter")
                        $(".dDay-cityDeparture, .dDay-price").addClass("btn-filter")
                        $('.js-edit-remove').remove()
                        // Tu affiches les trajets AJAX demandés
                        for (rideIndex in rideCollection.rides) {
                            var currentRide = rideCollection.rides[rideIndex];
                            var returnDate = currentRide.returnDate;
                            // On conditionne l'affichage si c'est un trajet aller
                            if (returnDate == '1900-01-01T00:00:00+00:00') {
                                // On génère un nouveau trajet avec la méthode generateSingleCreatedRideElement
                                var rideElement = app.generateSingleRideElement(currentRide);
                                // Et on ajoute ce nouvel élement de type <template> dans le container prévu à cet effet dans le DOM
                                // Cette étape est très importante : si on l'oublie, le navigateur ne pourra pas l'afficher
                                $('#dDay-container').append(rideElement);
                            // Ou un trajet retour
                            } else {
                                // On génère un nouveau trajet avec la méthode generateReturnCreatedRideElement
                                var rideElement = app.generateReturnRideElement(currentRide);
                                // Et on ajoute ce nouvel élement de type <template> dans le container prévu à cet effet dans le DOM
                                $('#dDay-container').append(rideElement);
                            }
                        }
                        startDDaySpotArrival += limit;
                        // Ajoute les nouveaux trajets trouvés au DOM dans la bonne div
                        $("#dDay-container").append(rideCollection.rides);
                        var dDay = $('.load-dDay-spotArrival')
                        if (dDay.length > 0) {
                            var DOMRides = $('.load-dDay-spotArrival').length
                            if (rideCollection.ridesTotal.length > DOMRides) {
                                var moreRides= $('<div class="text-center"><input id="js-more-dDay-spotArrival" class="btn-lightgreen" type="button" value="Voir plus de résultats"/>');
                                $("#dDay-container").append(moreRides);
                            }
                            if (rideCollection.ridesTotal.length > 3) {
                                if (rideCollection.ridesTotal.length == DOMRides) {
                                    $('#dDay-container').append($('#js-edit-search-dDay').contents().clone())
                                }
                            }
                        }
                    },
                    error: function() {
                        window.location.replace("http://localhost/RameneTaPlanche/symfony/public/connexion");
                    },
                });
            /* DDay Price */
            } else if ($(event.currentTarget).hasClass("dDay-price") || $(event.target).attr("id") == "js-more-dDay-price") {
                $('.load-dDay-cityDeparture, .load-dDay-spotArrival').remove();
                if (reachedMaxDDayPrice) {
                    return;
                }
                searchId = $('#js-search-id').attr("data-id")
                // Reqûete AJAX
                $.ajax({
                    url: 'http://localhost/RameneTaPlanche/symfony/public/search/result/' + searchId + '/dDay/price',
                    method: 'GET',
                    dataType: 'json',
                    data: {
                        getData: 1,
                        startDDayPrice: startDDayPrice,
                        limit: limit,
                    },
                    success: function(rideCollection) {
                        console.log("dDay Price")
                        $("#js-more-dDay-cityDeparture, #js-more-dDay-spotArrival, #js-more-dDay-price").remove()
                        $(".dDay-price").removeClass("btn-filter")
                        $(".dDay-price").addClass("active-filter")
                        $(".dDay-cityDeparture, .dDay-spotArrival").removeClass("active-filter")
                        $(".dDay-cityDeparture, .dDay-spotArrival").addClass("btn-filter")
                        $('.js-edit-remove').remove()
                        // Tu affiches les trajets AJAX demandés
                        for (rideIndex in rideCollection.rides) {
                            var currentRide = rideCollection.rides[rideIndex];
                            var returnDate = currentRide.returnDate;
                            // On conditionne l'affichage si c'est un trajet aller
                            if (returnDate == '1900-01-01T00:00:00+00:00') {
                                // On génère un nouveau trajet avec la méthode generateSingleCreatedRideElement
                                var rideElement = app.generateSingleRideElement(currentRide);
                                // Et on ajoute ce nouvel élement de type <template> dans le container prévu à cet effet dans le DOM
                                // Cette étape est très importante : si on l'oublie, le navigateur ne pourra pas l'afficher
                                $('#dDay-container').append(rideElement);
                            // Ou un trajet retour
                            } else {
                                // On génère un nouveau trajet avec la méthode generateReturnCreatedRideElement
                                var rideElement = app.generateReturnRideElement(currentRide);
                                // Et on ajoute ce nouvel élement de type <template> dans le container prévu à cet effet dans le DOM
                                $('#dDay-container').append(rideElement);
                            }
                        }
                        startDDayPrice += limit;
                        // Ajoute les nouveaux trajets trouvés au DOM dans la bonne div
                        $("#dDay-container").append(rideCollection.rides);
                        var dDay = $('.load-dDay-price')
                        if (dDay.length > 0) {
                            var DOMRides = $('.load-dDay-price').length
                            if (rideCollection.ridesTotal.length > DOMRides) {
                                var moreRides= $('<div class="text-center"><input id="js-more-dDay-price" class="btn-lightgreen" type="button" value="Voir plus de résultats"/>');
                                $("#dDay-container").append(moreRides);
                            }
                            if (rideCollection.ridesTotal.length > 3) {
                                if (rideCollection.ridesTotal.length == DOMRides) {
                                    $('#dDay-container').append($('#js-edit-search-dDay').contents().clone())
                                }
                            }
                        }
                    },
                    error: function() {
                        window.location.replace("http://localhost/RameneTaPlanche/symfony/public/connexion");
                    },
                });
            /* OTHER Day cityDeparture */
            } else if ($(event.currentTarget).hasClass("otherDay-cityDeparture") || $(event.target).attr("id") == "js-more-otherDay-cityDeparture"){ 
                $('.load-otherDay-price, .load-otherDay-spotArrival, .load-otherDay-date').remove();
                if (reachedMaxOtherDayCityDeparture) {
                    return;
                }
                searchId = $('#js-search-id').attr("data-id")
                // Reqûete AJAX
                $.ajax({
                    url: 'http://localhost/RameneTaPlanche/symfony/public/search/result/' + searchId + '/otherDay/cityDeparture',
                    method: 'GET',
                    dataType: 'json',
                    data: {
                        getData: 1,
                        startOtherDayCityDeparture: startOtherDayCityDeparture,
                        limit: limit,
                    },
                    success: function(rideCollection) {
                        console.log("otherDay CityDeparture")
                        $("#js-more-otherDay-cityDeparture, #js-more-otherDay-spotArrival, #js-more-otherDay-price, #js-more-otherDay-date").remove()
                        $(".otherDay-cityDeparture").removeClass("btn-filter")
                        $(".otherDay-cityDeparture").addClass("active-filter")
                        $(".otherDay-price, .otherDay-spotArrival, .otherDay-date").removeClass("active-filter")
                        $(".otherDay-price, .otherDay-spotArrival, .otherDay-date").addClass("btn-filter")
                        $('.js-edit-remove').remove()
                        // Tu affiches les trajets AJAX demandés
                        for (rideIndex in rideCollection.rides) {
                            var currentRide = rideCollection.rides[rideIndex];
                            var returnDate = currentRide.returnDate;
                            // On conditionne l'affichage si c'est un trajet aller
                            if (returnDate == '1900-01-01T00:00:00+00:00') {
                                // On génère un nouveau trajet avec la méthode generateSingleCreatedRideElement
                                var rideElement = app.generateSingleRideElement(currentRide);
                                // Et on ajoute ce nouvel élement de type <template> dans le container prévu à cet effet dans le DOM
                                // Cette étape est très importante : si on l'oublie, le navigateur ne pourra pas l'afficher
                                $('#otherDay-container').append(rideElement);
                            // Ou un trajet retour
                            } else {
                                // On génère un nouveau trajet avec la méthode generateReturnCreatedRideElement
                                var rideElement = app.generateReturnRideElement(currentRide);
                                // Et on ajoute ce nouvel élement de type <template> dans le container prévu à cet effet dans le DOM
                                $('#otherDay-container').append(rideElement);
                            }
                        }
                        startOtherDayCityDeparture += limit;
                        // Ajoute les nouveaux trajets trouvés au DOM dans la bonne div
                        $("#otherDay-container").append(rideCollection.rides);
                        // Bouton plus de trajets
                        var otherDay = $('.load-otherDay-cityDeparture')
                        if (otherDay.length > 0) {
                            var DOMRides = $('.load-otherDay-cityDeparture').length
                            if (rideCollection.ridesTotal.length > DOMRides) {
                                var moreRides= $('<div class="text-center"><input id="js-more-otherDay-cityDeparture" class="btn-lightgreen" type="button" value="Voir plus de résultats"/>');
                                $("#otherDay-container").append(moreRides);
                            }
                            if (rideCollection.ridesTotal.length > 3) {
                                if (rideCollection.ridesTotal.length == DOMRides) {
                                    $('#otherDay-container').append($('#js-edit-search-otherDay').contents().clone())
                                }
                            }
                        }
                    },
                    error: function() {
                        window.location.replace("http://localhost/RameneTaPlanche/symfony/public/connexion");
                    },
                });
            /* OTHER Day spotArrival */
            } else if ($(event.currentTarget).hasClass("otherDay-spotArrival") || $(event.target).attr("id") == "js-more-otherDay-spotArrival"){ 
                $('.load-otherDay-price, .load-otherDay-cityDeparture, .load-otherDay-date').remove();
                if (reachedMaxOtherDaySpotArrival) {
                    return;
                }
                searchId = $('#js-search-id').attr("data-id")
                // Reqûete AJAX
                $.ajax({
                    url: 'http://localhost/RameneTaPlanche/symfony/public/search/result/' + searchId + '/otherDay/spotArrival',
                    method: 'GET',
                    dataType: 'json',
                    data: {
                        getData: 1,
                        startOtherDaySpotArrival: startOtherDaySpotArrival,
                        limit: limit,
                    },
                    success: function(rideCollection) {
                        console.log("otherDay spotArrival")
                        $("#js-more-otherDay-cityDeparture, #js-more-otherDay-spotArrival, #js-more-otherDay-price, #js-more-otherDay-date").remove()
                        $(".otherDay-spotArrival").removeClass("btn-filter")
                        $(".otherDay-spotArrival").addClass("active-filter")
                        $(".otherDay-price, .otherDay-cityDeparture, .otherDay-date").removeClass("active-filter")
                        $(".otherDay-price, .otherDay-cityDeparture, .otherDay-date").addClass("btn-filter")
                        $('.js-edit-remove').remove()
                        // Tu affiches les trajets AJAX demandés
                        for (rideIndex in rideCollection.rides) {
                            var currentRide = rideCollection.rides[rideIndex];
                            var returnDate = currentRide.returnDate;
                            // On conditionne l'affichage si c'est un trajet aller
                            if (returnDate == '1900-01-01T00:00:00+00:00') {
                                // On génère un nouveau trajet avec la méthode generateSingleCreatedRideElement
                                var rideElement = app.generateSingleRideElement(currentRide);
                                // Et on ajoute ce nouvel élement de type <template> dans le container prévu à cet effet dans le DOM
                                // Cette étape est très importante : si on l'oublie, le navigateur ne pourra pas l'afficher
                                $('#otherDay-container').append(rideElement);
                            // Ou un trajet retour
                            } else {
                                // On génère un nouveau trajet avec la méthode generateReturnCreatedRideElement
                                var rideElement = app.generateReturnRideElement(currentRide);
                                // Et on ajoute ce nouvel élement de type <template> dans le container prévu à cet effet dans le DOM
                                $('#otherDay-container').append(rideElement);
                            }
                        }
                        startOtherDaySpotArrival += limit;
                        // Ajoute les nouveaux trajets trouvés au DOM dans la div #otherDay-container
                        $("#otherDay-container").append(rideCollection.rides);
                        // Bouton afficher plus de trajets
                        var DOMRides = $('.load-otherDay-spotArrival').length
                        if (rideCollection.ridesTotal.length > DOMRides) {
                            var moreRides = $('<div class="text-center"><input id="js-more-otherDay-spotArrival" class="btn-lightgreen" type="button" value="Voir plus de résultats"/>');
                            $("#otherDay-container").append(moreRides);
                        }
                        if (rideCollection.ridesTotal.length > 3) {
                            if (rideCollection.ridesTotal.length == DOMRides) {
                                $('#otherDay-container').append($('#js-edit-search-otherDay').contents().clone())
                            }
                        }
                    },
                    error: function() {
                        window.location.replace("http://localhost/RameneTaPlanche/symfony/public/connexion");
                    },
                });
            /* OTHER Day Price */
            } else { 
                $('.load-otherDay-cityDeparture, .load-otherDay-spotArrival, .load-otherDay-date').remove();
                if (reachedMaxOtherDayPrice) {
                    return;
                }
                searchId = $('#js-search-id').attr("data-id")
                // Reqûete AJAX
                $.ajax({
                    url: 'http://localhost/RameneTaPlanche/symfony/public/search/result/' + searchId + '/otherDay/price',
                    method: 'GET',
                    dataType: 'json',
                    data: {
                        getData: 1,
                        startOtherDayPrice: startOtherDayPrice,
                        limit: limit,
                    },
                    success: function(rideCollection) {
                        console.log("otherDay Price")
                        $("#js-more-otherDay-cityDeparture, #js-more-otherDay-spotArrival, #js-more-otherDay-price, #js-more-otherDay-date").remove()
                        $(".otherDay-price").removeClass("btn-filter")
                        $(".otherDay-price").addClass("active-filter")
                        $(".otherDay-cityDeparture, .otherDay-spotArrival, .otherDay-date").removeClass("active-filter")
                        $(".otherDay-cityDeparture, .otherDay-spotArrival, .otherDay-date").addClass("btn-filter")
                        $('.js-edit-remove').remove()
                        // Tu affiches les trajets AJAX demandés
                        for (rideIndex in rideCollection.rides) {
                            var currentRide = rideCollection.rides[rideIndex];
                            var returnDate = currentRide.returnDate;
                            // On conditionne l'affichage si c'est un trajet aller
                            if (returnDate == '1900-01-01T00:00:00+00:00') {
                                // On génère un nouveau trajet avec la méthode generateSingleCreatedRideElement
                                var rideElement = app.generateSingleRideElement(currentRide);
                                // Et on ajoute ce nouvel élement de type <template> dans le container prévu à cet effet dans le DOM
                                // Cette étape est très importante : si on l'oublie, le navigateur ne pourra pas l'afficher
                                $('#otherDay-container').append(rideElement);
                            // Ou un trajet retour
                            } else {
                                // On génère un nouveau trajet avec la méthode generateReturnCreatedRideElement
                                var rideElement = app.generateReturnRideElement(currentRide);
                                // Et on ajoute ce nouvel élement de type <template> dans le container prévu à cet effet dans le DOM
                                $('#otherDay-container').append(rideElement);
                            }
                        }
                        startOtherDayPrice += limit;
                        // Ajoute les nouveaux trajets trouvés au DOM dans la div #otherDay-container
                        $("#otherDay-container").append(rideCollection.rides);
                        // Bouton afficher plus de trajets
                        var DOMRides = $('.load-otherDay-price').length
                        if (rideCollection.ridesTotal.length > DOMRides) {
                            var moreRides = $('<div class="text-center"><input id="js-more-otherDay-price" class="btn-lightgreen" type="button" value="Voir plus de résultats"/>');
                            $("#otherDay-container").append(moreRides);
                        }
                        if (rideCollection.ridesTotal.length > 3) {
                            if (rideCollection.ridesTotal.length == DOMRides) {
                                $('#otherDay-container').append($('#js-edit-search-otherDay').contents().clone())
                            }
                        }
                    },
                    error: function() {
                        window.location.replace("http://localhost/RameneTaPlanche/symfony/public/connexion");
                    },
                });
            }
        };
    },

    /********************************* GENERATE RESULTS *********************************/

    // Generate a single ride
    generateSingleRideElement:function(currentRide) {
        console.log('ALLER SIMPLE RIDE AJAAAAAAAAX');

        var newSingleRide = $('#js-show-others-single').contents().clone();

        if ($('.dDay-cityDeparture').hasClass("active-filter")) {
            newSingleRide.find('.js-ride-type-single').toggleClass("js-ride-type-single load-dDay-cityDeparture")
        } else if ($('.dDay-spotArrival').hasClass("active-filter")) {
            newSingleRide.find('.js-ride-type-single').toggleClass("js-ride-type-single load-dDay-spotArrival")
        } else if ($('.dDay-price').hasClass("active-filter")){
            newSingleRide.find('.js-ride-type-single').toggleClass("js-ride-type-single load-dDay-price")
        } else if ($('.otherDay-date').hasClass("active-filter")){
            newSingleRide.find('.js-ride-type-single').toggleClass("js-ride-type-single load-otherDay-date")
        } else if ($('.otherDay-cityDeparture').hasClass("active-filter")){
            newSingleRide.find('.js-ride-type-single').toggleClass("js-ride-type-single load-otherDay-cityDeparture")
        } else if ($('.otherDay-spotArrival').hasClass("active-filter")){
            newSingleRide.find('.js-ride-type-single').toggleClass("js-ride-type-single load-otherDay-spotArrival")
        } else {
            newSingleRide.find('.js-ride-type-single').toggleClass("js-ride-type-single load-otherDay-price")
        }

        moment.locale('fr')
        var departureDateFormat = moment(currentRide.departureDate).format('ddd D MMM')
        var departureHourFormat = moment(currentRide.departureHour).format('LT')

        newSingleRide.find('.js-single-ride-slug').attr("href", "http://localhost/RameneTaPlanche/symfony/public/resume/trajet/" + currentRide.id);
        newSingleRide.find('.js-single-cityDeparture').text(currentRide.cityDeparture);
        newSingleRide.find('.js-single-spotArrival').text(currentRide.spot.name);
        newSingleRide.find('.js-single-departureDate').text(departureDateFormat);
        newSingleRide.find('.js-single-departureHour').text(departureHourFormat);
        newSingleRide.find('.js-single-boardSizeMax').text(currentRide.boardSizeMax);
        newSingleRide.find('.js-single-driverFirstname').text(currentRide.driver.firstname + ', ');
        newSingleRide.find('.js-single-driverLevel').text(currentRide.driver.level.name);
        newSingleRide.find('.js-single-price').text(currentRide.price);
        newSingleRide.find('.js-single-driver-picture').attr("src", "/RameneTaPlanche/symfony/public/assets/images/users/" + currentRide.driver.filename);

        if (currentRide.availableSeat == 1) {
            newSingleRide.find('.js-single-availableSeat').html('<i class="fas fa-user"></i>')
        } else if (currentRide.availableSeat == 2) {
            newSingleRide.find('.js-single-availableSeat').html('<i class="fas fa-user"></i><span> <i class="fas fa-user"></i></span></span>')
        } else if (currentRide.availableSeat == 3) {
            newSingleRide.find('.js-single-availableSeat').html('<i class="fas fa-user"></i><span> <i class="fas fa-user"></i></span><span> <i class="fas fa-user"></i></span>')
        } else {
            newSingleRide.find('.js-single-availableSeat').html('<i class="fas fa-user"></i><span> <i class="fas fa-user"></i></span><span> <i class="fas fa-user"></i><span> <i class="fas fa-plus"></i></span>')
        }

        if (currentRide.isSameGender == 1) {
            newSingleRide.find('.js-single-isSameGender').text("Trajet 100% masculin")
        } else if (currentRide.isSameGender == 2 ) {
            newSingleRide.find('.js-single-isSameGender').text("Trajet 100% féminin")
        } else {
            newSingleRide.find('.js-single-isSameGender').text("Trajet mixte")
        }

        return newSingleRide;

    },

    // Generate a return ride
    generateReturnRideElement:function(currentRide) {
        
        console.log('ALLER RETOUR RIDE AJAAAAAAAAX');

        var newReturnRide = $('#js-show-others-return').contents().clone();

        if ($('.dDay-cityDeparture').hasClass("active-filter")) {
            newReturnRide.find('.js-ride-type-return').toggleClass("js-ride-type-return load-dDay-cityDeparture")
        } else if ($('.dDay-spotArrival').hasClass("active-filter")) {
            newReturnRide.find('.js-ride-type-return').toggleClass("js-ride-type-return load-dDay-spotArrival")
        } else if ($('.dDay-price').hasClass("active-filter")){
            newReturnRide.find('.js-ride-type-return').toggleClass("js-ride-type-return load-dDay-price")
        } else if ($('.otherDay-date').hasClass("active-filter")){
            newReturnRide.find('.js-ride-type-return').toggleClass("js-ride-type-return load-otherDay-date")
        } else if ($('.otherDay-cityDeparture').hasClass("active-filter")){
            newReturnRide.find('.js-ride-type-return').toggleClass("js-ride-type-return load-otherDay-cityDeparture")
        } else if ($('.otherDay-spotArrival').hasClass("active-filter")){
            newReturnRide.find('.js-ride-type-return').toggleClass("js-ride-type-return load-otherDay-spotArrival")
        } else if ($('.otherDay-price').hasClass("active-filter")){
            newReturnRide.find('.js-ride-type-return').toggleClass("js-ride-type-return load-otherDay-price")
        } else {

        }

        moment.locale('fr')
        var departureDateFormat = moment(currentRide.departureDate).format('ddd D MMM')
        var departureHourFormat = moment(currentRide.departureHour).format('LT')
        var returnDateFormat = moment(currentRide.returnDate).format('ddd D MMM')
        var returnHourFormat = moment(currentRide.returnHour).format('LT')

        newReturnRide.find('.js-return-ride-slug').attr("href", "http://localhost/RameneTaPlanche/symfony/public/resume/trajet/" + currentRide.id);
        newReturnRide.find('.js-return-cityDeparture').text(currentRide.cityDeparture);
        newReturnRide.find('.js-return-spotArrival').text(currentRide.spot.name);
        newReturnRide.find('.js-return-departureDate').text(departureDateFormat);
        newReturnRide.find('.js-return-departureHour').text(departureHourFormat);
        newReturnRide.find('.js-return-returnDate').text(returnDateFormat);
        newReturnRide.find('.js-return-returnHour').text(returnHourFormat);
        newReturnRide.find('.js-return-boardSizeMax').text(currentRide.boardSizeMax);
        newReturnRide.find('.js-return-driverFirstname').text(currentRide.driver.firstname + ', ');
        newReturnRide.find('.js-return-driverLevel').text(currentRide.driver.level.name);
        newReturnRide.find('.js-return-price').text(currentRide.price);
        newReturnRide.find('.js-return-driver-picture').attr("src", "/RameneTaPlanche/symfony/public/assets/images/users/" + currentRide.driver.filename);

        if (currentRide.availableSeat == 1) {
            newReturnRide.find('.js-return-availableSeat').html('<i class="fas fa-user"></i>')
        } else if (currentRide.availableSeat == 2) {
            newReturnRide.find('.js-return-availableSeat').html('<i class="fas fa-user"></i><span> <i class="fas fa-user"></i></span></span>')
        } else if (currentRide.availableSeat == 3) {
            newReturnRide.find('.js-return-availableSeat').html('<i class="fas fa-user"></i><span> <i class="fas fa-user"></i></span><span> <i class="fas fa-user"></i></span>')
        } else {
            newReturnRide.find('.js-return-availableSeat').html('<i class="fas fa-user"></i><span> <i class="fas fa-user"></i></span><span> <i class="fas fa-user"></i><span> <i class="fas fa-plus"></i></span>')
        }

        if (currentRide.isSameGender == 1) {
            newReturnRide.find('.js-return-isSameGender').text("Trajet 100% masculin")
        } else if (currentRide.isSameGender == 2 ) {
            newReturnRide.find('.js-return-isSameGender').text("Trajet 100% féminin")
        } else {
            newReturnRide.find('.js-return-isSameGender').text("Trajet mixte")
        }

        return newReturnRide;

    },

    /********************************* HANDLE FAIL *********************************/
    
    handleFail:function() {
        console.log("HANDLE FAIL")
        $('.result__detail').remove();
        $('#js-show-noResult').show();               
    },

};

// Chargement du DOM
$(app.init);