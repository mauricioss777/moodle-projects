var textoAtual = $('#observacao').val();
var textoAtualAux = "";

var textoOrientacao = $('#registroOrientacaoTutor').val();
var textoOrientacaolAux = "";

$(".activity-button").click(function() {
    switch ($(this).data('status')) {
        case 0:
            $(this).data('status', 1);
            $(this).addClass('fa-check');
            $(this).parent('td').addClass('has-success');
            break;
        case 1:
            $(this).data('status', 2);
            $(this).parent('td').removeClass('has-success');
            $(this).removeClass('fa-check');
            $(this).addClass('fa-comments');
            break;
        case 2:
            $(this).data('status', 3);
            $(this).parent('td').removeClass('has-success');
            $(this).parent('td').addClass('has-error');
            $(this).removeClass('fa-comments');
            
            $(this).addClass('fa-question');
            break;
        case 3:
            $(this).data('status', 4);
            $(this).parent('td').removeClass('has-error');
            $(this).removeClass('fa-question');
            $(this).addClass('fa-ellipsis-h');
            break;
        case 4:
            $(this).data('status', 5);
            $(this).removeClass('fa-ellipsis-h');
            $(this).addClass('fa-bell');
            $(this).parent('td').addClass('has-warning');
            break;
	case 5: 
            $(this).data('status', 1);
            $(this).removeClass('fa-bell');
            $(this).addClass('fa-check');
            $(this).parent('td').removeClass('has-warning');
            $(this).parent('td').addClass('has-success');
        default:
            break;
    }

    $.post('ajax.php?controler=disciplina&action=updateStatus(' + $(this).data('registro') + ',' + $(this).data('status') + ')', function(data) {
    })
            .fail(function() {
                alert('Não foi possível conectar com o servidor.\n Sua alteração não foi aplicada.');
            });
});

$(".table-checklist th").dblclick(function() {
	var data = $(this).data('dataaula');
	var disciplinaid = $('#disciplinaid').val();
	var newclass="";
	var registro="";

	if($(this).hasClass('ead')){
	    newclass="presencial";
            $(this).removeClass('ead');
    	    $(this).addClass('presencial');
	    $('.table-checklist td').each(function(){
	        if($(this).data('dataaula') == data){
	       	    $(this).removeClass('ead');
		    $(this).addClass('presencial');
	        }
	    });
	}else{
	    newclass="ead";
            $(this).removeClass('presencial');
    	    $(this).addClass('ead');
	    $('.table-checklist td').each(function(){
	        if($(this).data('dataaula') == data){
	       	    $(this).removeClass('presencial');
		    $(this).addClass('ead');
	        }
	    });
	}
 	$.post("ajax.php?controler=disciplina&action=updatePresencial(" + disciplinaid + ",'" + data  + "','" + newclass + "')", function(data) { }).fail(function() {
	    alert('Não foi possível conectar com o servidor.\n Sua alteração não foi aplicada.');
        });

});
setInterval(function() {
    textoAtualAux = $('#observacao').val();
    if (textoAtual != textoAtualAux) {
        $.post('ajax.php?controler=disciplina&action=updateObservacao(' + $('#observacao').data('disciplina') + ')', {valor: $('#observacao').val()}, function(data) {
            $('#status').html(data);
        })
                .fail(function() {
                    var msg = '<div class="alert alert-danger">';
                    msg += '<ul class="fa-ul">';
                    msg += '<li>';
                    msg += '<i class="fa fa-exclamation-triangle fa-li fa-lg"></i>';
                    msg += 'Erro ao atualizar as observações.';
                    msg += '</li>';
                    msg += '</ul>';
                    msg += '</div>';
                    $('#status').html(msg);
                });
    } else {
        $('#status').text('');
    }
    textoAtual = textoAtualAux;
}, 5000);

setInterval(function() {
    textoOrientacaoAux = $('#registroOrientacaoTutor').val();
    if (textoOrientacao != textoOrientacaoAux) {
        $.post('ajax.php?controler=disciplina&action=updateOrientacao(' + $('#registroOrientacaoTutor').data('disciplina') + ')', {valor: $('#registroOrientacaoTutor').val()}, function(data) {
            $('#status-orientacao').html(data);
        })
                .fail(function() {
                    var msg = '<div class="alert alert-danger">';
                    msg += '<ul class="fa-ul">';
                    msg += '<li>';
                    msg += '<i class="fa fa-exclamation-triangle fa-li fa-lg"></i>';
                    msg += 'Erro ao atualizar as orientações.';
                    msg += '</li>';
                    msg += '</ul>';
                    msg += '</div>';
                    $('#status-orientacao').html(msg);
                });
    } else {
        $('#status-orientacao').text('');
    }
    textoOrientacao = textoOrientacaolAux;
}, 5000);

$('#dia-semana').on('change', function(){
	var dia = this.value;
	var header = $('thead')[0].outerHTML;
	var content = header+"<tbody>";

	$('tbody tr').each(function(){
		//console.log( $(this) );
	    if( dia == "Todos" || dia == $(this).find('td')[2].innerHTML ){
			$(this).css('display','block')
		}else{
			$(this).css('display', 'none')
		}
		content += $(this)[0].outerHTML;	

	});
	content += "</tbody>";
	$('table').html(content);
	//$(this).html(content);	
});
