$(document).ready(function() {    
    	
    // regles les options du datepicker
    $.datepicker.regional['fr'] = {
        closeText: 'Fermer',
        prevText: 'Précédent',
        nextText: 'Suivant',
        currentText: 'Aujourd\'hui',
        monthNames: ['Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'],
        monthNamesShort: ['Janv.','Févr.','Mars','Avril','Mai','Juin','Juil.','Août','Sept.','Oct.','Nov.','Déc.'],
        dayNames: ['Dimanche','Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'],
        dayNamesShort: ['Dim.','Lun.','Mar.','Mer.','Jeu.','Ven.','Sam.'],
        dayNamesMin: ['D','L','M','M','J','V','S'],
        weekHeader: 'Sem.',
        dateFormat: 'dd/mm/yy',
        firstDay: 1,
        isRTL: false,
        showMonthAfterYear: false,
        yearSuffix: '',
        showButtonPanel : true,
        changeMonth : true,
        changeYear : true
    };
    $.datepicker.setDefaults($.datepicker.regional['fr']);
    
    // ajoute un datepicker a tous les inputs ayant 'class="datepicker" '
    $( ".datepicker" ).datepicker();
    
    // quand le bouton radio selectionné change
    $('.rad').change(function() {
        var radio = $('.rad:checked').val();
        // appelle l'action passée (premier parametre), en passant les arguments
        // (deuxieme parametre), et execute la fonction de callback, une fois l'action terminée        
        $.get(getBaseUrl()+'palmares/infos',
            {
                radio:radio, 
                ajax:true, 
                beforeSend : function() {
                    $('#recup').empty().html('<div style="text-align:center"><img src="img/loading.gif" alt="loading"/></div>');
                }
            }, function(data) { 
                // .prems est le premier tableau affiché a l'arrivée sur la page
                // on le supprime donc pour faire place au nouveau, regenéré dans la vue infos.phtml
                $('.prems').remove();

                // data contient ce qui est affiché par l'action
                // insert le contenu de la vue appelée par l'action, dans le div id="recup"
                $('#recup').empty();
                $('#recup').html(data);
            }
        );
    });
    /*
    $('#sel_communes').change(function() {
        var commune = $(this).val();
        var url = commune == 0 ? '' : '/commune/'+commune;
        window.location = '/index/graphique'+url;
    });*/
    
    $('.cbsite').click(function() {
        idSite = $(this).val();
        etat = $(this).is(':checked') ? 1 : 0;
        idCb = $(this).parent().attr('id');
        
        $.ajax({
            // passer un nombre aléatoire empeche l'utiisation du cache
            // sinon le cache garde en mémoire l'etat initial des checkbox et ne les modifie pas
            url : getBaseUrl()+'index/changeconteneur/gruge/'+Math.random(),
            data : {site:idSite, etat:etat, ajax:true},
            beforeSend : function() {
                $('#'+idCb).append('<img id="tempo" width="20px" height="20px" style="position:absolute; right:15px;" src="../img/load.gif"/>');
            }
        }).done(function() {
           $('#tempo').remove();
        });
    });
    
    
    $('#Fimport').on('submit', function() {
        mat = $('#Fimport .mats:checked').val();
        if(mat == null || mat == '')
        {
            alert('Veuillez sélectionner une matière');
            return false;
        }
    });
    $('#fnvsite').on('submit', function() {
        mat = $('#fnvsite input[type="checkbox"]:checked').val();    
        if(mat == null || mat == '')
        {
            alert('Veuillez sélectionner au moins une matière');
            return false;
        }
    });
    
    $('#input_fichier').on('change', function() {
        $(this).css('color', '#000');
    });
    
     precharger_image(getBaseUrl()+'img/load.gif');
     precharger_image(getBaseUrl()+'img/loading.gif');     
});
function getBaseUrl() {
    var url = document.URL.substring(0, document.URL.indexOf('/public'));
    return url+'/public/';
}
function change() {
    var matiere = $('#sel_matiere').val();
    $.ajax({
        method : 'get',
        url: getBaseUrl()+'index/tonnageajax',
        data : {sel_matiere:matiere, ajax:true},
        beforeSend : function() {
            $('#nSite').empty();
        },
        success : function(data) {
            $('#blocFormTonnage').empty();
            $('#blocFormTonnage').html(data);
            $(".datepicker").datepicker();
        }
    });
}
function showdates() {
    $('.divdate').fadeIn(500);
    // supprime le bouton que l'on vient de cliquer
    $('#show').remove();
    // créé le nouveau bouton (celui qui servira a cacher ce qu'on vien de montrer)
    $('#blocFormTonnage form > .divform:last-child').prepend('<button type="button" id="hide" name="hideDates" onclick="hidedates()">Annuler</button>');
}
function hidedates() {
    // vide les champs de dates
    $('#dateDebut').val('');
    $('#dateFin').val('');
    // cache les champs date avec un effet
    $('.divdate').hide('drop', { direction: "up" }, 300);

    // supprime le bouton qui vient d'être cliqué
    $('#hide').remove();
    // créé le nouveau bouton, qui sert a faire apparaitre les dates
    $('#blocFormTonnage form > .divform:last-child').prepend('<button type="button" id="show" name="showDates" onclick="showdates()">Entrer une periode</button>');
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

function doDatatables(aoColumns) {
    //alert(aoColumns);
    oTable = $('#datatable').dataTable({
        "bJQueryUI": true,  // ajoute le style par defaut (jqueryUI) sur la table
        // affiche une information de chargement de la table si trop longue a charger
        "bProcessing" : true,
        "sPaginationType": "full_numbers",  // le mode de navigation dans les pages du tableau
        // le nombre de résultat que l'utilisateur peut choisir
        "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Tous"]],
        
        // appelé a la création du footer de la table
        "fnFooterCallback": function ( nRow, aaData, iStart, iEnd, aiDisplay ) 
        {            
            // recupere la ligne du footer
            var tr = nRow.getElementsByTagName('th');
            // recupere le th qui concerne le tonnage
            var thtonnage = tr[4];
            
            if( thtonnage.textContent !== "Utiliser ?") 
            {
                // parcours seulement les lignes affichées (a cause d'un filtre, tri...)
                TenCours = 0;
                var totalpage = 0;
                for ( var i=iStart ; i<iEnd ; i++ )
                {
                    // recupere le tonnage de la ligne en cours
                    TenCours = aaData[ aiDisplay[i] ][4];
                    // remplace les "," par des "." pour la conversion en float
                    TenCours = TenCours.replace(',','.');
                    // converti en float
                    TenCours = parseFloat(TenCours);

                    // ajoute le tonnage en cours au tonnage total de la page
                    totalpage += TenCours; 

                }

                // modifie le td du footer, en y placant le total recupéré
                /*
                var nCells = nRow.getElementsByTagName('th');
                nCells[4].innerHTML = totalpage.toFixed(3) + ' T <br>('+ total.toFixed(3) +' Total)';
                */
                
                // insere le tonnage des lignes affichées dans l'élément d'id='ttotal'
                // en l'arrondissant à 3 nombres apres la virgule
                $('#ttotal').html(totalpage.toFixed(3)+' T');
            }
        },
        // declarer le type des colonnes (null, laisse l'api décider)
        "aoColumns": [
            null,
            null,
            null,
            null,
            aoColumns,
            null
        ]
    });
    
    // permet de trier les nombres a virgules /*
    jQuery.fn.dataTableExt.oSort['numeric-comma-asc']  = function(a,b) {
	var x = (a == "-") ? 0 : a.replace( /,/, "." );
	var y = (b == "-") ? 0 : b.replace( /,/, "." );
	x = parseFloat( x );
	y = parseFloat( y );
	return ((x < y) ? -1 : ((x > y) ?  1 : 0));
    };
    
    jQuery.fn.dataTableExt.oSort['numeric-comma-desc'] = function(a,b) {
            var x = (a == "-") ? 0 : a.replace( /,/, "." );
            var y = (b == "-") ? 0 : b.replace( /,/, "." );
            x = parseFloat( x );
            y = parseFloat( y );
            return ((x < y) ?  1 : ((x > y) ? -1 : 0));
    };
    //  */
}

function creationpdf(bt) {
    $('#genpdf').remove();
    $(bt).before('<span id="genpdf">Création du fichier pdf en cours...<img alt="loading"  width="16" height="16" src="img/load.gif" alt="chargement" title="Chargement en cours..."/></span>');
}
// precharge une image pour qu'elle soit affichée des le 1er appel
// autrement elle n'apparait qu'au 2e
function precharger_image(url) {
    var img = new Image();
    img.src=url;
    return img;
}