<?php
/** @var yii\web\View $this */

use hail812\adminlte\widgets\Alert;
use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;
use yii\grid\GridView;
use yii\widgets\Pjax;
use common\models\Manutencao;

$this->title = 'Gestão de Manutenções';
?>

<!-- Alert Básico -->
<div class="row">
    <div class="col-12">
        <?= Alert::widget([
            'type' => 'info',
            'body' => '<h4><i class="icon fas fa-hospital"></i> Gestão de Blocos Operatórios</h4>Utilize esta página para gerir todos os blocos operatórios do sistema',
        ]) ?>
    </div>
</div>

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
                                                                [
                                                                    'class' => 'btn btn-sm btn-outline-primary',
                                                                    'title' => 'Criar manutenção para este equipamento. Você poderá adicionar uma sala se necessário.',
                                                                    'data-bs-toggle' => 'tooltip',
                                                                    'data-bs-placement' => 'top'
                                                                ]
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
                                                                ['create', 'equipamento_id' => $sala->id],
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
            <?php Pjax::begin(['id' => 'manutencao-grid']); ?>
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
                                        'confirm' => 'Tem a certeza que deseja eliminar PERMANENTEMENTE esta manutenção?\n\nEsta ação NÃO pode ser desfeita!',
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