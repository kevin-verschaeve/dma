$(document).ready(function() {
    // ajoute un datepicker a tous les inputs ayant 'class="datepicker" '
    $( ".datepicker" ).datepicker(  {
        showButtonPanel : true,
        changeMonth : true,
        changeYear : true 
    });
    
    // quand le bouton radio selectionné change
    $('.rad').change(function() {
        var radio = $('.rad:checked').val();
        // appelle l'action passée (premier parametre), en passant les arguments
        // (deuxieme parametre), et execute la fonction de callback, une fois l'action terminée        
        $.get('/palmares/infos', {radio:radio, ajax:true}, function(data) { 
            // .prems est le premier tableau affiché a l'arrivée sur la page
            // on le supprime donc pour faire place au nouveau, regenéré dans la vue infos.phtml
            $('.prems').remove();
            
            $('#removeBouton').empty();
            // data contient ce qui est affiché par l'action
            // insert le contenu de la vue appelée par l'action, dans le div id="recup"
            $('#recup').empty();
            $('#recup').html(data);
        });
    });
    // au changement de valeur du select
    $('.selec').change(function() {
        // recupere la valeur selectionnée
        var matiere = $('.selec').val();
        // appel a la fonction permettant de recuperer les infos en fonction de la matiere
        $.get('/index/tonnageajax', {sel_matiere:matiere, ajax:true}, function(data) {
            // vide le div.formConteneur
            $('.formConteneur').empty();
            // y place la vue de tonnageajax
            $('.formConteneur').html(data);
        });
    });
    
    $('#sel_communes').change(function() {
        var commune = $('#sel_communes').val();
        window.location = '/index/graphique/commune/'+commune;
    });
    
       
    $('.formConteneur .divform:last-child').prepend('<button type="button" id="show" name="showDates">Entrer une periode</button>');
    $('.formConteneur .divdate .divform:last-child button').remove();
    attendAction();
    
});
function attendAction() {
    // au click sur id=show
    $('#show').on('click', function() {
        // on verifie si on est sur IE, pour ajuster un effet, et l'affichage des dates
        // qui n'allaient pas
        if(checkIE()) {
            $('.divdate').fadeIn(500);
            $('.formConteneur .divdate').css('display', 'inline-block');
        }
        else {
            // un autre effet pour les autres navigateurs
            $('.divdate').effect('bounce', {times : 3}, 500);
        }
        
        // supprime le bouton que l'on vient de cliqué
        $('#show').remove();
        $('#sub').css('margin-top', '5px');
        // créé le nouveau bouton (celui qui servira a cacher ce qu'on vien de montrer)
        $('.formConteneur .divform:last-child').prepend('<button type="button" id="hide" name="hideDates">Annuler</button>');
        $('.formConteneur .divdate .divform:last-child button').remove();
        // rappel de cette fonction pour attendre un nouveau click
        attendAction();
    });
    
    // au click sur id="hide"
    $('#hide').on('click', function() {
        // vide les champs de dates
        $('#dateDebut').val('');
        $('#dateFin').val('');
        // cache les champs date avec un effet
        $('.divdate').hide('drop', { direction: "up" }, 300);
        
        // supprime le bouton qui vient d'être cliqué
        $('#hide').remove();
        $('#sub').css('margin-top', '0');
        // créé le nouveau bouton, qui sert a faire apparaitre les dates
        $('.formConteneur .divform:last-child').prepend('<button type="button" id="show" name="showDates">Entrer une periode</button>');
        $('.formConteneur .divdate .divform:last-child button').remove();
        // attend la prochaine action
        attendAction();
    });    
    
    $('.cbsite').click(function() {
        idSite = $(this).val();
        etat = $(this).is(':checked') ? 1 : 0;
        idCb = $(this).parent().attr('id');
        
        $.ajax({
            url: '/index/changeconteneur',
            data : {site:idSite, etat:etat, ajax:true},
            beforeSend : function() {
                $('#'+idCb).append('<img id="tempo" width="20px" height="20px" style="position:absolute; right:15px;" src="/img/load.gif"/>');
            }
        }).done(function() {
           $('#tempo').remove();
        });
    });
    
}
/**
 * 
 * retourne true si le navigateur est internet explorer (toutes versions), false sinon
 */
function checkIE() {
    var regexp = /MSIE (\d+\.\d+);/;
    return regexp.test(navigator.userAgent);
    
    /*  pour aller plus loin dans la detection de navigateur
    if (regexp.test(navigator.userAgent)) {
        var ieversion=new Number(RegExp.$1);
        if (ieversion <= 9)
        {
            alert('ie < 9');
        }
    }
    else {
        alert('pas ie');
    }*/
}