/**
 * Created by Pierre-Yves on 26/09/2015.
 */

var initCommand = function(){
    $('[data-command]').click(function(ev){
        var hash = $(this).attr("href");
        $("div#cmdresult").load( publishUri, {
            'file' : hash
        }, function( response, status, xhr ) {
            if ( status == "error" ) {
                var msg = "une erreur est survenue : ";
                $( "div#cmdresult" ).html( msg + xhr.status + " " + xhr.statusText +" / Demande : " + hash);
            }
        });
        ev.preventDefault();
        return false;
    });
};
$(document).ready(function(){
    $('[data-toggle="tooltip"]').tooltip();

    initCommand();
});
