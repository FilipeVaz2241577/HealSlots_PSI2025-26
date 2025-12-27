<?php
/** @var yii\web\View $this */

use hail812\adminlte\widgets\Alert;
use hail812\adminlte\widgets\SmallBox;
use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;
use yii\grid\GridView;
use yii\widgets\Pjax;
use common\models\Sala;
use common\models\Bloco;

$this->title = 'Gestão de Salas Operatórias';
$this->params['breadcrumbs'] = [['label' => $this->title]];

// Debug - verificar se as variáveis estão a chegar
// echo "<!-- DEBUG: totalSalasCount: " . ($totalSalasCount ?? 'NULL') . " -->";
// echo "<!-- DEBUG: salasLivresCount: " . ($salasLivresCount ?? 'NULL') . " -->";
// echo "<!-- DEBUG: salasEmUsoCount: " . ($salasEmUsoCount ?? 'NULL') . " -->";
// echo "<!-- DEBUG: salasManutencaoCount: " . ($salasManutencaoCount ?? 'NULL') . " -->";
?>

<div class="container-fluid">
    <!-- Alert -->
    <div class="row">
        <div class="col-12">
            <?= Alert::widget([
                'type' => 'info',
                'body' => '<h4><i class="icon fas fa-door-open"></i> Gestão de Salas Operatórias</h4>Utilize esta página para gerir todas as salas operatórias do sistema',
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
                        Lista de Salas Operatórias
                    </h3>
                    <div class="card-tools">
                        <?= Html::a('<i class="fas fa-plus me-1"></i> Adicionar Sala', ['create'], ['class' => 'btn btn-primary btn-sm']) ?>
                    </div>
                </div>
                <div class="card-body">



                    <!-- Tabela CRUD -->
                    <?php Pjax::begin(['timeout' => 5000]); ?>
                    <?= GridView::widget([
                        'dataProvider' => $dataProvider,
                        'filterModel' => $searchModel,
                        'tableOptions' => ['class' => 'table table-striped table-bordered table-hover mb-0'],
                        'options' => ['class' => 'table-responsive'],
                        'layout' => "{summary}\n{items}\n<div class='d-flex justify-content-between align-items-center'>{pager}</div>",
                        'summary' => 'A mostrar <b>{begin}-{end}</b> de <b>{totalCount}</b> salas',
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
                                'filterInputOptions' => ['class' => 'form-control form-control-sm', 'placeholder' => 'Nome da sala']
                            ],
                            [
                                'attribute' => 'blocoName',
                                'label' => 'Bloco',
                                'format' => 'raw',
                                'value' => function($model) {
                                    return $model->bloco ?
                                        Html::a(Html::encode($model->bloco->nome), ['bloco/view', 'id' => $model->bloco_id], [
                                            'class' => 'text-success fw-bold text-decoration-none',
                                            'data-pjax' => 0,
                                        ]) :
                                        '<span class="text-muted">N/A</span>';
                                },
                                'filterInputOptions' => ['class' => 'form-control form-control-sm', 'placeholder' => 'Nome do bloco']
                            ],
                            [
                                'attribute' => 'estado',
                                'value' => function($model) {
                                    $badges = [
                                        'Livre' => '<span class="badge bg-success"><i class="fas fa-circle-check me-1"></i>Livre</span>',
                                        'EmUso' => '<span class="badge bg-danger"><i class="fas fa-procedures me-1"></i>Em Uso</span>',
                                        'Manutencao' => '<span class="badge bg-warning"><i class="fas fa-tools me-1"></i>Manutenção</span>',
                                        'Desativada' => '<span class="badge bg-secondary"><i class="fas fa-ban me-1"></i>Desativada</span>'
                                    ];
                                    return $badges[$model->estado] ?? '<span class="badge bg-dark"><i class="fas fa-question-circle me-1"></i>Desconhecido</span>';
                                },
                                'format' => 'raw',
                                'filter' => Sala::optsEstado(),
                                'filterInputOptions' => ['class' => 'form-control form-control-sm', 'prompt' => 'Todos'],
                                'headerOptions' => ['style' => 'width: 140px;'],
                                'contentOptions' => ['class' => 'text-center'],
                            ],
                            [
                                'class' => 'yii\grid\ActionColumn',
                                'header' => '<i class="fas fa-cogs"></i> Ações',
                                'headerOptions' => ['style' => 'width: 120px;', 'class' => 'text-center'],
                                'contentOptions' => ['class' => 'text-center py-2'],
                                'template' => '<div class="btn-group btn-group-sm">{view} {update} {delete}</div>',
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
                                        return Html::a('<i class="fas fa-edit"></i>', $url, [
                                            'class' => 'btn btn-warning',
                                            'title' => 'Editar sala',
                                            'data-pjax' => 0,
                                            'data-bs-toggle' => 'tooltip',
                                        ]);
                                    },
                                    'delete' => function ($url, $model, $key) {
                                        return Html::a('<i class="fas fa-trash"></i>', $url, [
                                            'class' => 'btn btn-danger',
                                            'title' => 'Eliminar sala',
                                            'data' => [
                                                'confirm' => 'Tem a certeza que deseja eliminar a sala \"' . $model->nome . '\"?\n\nEsta ação não pode ser desfeita!',
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
                                <span class="badge bg-success me-1"><i class="fas fa-circle-check me-1"></i>Livre</span>
                                <span class="badge bg-danger me-1"><i class="fas fa-procedures me-1"></i>Em Uso</span>
                                <span class="badge bg-warning me-1"><i class="fas fa-tools me-1"></i>Manutenção</span>
                                <span class="badge bg-secondary"><i class="fas fa-ban me-1"></i>Desativada</span>
                            </small>
                        </div>
                        <div class="col-md-6 text-end">
                            <small class="text-muted">
                                <i class="fas fa-database me-1"></i>
                                Total: <strong><?= $dataProvider->getTotalCount() ?? 0 ?></strong> salas operatórias
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Estatísticas com SmallBox -->
<div class="row mb-4">
    <div class="col-lg-3 col-6">
        <?= SmallBox::widget([
            'title' => $totalSalasCount ?? 0,
            'text' => 'Total Salas',
            'icon' => 'fas fa-door-open',
            'theme' => 'info'
        ]) ?>
    </div>
    <div class="col-lg-3 col-6">
        <?= SmallBox::widget([
            'title' => $salasLivresCount ?? 0,
            'text' => 'Salas Livres',
            'icon' => 'fas fa-circle-check',
            'theme' => 'success'
        ]) ?>
    </div>
    <div class="col-lg-3 col-6">
        <?= SmallBox::widget([
            'title' => $salasEmUsoCount ?? 0,
            'text' => 'Salas Em Uso',
            'icon' => 'fas fa-procedures',
            'theme' => 'danger'
        ]) ?>
    </div>
    <div class="col-lg-3 col-6">
        <?= SmallBox::widget([
            'title' => $salasManutencaoCount ?? 0,
            'text' => 'Em Manutenção',
            'icon' => 'fas fa-tools',
            'theme' => 'warning'
        ]) ?>
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

        // Confirmação para eliminação
        const deleteButtons = document.querySelectorAll('a.btn-danger[data-confirm]');
        deleteButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                const salaName = this.closest('tr').querySelector('a.text-primary')?.textContent || 'esta sala';
                const confirmationMessage = '🚨 ELIMINAÇÃO DE SALA OPERATÓRIA 🚨\n\n' +
                    'Tem a ABSOLUTA certeza que deseja eliminar PERMANENTEMENTE a sala:\n' +
                    '➤ ' + salaName + '\n\n' +
                    '▶️ Esta ação NÃO pode ser desfeita!\n' +
                    '▶️ Todos os dados da sala serão PERDIDOS!\n\n' +
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