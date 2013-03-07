$(document).ready(function() {
    // ajoute un datepicker a tous les inputs ayant 'class="datepicker" '
    $( ".datepicker" ).datepicker({ dateFormat: "yy-mm-dd" });
    
    // quand le bouton radio selectionné change
    $('input[type="radio"]').change(function() {
        radio = $('.rad:checked').val();
        
        // appelle l'action passée en premier parametre, en passant les arguments
        // en deuxieme parametre, et execute la fonction de callback, une fois l'action terminée        
        $.get('/palmares/infos', {radio:radio, ajax:true}, function(data) {
            // data contient ce qui est affiché par l'action
            // insert le contenu de la vue appelée par l'action, dans le div id="recup"
            $('#first').remove();
            $('#recup').html(data);
        });
    });
});