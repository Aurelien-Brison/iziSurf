var app = {

    init: function() {

        console.log('init');

        /********************************* AU CHARGEMENT DE LA PAGE *********************************/

        // Variables globales pour la pagination des trajets
        var reachedMaxCreated = false;
        var startCreated = 0;
        var limitCreated = 3;

        var reachedMaxArchived = false;
        var startArchived = 0;
        var limitArchived = 3;

        var reachedMaxFavourites = false;
        var startFavourites = 0;
        var limitFavourites = 3;

        var reachedMaxAccepted = false;
        var startAccepted = 0;
        var limitAccepted = 3;

        var reachedMaxPending = false;
        var startPending = 0;
        var limitPending = 3;

        // Affiche les premiers trajets créés au chargement de la page
        $(document).ready(function () {
            getCreatedRides();
        });
        
        // Affiche les trajets créés
        function getCreatedRides() {
            if (reachedMaxCreated) {
                return;
            }
            baseUrl = window.location.href;
            // Reqûete AJAX
            $.ajax({
                url: baseUrl + '/created-rides',
                method: 'GET',
                dataType: 'json',
                data: {
                    getData: 1,
                    startCreated: startCreated,
                    limitCreated: limitCreated,
                },
                // Si la requête aboutit
                success: function(rideCollection) {
                    console.log("CREAAAAAAAAAAAAAAAAAAAAAAATED")
                    // On remove les boutons "voir plus de trajets" qui ont déjà pu être affichés
                    $("#js-more-created-rides, #js-more-favourite-rides, #js-more-accepted-rides, #js-more-pending-rides, #js-more-archived-rides").remove()
                    // On remove les autres trajets qui ont pu être affichés
                    $('.load-favourite-rides, .load-accepted-rides, .load-pending-rides, .load-archived-rides').remove();
                    // On modifie le text du h3
                    $('#js-myride-h3').text("Trajets créés");
                    // Puis on ajoute la classe active sur le menu
                    $(".created-rides").removeClass("list-group-item")
                    $(".created-rides").addClass("active")
                    // ET on enlève les autres classes actives s'il y en a eu
                    $(".favourite-rides, .accepted-rides, .pending-rides, .archived-rides").removeClass("active")
                    $(".favourite-rides, .accepted-rides, .pending-rides, .archived-rides").addClass("list-group-item")
                    // RideCollection peut ne pas renvoyer de résultat pour la reqûete en cours
                    if (rideCollection == "")
                        // S'il n'y a pas du tout de trajet, on affiche la div noResult
                        if ((this.url) == baseUrl + "/created-rides?getData=1&startCreated=0&limitCreated=3") {
                        console.log('Pas du tout de trajet')
                        app.handleFail();
                        // Sinon cela veut dire que toutes les reqûetes ont été affichées via la pagination, on renvoit true pour stopper le script à la prochaine boucle
                        } else {
                            reachedMaxCreated = true;
                        }
                    // S'il reste des trajets à afficher
                    else {
                        // Tu caches la div noResult dans l'ajax container si elle s'est déjà affichée
                        $('#js-show-noResult').hide();
                        // Et tu affiches les trajets AJAX demandés
                        for (rideIndex in rideCollection.rides) {
                            var currentRide = rideCollection.rides[rideIndex];
                            var returnDate = currentRide.returnDate;
                            // On conditionne l'affichage si c'est un trajet aller
                            if (returnDate == '1900-01-01T00:00:00+00:00') {
                                // On génère un nouveau trajet avec la méthode generateSingleCreatedRideElement
                                var rideElement = app.generateSingleCreatedRideElement(currentRide);
                                // Et on ajoute ce nouvel élement de type <template> dans le container prévu à cet effet dans le DOM
                                // Cette étape est très importante : si on l'oublie, le navigateur ne pourra pas l'afficher
                                $('#ajax-rideContainer').append(rideElement);
                            // Ou un trajet retour
                            } else {
                                // On génère un nouveau trajet avec la méthode generateReturnCreatedRideElement
                                var rideElement = app.generateReturnCreatedRideElement(currentRide);
                                console.log(rideElement)
                                // Et on ajoute ce nouvel élement de type <template> dans le container prévu à cet effet dans le DOM
                                $('#ajax-rideContainer').append(rideElement);
                            }
                        }
                    }
                    // Incrémente le startCreated de 3 pour aller chercher les 3 trajets suivants
                    startCreated += limitCreated
                    // Ajoute les nouveaux trajets trouvés au DOM dans la div #ajax-rideContainer
                    $("#ajax-rideContainer").append(rideCollection.rides);
                    var DOMcreatedRides = $('.load-created-rides').length
                    console.log(DOMcreatedRides)
                    if (rideCollection.ridesTotal.length > DOMcreatedRides) {
                        var moreRides= $('<div class="text-center"><input id="js-more-created-rides" class="btn-lightgreen" type="button" value="Voir plus de trajets"/>');
                        $("#ajax-rideContainer").append(moreRides);
                    }
                },
                // Si error, cela veut dire que le user n'est plus connecté
                error: function() {
                    // On le redirige donc vers la page de connexion
                    window.location.replace("http://localhost/izisurf/RameneTaPlanche/symfony/public/connexion");
                },
            });
        };

        /********************************* SI CLIC SUR VOIR PLUS *********************************/

        $(document).on('click','#js-more-created-rides',function(){
            getCreatedRides();   
        }),
        $(document).on('click','#js-more-favourite-rides',function(){
            getOtherRides(event);   
        }),
        $(document).on('click','#js-more-accepted-rides',function(){
            getOtherRides(event);   
        }),
        $(document).on('click','#js-more-pending-rides',function(){
            getOtherRides(event);   
        }),
        $(document).on('click','#js-more-archived-rides',function(){
            getOtherRides(event);   
        }),

        /********************************* SI CLIC SUR AUTRES TRAJETS *********************************/

        $(".js-other-rides").click(function(event) {
            // On supprime les flash messages
            $('.alert-created-ride').remove();
            // Pour chaque clic, check son currentTarget, et exécute la méthode qui correspond
            $(event.currentTarget).each(function() {
                // Si le user clique sur un bouton qui a déjà la classe active, sors de là pour éviter une nouvelle requête ajax inutile
                if ($(event.currentTarget).hasClass("active")) {
                    return;   
                }        
                //Sinon traite la demande
                else {
                    if ($(event.currentTarget).hasClass("archived-rides")) {
                        reachedMaxArchived = false;
                        startArchived = 0;
                        getOtherRides(event);
                    } else if ($(event.currentTarget).hasClass("favourite-rides")) {
                        reachedMaxFavourites = false;
                        startFavourites = 0;
                        getOtherRides(event);
                    } else if ($(event.currentTarget).hasClass("accepted-rides")) {
                        reachedMaxAccepted = false;
                        startAccepted = 0;
                        getOtherRides(event);
                    } else if ($(event.currentTarget).hasClass("pending-rides")) {
                        reachedMaxPending = false;
                        startPending = 0;
                        getOtherRides(event);
                    } else  {
                        reachedMaxCreated = false;
                        startCreated = 0;
                        getCreatedRides();
                    }
                }
            })
        });

        function getOtherRides(event) {

            /* TRAJETS ARCHIVES */
            if ($(event.currentTarget).hasClass("archived-rides")|| $(event.target).attr("id") == "js-more-archived-rides") {
                $('.load-created-rides, .load-favourite-rides, .load-accepted-rides, .load-pending-rides').remove();
                if (reachedMaxArchived) {
                    return;
                }
                var baseUrl = window.location.href
                $.ajax(
                    {
                        url:  baseUrl + '/archived-rides',
                        method: 'GET',
                        dataType: 'json',
                        data: {
                            getData: 1,
                            startArchived: startArchived,
                            limitArchived: limitArchived,
                    },
                    success: function(rideCollection) {
                        console.log("ARCHIVEEEEEEEEEEEEEED")
                        $("#js-more-created-rides, #js-more-favourite-rides, #js-more-accepted-rides, #js-more-pending-rides, #js-more-archived-rides").remove()
                        $('#js-myride-h3').text("Trajets archivés");
                        $(".archived-rides").removeClass("list-group-item")
                        $(".archived-rides").addClass("active")
                        $(".created-rides, .favourite-rides, .accepted-rides, .pending-rides").removeClass("active")
                        $(".created-rides, .favourite-rides, .accepted-rides, .pending-rides").addClass("list-group-item")
                        if (rideCollection == "")
                            if ((this.url) == baseUrl + "/archived-rides?getData=1&startArchived=0&limitArchived=3") { 
                                console.log('Pas du tout de trajet')
                                app.handleFail();
                            } else {
                                reachedMaxArchived = true;
                            }
                        else { 
                             // Tu caches la div noResult dans l'ajax container si elle s'est déjà affichée
                            $('#js-show-noResult').hide();
                            // Et tu affiches les trajets AJAX demandés
                            for (rideIndex in rideCollection.rides) {
                                var currentRide = rideCollection.rides[rideIndex];
                                var returnDate = currentRide.returnDate;
                                // On conditionne l'affichage si c'est un trajet aller
                                if (returnDate == '1900-01-01T00:00:00+00:00') {
                                    // On génère un nouveau trajet avec la méthode generateSingleCreatedRideElement
                                    var rideElement = app.generateSingleCreatedRideElement(currentRide);
                                    // Et on ajoute ce nouvel élement de type <template> dans le container prévu à cet effet dans le DOM
                                    // Cette étape est très importante : si on l'oublie, le navigateur ne pourra pas l'afficher
                                    $('#ajax-rideContainer').append(rideElement);
                                // Ou un trajet retour
                                } else {
                                    // On génère un nouveau trajet avec la méthode generateReturnCreatedRideElement
                                    var rideElement = app.generateReturnCreatedRideElement(currentRide);
                                    console.log(rideElement)
                                    // Et on ajoute ce nouvel élement de type <template> dans le container prévu à cet effet dans le DOM
                                    $('#ajax-rideContainer').append(rideElement);
                                }
                            }
                        }
                        // Incrémente le startCreated de 3 pour aller chercher les 3 trajets suivants
                        startArchived += limitArchived
                        // Ajoute les nouveaux trajets trouvés au DOM dans la div #ajax-rideContainer
                        $("#ajax-rideContainer").append(rideCollection.rides);
                        var DOMcreatedRides = $('.load-archived-rides').length
                        if (rideCollection.ridesTotal.length > DOMcreatedRides) {
                            var moreRides= $('<div class="text-center"><input id="js-more-archived-rides" class="btn-lightgreen" type="button" value="Voir plus de trajets"/>');
                            $("#ajax-rideContainer").append(moreRides);
                        }
                    },
                    error: function() {
                        window.location.replace("http://localhost/izisurf/RameneTaPlanche/symfony/public/connexion");
                    },
                });
            /* TRAJETS FAVORIS */
            } else if ($(event.currentTarget).hasClass("favourite-rides") || $(event.target).attr("id") == "js-more-favourite-rides") {
                $('.load-created-rides, .load-accepted-rides, .load-pending-rides, .load-archived-rides').remove();
                if (reachedMaxFavourites) {
                    return;
                }
                var baseUrl = window.location.href
                $.ajax(
                    {
                        url:  baseUrl + '/favourite-rides',
                        method: 'GET',
                        dataType: 'json',
                        data: {
                            getData: 1,
                            startFavourites: startFavourites,
                            limitFavourites: limitFavourites,
                        },
                    success: function(rideCollection) {
                        console.log("FAVOUUUUUUUUUUUUUUUUUUUUUUUUUUUVOUVOUVOURITE")
                        $("#js-more-created-rides, #js-more-favourite-rides, #js-more-accepted-rides, #js-more-pending-rides, #js-more-archived-rides").remove()
                        $('#js-myride-h3').text("Trajets favoris");
                        $(".favourite-rides").removeClass("list-group-item")
                        $(".favourite-rides").addClass("active")
                        $(".created-rides, .accepted-rides, .pending-rides, .archived-rides").removeClass("active")
                        $(".created-rides, .accepted-rides, .pending-rides, .archived-rides").addClass("list-group-item")
                        if (rideCollection == "")
                            if ((this.url) == baseUrl + "/favourite-rides?getData=1&startFavourites=0&limitFavourites=3") { 
                            console.log('Pas du tout de trajet')
                            app.handleFail();
                            } else {
                                reachedMaxFavourites = true;
                            }
                        else {   
                            app.checkCollection(rideCollection.rides);
                            startFavourites += limitFavourites;
                            // Ajoute les nouveaux trajets trouvés au DOM dans la div #ajax-rideContainer
                            $("#ajax-rideContainer").append(rideCollection.rides);
                            // Bouton afficher plus de trajets
                            var DOMcreatedRides = $('.load-favourite-rides').length
                            if (rideCollection.ridesTotal.length > DOMcreatedRides) {
                                var moreRides = $('<div class="text-center"><input id="js-more-favourite-rides" class="btn-lightgreen" type="button" value="Voir plus de trajets"/>');
                                $("#ajax-rideContainer").append(moreRides);
                            }
                        }
                    },
                    error: function() {
                        window.location.replace("http://localhost/izisurf/RameneTaPlanche/symfony/public/connexion");
                    },
                });
            /* DEMANDES ACCEPTEES */
            } else if ($(event.currentTarget).hasClass("accepted-rides")|| $(event.target).attr("id") == "js-more-accepted-rides") {
                $('.load-created-rides, .load-favourite-rides, .load-pending-rides, .load-archived-rides').remove();
                if (reachedMaxAccepted) {
                    return;
                }
                var baseUrl = window.location.href
                $.ajax(
                    {
                        url:  baseUrl + '/accepted-rides',
                        method: 'GET',
                        dataType: 'json',
                        data: {
                            getData: 1,
                            startAccepted: startAccepted,
                            limitAccepted: limitAccepted,
                        },
                    success: function(rideCollection) {
                        console.log("ACCCCCCEPTED")
                        $("#js-more-created-rides, #js-more-favourite-rides, #js-more-accepted-rides, #js-more-pending-rides, #js-more-archived-rides").remove()
                        $('#js-myride-h3').text("Demandes acceptées");
                        $(".accepted-rides").removeClass("list-group-item")
                        $(".accepted-rides").addClass("active")
                        $(".created-rides, .favourite-rides, .pending-rides, .archived-rides").removeClass("active")
                        $(".created-rides, .favourite-rides, .pending-rides, .archived-rides").addClass("list-group-item")
                        if (rideCollection == "")
                            if ((this.url) == baseUrl + "/accepted-rides?getData=1&startAccepted=0&limitAccepted=3") { 
                            console.log('Pas du tout de trajet')
                            app.handleFail();
                            } else {
                                reachedMaxAccepted = true;
                            }
                        else {  
                            app.checkCollection(rideCollection.rides)
                            startAccepted += limitAccepted;
                            $("#ajax-rideContainer").append(rideCollection.rides);
                            var DOMcreatedRides = $('.load-accepted-rides').length
                            console.log(DOMcreatedRides)
                            if (rideCollection.ridesTotal.length > DOMcreatedRides) {
                                var moreRides = $('<div class="text-center"><input id="js-more-accepted-rides" class="btn-lightgreen" type="button" value="Voir plus de trajets"/>');
                                $("#ajax-rideContainer").append(moreRides);
                            }
                        }
                    },
                    error: function() {
                        window.location.replace("http://localhost/izisurf/RameneTaPlanche/symfony/public/connexion");
                    },
                });
            /* DEMANDES EN ATTENTE */
            } else if ($(event.currentTarget).hasClass("pending-rides")|| $(event.target).attr("id") == "js-more-pending-rides") {
                $('.load-created-rides, .load-favourite-rides, .load-accepted-rides, .load-archived-rides').remove();
                if (reachedMaxPending) {
                    return;
                }
                var baseUrl = window.location.href
                $.ajax(
                    {
                        url:  baseUrl + '/pending-rides',
                        method: 'GET',
                        dataType: 'json',
                        data: {
                            getData: 1,
                            startPending: startPending,
                            limitPending: limitPending,
                    },
                    success: function(rideCollection) {
                        console.log("PEEEEEEEENDING")
                        $("#js-more-created-rides, #js-more-favourite-rides, #js-more-accepted-rides, #js-more-pending-rides, #js-more-archived-rides").remove()
                        $('#js-myride-h3').text("Demandes en attente");
                        $(".pending-rides").removeClass("list-group-item")
                        $(".pending-rides").addClass("active")
                        $(".created-rides, .favourite-rides, .accepted-rides, .archived-rides").removeClass("active")
                        $(".created-rides, .favourite-rides, .accepted-rides, .archived-rides").addClass("list-group-item")
                        if (rideCollection == "")
                            if ((this.url) == baseUrl + "/pending-rides?getData=1&startPending=0&limitPending=3") { 
                            console.log('Pas du tout de trajet')
                            app.handleFail();
                            } else {
                                reachedMaxPending = true;
                            }
                        else {  
                            app.checkCollection(rideCollection.rides)
                            startPending += limitPending;
                            // PB si le trajet est à la fois en fav et en attente, il n'y aura pas de load-pending-rides, mais que load-favourite-rides
                            $("#ajax-rideContainer").append(rideCollection.rides);
                            if ($('.load-favourite-rides').length > 0) {
                                $('.load-favourite-rides').addClass('load-pending-rides')
                                $('.load-favourite-rides').removeClass('load-favourite-rides')
                            }
                            var DOMcreatedRides = $('.load-pending-rides').length
                            if (rideCollection.ridesTotal.length > DOMcreatedRides) {
                                var moreRides= $('<div class="text-center"><input id="js-more-pending-rides" class="btn-lightgreen" type="button" value="Voir plus de trajets"/>');
                                $("#ajax-rideContainer").append(moreRides);
                            }
                        }
                    },
                    error: function() {
                        window.location.replace("http://localhost/izisurf/RameneTaPlanche/symfony/public/connexion");
                    },
                });
            }

        };
    },

    checkCollection:function(rideCollection) {
        //Tu caches la div noResult dans l'ajax container si elle s'est déjà affichée
        $('#js-show-noResult').hide();
        // Et tu affiches les trajets AJAX demandés
        for (rideIndex in rideCollection) {
            var currentRide = rideCollection[rideIndex];
            var returnDate = currentRide.ride.returnDate;
            // On conditionne l'affichage si c'est un trajet aller
            if (returnDate == '1900-01-01T00:00:00+00:00') {
                // On génère un nouveau trajet avec la méthode generateSingleRideElement
                var rideElement = app.generateSingleRideElement(currentRide);
                // Et on ajoute ce nouvel élement de type <template> dans le container prévu à cet effet dans le DOM
                // Cette étape est très importante : si on l'oublie, le navigateur ne pourra pas l'afficher
                $('#ajax-rideContainer').append(rideElement);
            // Ou un trajet retour
            } else {
                // On génère un nouveau trajet avec la méthode generateReturnRideElement
                var rideElement = app.generateReturnRideElement(currentRide);
                // Et on ajoute ce nouvel élement de type <template> dans le container prévu à cet effet dans le DOM
                $('#ajax-rideContainer').append(rideElement);
            }
        }
    },

    /********************************* GENERATE CREATED RIDES ONLY *********************************/

    // Generate a single ride (createdRides + archived)
    generateSingleCreatedRideElement:function(currentRide) {
        console.log('ALLER SIMPLE RIDE AJAAAAAAAAX');

        var newSingleRide = $('#js-show-others-single').contents().clone();

        var today = new Date();
        var dd = String(today.getDate()).padStart(2, '0');
        var mm = String(today.getMonth() + 1).padStart(2, '0');
        var yyyy = today.getFullYear();
        today = yyyy + '-' + mm + '-' + dd;

        if (currentRide.departureDate < today) {
            newSingleRide.find('.js-ride-type-single').toggleClass("js-ride-type-single load-archived-rides")
        } else {
            newSingleRide.find('.js-ride-type-single').toggleClass("js-ride-type-single load-created-rides")
        }

        moment.locale('fr')
        var departureDateFormat = moment(currentRide.departureDate).format('ddd D MMM')
        var departureHourFormat = moment(currentRide.departureHour).format('LT')

        newSingleRide.find('.js-single-ride-slug').attr("href", "/RameneTaPlanche/symfony/public/summary/ride/" + currentRide.id);
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

    // Generate a return ride (createdRides + archived)
    generateReturnCreatedRideElement:function(currentRide) {
        
        console.log('ALLER RETOUR RIDE AJAAAAAAAAX');

        var newReturnRide = $('#js-show-others-return').contents().clone();

        var today = new Date();
        var dd = String(today.getDate()).padStart(2, '0');
        var mm = String(today.getMonth() + 1).padStart(2, '0');
        var yyyy = today.getFullYear();
        today = yyyy + '-' + mm + '-' + dd;

        if (currentRide.departureDate < today) {
            newReturnRide.find('.js-ride-type-return').toggleClass("js-ride-type-return load-archived-rides")
        } else {
            newReturnRide.find('.js-ride-type-return').toggleClass("js-ride-type-return load-created-rides")
        }

        moment.locale('fr')
        var departureDateFormat = moment(currentRide.departureDate).format('ddd D MMM')
        var departureHourFormat = moment(currentRide.departureHour).format('LT')
        var returnDateFormat = moment(currentRide.returnDate).format('ddd D MMM')
        var returnHourFormat = moment(currentRide.returnHour).format('LT')

        newReturnRide.find('.js-return-ride-slug').attr("href", "/RameneTaPlanche/symfony/public/summary/ride/" + currentRide.id);
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

    /********************************* GENERATE OTHERS *********************************/

    // Generate a single ride (others)
    generateSingleRideElement:function(currentRide) {
        
        console.log('ALLER SIMPLE RIDE AJAAAAAAAAX');
        
        var newSingleRide = $('#js-show-others-single').contents().clone();

        if (currentRide.isFavorite) {
            newSingleRide.find('.js-ride-type-single').toggleClass("js-ride-type-single load-favourite-rides")
        } else if ((currentRide.status.name) == "En attente") {
            newSingleRide.find('.js-ride-type-single').toggleClass("js-ride-type-single load-pending-rides")
        } else {
            newSingleRide.find('.js-ride-type-single').toggleClass("js-ride-type-single load-accepted-rides")
        }

        moment.locale('fr')
        var departureDateFormat = moment(currentRide.ride.departureDate).format('ddd D MMM')
        var departureHourFormat = moment(currentRide.ride.departureHour).format('LT')

        newSingleRide.find('.js-single-ride-slug').attr("href", "/RameneTaPlanche/symfony/public/summary/ride/" + currentRide.ride.id);
        newSingleRide.find('.js-single-cityDeparture').text(currentRide.ride.cityDeparture);
        newSingleRide.find('.js-single-spotArrival').text(currentRide.ride.spot.name);
        newSingleRide.find('.js-single-departureDate').text(departureDateFormat);
        newSingleRide.find('.js-single-departureHour').text(departureHourFormat);
        newSingleRide.find('.js-single-boardSizeMax').text(currentRide.ride.boardSizeMax);
        newSingleRide.find('.js-single-driverFirstname').text(currentRide.ride.driver.firstname + ', ');
        newSingleRide.find('.js-single-driverLevel').text(currentRide.ride.driver.level.name);
        newSingleRide.find('.js-single-price').text(currentRide.ride.price);
        newSingleRide.find('.js-single-driver-picture').attr("src", "/RameneTaPlanche/symfony/public/assets/images/users/" + currentRide.ride.driver.filename);

        if (currentRide.ride.availableSeat == 1) {
            newSingleRide.find('.js-single-availableSeat').html('<i class="fas fa-user"></i>')
        } else if (currentRide.ride.availableSeat == 2) {
            newSingleRide.find('.js-single-availableSeat').html('<i class="fas fa-user"></i><span> <i class="fas fa-user"></i></span></span>')
        } else if (currentRide.ride.availableSeat == 3) {
            newSingleRide.find('.js-single-availableSeat').html('<i class="fas fa-user"></i><span> <i class="fas fa-user"></i></span><span> <i class="fas fa-user"></i></span>')
        } else {
            newSingleRide.find('.js-single-availableSeat').html('<i class="fas fa-user"></i><span> <i class="fas fa-user"></i></span><span> <i class="fas fa-user"></i><span> <i class="fas fa-plus"></i></span>')
        }

        if (currentRide.ride.isSameGender == 1) {
            newSingleRide.find('.js-single-isSameGender').text("Trajet 100% masculin")
        } else if (currentRide.ride.isSameGender == 2 ) {
            newSingleRide.find('.js-single-isSameGender').text("Trajet 100% féminin")
        } else {
            newSingleRide.find('.js-single-isSameGender').text("Trajet mixte")
        }

        return newSingleRide;

    },

    // Generate a return ride (others)
    generateReturnRideElement:function(currentRide) {
        
        console.log('ALLER RETOUR RIDE AJAAAAAAAAX');

        var newReturnRide = $('#js-show-others-return').contents().clone();

        if (currentRide.isFavorite) {
            newReturnRide.find('.js-ride-type-return').toggleClass("js-ride-type-return load-favourite-rides")
        } else if ((currentRide.status.name) == "En attente") {
            newReturnRide.find('.js-ride-type-return').toggleClass("js-ride-type-return load-pending-rides")
        } else {
            newReturnRide.find('.js-ride-type-return').toggleClass("js-ride-type-return load-accepted-rides")
        }

        moment.locale('fr')
        var departureDateFormat = moment(currentRide.ride.departureDate).format('ddd D MMM')
        var departureHourFormat = moment(currentRide.ride.departureHour).format('LT')
        var returnDateFormat = moment(currentRide.ride.returnDate).format('ddd D MMM')
        var returnHourFormat = moment(currentRide.ride.returnHour).format('LT')

        newReturnRide.find('.js-return-ride-slug').attr("href", "/RameneTaPlanche/symfony/public/summary/ride/" + currentRide.ride.id);
        newReturnRide.find('.js-return-cityDeparture').text(currentRide.ride.cityDeparture);
        newReturnRide.find('.js-return-spotArrival').text(currentRide.ride.spot.name);
        newReturnRide.find('.js-return-departureDate').text(departureDateFormat);
        newReturnRide.find('.js-return-departureHour').text(departureHourFormat);
        newReturnRide.find('.js-return-returnDate').text(returnDateFormat);
        newReturnRide.find('.js-return-returnHour').text(returnHourFormat);
        newReturnRide.find('.js-return-boardSizeMax').text(currentRide.ride.boardSizeMax);
        newReturnRide.find('.js-return-driverFirstname').text(currentRide.ride.driver.firstname + ', ');
        newReturnRide.find('.js-return-driverLevel').text(currentRide.ride.driver.level.name);
        newReturnRide.find('.js-return-price').text(currentRide.ride.price);
        newReturnRide.find('.js-return-driver-picture').attr("src", "/RameneTaPlanche/symfony/public/assets/images/users/" + currentRide.ride.driver.filename);

        if (currentRide.ride.availableSeat == 1) {
            newReturnRide.find('.js-return-availableSeat').html('<i class="fas fa-user"></i>')
        } else if (currentRide.ride.availableSeat == 2) {
            newReturnRide.find('.js-return-availableSeat').html('<i class="fas fa-user"></i><span> <i class="fas fa-user"></i></span></span>')
        } else if (currentRide.ride.availableSeat == 3) {
            newReturnRide.find('.js-return-availableSeat').html('<i class="fas fa-user"></i><span> <i class="fas fa-user"></i></span><span> <i class="fas fa-user"></i></span>')
        } else {
            newReturnRide.find('.js-return-availableSeat').html('<i class="fas fa-user"></i><span> <i class="fas fa-user"></i></span><span> <i class="fas fa-user"></i><span> <i class="fas fa-plus"></i></span>')
        }

        if (currentRide.ride.isSameGender == 1) {
            newReturnRide.find('.js-return-isSameGender').text("Trajet 100% masculin")
        } else if (currentRide.ride.isSameGender == 2 ) {
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