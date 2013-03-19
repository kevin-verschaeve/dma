$(document).ready(function() {
    ready();    
});
function ready() {
    // ajoute un datepicker a tous les inputs ayant 'class="datepicker" '
    $( ".datepicker" ).datepicker(  {
        dateFormat: "dd/mm/yy" ,
        showButtonPanel : true,
        changeMonth : true,
        changeYear : true 
    });
    
    // quand le bouton radio selectionné change
    $('.rad').change(function() {
        radio = $('.rad:checked').val();
        
        // appelle l'action passée (premier parametre), en passant les arguments
        // (deuxieme parametre), et execute la fonction de callback, une fois l'action terminée        
        $.get('/palmares/infos', {radio:radio, ajax:true}, function(data) {
            // data contient ce qui est affiché par l'action
            // insert le contenu de la vue appelée par l'action, dans le div id="recup"
            $('.prems').remove();
            $('#recup').html(data);
        });
    });
    $('.selec').change(function() {
        matiere = $('.selec').val();
        $.get('/index/tonnageajax', {sel_matiere:matiere, ajax:true}, function(data) {
            $('.formConteneur').empty();
            $('.formConteneur').html(data);
            ready();
        });
    });
    
}
function showDates() {
    $('.divdate').effect('bounce', {times : 3}, 500);
    $('#showHideDates').empty();
    $('#showHideDates').html('<a href="#" onclick="hideDates()">Annuler</a>');
}
function hideDates() {
    $('#dateDebut').val("");
    $('#dateFin').val("");
    $('.divdate').hide('drop', { direction: "up" }, 200);
    $('#showHideDates').empty();
    $('#showHideDates').html('<a href="#" onclick="showDates()">Entrer une periode</a>');
}