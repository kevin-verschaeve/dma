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
    
    $('#sel_communes').change(function() {
        var commune = $('#sel_communes').val();
        var url = commune == 0 ? '' : '/commune/'+commune;
        window.location = '/index/graphique'+url;
    });
   
    $('#blocFormTonnage form > .divform:last-child').prepend('<button type="button" id="show" name="showDates">Entrer une periode</button>');
    
    attendAction();    
});
function change() {
    var matiere = $('#sel_matiere').val();
    $.ajax({
        method : 'get',
        url: '/index/tonnageajax',
        data : {sel_matiere:matiere, ajax:true},
        success : function(data) {
            $('#blocFormTonnage').empty();
            $('#blocFormTonnage').html(data);
            $('#blocFormTonnage form > .divform:last-child').prepend('<button type="button" id="show" name="showDates">Entrer une periode</button>');
        }
    });
}
function attendAction() {
    // au click sur id=show
    $('#show').on('click', function() {
        $('.divdate').fadeIn(500);
        // on verifie si on est sur IE, pour ajuster un effet, et l'affichage des dates
        // qui n'allaient pas
        if(checkIE()) {
            $('#blocFormTonnage .divdate').css('display', 'inline');
        }
        
        // supprime le bouton que l'on vient de cliqué
        $('#show').remove();
        // créé le nouveau bouton (celui qui servira a cacher ce qu'on vien de montrer)
        $('#blocFormTonnage form > .divform:last-child').prepend('<button type="button" id="hide" name="hideDates">Annuler</button>');
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
        // créé le nouveau bouton, qui sert a faire apparaitre les dates
        $('#blocFormTonnage form > .divform:last-child').prepend('<button type="button" id="show" name="showDates">Entrer une periode</button>');
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

function doDatatables() {
    $('#datatable').dataTable({
        "bJQueryUI": true,
        "bProcessing" : true,
        "sPaginationType": "full_numbers",
        // appelé a chaque création de ligne
        "fnRowCallback": function( nRow, aData ) {
            // recupere la valeur de la colonne tonnage
            // remplace les , en . pour pouvoir convertir en float
            // converti en float
            var TenCours = aData[4];
            var Treplace = TenCours.replace(',','.');
            var tonnageEnCours = parseFloat(Treplace);
            
            // recupere la valeur de l'ancien tonnage (avant d'ajouter celui de cette ligne)
            // et fait les meme conversions que l'autre tonnage
            var oldT = $('#calcule').text();
            var oldTreplace = oldT.replace(',','.');
            var oldTonnage = parseFloat(oldTreplace);
            
            // calcule le nouveau tonnage
            var nvTonnage = oldTonnage + tonnageEnCours;
            // arrondi a 3 chiffre apres la virgule, insert le nouveau tonnage dans la page
            $('#calcule').html(nvTonnage.toFixed(3));
            return nRow;
        },
        "fnPreDrawCallback" : function() {
            // reinitialise le tonnage a 0
            $('#calcule').empty();
            $('#calcule').text('0');
        }
    });
}