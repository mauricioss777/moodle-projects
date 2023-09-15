<?php

require_once('../../../config.php');
require_once('../../../auth/ldap/auth.php');

/*
   create table mdl_local_presencas
   (id SERIAL PRIMARY KEY,
       username varchar(60),
       timecreated varchar(16),
       polo varchar(255),
       data varchar(255),
       turma bigint
);
 * */
if(!is_siteadmin() && !strpos($USER->email, '@univates.br')){
    die();
}

$componentes = Array();
$componentes[128079] = 'ANÁLISE E MODELAGEM DE DADOS';
$componentes[128082] = 'CONTABILIDADE PARA INICIANTES E NÃO CONTADORES';
$componentes[128084] = 'DIDÁTICA GERAL';
$componentes[128085] = 'EDUCAÇÃO E TECNOLOGIAS DA INFORMAÇÃO E COMUNICAÇÃO';
$componentes[128088] = 'MATEMÁTICA E ESTATÍSTICA APLICADA';
$componentes[128571] = 'BANCO DE DADOS';
$componentes[128572] = 'EXPERIÊNCIAS CORPORAIS NAS RELAÇÕES HUMANAS';
$componentes[128573] = 'GESTÃO DE CUSTOS E FORMAÇÃO DE PREÇOS';
$componentes[128574] = 'HISTÓRIA ANTIGA';
$componentes[128575] = 'INTRODUÇÃO À PESQUISA';
$componentes[128578] = 'JOGOS, BRINQUEDOS E BRINCADEIRAS';
$componentes[128580] = 'LABORATÓRIO DE PESQUISA I - FONTES DOCUMENTAIS';
$componentes[128581] = 'LEGISLAÇÃO E NORMAS DA PROFISSÃO CONTÁBIL';
$componentes[128582] = 'LEGISLAÇÃO EMPRESARIAL, TRABALHISTA E TRIBUTÁRIA';
$componentes[128583] = 'LINGUÍSTICA DO TEXTO';
$componentes[128585] = 'PRÁTICAS DE FORMAÇÃO DO LEITOR I: LITERATURA INFANTIL';
$componentes[128587] = 'PROGRAMAÇÃO DE COMPUTADORES I';
$componentes[128612] = 'SABERES E PRÁTICAS DA ALFABETIZAÇÃO NA EDUCAÇÃO INFANTIL E NOS ANOS INICIAIS II';
$componentes[128600] = 'SAÚDE OCUPACIONAL';
$componentes[128611] = 'TEORIAS DO DISCURSO';
$componentes[128614] = 'ALFABETIZAÇÃO MATEMÁTICA';
$componentes[128617] = 'GESTÃO EDUCACIONAL E ESCOLAR';
$componentes[128623] = 'EDUCAÇÃO FÍSICA	EDUCAÇÃO INFANTIL E ANOS INICIAIS DO ENSINO FUNDAMENTAL';
$componentes[128624] = 'ATLETISMO';
$componentes[128628] = 'AMBIENTE E DESENVOLVIMENTO';
$componentes[128629] = 'ANÁLISE DAS DEMONSTRAÇÕES CONTÁBEIS';
$componentes[128630] = 'CONTABILIDADE APLICADA AO SETOR PÚBLICO';
$componentes[128631] = 'ESTRUTURA E ANÁLISE DAS DEMONSTRAÇÕES FINANCEIRAS';
$componentes[128632] = 'FONÉTICA E FONOLOGIA';
$componentes[128635] = 'GENÉTICA E BIOLOGIA MOLECULAR';
$componentes[128638] = 'GESTÃO DE SISTEMAS DE INFORMAÇÃO';
$componentes[128641] = 'HISTÓRIA DA AMÉRICA II';
$componentes[128643] = 'MARKETING, VENDAS, E	COMMERCE E ATENDIMENTO AO CLIENTE';
$componentes[128644] = 'NOÇÕES E CÁLCULOS DE ATIVIDADES ATUARIAIS';
$componentes[128645] = 'PESQUISA OPERACIONAL';
$componentes[128648] = 'PRÁTICA EM RECURSOS HUMANOS';
$componentes[128656] = 'SABERES E PRÁTICAS DO MUNDO NATURAL E DA EDUCAÇÃO AMBIENTAL NA EDUCAÇÃO INFANTIL E NOS ANOS INICIAIS';
$componentes[128666] = 'TEMAS CONTEMPORÂNEOS';
$componentes[128667] = 'ANATOMIA E FISIOLOGIA HUMANA';
$componentes[138145] = 'FUNDAMENTOS DE REDES DE COMPUTADORES';
$componentes[138146] = 'PROGRAMAÇÃO DE APLICAÇÕES';

if(optional_param('action', '', PARAM_ALPHA) == 'check'){
    $codigo = required_param('codigo', PARAM_INT);
    $pessoa = $DB->get_record('user', Array('username' => $codigo));

    $log = $DB->get_record('local_presencas', Array('username' => $pessoa->username, 'data' => date('d/m/Y')));

    if($log){
        echo $pessoa->firstname . ' ' . $pessoa->lastname . ' - (' . $componentes[$log->turma].')';
    }else{
        echo $pessoa->firstname . ' ' . $pessoa->lastname;
    }
    die();
}

if(optional_param('action', '', PARAM_ALPHA) == 'register'){
    $codigo = required_param('codigo', PARAM_INT);
    $senha  = required_param('senha',  PARAM_RAW);

    $a = new auth_plugin_ldap();
    if(!$a->user_login($codigo, $senha)){
        echo 'false';die();
    }

    $pessoa = $DB->get_record('user', Array('username' => $codigo))->username;

    $log = $DB->get_record('local_presencas', Array('username' => $pessoa,
                                                         'data' => date('d/m/Y')));

    if(!$log){
        $log = new stdClass();
        $log->username = $pessoa;
        $log->timecreated = date('d/m/Y H:i');
        $DB->insert_record('local_presencas', $log);
        echo 'true';
        die();
    }

    $log->timecreated = date('d/m/Y H:i');
    $DB->update_record('local_presencas', $log);
    echo 'true';
    die();
}

echo $OUTPUT->header();

?>
<h1>Registro de presença para provas EAD</h1>

    <div id="msg_ok" class="hidden box generalbox m-b-1 adminerror alert alert-success py-15">
        <h2 style="color: #1ab7ea">Presença registrada</h2>
    </div>
    <div id="msg_error" class="hidden box generalbox m-b-1 adminerror alert alert-danger py-15">
        <h2 style="color: #c51162">Falha no registro</h2>
    </div>
    <label style="width: 10%">Codigo</label>
    <input type="number" name="codigo" id="codigo" style="width: 20%;"/>
    <input type="text" name="nome" disabled="1" id="nome" style="font-weight: bold; width:60%;"/>
    <br />
    <label style="width: 10%">Senha</label>
    <input type="password" name="codigo" id="senha" style="width: 80%;"/>
    <script src="<?=$CFG->wwwroot?>/lib/javascript.php/1551298413/lib/jquery/jquery-3.2.1.min.js"></script>
    <script>

    jQuery('#codigo').on('blur', function () {
        check();
    });

    jQuery('#senha').on('keyup', function (e) {
        if(e.keyCode != 13){
            return;
        }
        register();
    });

    function check() {
        jQuery.ajax({
            method: "POST",
            url: "#",
            data: {action: 'check', codigo: jQuery('#codigo').val()}
        }).done(function (msg) {
            jQuery('#nome').val(msg);
        }).fail(function (e) {
            alert('Houve algum erro');
        });
    }

    function register(){
        var ret = '';
        jQuery.ajax({
            method: "POST",
            url: "#",
            async: false,
            data: {action: 'register', codigo: jQuery('#codigo').val(), senha: jQuery('#senha').val()}
        }).done(function (msg) {
            ret = msg;
        }).fail(function (e) {
            alert('Houve algum erro');
        });
        if(ret == 'true'){
            jQuery('#codigo').val('').focus();
            jQuery('#nome').val('');
            jQuery('#senha').val('');
            jQuery('#msg_ok').removeClass('hidden');
        }else{
            jQuery('#senha').val('');
            jQuery('#msg_error').removeClass('hidden');
        }
        setTimeout(function(){
            jQuery('#msg_ok').addClass('hidden');
            jQuery('#msg_error').addClass('hidden');
        }, 1500);
    }
    </script>
<?php

    if(is_siteadmin()){
        echo '<hr />';
        $out =  "<table border='1' style='width:100%; margin-top: 60px;' >";
        $out .= "<tr style='text-align: center'> <th>Usuário</th> <th>Polo</th> <th>Hora</th> </tr>";
        foreach(array_keys($componentes) as $component){
            $out .= "<tr style='text-align:center; font-weight:bold; font-size: 1.5em;'> <td colspan='3'> $component - $componentes[$component]</td> </tr>";
            $records = $DB->get_records('local_presencas', Array('turma' => $component));
            foreach ($records as $record){
                if(!$record->timecreated){
                    $out .= "<tr style='color:red;'> <td>$record->username</td> <td>$record->polo</td> <td>$record->timecreated</td> </tr>";
                }else{
                    $out .= "<tr style='color:green;'> <td>$record->username</td> <td>$record->polo</td> <td>$record->timecreated</td> </tr>";
                }
            }
        }

        $out .=   "</table>";
        echo $out;

        echo '<hr />';
        $out =  "<table border='1' style='width:100%; margin-top: 60px;' >";
        $out .= "<tr style='text-align: center'> <th>Usuário</th> <th>Hora</th> </tr>";
        $records = $DB->get_records('local_presencas', Array('turma' => null));
        foreach($records as $record){
            $out .= "<tr> <td>$record->username</td> <td>$record->timecreated</td> </tr>";
        }
        echo $out;
    }

    echo $OUTPUT->footer();
