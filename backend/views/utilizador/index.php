<?php
/** @var yii\web\View $this */

use hail812\adminlte\widgets\Alert;
use hail812\adminlte\widgets\SmallBox;
use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;
use yii\grid\GridView;
use yii\widgets\Pjax;

$this->title = 'Gestão de Utilizadores';
$this->params['breadcrumbs'] = [['label' => $this->title]];
?>

<div class="container-fluid">
    <!-- Alert -->
    <div class="row">
        <div class="col-12">
            <?= Alert::widget([
                'type' => 'info',
                'body' => '<h4><i class="icon fas fa-users"></i> Gestão de Utilizadores</h4>Utilize esta página para gerir todos os utilizadores do sistema',
            ]) ?>
        </div>
    </div>

    <!-- Estatísticas com SmallBox -->
    <div class="row mb-4">
        <div class="col-lg-3 col-6">
            <?= SmallBox::widget([
                'title' => $dataProvider->getTotalCount() ?? 0,
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
                        Lista de Utilizadores
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
                        'columns' => [
                            [
                                'attribute' => 'id',
                                'headerOptions' => ['style' => 'width: 80px;'],
                            ],
                            [
                                'attribute' => 'username',
                                'format' => 'raw',
                                'value' => function($model) {
                                    return Html::a($model->username, ['view', 'id' => $model->id], [
                                        'data-pjax' => 0,
                                        'class' => 'text-primary'
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
                                        10 => '<span class="badge bg-success">Ativo</span>',
                                        9 => '<span class="badge bg-warning">Inativo</span>',
                                        0 => '<span class="badge bg-danger">Eliminado</span>',
                                    ];
                                    return $statusLabels[$model->status] ?? '<span class="badge bg-secondary">Desconhecido</span>';
                                },
                                'format' => 'raw',
                                'filter' => [
                                    10 => 'Ativo',
                                    9 => 'Inativo',
                                    0 => 'Eliminado'
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
                                'headerOptions' => ['style' => 'width: 150px;', 'class' => 'text-center'],
                                'contentOptions' => ['class' => 'text-center'],
                                'template' => '{view} {update} {delete}',
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
                                    'delete' => function ($url, $model, $key) {
                                        // Verificar se é o próprio utilizador
                                        if ($model->id === Yii::$app->user->id) {
                                            return Html::button('<i class="fas fa-trash"></i>', [
                                                'class' => 'btn btn-danger btn-xs disabled',
                                                'title' => 'Não pode eliminar a sua própria conta',
                                                'disabled' => true,
                                            ]);
                                        }

                                        return Html::a('<i class="fas fa-trash"></i>', $url, [
                                            'class' => 'btn btn-danger btn-xs',
                                            'title' => 'Eliminar permanentemente',
                                            'data' => [
                                                'confirm' => '⚠️ ELIMINAÇÃO PERMANENTE ⚠️\n\nTem a certeza que deseja eliminar PERMANENTEMENTE este utilizador?\n\nEsta ação NÃO pode ser desfeita!',
                                                'method' => 'post',
                                            ],
                                        ]);
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
                    <div class="float-right">
                        <small class="text-muted">
                            Total: <strong><?= $dataProvider->getTotalCount() ?? 0 ?></strong> utilizadores
                        </small>
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
    }
    .btn-xs {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
        margin: 0 1px;
    }
    .badge {
        font-size: 0.75em;
    }
    .btn-danger.disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
    .card-title {
        font-weight: 600;
    }
</style>

<script>
    // Confirmação reforçada para eliminação permanente
    document.addEventListener('DOMContentLoaded', function() {
        const deleteButtons = document.querySelectorAll('a.btn-danger[data-confirm]');
        deleteButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                const confirmationMessage = '🚨 ELIMINAÇÃO PERMANENTE 🚨\n\n' +
                    'Tem a ABSOLUTA certeza que deseja eliminar PERMANENTEMENTE este utilizador?\n\n' +
                    '▶️ Esta ação NÃO pode ser desfeita!\n' +
                    '▶️ Todos os dados do utilizador serão PERDIDOS!\n' +
                    '▶️ O utilizador não poderá voltar a aceder ao sistema!\n\n' +
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