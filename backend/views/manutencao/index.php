<?php
/** @var yii\web\View $this */

use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;
use yii\grid\GridView;
use yii\widgets\Pjax;
use common\models\Manutencao;

$this->title = 'Gestão de Manutenções';
?>

<div class="manutencao-index">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?= Html::encode($this->title) ?></h1>
        <?= Html::a('<i class="fa fa-plus me-2"></i>Nova Manutenção', ['create'], ['class' => 'btn btn-primary']) ?>
    </div>

    <div class="container-fluid pt-4 px-4">
        <div class="bg-light rounded p-4">

            <!-- Formulário de Pesquisa -->
            <div class="row mb-4">
                <div class="col-md-8">
                    <?php $form = ActiveForm::begin([
                        'method' => 'get',
                        'action' => ['index'],
                    ]); ?>

                    <div class="input-group">
                        <?= Html::textInput('search', Yii::$app->request->get('search'), [
                            'class' => 'form-control',
                            'placeholder' => 'Pesquisar manutenções...'
                        ]) ?>
                        <?= Html::submitButton('<i class="fa fa-search"></i>', ['class' => 'btn btn-primary']) ?>
                    </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>

            <!-- Tabela CRUD -->
            <?php Pjax::begin(); ?>
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'tableOptions' => ['class' => 'table table-striped table-bordered table-hover'],
                'columns' => [
                    [
                        'attribute' => 'id',
                        'headerOptions' => ['style' => 'width: 80px;'],
                    ],
                    [
                        'attribute' => 'equipamentoNome',
                        'label' => 'Equipamento',
                        'value' => 'equipamento.nome',
                        'headerOptions' => ['style' => 'width: 150px;'],
                    ],
                    [
                        'attribute' => 'salaNome',
                        'label' => 'Sala',
                        'value' => 'sala.nome',
                        'headerOptions' => ['style' => 'width: 120px;'],
                    ],
                    [
                        'attribute' => 'userNome',
                        'label' => 'Técnico',
                        'value' => 'user.username',
                        'headerOptions' => ['style' => 'width: 120px;'],
                    ],
                    [
                        'attribute' => 'dataInicio',
                        'format' => ['datetime', 'php:d/m/Y H:i'],
                        'headerOptions' => ['style' => 'width: 160px;'],
                    ],
                    [
                        'attribute' => 'dataFim',
                        'format' => ['datetime', 'php:d/m/Y H:i'],
                        'headerOptions' => ['style' => 'width: 160px;'],
                    ],
                    [
                        'attribute' => 'status',
                        'value' => function($model) {
                            return $model->getStatusBadge();
                        },
                        'format' => 'raw',
                        'filter' => Manutencao::getStatusOptions(),
                        'headerOptions' => ['style' => 'width: 120px;'],
                    ],
                    [
                        'attribute' => 'descricao',
                        'value' => function($model) {
                            return $model->descricao ? Html::tag('div', $model->descricao, [
                                'class' => 'text-truncate',
                                'style' => 'max-width: 200px;',
                                'title' => $model->descricao
                            ]) : '-';
                        },
                        'format' => 'raw',
                    ],
                    [
                        'class' => 'yii\grid\ActionColumn',
                        'header' => 'Ações',
                        'headerOptions' => ['style' => 'width: 150px;'],
                        'template' => '{view} {update} {delete}',
                        'buttons' => [
                            'view' => function ($url, $model, $key) {
                                return Html::a('<i class="fa fa-eye"></i>', $url, [
                                    'class' => 'btn btn-sm btn-info',
                                    'title' => 'Ver detalhes',
                                    'data-pjax' => 0,
                                ]);
                            },
                            'update' => function ($url, $model, $key) {
                                return Html::a('<i class="fa fa-edit"></i>', $url, [
                                    'class' => 'btn btn-sm btn-warning',
                                    'title' => 'Editar',
                                    'data-pjax' => 0,
                                ]);
                            },
                            'delete' => function ($url, $model, $key) {
                                return Html::a('<i class="fa fa-trash"></i>', $url, [
                                    'class' => 'btn btn-sm btn-danger',
                                    'title' => 'Eliminar permanentemente',
                                    'data' => [
                                        'confirm' => '⚠️ ELIMINAÇÃO PERMANENTE ⚠️\n\nTem a certeza que deseja eliminar PERMANENTEMENTE esta manutenção?\n\nEsta ação NÃO pode ser desfeita!',
                                        'method' => 'post',
                                    ],
                                ]);
                            },
                        ],
                    ],
                ],
                'pager' => [
                    'options' => ['class' => 'pagination justify-content-center'],
                    'linkOptions' => ['class' => 'page-link'],
                    'disabledPageCssClass' => 'page-link disabled',
                ],
            ]); ?>
            <?php Pjax::end(); ?>

            <!-- Estatísticas -->
            <div class="row mt-4">
                <div class="col-sm-6 col-xl-3">
                    <div class="bg-primary rounded d-flex align-items-center justify-content-between p-3 text-white">
                        <i class="fa fa-tools fa-2x"></i>
                        <div class="ms-3">
                            <p class="mb-0">Total Manutenções</p>
                            <h6 class="mb-0"><?= $totalManutencoes ?? 0 ?></h6>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="bg-warning rounded d-flex align-items-center justify-content-between p-3 text-white">
                        <i class="fa fa-clock fa-2x"></i>
                        <div class="ms-3">
                            <p class="mb-0">Pendentes</p>
                            <h6 class="mb-0"><?= $manutencoesPendentes ?? 0 ?></h6>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="bg-info rounded d-flex align-items-center justify-content-between p-3 text-white">
                        <i class="fa fa-play-circle fa-2x"></i>
                        <div class="ms-3">
                            <p class="mb-0">Em Curso</p>
                            <h6 class="mb-0"><?= $manutencoesCurso ?? 0 ?></h6>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="bg-success rounded d-flex align-items-center justify-content-between p-3 text-white">
                        <i class="fa fa-check-circle fa-2x"></i>
                        <div class="ms-3">
                            <p class="mb-0">Concluídas</p>
                            <h6 class="mb-0"><?= $manutencoesConcluidas ?? 0 ?></h6>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- CSS adicional -->
<style>
    .table th {
        background-color: #f8f9fa;
        font-weight: 600;
    }
    .btn-sm {
        margin: 0 2px;
    }
    .badge {
        font-size: 0.75em;
    }
    .text-truncate {
        max-width: 200px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
</style>

<script>
    // Confirmação reforçada para eliminação permanente
    document.addEventListener('DOMContentLoaded', function() {
        const deleteButtons = document.querySelectorAll('a.btn-danger[data-confirm]');
        deleteButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                const confirmationMessage = '🚨 ELIMINAÇÃO PERMANENTE 🚨\n\n' +
                    'Tem a ABSOLUTA certeza que deseja eliminar PERMANENTEMENTE esta manutenção?\n\n' +
                    '▶️ Esta ação NÃO pode ser desfeita!\n' +
                    '▶️ Todos os dados da manutenção serão PERDIDOS!\n\n' +
                    'Digite "ELIMINAR" para confirmar:';

                const userInput = prompt(confirmationMessage);
                if (userInput !== 'ELIMINAR') {
                    e.preventDefault();
                    e.stopPropagation();
                    alert('Eliminação cancelada.');
                    return false;
                }
            });
        });
    });
</script>