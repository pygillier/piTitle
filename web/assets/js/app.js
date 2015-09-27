/**
 * Created by Pierre-Yves on 26/09/2015.
 */

var initCommand = function(){
    $('[data-command]').click(function(ev){
        var hash = $(this).attr("href");
        var publishRequest = $.post(publishUri, {file:hash}, 'json');

        publishRequest.always(function(){
            $("div#cmdresult").html("<p>Request sent, waiting for reply...</p>")
        });

        publishRequest.fail(function(data){
            console.log(data.statusText);
            $("div#cmdresult").append("Request error : "+data.statusText+"!");

        });

        publishRequest.done(function(data){
            $("div#cmdresult").append("Request successful !");
            $("div#imgdisplay").html('<p><img src="'+data.thumb+'"/></p><p class="help-block">File : '+data.file+'</p>');
        });

        // Don't update browser state
        ev.preventDefault();
        return false;
    });
};

var fbiCheck = function() {

    $("span#fbicount").load( fbiCheckUri, function( response, status, xhr ) {
        if ( status == "error" ) {
            var msg = "une erreur est survenue : ";
            $( "span#fbicount" ).html( msg + xhr.status + " " + xhr.statusText);
        }
    });
};

var initPopover = function() {
    $('a[rel=popover]').popover({
        html: true,
        trigger: 'hover',
        content: function () {
            return '<img src="'+$(this).data('img') + '" />';
        }
    });
};

$(document).ready(function(){
    $('[data-toggle="tooltip"]').tooltip();

    initCommand();

    initPopover();

    fbiCheck();
    setInterval(fbiCheck, 10000);
});
