<?php
/** @var yii\web\View $this */

use hail812\adminlte\widgets\Alert;
use hail812\adminlte\widgets\SmallBox;
use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;
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
                                    'placeholder' => 'Pesquisar utilizadores...'
                                ]) ?>
                                <?= Html::submitButton('<i class="fas fa-search"></i>', ['class' => 'btn btn-primary']) ?>
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
                        'options' => ['class' => 'table-responsive'],
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
                                'headerOptions' => ['style' => 'width: 80px;'],
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
                                        'class' => $model->status === User::STATUS_INACTIVE ? 'text-warning' : 'text-primary'
                                    ]);
                                },
                            ],
                            'email:email',
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
                                    User::STATUS_ACTIVE => 'Ativo',
                                    User::STATUS_INACTIVE => 'Inativo'
                                ],
                                'headerOptions' => ['style' => 'width: 120px;'],
                            ],
                            [
                                'attribute' => 'created_at',
                                'format' => ['datetime', 'php:d/m/Y H:i'],
                                'headerOptions' => ['style' => 'width: 180px;'],
                            ],
                            [
                                'attribute' => 'updated_at',
                                'format' => ['datetime', 'php:d/m/Y H:i'],
                                'headerOptions' => ['style' => 'width: 180px;'],
                            ],
                            [
                                'class' => 'yii\grid\ActionColumn',
                                'header' => 'Ações',
                                'headerOptions' => ['style' => 'width: 200px;', 'class' => 'text-center'],
                                'contentOptions' => ['class' => 'text-center'],
                                'template' => '{view} {update} {activate} {deactivate}',
                                'buttons' => [
                                    'view' => function ($url, $model, $key) {
                                        return Html::a('<i class="fas fa-eye"></i>', $url, [
                                            'class' => 'btn btn-info btn-xs',
                                            'title' => 'Ver detalhes',
                                            'data-pjax' => 0,
                                        ]);
                                    },
                                    'update' => function ($url, $model, $key) {
                                        return Html::a('<i class="fas fa-edit"></i>', $url, [
                                            'class' => 'btn btn-warning btn-xs',
                                            'title' => 'Editar',
                                            'data-pjax' => 0,
                                        ]);
                                    },
                                    'deactivate' => function ($url, $model, $key) {
                                        // Mostrar apenas para utilizadores ativos e que não sejam o próprio
                                        if ($model->status === User::STATUS_ACTIVE && $model->id !== Yii::$app->user->id) {
                                            return Html::a('<i class="fas fa-user-slash"></i>', ['delete', 'id' => $model->id], [
                                                'class' => 'btn btn-danger btn-xs',
                                                'title' => 'Desativar utilizador',
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
                                                'class' => 'btn btn-success btn-xs',
                                                'title' => 'Ativar utilizador',
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
                            'options' => ['class' => 'pagination justify-content-center m-0'],
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
                                <i class="fas fa-info-circle text-info"></i>
                                <strong>Utilizadores Inativos</strong> aparecem a amarelo e não podem aceder ao sistema.
                            </small>
                        </div>
                        <div class="col-md-6 text-end">
                            <small class="text-muted">
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

<!-- CSS adicional -->
<style>
    .table th {
        background-color: #f8f9fa;
        font-weight: 600;
        border-bottom: 2px solid #dee2e6;
    }
    .btn-xs {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
        margin: 0 1px;
    }
    .badge {
        font-size: 0.75em;
    }
    .table-warning {
        background-color: rgba(255, 193, 7, 0.1) !important;
    }
    .table-warning:hover {
        background-color: rgba(255, 193, 7, 0.2) !important;
    }
    .text-warning {
        font-weight: 500;
    }
    .card-title {
        font-weight: 600;
    }
    .btn-success.btn-xs, .btn-danger.btn-xs {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
        margin: 0 1px;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Confirmação para desativar
        const deactivateButtons = document.querySelectorAll('a.btn-danger[data-confirm]');
        deactivateButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                const confirmationMessage = '⚠️ DESATIVAR UTILIZADOR ⚠️\n\n' +
                    'Tem a certeza que deseja desativar este utilizador?\n\n' +
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
                const confirmationMessage = '✅ ATIVAR UTILIZADOR ✅\n\n' +
                    'Tem a certeza que deseja ativar este utilizador?\n\n' +
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