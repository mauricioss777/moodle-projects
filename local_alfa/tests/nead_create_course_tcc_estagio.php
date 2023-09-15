<?php
///**
// * Testa a atribuição em lote de usuários em um curso
// */
//require_once('soap_test.class.php');
//require_once('../classes/alfa.class.php');
//require_once('../../../config.php');
//
///*
// * page start
// */
//if(!is_siteadmin()){
//    die('Você não tem permissão de executar este script.');
//}
//
//?>
<!---->
<!--<!DOCTYPE html>-->
<!--<html>-->
<!--<head>-->
<!--	 <meta charset="UTF-8"> -->
<!--	<title>Criar ambientes TCC/Estágio</title>-->
<!--	<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>-->
<!--<style>-->
<!--*{-->
<!--	color: #333;-->
<!--	font-size: 14px;-->
<!--	font-family: verdana;-->
<!--}-->
<!--html, body-->
<!--{-->
<!--    height: 100%;-->
<!--    min-height: 100%;-->
<!--    margin: 0px;-->
<!--    padding: 0px;-->
<!--    overflow: hidden;-->
<!---->
<!--}-->
<!--body{-->
<!--    padding: 15px;-->
<!--	margin: auto;-->
<!--	text-align: center;-->
<!--}-->
<!--body *{-->
<!--	text-align: left;-->
<!--}-->
<!--form{-->
<!--	width: 50%;-->
<!--	height: 100%;-->
<!--}-->
<!--h3{-->
<!--	width: 50%;-->
<!--	border-bottom: solid 1px #777;-->
<!--}-->
<!--input, textarea{-->
<!--	width: 70%;-->
<!--	border: solid 1px #AAA;-->
<!--	margin-bottom: 10px;-->
<!--	padding: 5px;-->
<!--}-->
<!--textarea{-->
<!--	height: 200px;-->
<!--	resize: none;-->
<!--}-->
<!--label{-->
<!--	float:left;-->
<!--	width: 25%;-->
<!--}-->
<!--input#add-group{-->
<!--	display: block;-->
<!--	margin: auto;-->
<!--	text-align: center; -->
<!--	margin-top: 10px;-->
<!--	padding: 5px;-->
<!---->
<!--}-->
<!--#list-groups{-->
<!--	border-left: solid 2px #CCC;-->
<!--}-->
<!--.group{-->
<!--	border-bottom: solid 1px #CCC;-->
<!--	padding: 10px;-->
<!--}-->
<!--.group .teacher{-->
<!--	padding-left: 5px;-->
<!--	font-weight: bold;-->
<!--}-->
<!--.group .list-students{-->
<!--	margin-left: 25px;-->
<!---->
<!--	color: #777;-->
<!--}-->
<!--.student:hover{-->
<!--	background-color: #FFA;-->
<!--}-->
<!--.delete{-->
<!--	cursor: pointer;-->
<!--	color: blue;-->
<!--	float:right;-->
<!--}-->
<!--.delete:hover{-->
<!--	text-decoration: underline;-->
<!--}-->
<!--.add-student #student,-->
<!--.add-student input{-->
<!--	width: 180px;-->
<!--}-->
<!--.add-student #adduser{-->
<!--	margin-left: 20px;-->
<!--	text-align: center;-->
<!--}-->
<!---->
<!--#form-elements{-->
<!--	height: 100%;-->
<!--}-->
<!--#list-groups{-->
<!--	width: 49%;-->
<!--	height: 99%;-->
<!--	min-height: 99%;-->
<!--	overflow: auto;-->
<!--	position: absolute;-->
<!--	right: 0px;-->
<!--	top: 0px;-->
<!--	max-height: 99%;-->
<!--}-->
<!--#create-course,-->
<!--#update-course{-->
<!--	width: 200px;-->
<!--	text-align: center;-->
<!--	background-color: rgb(0, 90, 168);-->
<!--	color: rgb(255, 255, 255);-->
<!--	border-color: rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.25);-->
<!--	text-shadow: 0px -1px 0px rgba(0, 0, 0, 0.25);-->
<!--	box-shadow: 0px -2px 2px rgba(0, 0, 0, 0.05) inset, 0px 1px 2px rgba(0, 0, 0, 0.1);-->
<!--	border-radius: 0px;-->
<!--	text-transform: uppercase;-->
<!--	font-family: "Istok Web",Arial;-->
<!--	height: 40px;-->
<!--	background-image: linear-gradient(to bottom, rgb(0, 112, 168), rgb(0, 56, 168));-->
<!--	background-repeat: repeat-x;-->
<!--	font-size: 13px;-->
<!--	font-weight: bold;-->
<!--}-->
<!--#update-course{-->
<!--	display: none;-->
<!--}-->
<!--#create-course[disabled],-->
<!--#update-course[disabled]{-->
<!--	background-image: linear-gradient(to bottom, rgb(112, 112, 112), rgb(30, 30, 30));-->
<!--}-->
<!--#btn-add-course{-->
<!--	text-align: center;-->
<!--	height: 100%;-->
<!--	position: absolute;-->
<!--	right: 50%;-->
<!--	top: 90%;-->
<!--}-->
<!--#btn-add-course input{-->
<!--	cursor: pointer;-->
<!--}-->
<!--.duplicated{-->
<!--	color: #F00;-->
<!--}-->
<!--#created-course-information{-->
<!--	display: none;-->
<!--	padding: 20px;-->
<!--	border: solid 1px #CCC;-->
<!--	background-color: #CFC;-->
<!--	margin-top: 15px;-->
<!--	-webkit-border-radius: 15px;-->
<!--	-moz-border-radius: 15px;-->
<!--	border-radius: 15px;-->
<!--}-->
<!--#created-course-information a{-->
<!--	color: blue;-->
<!--}-->
<!--#course-updated{-->
<!--	display: none;-->
<!--	border: solid 1px #0F0;-->
<!--	background-color: #AFA;-->
<!--	padding: 20px;-->
<!--	margin: 20px;-->
<!--	text-align: center;-->
<!--	-webkit-border-radius: 15px;-->
<!--	-moz-border-radius: 15px;-->
<!--	border-radius: 15px;-->
<!--}-->
<!--@media (max-width: 979px){-->
<!--	label{-->
<!--		display: none;-->
<!--	}-->
<!--	input, textarea{-->
<!--		width: 95%;-->
<!--	}-->
<!--}-->
<!--</style>-->
<!--</head>-->
<!--<body>-->
<!--	<h3>Criar ambiente de TCC/Estágio</h3>-->
<!--	<form action = "nead_criar_curso_tcc_estagio.php">-->
<!--		-->
<!--		<label for="courseid">ID do curso no Moodle:</label>-->
<!--		<input type="number" id="courseid" name="courseid" placeholder="ID da disciplina no Moodle"/>-->
<!--		-->
<!--		<div id="form-elements">-->
<!--			<label for="coursename">Nome da disciplina:</label>-->
<!--			<input type="text" id="coursename" name="coursename" placeholder="Nome que o ambiente. Não é preciso adicionar referência."/>-->
<!---->
<!--			<label for="description">Descrição do rótulo 1:</label>-->
<!--			<input type="text" id="description" name="description" placeholder="Descrição do rótulo 1"/>-->
<!--			<fieldset name="add-group">-->
<!--				<legend>Adicionar Grupo</legend>-->
<!--				<label for="teacher">Professor:</label>-->
<!--				<input type="number" id="teacher" name="teacher" placeholder="Código do professor"/><br />		-->
<!---->
<!--				<label for="students">Estudantes:</label>-->
<!--				<textarea id="students" name="students" placeholder="Códigos dos alunos separados por ponto e vírgula"></textarea>-->
<!--				<input type="button" id="add-group" name ="addusers" value="Adicionar" />-->
<!--			</fieldset>-->
<!--			<div id="btn-add-course">-->
<!--				<input type="button" id="create-course" name ="createcourse" value="Criar Ambiente" />-->
<!--				<input type="button" id="update-course" name ="updatecourse" value="Atualizar Ambiente" />-->
<!--			</div>-->
<!--			<div id="created-course-information">-->
<!--				<span><b>Categoria: </b></span><span class="category" style="color: red;">AMBIENTES EM DESENVOLVIMENTO</span><br />-->
<!--				<span><b>Nome: </b></span><span class="coursename"></span><br />-->
<!--				<span><b>Data de encerramento: </b></span><span class="timeclosed"></span><br />-->
<!--				<span style="color: red;">Este ambiente está oculto.<br /> </span>-->
<!--			</div>-->
<!--			<div id="course-updated">-->
<!--				Ambiente atualizado com sucesso.-->
<!--			</div>-->
<!--			<p></p>-->
<!--			<div id="course-info">-->
<!---->
<!--			</div>-->
<!--		</div>-->
<!---->
<!--		<!-- listagem de grupos/ estudantes-->-->
<!--		<div id="list-groups">-->
<!--			<!-- Como a estrutura interna de cada grupo deve ser-->
<!--			<div class="group">-->
<!--				<div class="teacher" data-username="código do professor">#professor#</div>-->
<!--				<div class="list-students">-->
<!--					<div class="student" data-username="código do aluno">#Aluno# <span class="delete">Remover</span></div>-->
<!--					<div class="student" data-username="código do aluno">#Aluno# <span class="delete">Remover</span></div>-->
<!--					<div class="student" data-username="código do aluno">#Aluno# <span class="delete">Remover</span></div>-->
<!--					<div class="student" data-username="código do aluno">#Aluno# <span class="delete">Remover</span></div>-->
<!--					<div class="student" data-username="código do aluno">#Aluno# <span class="delete">Remover</span></div>-->
<!--					<div class="add-student"><input type="number" name="student" class="newstudent"><input type="button" id="adduser" name ="adduser" value="Adicionar Estudante" /></div>-->
<!--				</div>-->
<!--			</div>-->
<!--			 -->-->
<!--		</div>-->
<!--	</form>-->
<!---->
<!---->
<!--<script language="javascript">-->
<!--$(document).ready(function(){-->
<!--	/**-->
<!--	 * Função auxiliar que converte o timestemp para data.-->
<!--	 */-->
<!--	var __format_date = function(date, fmt) {-->
<!--	    function pad(value) {-->
<!--	        return (value.toString().length < 2) ? '0' + value : value;-->
<!--	    }-->
<!--	    return fmt.replace(/%([a-zA-Z])/g, function (_, fmtCode) {-->
<!--	        switch (fmtCode) {-->
<!--	        case 'Y':-->
<!--	            return date.getUTCFullYear();-->
<!--	        case 'M':-->
<!--	            return pad(date.getUTCMonth() + 1);-->
<!--	        case 'd':-->
<!--	            return pad(date.getUTCDate());-->
<!--	        case 'H':-->
<!--	            return pad(date.getUTCHours());-->
<!--	        case 'm':-->
<!--	            return pad(date.getUTCMinutes());-->
<!--	        case 's':-->
<!--	            return pad(date.getUTCSeconds());-->
<!--	        default:-->
<!--	            throw new Error('Unsupported format code: ' + fmtCode);-->
<!--	        }-->
<!--	    });-->
<!--	}-->
<!---->
<!--	/**-->
<!--	 * Busca as informações dos usuários por ajax-->
<!--	 */-->
<!--	var func_get_users_information = function(usernames){-->
<!--	    -->
<!--	    var users = [];-->
<!--	    if($.isArray(usernames)){-->
<!--		    for (var username of usernames){-->
<!--		        users.push(username);-->
<!--		    }-->
<!--		}else{-->
<!--			users.push(usernames);//transforma em array para enviar pelo ajax-->
<!--		}-->
<!---->
<!--	    var result = $.ajax({type: "post", url: "../ajax.php",data: {'action':'getMoodleUsersInformation', usernames : users}, async: false}).responseText;-->
<!--	    return $.parseJSON(result);-->
<!--	}-->
<!---->
<!--	/**-->
<!--	 * botão adicionar usuário que fica dentro de cada grupo-->
<!--	 */-->
<!--	var func_add_user = function(){-->
<!--		var username = $(this).parent('.add-student').find('.newstudent').val();-->
<!---->
<!--		if(username != ""){-->
<!--			var user = func_get_users_information(username);-->
<!--			if (typeof user[0] === 'undefined' || user[0] === null) {-->
<!--				//usuário não encontrado.-->
<!--				return false;-->
<!--			}-->
<!--			//console.log(user);-->
<!--			var newuser = $('<div>').addClass('student').addClass('newstudent').attr('data-username',user[0].username).attr('data-id',user[0].id).html(user[0].fullname);-->
<!--			var removeuser = $('<span>').addClass('delete').html("Remover");-->
<!--			removeuser.click(function(){-->
<!--				$(this).parent('.student').remove();-->
<!--				func_calc_users();-->
<!--				func_search_duplicated_user();-->
<!--			});-->
<!--			newuser.append(removeuser);-->
<!--			newuser.insertBefore($(this).parent('.add-student'));-->
<!--			$(this).parent('.add-student').find('.newstudent').val("");-->
<!--		}-->
<!--		func_calc_users();-->
<!---->
<!--		//verifica se não existem usuários duplicados-->
<!--		func_search_duplicated_user();-->
<!--	};-->
<!---->
<!--	var func_calc_users = function(){-->
<!--		var teachers = $('.teacher').length;-->
<!--		var students = $('.student').length;-->
<!--		var msg = '<b>Professores: </b>'+teachers+"<br /><b>Estudantes: </b>"+students;-->
<!--		$('#course-info').html(msg);-->
<!--	};-->
<!---->
<!--	var func_search_duplicated_user = function(){-->
<!--		var  allusers = $('div[data-username]');-->
<!--		$.each(allusers, function( index, user ) {-->
<!--			if($('div[data-username="'+$(user).data('username')+'"]').length > 1){-->
<!--				$('div[data-username="'+$(user).data('username')+'"]').removeClass('duplicated');-->
<!--				$('div[data-username="'+$(user).data('username')+'"]').addClass('duplicated');-->
<!--			}else{-->
<!--				$('div[data-username="'+$(user).data('username')+'"]').removeClass('duplicated');-->
<!--			}-->
<!--		});-->
<!--	};-->
<!---->
<!--	/**-->
<!--	 * Busca as informações do curso por ajax-->
<!--	 * Retorna todos os usuários inscritos com seus devidos grupos.-->
<!--	 */-->
<!--	var func_get_course_information = function(){-->
<!--		var courseid = $('#courseid').val();-->
<!--	    var result = $.ajax({type: "post", url: "../ajax.php",data: {'action':'getMoodleCourseInformation', 'courseid' : courseid}, async: false}).responseText;-->
<!--	    return $.parseJSON(result);-->
<!--	}-->
<!---->
<!--	/**-->
<!--	 * Adiciona um grupo com base nas informações de uma disciplina já existente-->
<!--	 */-->
<!--	var func_add_moodle_group = function(info){-->
<!---->
<!--		var newgroup = $('<div>').addClass('group').addClass('moodlegroup').attr('data-groupid',info.group.id);-->
<!--		newgroup.append($('<div>').addClass('teacher').html(info.group.name));-->
<!---->
<!--		$('#list-groups').prepend(newgroup);//adiciona o novo grupo no topo da lista-->
<!--		var inputadduser_number = $('<input>').attr('type','number').attr('name','student').addClass('newstudent');-->
<!--		var inputadduser_button = $('<input>').attr('type','button').attr('id','adduser').attr('name','adduser').val('Adicionar Estudante').click(func_add_user);-->
<!--		$(inputadduser_number).keypress(function(e){-->
<!--			if(e.keyCode == 13){-->
<!--				inputadduser_button.click();-->
<!--			}-->
<!--		});-->
<!---->
<!--		var addstudent = $('<div>').addClass("add-student").append(inputadduser_number).append(inputadduser_button);-->
<!--		var liststudents = $('<div>').addClass('list-students');-->
<!--		newgroup.append(liststudents);-->
<!--		liststudents.append(addstudent);-->
<!---->
<!--		$.each(info.users, function( index, student ) {-->
<!--			var newuser = $('<div>').addClass('student').attr('data-username',student.username).attr('data-id',student.id).html(student.fullname);-->
<!--			var removeuser = $('<span>').addClass('delete').html("Remover");-->
<!--			removeuser.click(function(){-->
<!--				var uid = $(this).parent('.student').data('id');-->
<!--				var gid = $(this).closest('.group').data('groupid');-->
<!--				//console.log(uid,gid);-->
<!--				var change = $('<div>').addClass('remove-user').attr('data-userid', uid).attr('data-groupid',gid).html("remover estudante "+$(this).parent('.student').html());-->
<!--				change.find('.delete').remove();-->
<!--				$(this).closest('.group').append(change);-->
<!--				$(this).parent('.student').remove();-->
<!--				func_calc_users();-->
<!--				func_search_duplicated_user();-->
<!--			});-->
<!---->
<!--			newuser.append(removeuser);-->
<!--			newuser.insertBefore($(addstudent));-->
<!--			$(inputadduser_number).val("");-->
<!--		});-->
<!--		//limpa o form-->
<!--		$('#teacher').val("");-->
<!--		$('#students').val("");-->
<!---->
<!--		//recalcula o número de usuários-->
<!--		func_calc_users();-->
<!---->
<!--		//verifica se não existem usuários duplicados-->
<!--		func_search_duplicated_user();-->
<!--	}-->
<!--	-->
<!--	// botão adicionar um grupo (professor com estudantes)-->
<!--	$('#add-group').click(function(){-->
<!--		var teacher = $('#teacher').val();-->
<!--		var usernames = $('#students').val();-->
<!--		usernames = usernames.match(/[0-9]{2,6}/g);-->
<!--		if(teacher != "" && usernames != null){-->
<!---->
<!--			var moodle_teacher = func_get_users_information(teacher)[0];-->
<!--			var newgroup = $('<div>').addClass('group').addClass('newgroup');-->
<!--			newgroup.append($('<div>').addClass('teacher').attr('data-username',moodle_teacher.username).attr('data-id',moodle_teacher.id).html(moodle_teacher.fullname));-->
<!--			var removegroup = $('<span>').addClass('delete').html("Remover grupo");-->
<!--			removegroup.click(function(){-->
<!--				$(this).closest('.group').remove();-->
<!--				func_calc_users();-->
<!--				func_search_duplicated_user();-->
<!--			});-->
<!--			newgroup.find('.teacher').append(removegroup);-->
<!---->
<!--			$('#list-groups').prepend(newgroup);//adiciona o novo grupo no topo da lista-->
<!--			var inputadduser_number = $('<input>').attr('type','number').attr('name','student').addClass('newstudent');-->
<!--			var inputadduser_button = $('<input>').attr('type','button').attr('id','adduser').attr('name','adduser').val('Adicionar Estudante').click(func_add_user)-->
<!--			$(inputadduser_number).keypress(function(e){-->
<!--				if(e.keyCode == 13){-->
<!--    				inputadduser_button.click();-->
<!--    			}-->
<!--			});-->
<!--			var addstudent = $('<div>').addClass("add-student").append(inputadduser_number).append(inputadduser_button);-->
<!--			var liststudents = $('<div>').addClass('list-students');-->
<!--			newgroup.append(liststudents);-->
<!--			liststudents.append(addstudent);-->
<!---->
<!--			var moodle_students = func_get_users_information(usernames);-->
<!--			$.each(moodle_students, function( index, student ) {-->
<!--				var newuser = $('<div>').addClass('student').attr('data-username',student.username).attr('data-id',student.id).html(student.fullname);-->
<!--				var removeuser = $('<span>').addClass('delete').html("Remover");-->
<!--				removeuser.click(function(){-->
<!--					$(this).parent('.student').remove();-->
<!--					func_calc_users();-->
<!--					func_search_duplicated_user();-->
<!--				});-->
<!---->
<!--				newuser.append(removeuser);-->
<!--				newuser.insertBefore($(addstudent));-->
<!--				$(inputadduser_number).val("");-->
<!--			});-->
<!--			//limpa o form-->
<!--			$('#teacher').val("");-->
<!--			$('#students').val("");-->
<!---->
<!--			//recalcula o número de usuários-->
<!--			func_calc_users();-->
<!---->
<!--			//verifica se não existem usuários duplicados-->
<!--			func_search_duplicated_user();-->
<!--		}-->
<!--	});-->
<!---->
<!--	//botão para criar o curso-->
<!--	$('#create-course').click(function(){-->
<!---->
<!--		var coursename = $('#coursename').val();-->
<!--		var description = $('#description').val();-->
<!---->
<!--		var groups = [];-->
<!--		$('.group').each(function(){-->
<!--			var group = [];-->
<!--			group[0] = $(this).find('.teacher').data('id');//professor-->
<!--			$(this).find('.student').each(function(index, value){-->
<!--				group.push($(value).data('id'));-->
<!--			});-->
<!--			//console.log(group);-->
<!--			groups.unshift(group);-->
<!--		});-->
<!--		-->
<!--		var data = {-->
<!--			'action':'createCourseBasedOnModel',-->
<!--			'coursename': coursename,-->
<!--			'description': description,-->
<!--			'groups': groups-->
<!--		};-->
<!--		console.log(data);-->
<!--		$('#create-course').attr('disabled','disabled').val('Por favor, aguarde...');-->
<!--		var result = $.ajax({type: "post", url: "../ajax.php",'data': data, async: false}).responseText;-->
<!--		var course = $.parseJSON(result);-->
<!--		//console.log(course);-->
<!---->
<!--		//preenche a div com as informações da disciplina-->
<!--		var link = $('<a>').attr('href',course.linkead).attr('target','_blank').html(course.fullname);-->
<!--		$('#created-course-information .coursename').html(link);-->
<!--		var date = __format_date(new Date(course.timeclosed * 1000),'%d/%M/%Y');-->
<!--		$('#created-course-information .timeclosed').html(date);-->
<!--		$('#create-course').val('Finalizado.');-->
<!--	    -->
<!--	    $('#created-course-information').fadeIn();-->
<!--	    -->
<!--	});-->
<!---->
<!--	//Modifica o form para o modo edição de disciplina-->
<!--	$('#courseid').keypress(function(event){-->
<!--		-->
<!--		//bloqueia o usuário de adicionar caracteres não numéricos no camo "ID do curso"-->
<!--		if((event.which != 8 && isNaN(String.fromCharCode(event.which))) || event.which === 32){-->
<!--			event.preventDefault(); //stop character from entering input-->
<!--		}else{-->
<!--			if($('#courseid').val() != ''){-->
<!--				$('#coursename').attr('disabled','disabled');-->
<!--				$('#description').attr('disabled','disabled');-->
<!--				-->
<!--				//se prescionou enter-->
<!--				if(event.which === 13){-->
<!--					$('#list-groups').html(" ");-->
<!--					$('#create-course').hide();-->
<!--					$('#update-course').show();-->
<!---->
<!--					var courseinfo = func_get_course_information();-->
<!--					//console.log(courseinfo);-->
<!--					$("#coursename").val(courseinfo.coursename);-->
<!--					$('#courseid').attr('disabled','disabled');-->
<!--					delete courseinfo.coursename;-->
<!--					if(courseinfo){-->
<!--						$.each(courseinfo, function( index, group ) {-->
<!--							func_add_moodle_group(group);-->
<!--						});-->
<!--					}-->
<!--				}-->
<!---->
<!--			}-->
<!--		}-->
<!--	});-->
<!---->
<!---->
<!---->
<!--	$('#update-course').click(function(){-->
<!---->
<!--		//usuários a serem removidos dos grupos já existentes-->
<!--		var to_remove = [];-->
<!--		$.each($('.moodlegroup .remove-user'), function( index, user ) {-->
<!--			to_remove.push({'userid': $(user).data('userid'), 'groupid': $(user).data('groupid')})-->
<!--		});-->
<!--		//console.log(to_remove);-->
<!---->
<!---->
<!--		//usuários a serem adicionados em grupos já existentes-->
<!--		var to_add = [];-->
<!--		$.each($('.moodlegroup .student.newstudent'), function(index, user){-->
<!--			to_add.push({'userid': $(user).data('id'), 'groupid': $(user).closest('.moodlegroup').data('groupid')});-->
<!--		});-->
<!--		//console.log(to_add);-->
<!---->
<!--		//novos grupos a serem adicionados-->
<!--		var newgroups = [];-->
<!--		$('.newgroup').each(function(){-->
<!--			var group = [];-->
<!--			group[0] = $(this).find('.teacher').data('id');//professor-->
<!--			$(this).find('.student').each(function(index, value){-->
<!--				group.push($(value).data('id'));-->
<!--			});-->
<!--			//console.log(group);-->
<!--			newgroups.unshift(group);-->
<!--		});-->
<!--		//console.log(newgroups);-->
<!--		//console.log(data);-->
<!--		$('#update-course').attr('disabled','disabled').val('Por favor, aguarde...');-->
<!--		var data = {-->
<!--			'action' : 'updateCourseBasedOnModel',-->
<!--			'courseid' : $('#courseid').val(),-->
<!--			'moodlegroup_remove': to_remove,-->
<!--			'moodlegroup_add' : to_add,-->
<!--			'newgroups' : newgroups-->
<!--		};-->
<!--		var result = $.ajax({type: "post", url: "../ajax.php",'data': data, async: false}).responseText;-->
<!--		var success = $.parseJSON(result);-->
<!--		if(success === true){-->
<!---->
<!--		}-->
<!---->
<!--		$('#update-course').val('Finalizado.');-->
<!--		$('#course-updated').fadeIn('slow');-->
<!--	});-->
<!--});-->
<!--</script>-->
<!--</body>-->
<!--</html>-->
