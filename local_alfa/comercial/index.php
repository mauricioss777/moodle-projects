<?php
require_once '../../../config.php';
require_once($CFG->dirroot."/user/lib.php");
require_login(0, true, null, false); //solicta autenticação


//verifica se o usuário logado pode e ter acesso a esta página
$path = 'config.json';
$jsonString = file_get_contents($path);
$config = (object) json_decode($jsonString, true);

$allowids= array();

foreach($config->allowusers as $u){
    $allowids[] = $u['id'];
}

if(!in_array($USER->id, $allowids)){
    echo "Acesso restrito. Faça login com um usuário que tenha autorização para utilizar esta página."; die();
}

/* SQL que busca todos os cursos com a tag de CURSOS LIVRES */
$sqlcourses = "SELECT c.id,
                      c.fullname,
                      t.id as tagid,
                      t.rawname
                 FROM {course} as c,
                      {tag} as t,
                      {tag_instance} as ti 
                WHERE c.id = ti.itemid AND
                      t.id = ti.tagid AND
                      ti.itemtype = 'course' AND
                      t.rawname='CURSOS LIVRES'";
$courses = $DB->get_records_sql($sqlcourses);

//caso o formulário já tenha sido submetido, recebe as informações e faz a criação do usuário.

$newuser =  json_decode(array_keys($_GET)[0]);// GAMBIARRA ALERT! -> não sei pq essa porra veio assim do ajax \(*.*)/

if(isset($newuser->username)){

    $response = array();
    //verifica se o usuário já existe no moodle
    if($u = $DB->get_record_sql("SELECT * FROM mdl_user WHERE username ='$newuser->username'")){
        $response['username'] = $u->username;
        $response['alert'] = 'O usuário já existe';
        echo json_encode($response); die();
    }else{
        $u = array();
        $u['username'] = $newuser->username;
        $u['firstname'] = $newuser->username;
        $u['lastname'] = 'DEMONSTRACAO';
        $u['auth'] = 'manual';
        $u['confirmed'] = '1';
        $u['mnethostid'] = '3';
        $u['password'] = md5($newuser->password);
        $u['email'] = 'naoresponda@univates.br';
        $u['emailstop'] = '1';
        $u['city'] = $newuser->type == 'all' ? 'DEMONSTRACAO' : 'DEMONSTRACAO PARCIAL';
        $u['country'] = 'BR';
        $u['lang'] = 'pt_br';
        $u['id'] = user_create_user($u,false); // cria usuário no moodle

        $response['user'] = $u;
        $response['pass'] = $newuser->password;
    }



    $selectedCourses = explode(',',$newuser->courses);
    if(!in_array('allcourses', $selectedCourses)){ 
        $courses = $DB->get_records_sql('SELECT * FROM  {course} WHERE id in ('.$newuser->courses.')');
    }

    //vincula usuário aos cursos
    if(!empty($courses)){
        foreach($courses as $course){
            $context = context_course::instance($course->id, MUST_EXIST);
            $maninstance = $DB->get_record('enrol', array(
                'courseid' => $course->id,
                'enrol'    => 'manual',
            ), '*', MUST_EXIST);
            $manual = enrol_get_plugin('manual');

            
            $fdate = date_create_from_format('d/m/Y', $newuser->dateto);
            $datetoTimestamp = strtotime($fdate->format('Y-m-d'));

            $manual->enrol_user($maninstance, $response['user']['id'], '5', 0, $datetoTimestamp);
            $response['courses'][] = array('id'=>$course->id, 'fullname'=>$course->fullname, 'link'=>$CFG->wwwroot.'/course/view.php?id='.$course->id);
            
        }
        $response['dateto'] = $newuser->dateto;
        $response['type'] = $newuser->type;
        $response['success'] = 'true';
        echo json_encode($response);
    }

    die();
}



?>
<!DOCTYPE html>
<html lang="pt-BR">
  <head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <meta charset="utf-8">
    <title>Form</title>
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css"> 
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="css/bootstrap-datepicker.css">
    <script src="js/bootstrap-datepicker.min.js"></script>
    <script src="locales/bootstrap-datepicker.pt-BR.min.js"></script>

    <style type="text/css">
        html, body{
            position: relative;
            max-width: 60%;
            margin: 0 auto;
            text-align: center;
        }
        #form-empresas{
            padding-top: 50px;
            width: 100%;

        }

    </style>
  </head>
  <body id="form-empresas">
    <!-- Page Content  -->
    <div id="content">
        <form action="#" method="POST">
        <div class="form-group row">
            <div class="col-12">
                <h3>Criar usuário temporário para empresas:</h3>
            </div>
        </div>
        <div class="form-group row">
            <div class="col-12">
            <div class="input-group">
                <div class="input-group-prepend">
                <div class="input-group-text">
                    <i class="fa fa-address-book"></i>
                </div>
                </div> 
                <input id="username" name="username" placeholder="nome de usuário" type="text" class="form-control" required="required">
            </div>
            </div>
        </div>
        <div class="form-group row">
            <div class="col-12">
            <div class="input-group">
                <div class="input-group-prepend">
                <div class="input-group-text">
                    <i class="fa fa-key"></i>
                </div>
                </div> 
                <input id="password" name="password" placeholder="Senha " type="text" class="form-control" required="required">
            </div>
            </div>
        </div>
        <div class="form-group row">
            <div class="col-12">
            <select id="courses" name="courses" class="custom-select" multiple="multiple" required="required">
                <option class="course-option" value="allcourses" selected="selected">Todos</option>
                <?php
                foreach ($courses as $course){
                    echo '<option class="course-option" value="'.$course->id.'">'.$course->fullname.'</option>';
                }
                ?>
            </select>
            </div>
        </div> 
        <div class="form-group row">
            <div class="col-12">
            <div class="input-group">
                <div class="input-group-prepend">
                <div class="input-group-text">
                    <i class="fa fa-calendar"></i>
                </div>
                </div> 
                <input id="dateto" data-provide="datepicker" placeholder="Data limite" type="text" class="form-control">
            </div>
            </div>
        </div>
        <div class="form-group row">
            <div class="col-12">
            <div class="custom-control custom-radio custom-control-inline">
                <input name="unidades" id="unidades_0" type="radio" class="custom-control-input" value="all"> 
                <label for="unidades_0" class="custom-control-label">Liberar todo o conteúdo</label>
            </div>
            <div class="custom-control custom-radio custom-control-inline">
                <input name="unidades" id="unidades_1" type="radio" class="custom-control-input" value="preview" checked> 
                <label for="unidades_1" class="custom-control-label">Liberar apenas uma parte do conteúdo</label>
            </div>
            </div>
        </div> 
        <div class="form-group row">
            <div class="col-12">
            <button id="submit-form" name="submit" type="button" class="btn btn-primary">Criar Usuário</button>
            </div>
        </div>
        <div class="form-error alert alert-danger" role="alert"></div>
        </form>

    </div>

    <script type="text/javascript">
        $('#dateto').datepicker({
            language: "pt-BR"
        });

        $( document ).ready(function() {
            $('.form-error').hide();
            $('#submit-form').on('click', function(){
                let username = $('#username').val();
                let pass = $('#password').val();
                let courses = $(".course-option:selected").map(function(){ return this.value }).get().join(",");
                let dateto = $("#dateto").val();
                let type = $('input[name=unidades]:checked').val();
                let error = false;

                //validação do username
                $('.form-error').html('');
                if (username == '') {
                    $('.form-error').append('O nome de usuário não pode estar vazio<br />')
                } else if (/\s/g.test(username)) {
                    $('.form-error').append('O nome de usuário não pode conter espaços<br />');
                } else if (!/^[a-zA-Z0-9._]+$/.test(username)) {
                    $('.form-error').append('O nome de usuário é permitido apenas com letras, números, ponto e underline<br />');
                } else if (username.length < 4) {
                    $('.form-error').append('O nome de usuário deve ter pelo menos 4 caracteres<br />');
                }

                //validação senha
                if (pass == '') {
                    $('.form-error').append('O campo de senha não pode estar vazio<br />')
                } else if (/\s/g.test(pass)) {
                    $('.form-error').append('O campo de senha não pode conter espaços<br />');
                } else if (!/^[a-zA-Z0-9._]+$/.test(pass)) {
                    $('.form-error').append('No campo de senha é permitido apenas com letras, números, ponto e underline<br />');
                } else if (pass.length < 4) {
                    $('.form-error').append('O campo de senha deve ter pelo menos 4 caracteres<br />');
                }

                //validação dos cursos
                if(courses == ''){
                    $('.form-error').append('Selecione ao menos 1 curso<br />');
                    error=true;
                }

                //validação da data
                if(dateto == ''){
                    $('.form-error').append('Selecione uma data de encerramento do acesso do usuário.<br />');
                    error=true;
                }

                //mostra mensagem de erro
                if(error){
                    $('.form-error').show();
                }else{
                    $('.form-error').hide();
                    // Crie um objeto JavaScript
                    var newUser = {
                        'username': username,
                        'password': pass,
                        'courses': courses,
                        'dateto': dateto,
                        'type': type
                    };

                    if(!error){

                        // Faça o post do objeto usando o método $.ajax()
                        $.ajax({
                            type: "GET", 
                            url: "index.php", 
                            data: JSON.stringify(newUser), 
                            contentType: "application/json; charset=utf-8", 
                            dataType: "json", 
                            success: function (data) {
                                // Função executada quando a requisição é bem-sucedida
                                if(data.alert){
                                    $('.form-error').append(data.alert);
                                    $('.form-error').show();
                                }else{
                                    $('#id-success').remove();
                                    let htmlresponse = '<div id="id-success" class="alert alert-success" role="alert" style="text-align: left;">';
                                    htmlresponse+= '    <h3 class="alert-heading">Usuário criado e inserido com sucesso.</h3>';
                                    htmlresponse+= '    <p class="mb-0">Informações de acesso:</p>';
                                    htmlresponse+= '    <div> <strong>Usuário:</strong> '+data.user.username+'</div>';
                                    htmlresponse+= '    <div> <strong>Senha:</strong> '+data.pass+'</div>';
                                    htmlresponse+= '<br /> <br />';
                                    htmlresponse+= '<table class="table table-striped">';
                                    htmlresponse+= '<tr><th scope="col">Curso</th><th scope="col">Link de acesso</th></tr>';
                                    for(let i=0; i< data.courses.length; i++){
                                        htmlresponse+= '<tr scope="row"><td>'+data.courses[i].fullname+'</td><td><a href="'+data.courses[i].link+'" target="_blank">'+data.courses[i].link+'</a></td></tr>';    
                                    }
                                    htmlresponse+= '</table>';
                                    htmlresponse+= '<br />';
                                    htmlresponse+= '<strong> O acesso aos ambientes ficará disponível até: '+ data.dateto + '</strong>';
                                    htmlresponse+= '';
                                    htmlresponse+= '';
                                    htmlresponse+= '</div>';
                                    $('#content').append(htmlresponse);
                                }
                            },
                            error: function (xhr, status, error) {
                                // Função executada quando a requisição falha
                                console.log(error);
                            }
                        });
                    }
                }              
            });
        });
    </script>
  </body>
</html>
