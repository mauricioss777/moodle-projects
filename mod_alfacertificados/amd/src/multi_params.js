
define(['jquery', 'core/url'], function($, _url) {

    $('select[name ="type"]').on('change', function(){
        searchRecord('params', {'value' : $(this).val(), 'course' : findGetParameter('course')}, $('#params') );
    });

    function searchRecord( method, payload, target ){
        $.ajax({
            method: 'POST',
            url: _url.relativeUrl('mod/alfacertificados/ajax.php', { 'method':method } ),
            data: payload 
        })
        .done( function(data){ buildOption(data, target) } ) 
        .fail( function(msg){ buildOption(msg.responseText, target) } );
    }

    function buildOption(data, target){
        $('#params').html(data);
    }

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
