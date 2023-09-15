<?php

defined('MOODLE_INTERNAL') || die();

?>

<style>

    .card {
        padding: 0px;
        background-color: #f1f4f5;
    }

    .inner-card {
        padding: 0px;
        background-color: #fff;
    }

    .card-block {
        padding: 0px;
    }

    .card-block > ul {
        border-top: #f1f4f5 solid 2px;
        padding: 0px;
        margin: 0px;
        list-style: none;
    }

    .card-block > ul li {
        padding: 10px;
    }

    .topic{
        font-size: 1.5em;
        line-height: 1.2;
        color: #37474f;
    }

    #item_list input{
        width: 100%;
    }

</style>

<div class="page-content container-fluid">
    <div class="row">
        <div class="col-lg-3 col-xs-12">
            <div class="card card-shadow text-center inner-card">
                <div class="card-block">
                    <ul id="course-list">
                        <?= $course_list ?>
                    </ul>

                    <div style="border-top: #f1f4f5 solid 2px;">
                        <select class="category_list">
                            <?= $category_list ?>
                        </select>
                        <br/>
                        <select class="course_list">
                            <option>Select Course</option>
                        </select>
                        <br/>
                        <button id="list-add"> Adicionar a lista</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-9 col-xs-12">
            <div class="card card-shadow text-center inner-card">
                <h2 id='title' style="text-align: left; padding: 15px;"></h2>
                <div class="card-block">
                    <div class="nav-tabs-animate nav-tabs-horizontal">
                        <ul class="nav nav-tabs nav-tabs-line hidden" role="tablist" disabled="" data-id="">
                            <li class="nav-item" role="presentation">
                                <a class="nav-link" data-toggle="tab" href="#report" role="tab" data-action="load_report">
                                    Relatórios
                                </a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link" data-toggle="tab" href="#dates" role="tab" data-action="load_mods">
                                    Datas
                                </a>
                            </li>
                        </ul>
                        <div class="tab-content">
                            <div id="report" class="tab-pane">&nbsp;</div>
                            <div id="dates" class="tab-pane"> </div>
                        </div>
                    </div>
                </div>
                <div class="preloader hidden">
                    <img src="templates/preload.gif"/>
                </div>
            </div>
        </div>

    </div>
</div>


<script src="<?=$CFG->wwwroot?>/lib/javascript.php/1613161749/lib/jquery/jquery-3.4.1.min.js"></script>

<script>

    function load(action, reference, data = {}) {
        jQuery('.preloader').removeClass('hidden');
        jQuery.ajax({
            method: "POST",
            url: "ajax.php",
            data: {action: action, data: data}
        }).done(function (msg) {
            result = msg;
            if (reference != null) {
                jQuery(reference).html(msg);
            }
            if(action == 'save_date'){ alert('Salvo') }
        }).fail(function (e) {
            alert('Houve algum erro');
        }).always(function () {
            jQuery('.preloader').addClass('hidden');
            revalidate();
        });
    }

    function revalidate() {

        jQuery('.category_list').off();
        jQuery('.category_list').on('change', function () {
            load('search_courses', jQuery('.course_list'), jQuery('.category_list option:selected').val());
        });

        jQuery('#list-add').off();
        jQuery('#list-add').on('click', function () {
            if (jQuery('.course-list option:selected').val() != -1) {
                load('add_course', jQuery('#course-list'), jQuery('.course_list option:selected').val());
            }
        });

        jQuery('#course-list a').off();
        jQuery('#course-list a').on('click', function () {
            jQuery('.tab-content').find('div').each(function(i, item){
                jQuery(item).addClass('hidden').html('');
            });
            jQuery('.nav-tabs-line').find('a').each(function(i, item){
                jQuery(item).removeClass('active')
            });
            var title = "<a href='<?=$CFG->wwwroot?>/course/view.php?id="+jQuery(this).data('id')+"' target='_BLANK'>";
            title += jQuery(this).html() +"</a>";
            jQuery('#title').html( title );
            course = jQuery(this).data('id');
            jQuery('.nav-tabs-line').removeClass('hidden');
        });

        jQuery('.nav-item a').off();
        jQuery('.nav-item a').on('click', function(){
            load(jQuery(this).data('action'), jQuery( jQuery(this).attr('href') ), course );
        });

        jQuery('#item_list button').off();
        jQuery('#item_list button').on('click', function(){
            var data = {};
            data.activity = jQuery(this).parent().parent().data('type');
            data.id = jQuery(this).parent().parent().data('id');

            switch (data.activity){
                case 'topic':
                    data.avaiability = jQuery(this).parent().parent().find('.avaiability').val();
                    break;
                case 'quiz':
                    data.timeopen = jQuery(this).parent().parent().find('.timeopen').val();
                    data.timeclose = jQuery(this).parent().parent().find('.timeclose').val();
                    break;
                case 'bigbluebuttonbn':
                    data.openingtime = jQuery(this).parent().parent().find('.openingtime').val();
                    data.closingtime = jQuery(this).parent().parent().find('.closingtime').val();
                    break;
                case 'assign':
                    data.allowsubmissionsfromdate =  jQuery(this).parent().parent().find('.allowsubmissionsfromdate').val();
                    data.duedate = jQuery(this).parent().parent().find('.duedate').val();
                    data.cutoffdata = jQuery(this).parent().parent().find('.cutoffdate').val();
                    break;
            }
            load('save_date', null, data);
        });
    }

    revalidate();

    if(course != 0){
        jQuery('.tab-content').find('div').each(function(i, item){
            jQuery(item).addClass('hidden').html('');
        });
        jQuery('.nav-tabs-line').find('a').each(function(i, item){
            jQuery(item).removeClass('active')
        });
        var title = "<a href='<?=$CFG->wwwroot?>/course/view.php?id="+course+"' target='_BLANK'>";
        title += (course_name +"</a>");
        jQuery('#title').html( title );
        jQuery('.nav-tabs-line').removeClass('hidden');
    }

    (function () {

    });

</script>
