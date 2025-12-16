<?php

use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;

$this->title = 'Calendário de Requisições';
$this->params['breadcrumbs'][] = ['label' => 'Gestão de Requisições', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$this->registerCssFile('https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css');
$this->registerJsFile('https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js');
$this->registerJsFile('https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/pt.js');
?>

<div class="requisicao-calendar">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card card-primary">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h3 class="card-title">
                                <i class="fas fa-calendar-alt me-2"></i>
                                <?= Html::encode($this->title) ?>
                            </h3>
                            <div>
                                <?= Html::a('<i class="fas fa-list me-2"></i>Lista de Requisições', ['index'], ['class' => 'btn btn-secondary btn-sm']) ?>
                                <?= Html::a('<i class="fas fa-plus me-2"></i>Nova Requisição', ['create'], ['class' => 'btn btn-primary btn-sm']) ?>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Filtros do Calendário -->
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label fw-bold">Filtrar por Sala:</label>
                                    <select id="filter-sala" class="form-select">
                                        <option value="">Todas as Salas</option>
                                        <?php foreach ($salas as $sala): ?>
                                            <option value="<?= $sala->id ?>">
                                                <?= $sala->bloco->nome ?? 'Sem Bloco' ?> - <?= $sala->nome ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label fw-bold">Filtrar por Estado:</label>
                                    <select id="filter-status" class="form-select">
                                        <option value="">Todos os Estados</option>
                                        <option value="Ativa">Ativas</option>
                                        <option value="Concluída">Concluídas</option>
                                        <option value="Cancelada">Canceladas</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label fw-bold">Vista:</label>
                                    <select id="calendar-view" class="form-select">
                                        <option value="dayGridMonth">Mês</option>
                                        <option value="timeGridWeek">Semana</option>
                                        <option value="timeGridDay">Dia</option>
                                        <option value="listMonth">Lista (Mês)</option>
                                        <option value="listWeek">Lista (Semana)</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Calendário -->
                        <div id="calendar"></div>

                        <!-- Legenda -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card border-0 bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title mb-3">
                                            <i class="fas fa-info-circle me-2"></i>Legenda do Calendário
                                        </h6>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="d-flex align-items-center">
                                                    <div class="legend-color bg-success me-2" style="width: 20px; height: 20px;"></div>
                                                    <span>Requisições Ativas</span>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="d-flex align-items-center">
                                                    <div class="legend-color bg-secondary me-2" style="width: 20px; height: 20px;"></div>
                                                    <span>Requisições Concluídas</span>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="d-flex align-items-center">
                                                    <div class="legend-color bg-danger me-2" style="width: 20px; height: 20px;"></div>
                                                    <span>Requisições Canceladas</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    #calendar {
        height: 600px;
        background-color: white;
        border-radius: 0.25rem;
        padding: 20px;
        box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
    }
    .legend-color {
        border-radius: 3px;
    }
    .fc-toolbar-title {
        font-size: 1.5rem !important;
    }
    .fc-button-primary {
        background-color: #3498db !important;
        border-color: #3498db !important;
    }
    .fc-button-primary:hover {
        background-color: #2980b9 !important;
        border-color: #2980b9 !important;
    }
    .fc-button-active {
        background-color: #2980b9 !important;
        border-color: #2980b9 !important;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');

        // Inicializar calendário
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: 'pt',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
            },
            buttonText: {
                today: 'Hoje',
                month: 'Mês',
                week: 'Semana',
                day: 'Dia',
                list: 'Lista'
            },
            events: <?= $events ?>,
            eventClick: function(info) {
                info.jsEvent.preventDefault();
                if (info.event.url) {
                    window.open(info.event.url, '_blank');
                }
            },
            eventContent: function(info) {
                // Personalizar conteúdo do evento
                var title = info.event.title;
                var time = info.timeText;

                return {
                    html: '<div class="fc-event-title">' + title + '</div>' +
                        '<div class="fc-event-time">' + time + '</div>'
                };
            },
            eventTimeFormat: {
                hour: '2-digit',
                minute: '2-digit',
                meridiem: false,
                hour12: false
            }
        });

        calendar.render();

        // Filtros
        $('#filter-sala, #filter-status').on('change', function() {
            var salaId = $('#filter-sala').val();
            var status = $('#filter-status').val();

            // Filtrar eventos
            calendar.removeAllEvents();

            var allEvents = <?= $events ?>;
            var filteredEvents = allEvents.filter(function(event) {
                var eventSalaId = event.extendedProps && event.extendedProps.sala_id;
                var eventStatus = event.extendedProps && event.extendedProps.status;

                var salaMatch = !salaId || (eventSalaId && eventSalaId.toString() === salaId);
                var statusMatch = !status || (eventStatus && eventStatus === status);

                return salaMatch && statusMatch;
            });

            calendar.addEventSource(filteredEvents);
        });

        // Alterar vista
        $('#calendar-view').on('change', function() {
            var view = $(this).val();
            calendar.changeView(view);
        });

        // Botão para hoje
        $('.fc-today-button').on('click', function() {
            calendar.today();
        });
    });
</script>