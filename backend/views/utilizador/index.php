<?php
/** @var yii\web\View $this */

use hail812\adminlte\widgets\Alert;
use hail812\adminlte\widgets\SmallBox;
use yii\bootstrap5\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use common\models\User;

$this->title = 'Gestão de Utilizadores';
$this->params['breadcrumbs'] = [['label' => $this->title]];
?>

<div class="container-fluid">
    <!-- Alert -->
    <div class="row">
        <div class="col-12">
            <?= Alert::widget([
                'type' => 'info',
                'body' => '<h4><i class="icon fas fa-users"></i> Gestão de Utilizadores</h4>Utilize esta página para gerir todos os utilizadores do sistema. Utilizadores inativos aparecem destacados.',
            ]) ?>
        </div>
    </div>

    <!-- Estatísticas com SmallBox -->
    <div class="row mb-4">
        <div class="col-lg-3 col-6">
            <?= SmallBox::widget([
                'title' => $totalUsersCount ?? 0,
                'text' => 'Total Utilizadores',
                'icon' => 'fas fa-users',
                'theme' => 'info'
            ]) ?>
        </div>
        <div class="col-lg-3 col-6">
            <?= SmallBox::widget([
                'title' => $activeUsersCount ?? 0,
                'text' => 'Utilizadores Ativos',
                'icon' => 'fas fa-user-check',
                'theme' => 'success'
            ]) ?>
        </div>
        <div class="col-lg-3 col-6">
            <?= SmallBox::widget([
                'title' => $inactiveUsersCount ?? 0,
                'text' => 'Utilizadores Inativos',
                'icon' => 'fas fa-user-clock',
                'theme' => 'warning'
            ]) ?>
        </div>
        <div class="col-lg-3 col-6">
            <?= SmallBox::widget([
                'title' => $differentRolesCount ?? 0,
                'text' => 'Diferentes Roles',
                'icon' => 'fas fa-user-shield',
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
                        Lista de Todos os Utilizadores
                    </h3>
                    <div class="card-tools">
                        <?= Html::a('<i class="fas fa-plus me-1"></i> Adicionar Utilizador', ['create'], ['class' => 'btn btn-primary btn-sm']) ?>
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
                        'summary' => 'A mostrar <b>{begin}-{end}</b> de <b>{totalCount}</b> utilizadores',
                        'rowOptions' => function ($model, $key, $index, $grid) {
                            // Destacar utilizadores inativos
                            if ($model->status === User::STATUS_INACTIVE) {
                                return ['class' => 'table-warning', 'title' => 'Utilizador Inativo'];
                            }
                            return [];
                        },
                        'columns' => [
                            [
                                'attribute' => 'id',
                                'headerOptions' => ['style' => 'width: 70px;', 'class' => 'text-center'],
                                'contentOptions' => ['class' => 'text-center'],
                                'filterInputOptions' => ['class' => 'form-control form-control-sm', 'placeholder' => 'ID']
                            ],
                            [
                                'attribute' => 'username',
                                'format' => 'raw',
                                'value' => function($model) {
                                    $icon = '';
                                    if ($model->status === User::STATUS_INACTIVE) {
                                        $icon = ' <i class="fas fa-user-clock text-warning" title="Utilizador Inativo"></i>';
                                    }
                                    return Html::a($model->username . $icon, ['view', 'id' => $model->id], [
                                        'data-pjax' => 0,
                                        'class' => ($model->status === User::STATUS_INACTIVE ? 'text-warning' : 'text-primary') . ' fw-bold text-decoration-none'
                                    ]);
                                },
                                'filterInputOptions' => ['class' => 'form-control form-control-sm', 'placeholder' => 'Username']
                            ],
                            [
                                'attribute' => 'email',
                                'format' => 'email',
                                'filterInputOptions' => ['class' => 'form-control form-control-sm', 'placeholder' => 'Email']
                            ],
                            [
                                'attribute' => 'role',
                                'label' => 'Role',
                                'value' => function($model) {
                                    $auth = Yii::$app->authManager;
                                    $roles = $auth->getRolesByUser($model->id);
                                    $roleNames = [];
                                    foreach ($roles as $role) {
                                        $roleNames[] = $role->name;
                                    }
                                    return !empty($roleNames) ? implode(', ', $roleNames) : '<span class="text-muted">Sem role</span>';
                                },
                                'format' => 'raw',
                                'filter' => $rolesList,
                                'filterInputOptions' => ['class' => 'form-control form-control-sm', 'prompt' => 'Todas'],
                                'headerOptions' => ['style' => 'width: 150px;'],
                            ],
                            [
                                'attribute' => 'status',
                                'value' => function($model) {
                                    $statusLabels = [
                                        User::STATUS_ACTIVE => '<span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Ativo</span>',
                                        User::STATUS_INACTIVE => '<span class="badge bg-warning"><i class="fas fa-clock me-1"></i>Inativo</span>',
                                        User::STATUS_DELETED => '<span class="badge bg-danger"><i class="fas fa-times-circle me-1"></i>Eliminado</span>',
                                    ];
                                    return $statusLabels[$model->status] ?? '<span class="badge bg-secondary">Desconhecido</span>';
                                },
                                'format' => 'raw',
                                'filter' => [
                                    '' => 'Todos',
                                    User::STATUS_ACTIVE => 'Ativo',
                                    User::STATUS_INACTIVE => 'Inativo'
                                ],
                                'filterInputOptions' => ['class' => 'form-control form-control-sm'],
                                'headerOptions' => ['style' => 'width: 120px;'],
                                'contentOptions' => ['class' => 'text-center'],
                            ],
                            [
                                'attribute' => 'created_at',
                                'label' => 'Criado em',
                                'format' => ['datetime', 'php:d/m/Y H:i'],
                                'headerOptions' => ['style' => 'width: 180px;'],
                                'filter' => '<div class="d-flex" style="gap: 2px;">
                                    <input type="date" class="form-control form-control-sm" name="UserSearch[created_at_start]" value="' . ($searchModel->created_at_start ?? '') . '" placeholder="De" style="width: 110px;">
                                </div>',
                                'filterOptions' => ['style' => 'min-width: 240px;'],
                            ],
                            [
                                'attribute' => 'updated_at',
                                'label' => 'Atualizado em',
                                'headerOptions' => ['style' => 'width: 180px;'],
                                'value' => function($model) {
                                    return Yii::$app->formatter->asDatetime($model->updated_at, 'php:d/m/Y H:i');
                                },
                                'format' => 'raw',
                            ],
                            [
                                'class' => 'yii\grid\ActionColumn',
                                'header' => '<i class="fas fa-cogs"></i> Ações',
                                'headerOptions' => ['style' => 'width: 140px;', 'class' => 'text-center'],
                                'contentOptions' => ['class' => 'text-center py-2'],
                                'template' => '<div class="btn-group btn-group-sm">{view} {update} {activate} {deactivate}</div>',
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
                                            'title' => 'Editar utilizador',
                                            'data-pjax' => 0,
                                            'data-bs-toggle' => 'tooltip',
                                        ]);
                                    },
                                    'deactivate' => function ($url, $model, $key) {
                                        // Mostrar apenas para utilizadores ativos e que não sejam o próprio
                                        if ($model->status === User::STATUS_ACTIVE && $model->id !== Yii::$app->user->id) {
                                            return Html::a('<i class="fas fa-user-slash"></i>', ['delete', 'id' => $model->id], [
                                                'class' => 'btn btn-danger',
                                                'title' => 'Desativar utilizador',
                                                'data-bs-toggle' => 'tooltip',
                                                'data' => [
                                                    'confirm' => 'Tem a certeza que deseja desativar este utilizador?',
                                                    'method' => 'post',
                                                ],
                                            ]);
                                        }
                                        return '';
                                    },
                                    'activate' => function ($url, $model, $key) {
                                        // Mostrar apenas para utilizadores inativos
                                        if ($model->status === User::STATUS_INACTIVE) {
                                            return Html::a('<i class="fas fa-user-check"></i>', ['restore', 'id' => $model->id], [
                                                'class' => 'btn btn-success',
                                                'title' => 'Ativar utilizador',
                                                'data-bs-toggle' => 'tooltip',
                                                'data' => [
                                                    'confirm' => 'Tem a certeza que deseja ativar este utilizador?',
                                                    'method' => 'post',
                                                ],
                                            ]);
                                        }
                                        return '';
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
                                <span class="badge bg-warning me-1"><i class="fas fa-clock me-1"></i>Inativo</span>
                                <span class="badge bg-danger me-1"><i class="fas fa-times-circle me-1"></i>Eliminado</span>
                                <span class="text-warning"><i class="fas fa-user-clock me-1"></i>Utilizador Inativo</span>
                            </small>
                        </div>
                        <div class="col-md-6 text-end">
                            <small class="text-muted">
                                <i class="fas fa-database me-1"></i>
                                Total: <strong><?= $dataProvider->getTotalCount() ?? 0 ?></strong> utilizadores
                                (<strong class="text-success"><?= $activeUsersCount ?? 0 ?> ativos</strong>,
                                <strong class="text-warning"><?= $inactiveUsersCount ?? 0 ?> inativos</strong>)
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
    .card-title {
        font-weight: 600;
    }
    .table-responsive {
        border-radius: 0.25rem;
    }
    .text-muted {
        font-size: 0.85em;
    }
    .table-warning {
        background-color: rgba(255, 193, 7, 0.1) !important;
    }
    .table-warning:hover {
        background-color: rgba(255, 193, 7, 0.2) !important;
    }
    .form-control-sm {
        height: calc(1.8125rem + 2px);
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
    /* Estilo específico para os campos de data */
    .table th input[type="date"] {
        max-width: 110px;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Inicializar datepickers
        if (typeof $.fn.datepicker !== 'undefined') {
            $('input[type="date"]').datepicker({
                format: 'yyyy-mm-dd',
                autoclose: true,
                todayHighlight: true,
                language: 'pt'
            });
        }

        // Confirmação para desativar
        const deactivateButtons = document.querySelectorAll('a.btn-danger[data-confirm]');
        deactivateButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                const userName = this.closest('tr').querySelector('a.text-primary, a.text-warning')?.textContent || 'este utilizador';
                const confirmationMessage = '⚠️ DESATIVAR UTILIZADOR ⚠️\n\n' +
                    'Tem a certeza que deseja desativar o utilizador:\n' +
                    '➤ ' + userName + '\n\n' +
                    '▶️ O utilizador ficará INATIVO\n' +
                    '▶️ O utilizador não poderá aceder ao sistema\n' +
                    '▶️ Pode ativar o utilizador novamente a qualquer momento\n\n' +
                    'Confirma que deseja desativar?';

                if (!confirm(confirmationMessage)) {
                    e.preventDefault();
                    e.stopPropagation();
                    return false;
                }
            });
        });

        // Confirmação para ativar
        const activateButtons = document.querySelectorAll('a.btn-success[data-confirm]');
        activateButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                const userName = this.closest('tr').querySelector('a.text-warning, a.text-primary')?.textContent || 'este utilizador';
                const confirmationMessage = '✅ ATIVAR UTILIZADOR ✅\n\n' +
                    'Tem a certeza que deseja ativar o utilizador:\n' +
                    '➤ ' + userName + '\n\n' +
                    '▶️ O utilizador ficará ATIVO\n' +
                    '▶️ O utilizador poderá aceder ao sistema novamente\n\n' +
                    'Confirma que deseja ativar?';

                if (!confirm(confirmationMessage)) {
                    e.preventDefault();
                    e.stopPropagation();
                    return false;
                }
            });
        });
    });
</script>