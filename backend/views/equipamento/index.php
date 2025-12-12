<?php
/** @var yii\web\View $this */

use hail812\adminlte\widgets\Alert;
use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;
use yii\grid\GridView;
use yii\widgets\Pjax;
use common\models\Equipamento;

$this->title = 'Gestão de Equipamentos';
$this->params['breadcrumbs'] = [['label' => $this->title]];
?>

<div class="container-fluid">
    <!-- Alert -->
    <div class="row">
        <div class="col-12">
            <?= Alert::widget([
                'type' => 'info',
                'body' => '<h4><i class="icon fas fa-microchip"></i> Gestão de Equipamentos</h4>Utilize esta página para gerir todos os equipamentos médicos do sistema',
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
                        Lista de Equipamentos
                    </h3>
                    <div class="card-tools">
                        <?= Html::a('<i class="fas fa-plus me-1"></i> Novo Equipamento', ['create'], ['class' => 'btn btn-primary btn-sm']) ?>
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
                                <?= Html::textInput('EquipamentoSearch[search]', $searchModel->search ?? '', [
                                    'class' => 'form-control',
                                    'placeholder' => 'Pesquisar por equipamento, número de série...'
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
                        'summary' => 'A mostrar <b>{begin}-{end}</b> de <b>{totalCount}</b> equipamentos',
                        'columns' => [
                            [
                                'attribute' => 'id',
                                'headerOptions' => ['style' => 'width: 70px;', 'class' => 'text-center'],
                                'contentOptions' => ['class' => 'text-center'],
                                'filterInputOptions' => ['class' => 'form-control form-control-sm', 'placeholder' => 'ID']
                            ],
                            [
                                'attribute' => 'equipamento',
                                'format' => 'raw',
                                'value' => function($model) {
                                    return Html::a(Html::encode($model->equipamento), ['view', 'id' => $model->id], [
                                        'data-pjax' => 0,
                                        'class' => 'text-primary fw-bold text-decoration-none'
                                    ]);
                                },
                                'filterInputOptions' => ['class' => 'form-control form-control-sm', 'placeholder' => 'Nome do equipamento']
                            ],
                            [
                                'attribute' => 'numeroSerie',
                                'headerOptions' => ['style' => 'width: 150px;'],
                                'filterInputOptions' => ['class' => 'form-control form-control-sm', 'placeholder' => 'Número de série']
                            ],
                            [
                                'attribute' => 'tipoEquipamento_id',
                                'label' => 'Tipo',
                                'value' => 'tipoEquipamento.nome',
                                'filter' => \common\models\TipoEquipamento::getTiposArray(),
                                'filterInputOptions' => ['class' => 'form-control form-control-sm', 'prompt' => 'Todos os tipos'],
                                'headerOptions' => ['style' => 'width: 150px;'],
                            ],
                            [
                                'attribute' => 'estado',
                                'value' => function($model) {
                                    return $model->getEstadoBadge();
                                },
                                'format' => 'raw',
                                'filter' => Equipamento::optsEstado(),
                                'filterInputOptions' => ['class' => 'form-control form-control-sm', 'prompt' => 'Todos os estados'],
                                'headerOptions' => ['style' => 'width: 140px;'],
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
                                                'confirm' => 'Tem a certeza que deseja eliminar este equipamento?',
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
                                <i class="fas fa-info-circle me-1"></i>Legenda de Estados:
                                <?php
                                $estados = Equipamento::optsEstado();
                                foreach ($estados as $valor => $label) {
                                    $badgeClass = 'bg-secondary';
                                    if ($valor === 'operacional') $badgeClass = 'bg-success';
                                    if ($valor === 'manutencao') $badgeClass = 'bg-warning';
                                    if ($valor === 'emUso') $badgeClass = 'bg-primary';
                                    if ($valor === 'avariado') $badgeClass = 'bg-danger';
                                    echo '<span class="badge ' . $badgeClass . ' me-1">' . $label . '</span>';
                                }
                                ?>
                            </small>
                        </div>
                        <div class="col-md-6 text-end">
                            <small class="text-muted">
                                <i class="fas fa-database me-1"></i>
                                Total: <strong><?= $dataProvider->getTotalCount() ?? 0 ?></strong> equipamentos
                            </small>
                        </div>
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
                <h3><?= $totalEquipamentosCount ?? 0 ?></h3>
                <p>Total Equipamentos</p>
            </div>
            <div class="icon">
                <i class="fas fa-microchip"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3><?= $operacionaisCount ?? 0 ?></h3>
                <p>Operacionais</p>
            </div>
            <div class="icon">
                <i class="fas fa-check-circle"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3><?= $manutencaoCount ?? 0 ?></h3>
                <p>Em Manutenção</p>
            </div>
            <div class="icon">
                <i class="fas fa-tools"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-primary">
            <div class="inner">
                <h3><?= $emUsoCount ?? 0 ?></h3>
                <p>Em Uso</p>
            </div>
            <div class="icon">
                <i class="fas fa-procedures"></i>
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
    .badge {
        font-size: 0.75em;
        font-weight: 500;
    }
    .card-title {
        font-weight: 600;
    }
    .table-responsive {
        border-radius: 0.25rem;
    }
</style>