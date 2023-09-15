<?php
/**
 * Testa a criação de cursos do tipo array via webservice
 */
require_once('soap_test.class.php');
class soap_alfa_create_course_array_test extends soap_test{
    
    function __construct(){
        parent::__construct('local_alfa_create_course_array');
    }

    function test(){
        $rand = rand(1,100000);
        $c['idofferarray']   = ''.$rand.','.$rand.'1,'.$rand.'2,'.$rand.'3,'.$rand.'4';
        $c['fullname']  = 'ESP. EM ATENÇÃO MULTID. EM ONCOLOGIA - '.$rand.' EDIÇÃO';
        $c['shortname'] = 'ESP. EM ATENÇÃO MULTID. EM ONCOLOGIA - '.$rand.' EDIÇÃO';
        $c['category']  = 'ESPECIALIZAÇÃO';
        $c['period']    = 'ESPEC2015A';
        $c['inidate']   = '2019-01-01';
        $c['enddate']   = '2019-07-11';
        $c['dayshift']  = 'Manhã';
        $c['dayofweek'] = 'Terça-feira';
        $c['createlabels'] = true;
        $c['users'][0]['username'] = '3030';
        $c['users'][0]['roleid'] = '3';// 3== teacher

        for($i=1; $i<20; $i++){
            $rand = rand(35000,40000);
            $c['users'][$i]['username'] = "$rand";
            $c['users'][$i]['roleid'] = '5'; //5 == student
        }

        $c['offersinfo'][0]['teacher'] = 'GISELDA VERONICE HAHN';
        $c['offersinfo'][0]['coursename'] = 'ATUALIZAÇÃO EM ONCOLOGIA: IMUNOTERAPIA ATIVA E TERAPIA COM ANTICORPOS MONOCLONAIS';

        $c['offersinfo'][1]['teacher'] = 'LUCIANE SLOMKA';
        $c['offersinfo'][1]['coursename'] = 'ASPECTOS PSICOSSOCIAIS EM ONCOLOGIA';

        $c['offersinfo'][2]['teacher'] = 'MÔNICA FERNANDA JOHANN';
        $c['offersinfo'][2]['coursename'] = 'FISIOTERAPIA EM ONCOLOGIA';

        $c['offersinfo'][3]['teacher'] = 'ORILETE APARECIDA RAMINELLI';
        $c['offersinfo'][3]['coursename'] = 'CUIDADOS PALIATIVOS E COMPLEMENTARES EM ONCOLOGIA';

        $c['offersinfo'][4]['teacher'] = 'DANIEL SILVEIRA DA SILVA';
        $c['offersinfo'][4]['coursename'] = 'AVALIAÇÃO E CUIDADOS DE ENFERMAGEM EM ONCOLOGIA';


//    $c['idofferarray']  = '99730,99733,99736,99739,99740,99742,99744,99745,99746,99748,99750,99752,99757,99761,99763,99764';
//    $c['fullname']      = 'PÓS-GRADUAÇÃO LATO SENSU - ESPECIALIZAÇÃO EM AVALIAÇÃO DE IMPACTOS E RECUPERAÇÃO AMBIENTAL';
//    $c['shortname']     = 'PÓS-GRADUAÇÃO LATO SENSU - ESPECIALIZAÇÃO EM AVALIAÇÃO DE IMPACTOS E RECUPERAÇÃO AMBIENTAL';
//    $c['category']      = 'ESPECIALIZAÇÃO';
//    $c['period']        = 'POS-AIRA';
//    $c['inidate']       = '2017-09-15';
//    $c['enddate']       = '2019-08-26';
//    $c['dayshift']      = 'Manhã';
//    $c['dayofweek']     = 'Terça-feira';
//    $c['createlabels']  = true;
//    $c['users'][0]['roleid']    = '5';
//    $c['users'][0]['username']  = '101401';
//    $c['users'][1]['roleid']    = '5';
//    $c['users'][1]['username']  = '346208';
//    $c['users'][2]['roleid']    = '5';
//    $c['users'][2]['username']  = '507842';
//    $c['users'][3]['roleid']    = '5';
//    $c['users'][3]['username']  = '511510';
//    $c['users'][4]['roleid']    = '5';
//    $c['users'][4]['username']  = '511624';
//    $c['users'][5]['roleid']    = '5';
//    $c['users'][5]['username']  = '517580';
//    $c['users'][6]['roleid']    = '5';
//    $c['users'][6]['username']  = '524748';
//    $c['users'][7]['roleid']    = '5';
//    $c['users'][7]['username']  = '525698';
//    $c['users'][8]['roleid']    = '5';
//    $c['users'][8]['username']  = '528688';
//    $c['users'][9]['roleid']    = '5';
//    $c['users'][9]['username']  = '533464';
//    $c['users'][10]['roleid']   = '5';
//    $c['users'][10]['username']  = '537361';
//    $c['users'][11]['roleid']   = '5';
//    $c['users'][11]['username']  = '541892';
//    $c['users'][12]['roleid']   = '5';
//    $c['users'][12]['username']  = '551751';
//    $c['users'][13]['roleid']   = '5';
//    $c['users'][13]['username']  = '633978';
//    $c['users'][14]['roleid']   = '5';
//    $c['users'][14]['username']  = '635245';
//    $c['users'][15]['roleid']   = '5';
//    $c['users'][15]['username']  = '639105';
//    $c['users'][16]['roleid']   = '5';
//    $c['users'][16]['username']  = '639512';
//    $c['users'][17]['roleid']   = '5';
//    $c['users'][17]['username']  = '639759';
//    $c['users'][19]['roleid']   = '8';
//    $c['users'][19]['username']  = '2237';
//    $c['users'][20]['roleid']   = '8';
//    $c['users'][20]['username']  = '2487';
//    $c['users'][21]['roleid']   = '8';
//    $c['users'][21]['username']  = '2834';
//    $c['users'][22]['roleid']   = '8';
//    $c['users'][22]['username']  = '103869';
//    $c['users'][23]['roleid']   = '8';
//    $c['users'][23]['username']  = '200946';
//    $c['users'][24]['roleid']   = '8';
//    $c['users'][24]['username']  = '202663';
//    $c['users'][25]['roleid']   = '8';
//    $c['users'][25]['username']  = '321567';
//    $c['users'][26]['roleid']   = '8';
//    $c['users'][26]['username']  = '519730';
//    $c['users'][27]['roleid']   = '8';
//    $c['users'][27]['username']  = '564139';
//    $c['users'][28]['roleid']   = '8';
//    $c['users'][28]['username']  = '564305';
//    $c['users'][29]['roleid']   = '8';
//    $c['users'][29]['username']  = '565024';
//    $c['users'][30]['roleid']   = '8';
//    $c['users'][30]['username']  = '573368';
//    $c['users'][31]['roleid']   = '8';
//    $c['users'][31]['username']  = '584220';
//    $c['offersinfo'][0]['teacher']      = 'MARIA CRISTINA DE ALMEIDA SILVA / RAFAEL RODRIGO ECKHARDT';
//    $c['offersinfo'][0]['coursename']   = 'AVALIAÇÃO DE IMPACTOS AMBIENTAIS';
//    $c['offersinfo'][1]['teacher']      = 'MAURÍCIO HILGEMANN / TIAGO DE ALMEIDA';
//    $c['offersinfo'][1]['coursename']   = 'GEOQUÍMICA AMBIENTAL';
//    $c['offersinfo'][2]['teacher']      = 'SÉRGIO NUNES LOPES';
//    $c['offersinfo'][2]['coursename']   = 'AVALIAÇÃO DE IMPACTOS SOCIAIS E ECONÔMICOS';
//    $c['offersinfo'][3]['teacher']      = 'LUCIANA TURATTI';
//    $c['offersinfo'][3]['coursename']   = 'LEGISLAÇÃO E LICENCIAMENTO AMBIENTAL';
//    $c['offersinfo'][4]['teacher']      = 'ODORICO KONRAD';
//    $c['offersinfo'][4]['coursename']   = 'GERENCIAMENTO DE RESÍDUOS SÓLIDOS';
//    $c['offersinfo'][5]['teacher']      = 'MARIA CRISTINA DE ALMEIDA SILVA';
//    $c['offersinfo'][5]['coursename']   = 'GERENCIAMENTO DE EFLUENTES E EMISSÕES ATMOSFÉRICAS';
//    $c['offersinfo'][6]['teacher']      = 'MARIA CRISTINA DE ALMEIDA SILVA / EMERSON LUÍS MUSSKOPF';
//    $c['offersinfo'][6]['coursename']   = 'RECUPERAÇÃO DE ÁREAS DEGRADADAS';
//    $c['offersinfo'][7]['teacher']      = 'CLAUDETE REMPEL';
//    $c['offersinfo'][7]['coursename']   = 'METODOLOGIA DE PESQUISA';
//    $c['offersinfo'][8]['teacher']      = 'ELISETE MARIA DE FREITAS';
//    $c['offersinfo'][8]['coursename']   = 'MÉTODOS E TÉCNICAS DE ESTUDO DA VEGETAÇÃO';
//    $c['offersinfo'][9]['teacher']      = 'CAMILLE EICHELBERGER GRANADA / MARIA CRISTINA DE ALMEIDA SILVA';
//    $c['offersinfo'][9]['coursename']   = 'TÉCNICAS DE BIOMONITORAMENTO E BIORREMEDIAÇÃO';
//    $c['offersinfo'][10]['teacher']     = 'RAFAEL RODRIGO ECKHARDT / GUILHERME GARCIA DE OLIVEIRA';
//    $c['offersinfo'][10]['coursename']   = 'GEOTECNOLOGIA APLICADAS AO MEIO AMBIENTE';
//    $c['offersinfo'][11]['teacher']     = 'TIAGO DE ALMEIDA / GUILHERME GARCIA DE OLIVEIRA';
//    $c['offersinfo'][11]['coursename']   = 'DISPERSÃO DE CONTAMINANTES EM SOLOS E AMBIENTES AQUÁTICOS';
//    $c['offersinfo'][12]['teacher']     = 'MARIA CRISTINA DE ALMEIDA SILVA / MICHELY ZAT';


        parent::execute_test($c);
    }
}

$test = new soap_alfa_create_course_array_test();
$test->test();
?>
