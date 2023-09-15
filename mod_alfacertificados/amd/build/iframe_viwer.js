
// https://stackoverflow.com/questions/3846132/jquery-get-height-of-iframe-content-when-loaded
// https://stackoverflow.com/questions/751435/detecting-when-iframe-content-has-loaded-cross-browser

define(['jquery', 'core/url'], function($, url) {

    $('#pdf_document').on('load', function(e){ 
        $('#download').removeClass('hidden');
        $(this).css('height', '600px')
    });

    $('#users').on('change', function(){
        if( $(this).val() == 0 ){ return; }
        var id = findGetParameter('id');
        window.location.href = url.relativeUrl('mod/alfacertificados/view.php', {'id':id, 'userid':$(this).val()} );
    });

    function findGetParameter(parameterName) {
            var result = null,
                    tmp = [];
            location.search
                .substr(1)
                .split("&")
                .forEach(function (item) {
                              tmp = item.split("=");
                              if (tmp[0] === parameterName) result = decodeURIComponent(tmp[1]);
                            });
            return result;
    }

});
