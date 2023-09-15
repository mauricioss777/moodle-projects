
function loadCalendar(){
    var calendarEl = document.getElementById('calendar');
    var usuario = calendarEl.className;
    calendarEl.innerHTML = '';
    var calendar = new FullCalendar.Calendar(calendarEl, {
        plugins: [ 'interaction', 'dayGrid', 'timeGrid', 'list' ],
        height: 'auto',
        header: { left: 'title', center: '', right: 'prev,next today'},
        locale: 'pt-br',
        resourceGroupField: 'groupId',
        defaultView: 'timeGridWeek',
        eventColor: '#000000',
        events: 'ajax.php?method=calendar&tutor='+usuario
    });
    calendar.render();
}
