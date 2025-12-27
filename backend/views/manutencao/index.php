<?php
/** @var yii\web\View $this */

<<<<<<< HEAD
=======
use hail812\adminlte\widgets\Alert;
>>>>>>> origin/filipe
use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;
use yii\grid\GridView;
use yii\widgets\Pjax;
use common\models\Manutencao;

$this->title = 'Gestão de Manutenções';
<<<<<<< HEAD
?>

<div class="manutencao-index">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?= Html::encode($this->title) ?></h1>
        <?= Html::a('<i class="fa fa-plus me-2"></i>Nova Manutenção', ['create'], ['class' => 'btn btn-primary']) ?>
    </div>

    <div class="container-fluid pt-4 px-4">
        <div class="bg-light rounded p-4">

            <!-- Seção de Alertas - Itens em Manutenção sem Registo -->
            <?php if ($countEquipamentos > 0 || $countSalas > 0): ?>
                <div class="alert alert-warning mb-4">
                    <h5 class="alert-heading">
                        <i class="fa fa-exclamation-triangle me-2"></i>Itens em Manutenção sem Registo Formal
                    </h5>
                    <p class="mb-3">Existem itens marcados como "Em Manutenção" mas sem um registo de manutenção ativa:</p>

                    <div class="row">
                        <?php if ($countEquipamentos > 0): ?>
                            <div class="col-md-6">
                                <div class="card border-warning">
                                    <div class="card-header bg-warning text-dark">
                                        <strong>Equipamentos (<?= $countEquipamentos ?>)</strong>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-sm table-hover">
                                                <thead>
                                                <tr>
                                                    <th>Equipamento</th>
                                                    <th>Nº Série</th>
                                                    <th>Ações</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <?php foreach ($equipamentosSemManutencao as $equipamento): ?>
                                                    <tr>
                                                        <td><?= Html::encode($equipamento->equipamento) ?></td>
                                                        <td><?= Html::encode($equipamento->numeroSerie) ?></td>
                                                        <td>
                                                            <?= Html::a('Criar Manutenção',
                                                                ['create', 'equipamento_id' => $equipamento->id],
                                                                ['class' => 'btn btn-sm btn-outline-primary']
                                                            ) ?>
                                                            <?= Html::a('Ver',
                                                                ['/equipamento/view', 'id' => $equipamento->id],
                                                                ['class' => 'btn btn-sm btn-outline-secondary', 'target' => '_blank']
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
                            <div class="col-md-6">
                                <div class="card border-warning">
                                    <div class="card-header bg-warning text-dark">
                                        <strong>Salas (<?= $countSalas ?>)</strong>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-sm table-hover">
                                                <thead>
                                                <tr>
                                                    <th>Sala</th>
                                                    <th>Bloco</th>
                                                    <th>Ações</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <?php foreach ($salasSemManutencao as $sala): ?>
                                                    <tr>
                                                        <td><?= Html::encode($sala->nome) ?></td>
                                                        <td><?= $sala->bloco ? Html::encode($sala->bloco->nome) : '-' ?></td>
                                                        <td>
                                                            <?= Html::a('Criar Manutenção',
                                                                ['create', 'sala_id' => $sala->id],
                                                                ['class' => 'btn btn-sm btn-outline-primary']
                                                            ) ?>
                                                            <?= Html::a('Ver',
                                                                ['/sala/view', 'id' => $sala->id],
                                                                ['class' => 'btn btn-sm btn-outline-secondary', 'target' => '_blank']
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
            <?php endif; ?>

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
=======
$this->params['breadcrumbs'] = [['label' => $this->title]];
?>

    <div class="container-fluid">
        <!-- Alert Informativo -->
        <div class="row mb-3">
            <div class="col-12">
                <?= Alert::widget([
                    'type' => 'info',
                    'body' => '<h5><i class="icon fas fa-tools"></i> Gestão de Manutenções</h5>Utilize esta página para gerir todas as manutenções de equipamentos e salas do sistema',
                ]) ?>
            </div>
        </div>

        <!-- Alertas - Itens em Manutenção sem Registo -->
        <?php if ($countEquipamentos > 0 || $countSalas > 0): ?>
            <div class="row mb-4">
                <div class="col-12">
                    <div class="alert alert-warning">
                        <h5 class="alert-heading mb-2">
                            <i class="fa fa-exclamation-triangle me-2"></i>Itens em Manutenção sem Registo Formal
                        </h5>

                        <div class="row">
                            <?php if ($countEquipamentos > 0): ?>
                                <div class="col-md-6 mb-2">
                                    <div class="card border-warning">
                                        <div class="card-header bg-warning text-dark py-2">
                                            <strong><i class="fas fa-laptop-medical me-1"></i> Equipamentos (<?= $countEquipamentos ?>)</strong>
                                        </div>
                                        <div class="card-body p-0">
                                            <div class="table-responsive">
                                                <table class="table table-sm table-hover mb-0">
                                                    <thead>
                                                    <tr>
                                                        <th class="ps-3">Equipamento</th>
                                                        <th class="text-center" style="width: 100px;">Ações</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    <?php foreach ($equipamentosSemManutencao as $equipamento): ?>
                                                        <tr>
                                                            <td class="ps-3">
                                                                <?= Html::encode($equipamento->equipamento) ?>
                                                                <?php if ($equipamento->numeroSerie): ?>
                                                                    <br><small class="text-muted">Série: <?= Html::encode($equipamento->numeroSerie) ?></small>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td class="text-center">
                                                                <?= Html::a('<i class="fas fa-plus"></i> Criar',
                                                                    ['create', 'equipamento_id' => $equipamento->id],
                                                                    ['class' => 'btn btn-primary btn-sm']
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
                                <div class="col-md-6 mb-2">
                                    <div class="card border-warning">
                                        <div class="card-header bg-warning text-dark py-2">
                                            <strong><i class="fas fa-door-closed me-1"></i> Salas (<?= $countSalas ?>)</strong>
                                        </div>
                                        <div class="card-body p-0">
                                            <div class="table-responsive">
                                                <table class="table table-sm table-hover mb-0">
                                                    <thead>
                                                    <tr>
                                                        <th class="ps-3">Sala</th>
                                                        <th class="text-center" style="width: 100px;">Ações</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    <?php foreach ($salasSemManutencao as $sala): ?>
                                                        <tr>
                                                            <td class="ps-3">
                                                                <?= Html::encode($sala->nome) ?>
                                                                <?php if ($sala->bloco): ?>
                                                                    <br><small class="text-muted">Bloco: <?= Html::encode($sala->bloco->nome) ?></small>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td class="text-center">
                                                                <?= Html::a('<i class="fas fa-plus"></i> Criar',
                                                                    ['create', 'sala_id' => $sala->id],
                                                                    ['class' => 'btn btn-primary btn-sm']
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

                    <div class="card-body p-0">
                        <?php Pjax::begin(['id' => 'manutencao-grid']); ?>
                        <?= GridView::widget([
                            'dataProvider' => $dataProvider,
                            'filterModel' => $searchModel,
                            'tableOptions' => ['class' => 'table table-striped table-bordered table-hover mb-0'],
                            'options' => ['class' => 'table-responsive'],
                            'layout' => "{summary}\n{items}\n<div class='d-flex justify-content-between align-items-center p-3'>{pager}</div>",
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
                                    'value' => function($model) {
                                        return $model->dataFim ?
                                            Yii::$app->formatter->asDatetime($model->dataFim, 'php:d/m/Y H:i') :
                                            '<span class="badge bg-warning">Em andamento</span>';
                                    },
                                    'format' => 'raw',
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
                                    'headerOptions' => ['style' => 'width: 180px;', 'class' => 'text-center'],
                                    'contentOptions' => ['class' => 'text-center py-2'],
                                    'template' => '<div class="btn-group btn-group-sm">{start}{view}{update}{delete}</div>',
                                    'buttons' => [
                                        'start' => function ($url, $model, $key) {
                                            if ($model->status === Manutencao::STATUS_PENDENTE) {
                                                return Html::a('<i class="fas fa-play"></i>', ['iniciar', 'id' => $model->id], [
                                                    'class' => 'btn btn-success btn-sm',
                                                    'title' => 'Iniciar Manutenção',
                                                    'data' => [
                                                        'confirm' => 'Iniciar esta manutenção?',
                                                        'method' => 'post',
                                                    ],
                                                ]);
                                            } elseif ($model->status === Manutencao::STATUS_EM_CURSO) {
                                                return Html::a('<i class="fas fa-check"></i>', ['concluir', 'id' => $model->id], [
                                                    'class' => 'btn btn-primary btn-sm',
                                                    'title' => 'Concluir Manutenção',
                                                    'data' => [
                                                        'confirm' => 'Concluir esta manutenção?',
                                                        'method' => 'post',
                                                    ],
                                                ]);
                                            }
                                            return '';
                                        },
                                        'view' => function ($url, $model, $key) {
                                            return Html::a('<i class="fas fa-eye"></i>', $url, [
                                                'class' => 'btn btn-info btn-sm',
                                                'title' => 'Ver detalhes',
                                            ]);
                                        },
                                        'update' => function ($url, $model, $key) {
                                            return Html::a('<i class="fas fa-edit"></i>', $url, [
                                                'class' => 'btn btn-warning btn-sm',
                                                'title' => 'Editar',
                                            ]);
                                        },
                                        'delete' => function ($url, $model, $key) {
                                            return Html::a('<i class="fas fa-trash"></i>', $url, [
                                                'class' => 'btn btn-danger btn-sm',
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
>>>>>>> origin/filipe
                        </div>
                    </div>
                </div>
            </div>
<<<<<<< HEAD

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
    .alert-warning .card {
        margin-bottom: 0;
    }
    .alert-warning .table {
        margin-bottom: 0;
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
=======
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

<?php
// JavaScript para atualizar a tabela após ações
$js = <<<JS
$(document).ready(function() {
    // Atualizar a tabela após ações POST
    $(document).on('pjax:success', function(event, data, status, xhr, options) {
        if (xhr.responseURL.indexOf('iniciar') > -1 || 
            xhr.responseURL.indexOf('concluir') > -1) {
            $.pjax.reload({container: '#manutencao-grid', timeout: false});
        }
    });
});
JS;
$this->registerJs($js);
?>
>>>>>>> origin/filipe
