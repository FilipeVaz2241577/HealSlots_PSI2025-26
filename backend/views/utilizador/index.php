<?php
/** @var yii\web\View $this */

use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;
use yii\grid\GridView;
use yii\widgets\Pjax;

$this->title = 'Gestão de Utilizadores';
?>

<div class="utilizador-index">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?= Html::encode($this->title) ?></h1>
        <?= Html::a('<i class="fa fa-plus me-2"></i>Adicionar Utilizador', ['create'], ['class' => 'btn btn-primary']) ?>
    </div>

    <div class="container-fluid pt-4 px-4">
        <div class="bg-light rounded p-4">

            <!-- Formulário de Pesquisa -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <?php $form = ActiveForm::begin([
                        'method' => 'get',
                        'action' => ['index'],
                    ]); ?>

                    <div class="input-group">
                        <?= Html::textInput('search', Yii::$app->request->get('search'), [
                            'class' => 'form-control',
                            'placeholder' => 'Pesquisar utilizadores...'
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
                    // REMOVIDA A LINHA: ['class' => 'yii\grid\SerialColumn'],
                    [
                        'attribute' => 'id',
                        'headerOptions' => ['style' => 'width: 80px;'],
                    ],
                    [
                        'attribute' => 'username',
                        'format' => 'raw',
                        'value' => function($model) {
                            return Html::a($model->username, ['view', 'id' => $model->id], [
                                'data-pjax' => 0
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
                        'filter' => $rolesList, // Lista de roles para filtro
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
                                    'title' => 'Eliminar',
                                    'data' => [
                                        'confirm' => 'Tem a certeza que deseja eliminar este utilizador?',
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
                        <i class="fa fa-users fa-2x"></i>
                        <div class="ms-3">
                            <p class="mb-0">Total Utilizadores</p>
                            <h6 class="mb-0"><?= $dataProvider->getTotalCount() ?? 0 ?></h6>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="bg-success rounded d-flex align-items-center justify-content-between p-3 text-white">
                        <i class="fa fa-user-check fa-2x"></i>
                        <div class="ms-3">
                            <p class="mb-0">Utilizadores Ativos</p>
                            <h6 class="mb-0"><?= $activeUsersCount ?? 0 ?></h6>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="bg-warning rounded d-flex align-items-center justify-content-between p-3 text-white">
                        <i class="fa fa-user-clock fa-2x"></i>
                        <div class="ms-3">
                            <p class="mb-0">Utilizadores Inativos</p>
                            <h6 class="mb-0"><?= $inactiveUsersCount ?? 0 ?></h6>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="bg-info rounded d-flex align-items-center justify-content-between p-3 text-white">
                        <i class="fa fa-user-shield fa-2x"></i>
                        <div class="ms-3">
                            <p class="mb-0">Diferentes Roles</p>
                            <h6 class="mb-0"><?= $differentRolesCount ?? 0 ?></h6>
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
</style>