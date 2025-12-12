<?php
/** @var yii\web\View $this */

use hail812\adminlte\widgets\Alert;
use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;
use yii\grid\GridView;
use yii\widgets\Pjax;
use common\models\Manutencao;

$this->title = 'Gestão de Manutenções';
$this->params['breadcrumbs'] = [['label' => $this->title]];
?>

<div class="container-fluid">
    <!-- Alert -->
    <div class="row">
        <div class="col-12">
            <?= Alert::widget([
                'type' => 'info',
                'body' => '<h4><i class="icon fas fa-tools"></i> Gestão de Manutenções</h4>Utilize esta página para gerir todas as manutenções de equipamentos e salas do sistema',
            ]) ?>
        </div>
    </div>

    <!-- Alertas - Itens em Manutenção sem Registo -->
    <?php if ($countEquipamentos > 0 || $countSalas > 0): ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="alert alert-warning">
                    <h5 class="alert-heading mb-3">
                        <i class="fa fa-exclamation-triangle me-2"></i>Itens em Manutenção sem Registo Formal
                    </h5>

                    <div class="row">
                        <?php if ($countEquipamentos > 0): ?>
                            <div class="col-md-6 mb-3">
                                <div class="card border-warning">
                                    <div class="card-header bg-warning text-dark">
                                        <strong><i class="fas fa-laptop-medical me-1"></i> Equipamentos (<?= $countEquipamentos ?>)</strong>
                                    </div>
                                    <div class="card-body p-0">
                                        <div class="table-responsive">
                                            <table class="table table-sm table-hover mb-0">
                                                <thead>
                                                <tr>
                                                    <th class="ps-3">Equipamento</th>
                                                    <th>Nº Série</th>
                                                    <th class="text-center" style="width: 120px;">Ações</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <?php foreach ($equipamentosSemManutencao as $equipamento): ?>
                                                    <tr>
                                                        <td class="ps-3"><?= Html::encode($equipamento->equipamento) ?></td>
                                                        <td><?= Html::encode($equipamento->numeroSerie) ?></td>
                                                        <td class="text-center">
                                                            <?= Html::a('<i class="fas fa-plus"></i>',
                                                                ['create', 'equipamento_id' => $equipamento->id],
                                                                [
                                                                    'class' => 'btn btn-sm btn-primary',
                                                                    'title' => 'Criar manutenção'
                                                                ]
                                                            ) ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ($countSalas > 0): ?>
                            <div class="col-md-6 mb-3">
                                <div class="card border-warning">
                                    <div class="card-header bg-warning text-dark">
                                        <strong><i class="fas fa-door-closed me-1"></i> Salas (<?= $countSalas ?>)</strong>
                                    </div>
                                    <div class="card-body p-0">
                                        <div class="table-responsive">
                                            <table class="table table-sm table-hover mb-0">
                                                <thead>
                                                <tr>
                                                    <th class="ps-3">Sala</th>
                                                    <th>Bloco</th>
                                                    <th class="text-center" style="width: 120px;">Ações</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <?php foreach ($salasSemManutencao as $sala): ?>
                                                    <tr>
                                                        <td class="ps-3"><?= Html::encode($sala->nome) ?></td>
                                                        <td><?= $sala->bloco ? Html::encode($sala->bloco->nome) : '-' ?></td>
                                                        <td class="text-center">
                                                            <?= Html::a('<i class="fas fa-plus"></i>',
                                                                ['create', 'sala_id' => $sala->id],
                                                                [
                                                                    'class' => 'btn btn-sm btn-primary',
                                                                    'title' => 'Criar manutenção'
                                                                ]
                                                            ) ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Card Principal -->
    <div class="row">
        <div class="col-12">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-list me-2"></i>
                        Lista de Manutenções
                    </h3>
                    <div class="card-tools">
                        <?= Html::a('<i class="fas fa-plus me-1"></i> Nova Manutenção', ['create'], ['class' => 'btn btn-primary btn-sm']) ?>
                    </div>
                </div>
                <div class="card-body">

                    <!-- Formulário de Pesquisa -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <?php $form = ActiveForm::begin([
                                'method' => 'get',
                                'action' => ['index'],
                            ]); ?>

                            <div class="input-group input-group-sm">
                                <?= Html::textInput('search', Yii::$app->request->get('search'), [
                                    'class' => 'form-control',
                                    'placeholder' => 'Pesquisar manutenções...'
                                ]) ?>
                                <?= Html::submitButton('<i class="fas fa-search"></i> Pesquisar', ['class' => 'btn btn-primary']) ?>
                                <?= Html::a('<i class="fas fa-redo"></i>', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
                            </div>

                            <?php ActiveForm::end(); ?>
                        </div>
                    </div>

                    <!-- Tabela CRUD -->
                    <?php Pjax::begin(); ?>
                    <?= GridView::widget([
                        'dataProvider' => $dataProvider,
                        'filterModel' => $searchModel,
                        'tableOptions' => ['class' => 'table table-striped table-bordered table-hover mb-0'],
                        'options' => ['class' => 'table-responsive'],
                        'layout' => "{summary}\n{items}\n<div class='d-flex justify-content-between align-items-center'>{pager}</div>",
                        'summary' => 'A mostrar <b>{begin}-{end}</b> de <b>{totalCount}</b> manutenções',
                        'columns' => [
                            [
                                'attribute' => 'id',
                                'headerOptions' => ['style' => 'width: 70px;', 'class' => 'text-center'],
                                'contentOptions' => ['class' => 'text-center'],
                            ],
                            [
                                'attribute' => 'equipamentoNome',
                                'label' => 'Equipamento',
                                'value' => 'equipamento.equipamento',
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
                                'headerOptions' => ['style' => 'width: 140px;'],
                                'contentOptions' => ['class' => 'text-center'],
                            ],
                            [
                                'attribute' => 'dataFim',
                                'format' => ['datetime', 'php:d/m/Y H:i'],
                                'headerOptions' => ['style' => 'width: 140px;'],
                                'contentOptions' => ['class' => 'text-center'],
                            ],
                            [
                                'attribute' => 'status',
                                'value' => function($model) {
                                    return $model->getStatusBadge();
                                },
                                'format' => 'raw',
                                'filter' => Manutencao::getStatusOptions(),
                                'headerOptions' => ['style' => 'width: 130px;'],
                                'contentOptions' => ['class' => 'text-center'],
                            ],
                            [
                                'class' => 'yii\grid\ActionColumn',
                                'header' => '<i class="fas fa-cogs"></i> Ações',
                                'headerOptions' => ['style' => 'width: 150px;', 'class' => 'text-center'],
                                'contentOptions' => ['class' => 'text-center py-2'],
                                'template' => '<div class="btn-group btn-group-sm">{view} {update} {delete}</div>',
                                'buttons' => [
                                    'view' => function ($url, $model, $key) {
                                        return Html::a('<i class="fas fa-eye"></i>', $url, [
                                            'class' => 'btn btn-info',
                                            'title' => 'Ver detalhes',
                                            'data-pjax' => 0,
                                        ]);
                                    },
                                    'update' => function ($url, $model, $key) {
                                        return Html::a('<i class="fas fa-edit"></i>', $url, [
                                            'class' => 'btn btn-warning',
                                            'title' => 'Editar',
                                            'data-pjax' => 0,
                                        ]);
                                    },
                                    'delete' => function ($url, $model, $key) {
                                        return Html::a('<i class="fas fa-trash"></i>', $url, [
                                            'class' => 'btn btn-danger',
                                            'title' => 'Eliminar',
                                            'data' => [
                                                'confirm' => 'Tem a certeza que deseja eliminar esta manutenção?',
                                                'method' => 'post',
                                            ],
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
                                <i class="fas fa-info-circle me-1"></i>Legenda de Status
                            </small>
                        </div>
                        <div class="col-md-6 text-end">
                            <small class="text-muted">
                                <i class="fas fa-database me-1"></i>
                                Total: <strong><?= $dataProvider->getTotalCount() ?? 0 ?></strong> manutenções
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Estatísticas -->
    <div class="row mb-4">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3><?= $totalManutencoes ?? 0 ?></h3>
                    <p>Total Manutenções</p>
                </div>
                <div class="icon">
                    <i class="fas fa-tools"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3><?= $manutencoesPendentes ?? 0 ?></h3>
                    <p>Pendentes</p>
                </div>
                <div class="icon">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3><?= $manutencoesCurso ?? 0 ?></h3>
                    <p>Em Curso</p>
                </div>
                <div class="icon">
                    <i class="fas fa-play-circle"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3><?= $manutencoesConcluidas ?? 0 ?></h3>
                    <p>Concluídas</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .small-box {
        border-radius: 0.25rem;
        position: relative;
        display: block;
        margin-bottom: 20px;
        box-shadow: 0 1px 1px rgba(0,0,0,0.1);
        color: white;
    }
    .small-box > .inner {
        padding: 10px;
    }
    .small-box h3 {
        font-size: 2.2rem;
        font-weight: bold;
        margin: 0 0 10px 0;
        white-space: nowrap;
        padding: 0;
    }
    .small-box p {
        font-size: 1rem;
        margin: 0;
    }
    .small-box .icon {
        position: absolute;
        top: 15px;
        right: 15px;
        z-index: 0;
        font-size: 70px;
        color: rgba(0,0,0,0.15);
    }
    .small-box:hover {
        text-decoration: none;
        color: #f9f9f9;
    }

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
    .card-title {
        font-weight: 600;
    }
    .table-responsive {
        border-radius: 0.25rem;
    }
</style>