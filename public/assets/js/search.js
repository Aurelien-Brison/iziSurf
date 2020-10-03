var app = {
    init: function() {
      // Ecoute sur la checkbox
      $("#search-checkbox").change(app.handleCheckbox);
      // Ecoute sur la checkbox addCar
      $("#addCar-checkbox").click(app.handleAddCarCheckbox);
    },
    // Action sur la checkbox
    handleCheckbox: function(event) {
        //console.log('le fichier est chargé')
        if (this.checked) {
          //console.log('coché')
          $("#return-search").removeAttr("hidden", "hidden")
      } else {
          $("#return-search").attr("hidden", "hidden")
          //console.log('décoché')
      }
    },
    // Action sur la checkbox ADDCAR
    handleAddCarCheckbox: function(event) {
      
  },
  };
  // Chargement du DOM
  document.addEventListener('DOMContentLoaded', app.init);
