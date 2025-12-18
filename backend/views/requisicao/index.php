<?php

use hail812\adminlte\widgets\Alert;
use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;
use yii\grid\GridView;
use yii\widgets\Pjax;
use hail812\adminlte\widgets\SmallBox;

$this->title = 'Gestão de Requisições';
$this->params['breadcrumbs'] = [['label' => $this->title]];
?>

<div class="container-fluid">
    <!-- Alert -->
    <div class="row">
        <div class="col-12">
            <?= Alert::widget([
                'type' => 'info',
                'body' => '<h4><i class="icon fas fa-tools"></i> Gestão de Requisições</h4>Utilize esta página para gerir todas as requisições de salas do sistema',
            ]) ?>
        </div>
    </div>

    <!-- Card Principal -->
    <div class="row">
        <div class="col-12">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-list me-2"></i>
                        Lista de Requisições
                    </h3>
                    <div class="card-tools">
                        <?= Html::a('<i class="fas fa-plus me-1"></i> Nova Requisição', ['create'], ['class' => 'btn btn-primary btn-sm']) ?>
                        <?= Html::a('<i class="fas fa-calendar-alt me-1"></i> Calendário', ['calendar'], ['class' => 'btn btn-info btn-sm ms-1']) ?>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Tabela de Requisições -->
                    <?php Pjax::begin(['timeout' => 5000, 'id' => 'requisicoes-pjax']); ?>
                    <?= GridView::widget([
                        'dataProvider' => $dataProvider,
                        'filterModel' => $searchModel,
                        'tableOptions' => ['class' => 'table table-striped table-bordered table-hover mb-0'],
                        'options' => ['class' => 'table-responsive'],
                        'layout' => "{summary}\n{items}\n<div class='d-flex justify-content-between align-items-center'>{pager}</div>",
                        'summary' => 'A mostrar <b>{begin}-{end}</b> de <b>{totalCount}</b> requisições',
                        'columns' => [
                            [
                                'attribute' => 'id',
                                'headerOptions' => ['style' => 'width: 70px;', 'class' => 'text-center'],
                                'contentOptions' => ['class' => 'text-center'],
                            ],
                            [
                                'attribute' => 'sala_nome',
                                'label' => 'Sala',
                                'value' => function($model) {
                                    return Html::a($model->sala->nome, ['sala/view', 'id' => $model->sala_id], [
                                        'class' => 'text-primary'
                                    ]);
                                },
                                'format' => 'raw',
                            ],
                            [
                                'attribute' => 'bloco_nome',
                                'label' => 'Bloco',
                                'value' => function($model) {
                                    return $model->sala->bloco->nome ?? '-';
                                },
                            ],
                            [
                                'attribute' => 'user_name',
                                'label' => 'Utilizador',
                                'value' => function($model) {
                                    return $model->user->username ?? '-';
                                },
                            ],
                            [
                                'attribute' => 'dataInicio',
                                'value' => function($model) {
                                    return Yii::$app->formatter->asDatetime($model->dataInicio, 'php:d/m/Y H:i');
                                },
                                'filter' => Html::activeTextInput($searchModel, 'dataInicio', [
                                    'class' => 'form-control form-control-sm',
                                    'type' => 'date',
                                    'title' => 'Clique para selecionar data'
                                ]),
                                'headerOptions' => ['style' => 'width: 150px;'],
                            ],
                            [
                                'attribute' => 'dataFim',
                                'value' => function($model) {
                                    return $model->dataFim ? Yii::$app->formatter->asDatetime($model->dataFim, 'php:d/m/Y H:i') : '-';
                                },
                                'filter' => Html::activeTextInput($searchModel, 'dataFim', [
                                    'class' => 'form-control form-control-sm',
                                    'type' => 'date',
                                    'title' => 'Clique para selecionar data'
                                ]),
                                'headerOptions' => ['style' => 'width: 150px;'],
                            ],
                            [
                                'attribute' => 'status',
                                'value' => function($model) {
                                    $badges = [
                                        'Ativa' => '<span class="badge bg-success"><i class="fas fa-play-circle me-1"></i>Ativa</span>',
                                        'Concluída' => '<span class="badge bg-secondary"><i class="fas fa-check-circle me-1"></i>Concluída</span>',
                                        'Cancelada' => '<span class="badge bg-danger"><i class="fas fa-times-circle me-1"></i>Cancelada</span>'
                                    ];
                                    return $badges[$model->status] ?? '-';
                                },
                                'format' => 'raw',
                                'filter' => \common\models\Requisicao::optsStatus(),
                                'filterInputOptions' => ['class' => 'form-control form-control-sm', 'prompt' => 'Todos'],
                                'headerOptions' => ['style' => 'width: 120px;', 'class' => 'text-center'],
                                'contentOptions' => ['class' => 'text-center'],
                            ],
                            [
                                'label' => 'Duração',
                                'value' => function($model) {
                                    if (!$model->dataFim) return 'Em curso';
                                    $inicio = new DateTime($model->dataInicio);
                                    $fim = new DateTime($model->dataFim);
                                    $interval = $inicio->diff($fim);

                                    $parts = [];
                                    if ($interval->days > 0) $parts[] = $interval->days . 'd';
                                    if ($interval->h > 0) $parts[] = $interval->h . 'h';
                                    if ($interval->i > 0) $parts[] = $interval->i . 'm';

                                    return implode(' ', $parts) ?: '< 1m';
                                },
                                'format' => 'raw',
                                'headerOptions' => ['style' => 'width: 100px;', 'class' => 'text-center'],
                                'contentOptions' => ['class' => 'text-center'],
                            ],
                            [
                                'class' => 'yii\grid\ActionColumn',
                                'header' => '<i class="fas fa-cogs"></i> Ações',
                                'headerOptions' => ['style' => 'width: 180px;', 'class' => 'text-center'],
                                'contentOptions' => ['class' => 'text-center py-2'],
                                'template' => '<div class="btn-group btn-group-sm">{view} {update} {concluir} {cancelar} {delete}</div>',
                                'buttons' => [
                                    'view' => function ($url, $model, $key) {
                                        return Html::a('<i class="fas fa-eye"></i>', $url, [
                                            'class' => 'btn btn-info',
                                            'title' => 'Ver detalhes',
                                            'data-pjax' => 0,
                                            'data-bs-toggle' => 'tooltip',
                                        ]);
                                    },
                                    'update' => function ($url, $model, $key) {
                                        if ($model->isConcluida() || $model->isCancelada()) {
                                            return Html::button('<i class="fas fa-edit"></i>', [
                                                'class' => 'btn btn-outline-warning disabled',
                                                'title' => 'Não pode editar requisição ' . strtolower($model->getEstadoLabel()),
                                                'disabled' => true,
                                                'data-bs-toggle' => 'tooltip',
                                            ]);
                                        }
                                        return Html::a('<i class="fas fa-edit"></i>', $url, [
                                            'class' => 'btn btn-warning',
                                            'title' => 'Editar requisição',
                                            'data-pjax' => 0,
                                            'data-bs-toggle' => 'tooltip',
                                        ]);
                                    },
                                    'concluir' => function ($url, $model, $key) {
                                        if (!$model->isAtiva()) {
                                            return '';
                                        }

                                        return Html::a('<i class="fas fa-check"></i>', ['marcar-concluida', 'id' => $model->id], [
                                            'class' => 'btn btn-success',
                                            'title' => 'Marcar como concluída',
                                            'data' => [
                                                'confirm' => 'Tem a certeza que deseja marcar esta requisição como concluída?',
                                                'method' => 'post',
                                            ],
                                            'data-pjax' => 0,
                                            'data-bs-toggle' => 'tooltip',
                                        ]);
                                    },
                                    'cancelar' => function ($url, $model, $key) {
                                        if (!$model->isAtiva()) {
                                            return '';
                                        }

                                        return Html::a('<i class="fas fa-times"></i>', ['marcar-cancelada', 'id' => $model->id], [
                                            'class' => 'btn btn-danger',
                                            'title' => 'Cancelar requisição',
                                            'data' => [
                                                'confirm' => 'Tem a certeza que deseja cancelar esta requisição?',
                                                'method' => 'post',
                                            ],
                                            'data-pjax' => 0,
                                            'data-bs-toggle' => 'tooltip',
                                        ]);
                                    },
                                    'delete' => function ($url, $model, $key) {
                                        if ($model->isAtiva()) {
                                            return Html::button('<i class="fas fa-trash"></i>', [
                                                'class' => 'btn btn-outline-danger disabled',
                                                'title' => 'Não pode eliminar requisição ativa',
                                                'disabled' => true,
                                                'data-bs-toggle' => 'tooltip',
                                            ]);
                                        }
                                        return Html::a('<i class="fas fa-trash"></i>', $url, [
                                            'class' => 'btn btn-danger',
                                            'title' => 'Eliminar requisição',
                                            'data' => [
                                                'confirm' => 'Tem a certeza que deseja eliminar esta requisição?',
                                                'method' => 'post',
                                            ],
                                            'data-pjax' => 0,
                                            'data-bs-toggle' => 'tooltip',
                                        ]);
                                    },
                                ],
                            ],
                        ],
                        'pager' => [
                            'options' => ['class' => 'pagination justify-content-center mb-0'],
                            'linkOptions' => ['class' => 'page-link'],
                            'disabledPageCssClass' => 'page-link disabled',
                            'activePageCssClass' => 'active',
                        ],
                    ]); ?>
                    <?php Pjax::end(); ?>
                </div>
                <div class="card-footer">
                    <div class="row">
                        <div class="col-md-6">
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>Legenda:
                                <span class="badge bg-success me-1"><i class="fas fa-play-circle me-1"></i>Ativa</span>
                                <span class="badge bg-secondary me-1"><i class="fas fa-check-circle me-1"></i>Concluída</span>
                                <span class="badge bg-danger"><i class="fas fa-times-circle me-1"></i>Cancelada</span>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- SmallBox Widgets -->
    <div class="row mb-4">
        <div class="col-lg-3 col-6">
            <?= SmallBox::widget([
                'title' => $totalRequisicoes ?? 0,
                'text' => 'Total Requisições',
                'icon' => 'fas fa-calendar-alt',
                'theme' => 'info'
            ]) ?>
        </div>
        <div class="col-lg-3 col-6">
            <?= SmallBox::widget([
                'title' => $requisicoesAtivas ?? 0,
                'text' => 'Requisições Ativas',
                'icon' => 'fas fa-play-circle',
                'theme' => 'success'
            ]) ?>
        </div>
        <div class="col-lg-3 col-6">
            <?= SmallBox::widget([
                'title' => $requisicoesConcluidas ?? 0,
                'text' => 'Concluídas',
                'icon' => 'fas fa-check-circle',
                'theme' => 'secondary'
            ]) ?>
        </div>
        <div class="col-lg-3 col-6">
            <?= SmallBox::widget([
                'title' => $requisicoesCanceladas ?? 0,
                'text' => 'Canceladas',
                'icon' => 'fas fa-times-circle',
                'theme' => 'danger'
            ]) ?>
        </div>
    </div>
</div>

<style>
    .table th {
        background-color: #f8f9fa;
        font-weight: 600;
        border-bottom: 2px solid #dee2e6;
        vertical-align: middle;
    }
    .table td {
        vertical-align: middle;
    }
    .btn-group-sm > .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
        border-radius: 0.25rem;
        margin: 0 1px;
    }
    .badge {
        font-size: 0.7em;
        font-weight: 500;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        $(document).on('pjax:success', function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                var tooltip = bootstrap.Tooltip.getInstance(tooltipTriggerEl);
                if (tooltip) {
                    tooltip.dispose();
                }
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });

        // Converter datas do formato aaaa-mm-dd para dd/mm/aaaa
        function formatDateForDisplay(dateStr) {
            if (!dateStr) return '';
            const parts = dateStr.split('-');
            if (parts.length === 3) {
                return parts[2] + '/' + parts[1] + '/' + parts[0];
            }
            return dateStr;
        }

        // Quando o datepicker é fechado, converter para dd/mm/aaaa
        $('input[type="date"]').on('change', function() {
            if (this.value) {
                // O valor já está no formato aaaa-mm-dd (padrão do datepicker)
                // O backend vai converter automaticamente
                console.log('Data selecionada:', this.value);
            }
        });
    });
</script>