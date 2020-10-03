var app = {

    init: function() {

        //Infos perso
        var personnalInfos = document.querySelectorAll('.profile-info');
        for (var personnalInfo of personnalInfos) {
            personnalInfo.addEventListener('click', function(event) {
                // On affiche la bonne colonne
                var ColPersonnalInfo = document.getElementById("profile-info-col");
                ColPersonnalInfo.style.display='block';
                // Et on masque les autres
                var ColOtherInfos = document.querySelectorAll("#profile-picture-col, #profile-car-col, #profile-car-add-col");
                for (var i = 0; i < ColOtherInfos.length; i++) {
                    ColOtherInfos[i].style.display='none';
                }
                // On affiche la bonne rubrique
                var personnalInfo = document.getElementById("showProfileInfo");
                personnalInfo.style.display='block';
                // Et on cache les autres
                var otherInfo = document.querySelectorAll("showProfilePicure, showProfileCar, showProfileCarAdd");
                for (var i = 0; i < otherInfo.length; i++) {
                    otherInfo[i].style.display='none';
                }
                // Puis on ajoute la classe active sur le menu
                var personnalInfo = document.querySelectorAll(".profile-info");
                for (var i = 0; i < personnalInfo.length; i++) {
                    personnalInfo[i].classList.replace("list-group-item", "active");
                }
                // Et on enlève les autres classes actives sur le menu
                var otherInfo = document.querySelectorAll(".profile-picture, .profile-car, .profile-car-add");
                for (var i = 0; i < otherInfo.length; i++) {
                    otherInfo[i].classList.replace("active", "list-group-item");
                }      
            })
        }

        //Photo de profil
        var profilePicture = document.querySelectorAll('.profile-picture');
        for (var picture of profilePicture) {
            picture.addEventListener('click', function(event) {
                // On affiche la bonne colonne
                var ColProfilePicture = document.getElementById("profile-picture-col");
                ColProfilePicture.style.display='block';
                // Et on masque les autres
                var ColOtherInfos = document.querySelectorAll("#profile-info-col, #profile-car-col, #profile-car-add-col");
                for (var i = 0; i < ColOtherInfos.length; i++) {
                    ColOtherInfos[i].style.display='none';
                }
                // On affiche la bonne rubrique
                var profilePicture = document.getElementById("showProfilePicture");
                profilePicture.style.display='block';
                // Et on cache les autres
                var otherInfo = document.querySelectorAll("showProfileInfo, showProfileCar, showProfileCarAdd");
                for (var i = 0; i < otherInfo.length; i++) {
                    otherInfo[i].style.display='none';
                }
                // Puis on ajoute la classe active sur le menu
                var profilePicture = document.querySelectorAll(".profile-picture");
                for (var i = 0; i < profilePicture.length; i++) {
                    profilePicture[i].classList.replace("list-group-item", "active");
                }
                // Et on enlève les autres classes actives sur le menu
                var otherInfo = document.querySelectorAll(".profile-info, .profile-car, .profile-car-add");
                for (var i = 0; i < otherInfo.length; i++) {
                    otherInfo[i].classList.replace("active", "list-group-item");
                }      
            })
        }

        // Voitures
        var profileCars = document.querySelectorAll('.profile-car');
        for (var profileCar of profileCars) {
            profileCar.addEventListener('click', function(event) {
                // On affiche la bonne colonne
                var ColProfileCar = document.getElementById("profile-car-col");
                ColProfileCar.style.display='block';
                // Et on masque les autres
                var ColOtherInfos = document.querySelectorAll("#profile-picture-col, #profile-info-col, #profile-car-add-col");
                for (var i = 0; i < ColOtherInfos.length; i++) {
                    ColOtherInfos[i].style.display='none';
                }
                // On affiche la bonne rubrique
                var profileCar = document.getElementById("showProfileCar");
                profileCar.style.display='block';
                // Et on cache les autres
                var otherInfos = document.querySelectorAll("showProfileInfo, showProfilePicture, showProfileCarAdd");
                for (var i = 0; i < otherInfos.length; i++) {
                    otherInfos[i].style.display='none';
                }
                // Puis on ajoute la classe active sur le menu
                var profileCar = document.querySelectorAll(".profile-car");
                for (var i = 0; i < profileCar.length; i++) {
                    profileCar[i].classList.replace("list-group-item", "active");
                }
                // Et on enlève les autres classes actives sur le menu
                var otherInfo = document.querySelectorAll(".profile-picture, .profile-info, .profile-car-add");
                for (var i = 0; i < otherInfo.length; i++) {
                    otherInfo[i].classList.replace("active", "list-group-item");
                }      
            })
        }

        // Ajouter une voiture
        var profileCarAdd = document.querySelectorAll('.profile-car-add');
        for (var car of profileCarAdd) {
            car.addEventListener('click', function(event) {
                // On affiche la bonne colonne
                var ColProfileCarAdd = document.getElementById("profile-car-add-col");
                ColProfileCarAdd.style.display='block';
                // Et on masque les autres
                var ColOtherInfos = document.querySelectorAll("#profile-picture-col, #profile-info-col, #profile-car-col");
                for (var i = 0; i < ColOtherInfos.length; i++) {
                    ColOtherInfos[i].style.display='none';
                }
                // On affiche la bonne rubrique
                var profileCarAdd = document.getElementById("showProfileCarAdd");
                profileCarAdd.style.display='block';
                // Et on cache les autres
                var otherInfos = document.querySelectorAll("showProfileInfo, showProfilePicture, showProfileCar");
                for (var i = 0; i < otherInfos.length; i++) {
                    otherInfos[i].style.display='none';
                }
                // Puis on ajoute la classe active sur le menu
                var profileCarAdd = document.querySelectorAll(".profile-car-add");
                for (var i = 0; i < profileCarAdd.length; i++) {
                    profileCarAdd[i].classList.replace("list-group-item", "active");
                }
                // Et on enlève les autres classes actives sur le menu
                var otherInfo = document.querySelectorAll(".profile-info, .profile-picture, .profile-car");
                for (var i = 0; i < otherInfo.length; i++) {
                    otherInfo[i].classList.replace("active", "list-group-item");
                }      
            })
        }
    }
};

// Chargement du DOM
document.addEventListener('DOMContentLoaded', app.init);