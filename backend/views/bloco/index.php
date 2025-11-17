<?php
/** @var yii\web\View $this */

use hail812\adminlte\widgets\Alert;
use hail812\adminlte\widgets\SmallBox;
use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;
use yii\grid\GridView;
use yii\widgets\Pjax;
use common\models\Bloco;

$this->title = 'Gestão de Blocos Operatórios';
$this->params['breadcrumbs'] = [['label' => $this->title]];
?>

<div class="container-fluid">
    <!-- Alert -->
    <div class="row">
        <div class="col-12">
            <?= Alert::widget([
                'type' => 'info',
                'body' => '<h4><i class="icon fas fa-hospital"></i> Gestão de Blocos Operatórios</h4>Utilize esta página para gerir todos os blocos operatórios do sistema',
            ]) ?>
        </div>
    </div>

    <!-- Estatísticas com SmallBox -->
    <div class="row mb-4">
        <div class="col-lg-3 col-6">
            <?= SmallBox::widget([
                'title' => $totalBlocosCount ?? 0,
                'text' => 'Total Blocos',
                'icon' => 'fas fa-building',
                'theme' => 'info'
            ]) ?>
        </div>
        <div class="col-lg-3 col-6">
            <?= SmallBox::widget([
                'title' => $activeBlocosCount ?? 0,
                'text' => 'Blocos Ativos',
                'icon' => 'fas fa-check-circle',
                'theme' => 'success'
            ]) ?>
        </div>
        <div class="col-lg-3 col-6">
            <?= SmallBox::widget([
                'title' => $inactiveBlocosCount ?? 0,
                'text' => 'Blocos Inativos',
                'icon' => 'fas fa-times-circle',
                'theme' => 'warning'
            ]) ?>
        </div>
        <div class="col-lg-3 col-6">
            <?= SmallBox::widget([
                'title' => $totalSalasCount ?? 0,
                'text' => 'Total Salas',
                'icon' => 'fas fa-door-open',
                'theme' => 'primary'
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
                        Lista de Blocos Operatórios
                    </h3>
                    <div class="card-tools">
                        <?= Html::a('<i class="fas fa-plus me-1"></i> Adicionar Bloco', ['create'], ['class' => 'btn btn-primary btn-sm']) ?>
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
                                <?= Html::textInput('BlocoSearch[nome]', $searchModel->nome ?? '', [
                                    'class' => 'form-control',
                                    'placeholder' => 'Pesquisar por nome do bloco...'
                                ]) ?>
                                <?= Html::submitButton('<i class="fas fa-search"></i> Pesquisar', ['class' => 'btn btn-primary']) ?>
                                <?= Html::a('<i class="fas fa-redo"></i>', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
                            </div>

                            <?php ActiveForm::end(); ?>
                        </div>
                    </div>

                    <!-- Tabela CRUD -->
                    <?php Pjax::begin(['timeout' => 5000]); ?>
                    <?= GridView::widget([
                        'dataProvider' => $dataProvider,
                        'filterModel' => $searchModel,
                        'tableOptions' => ['class' => 'table table-striped table-bordered table-hover mb-0'],
                        'options' => ['class' => 'table-responsive'],
                        'layout' => "{summary}\n{items}\n<div class='d-flex justify-content-between align-items-center'>{pager}</div>",
                        'summary' => 'A mostrar <b>{begin}-{end}</b> de <b>{totalCount}</b> blocos',
                        'columns' => [
                            [
                                'attribute' => 'id',
                                'headerOptions' => ['style' => 'width: 70px;', 'class' => 'text-center'],
                                'contentOptions' => ['class' => 'text-center'],
                                'filterInputOptions' => ['class' => 'form-control form-control-sm', 'placeholder' => 'ID']
                            ],
                            [
                                'attribute' => 'nome',
                                'format' => 'raw',
                                'value' => function($model) {
                                    return Html::a(Html::encode($model->nome), ['view', 'id' => $model->id], [
                                        'data-pjax' => 0,
                                        'class' => 'text-primary fw-bold text-decoration-none'
                                    ]);
                                },
                                'filterInputOptions' => ['class' => 'form-control form-control-sm', 'placeholder' => 'Nome do bloco']
                            ],
                            [
                                'attribute' => 'estado',
                                'value' => function($model) {
                                    $badges = [
                                        'ativo' => '<span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Ativo</span>',
                                        'inativo' => '<span class="badge bg-secondary"><i class="fas fa-times-circle me-1"></i>Inativo</span>',
                                        'manutencao' => '<span class="badge bg-warning"><i class="fas fa-tools me-1"></i>Manutenção</span>'
                                    ];
                                    return $badges[$model->estado] ?? '<span class="badge bg-danger"><i class="fas fa-question-circle me-1"></i>Desconhecido</span>';
                                },
                                'format' => 'raw',
                                'filter' => Bloco::optsEstado(),
                                'filterInputOptions' => ['class' => 'form-control form-control-sm', 'prompt' => 'Todos'],
                                'headerOptions' => ['style' => 'width: 140px;'],
                                'contentOptions' => ['class' => 'text-center'],
                            ],
                            [
                                'label' => '<i class="fas fa-door-open" title="Número de Salas"></i> Nº Salas',
                                'encodeLabel' => false,
                                'value' => function($model) {
                                    $count = $model->salas ? count($model->salas) : 0;
                                    return $count > 0 ?
                                        '<span class="text-primary fw-bold">' . $count . '</span>' :
                                        '<span class="text-muted">' . $count . '</span>';
                                },
                                'format' => 'raw',
                                'headerOptions' => ['style' => 'width: 100px;', 'class' => 'text-center'],
                                'contentOptions' => ['class' => 'text-center'],
                            ],
                            [
                                'label' => '<i class="fas fa-circle-check text-success" title="Salas Livres"></i> Livres',
                                'encodeLabel' => false,
                                'value' => function($model) {
                                    if (!$model->salas) return '<span class="text-muted">0</span>';
                                    $livreCount = 0;
                                    foreach ($model->salas as $sala) {
                                        if ($sala->estado === 'Livre') {
                                            $livreCount++;
                                        }
                                    }
                                    return $livreCount > 0 ?
                                        '<span class="text-success fw-bold">' . $livreCount . '</span>' :
                                        '<span class="text-muted">' . $livreCount . '</span>';
                                },
                                'format' => 'raw',
                                'headerOptions' => ['style' => 'width: 90px;', 'class' => 'text-center'],
                                'contentOptions' => ['class' => 'text-center'],
                            ],
                            [
                                'label' => '<i class="fas fa-procedures text-danger" title="Salas Em Uso"></i> Em Uso',
                                'encodeLabel' => false,
                                'value' => function($model) {
                                    if (!$model->salas) return '<span class="text-muted">0</span>';
                                    $emUsoCount = 0;
                                    foreach ($model->salas as $sala) {
                                        if ($sala->estado === 'EmUso') {
                                            $emUsoCount++;
                                        }
                                    }
                                    return $emUsoCount > 0 ?
                                        '<span class="text-danger fw-bold">' . $emUsoCount . '</span>' :
                                        '<span class="text-muted">' . $emUsoCount . '</span>';
                                },
                                'format' => 'raw',
                                'headerOptions' => ['style' => 'width: 100px;', 'class' => 'text-center'],
                                'contentOptions' => ['class' => 'text-center'],
                            ],
                            [
                                'label' => '<i class="fas fa-tools text-warning" title="Salas em Manutenção"></i> Manutenção',
                                'encodeLabel' => false,
                                'value' => function($model) {
                                    if (!$model->salas) return '<span class="text-muted">0</span>';
                                    $manutencaoCount = 0;
                                    foreach ($model->salas as $sala) {
                                        if ($sala->estado === 'Manutencao') {
                                            $manutencaoCount++;
                                        }
                                    }
                                    return $manutencaoCount > 0 ?
                                        '<span class="text-warning fw-bold">' . $manutencaoCount . '</span>' :
                                        '<span class="text-muted">' . $manutencaoCount . '</span>';
                                },
                                'format' => 'raw',
                                'headerOptions' => ['style' => 'width: 120px;', 'class' => 'text-center'],
                                'contentOptions' => ['class' => 'text-center'],
                            ],
                            [
                                'class' => 'yii\grid\ActionColumn',
                                'header' => '<i class="fas fa-cogs"></i> Ações',
                                'headerOptions' => ['style' => 'width: 160px;', 'class' => 'text-center'],
                                'contentOptions' => ['class' => 'text-center py-2'],
                                'template' => '<div class="btn-group btn-group-sm">{view} {salas} {update} {delete}</div>',
                                'buttons' => [
                                    'view' => function ($url, $model, $key) {
                                        return Html::a('<i class="fas fa-eye"></i>', $url, [
                                            'class' => 'btn btn-info',
                                            'title' => 'Ver detalhes',
                                            'data-pjax' => 0,
                                            'data-bs-toggle' => 'tooltip',
                                        ]);
                                    },
                                    'salas' => function ($url, $model, $key) {
                                        $hasSalas = $model->salas && count($model->salas) > 0;
                                        return Html::a('<i class="fas fa-door-open"></i>', ['sala/index', 'SalaSearch[bloco_id]' => $model->id], [
                                            'class' => 'btn ' . ($hasSalas ? 'btn-success' : 'btn-outline-success'),
                                            'title' => 'Ver Salas (' . ($model->salas ? count($model->salas) : 0) . ')',
                                            'data-pjax' => 0,
                                            'data-bs-toggle' => 'tooltip',
                                        ]);
                                    },
                                    'update' => function ($url, $model, $key) {
                                        return Html::a('<i class="fas fa-edit"></i>', $url, [
                                            'class' => 'btn btn-warning',
                                            'title' => 'Editar bloco',
                                            'data-pjax' => 0,
                                            'data-bs-toggle' => 'tooltip',
                                        ]);
                                    },
                                    'delete' => function ($url, $model, $key) {
                                        // Verificar se tem salas associadas
                                        if ($model->salas && count($model->salas) > 0) {
                                            return Html::button('<i class="fas fa-trash"></i>', [
                                                'class' => 'btn btn-danger disabled',
                                                'title' => 'Não pode eliminar - tem ' . count($model->salas) . ' sala(s) associada(s)',
                                                'disabled' => true,
                                                'data-bs-toggle' => 'tooltip',
                                            ]);
                                        }

                                        return Html::a('<i class="fas fa-trash"></i>', $url, [
                                            'class' => 'btn btn-danger',
                                            'title' => 'Eliminar bloco',
                                            'data' => [
                                                'confirm' => 'Tem a certeza que deseja eliminar o bloco \"' . $model->nome . '\"?\n\nEsta ação não pode ser desfeita!',
                                                'method' => 'post',
                                            ],
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
                                <span class="badge bg-success me-1"><i class="fas fa-check-circle me-1"></i>Ativo</span>
                                <span class="badge bg-secondary me-1"><i class="fas fa-times-circle me-1"></i>Inativo</span>
                                <span class="badge bg-warning"><i class="fas fa-tools me-1"></i>Manutenção</span>
                            </small>
                        </div>
                        <div class="col-md-6 text-end">
                            <small class="text-muted">
                                <i class="fas fa-database me-1"></i>
                                Total: <strong><?= $dataProvider->getTotalCount() ?? 0 ?></strong> blocos operatórios
                            </small>
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
    .btn.disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
    .card-title {
        font-weight: 600;
    }
    .table-responsive {
        border-radius: 0.25rem;
    }
    .text-muted {
        font-size: 0.85em;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Confirmação melhorada para eliminação
        const deleteButtons = document.querySelectorAll('a.btn-danger[data-confirm]');
        deleteButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                const blocoName = this.closest('tr').querySelector('a.text-primary')?.textContent || 'este bloco';
                const confirmationMessage = '🚨 ELIMINAÇÃO DE BLOCO OPERATÓRIO 🚨\n\n' +
                    'Tem a ABSOLUTA certeza que deseja eliminar PERMANENTEMENTE o bloco:\n' +
                    '➤ ' + blocoName + '\n\n' +
                    '▶️ Esta ação NÃO pode ser desfeita!\n' +
                    '▶️ Todos os dados do bloco serão PERDIDOS!\n\n' +
                    'Digite "ELIMINAR" para confirmar:';

                const userInput = prompt(confirmationMessage);
                if (userInput !== 'ELIMINAR') {
                    e.preventDefault();
                    e.stopPropagation();
                    alert('❌ Eliminação cancelada.');
                    return false;
                }
            });
        });
    });
</script>