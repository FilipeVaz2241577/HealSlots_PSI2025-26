<?php

/** @var yii\web\View $this */
/** @var common\models\User $model */

use yii\widgets\DetailView;
use yii\bootstrap5\Html;

$this->title = $model->username;
$this->params['breadcrumbs'][] = ['label' => 'Utilizadores', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$auth = Yii::$app->authManager;
$userRoles = $auth->getRolesByUser($model->id);
$roleNames = [];
foreach ($userRoles as $role) {
    $roleNames[] = Html::tag('span', $role->name, ['class' => 'badge bg-primary']);
}
$roleDisplay = !empty($roleNames) ? implode(' ', $roleNames) : Html::tag('span', 'Sem role', ['class' => 'badge bg-secondary']);
?>

<div class="utilizador-view">
    <div class="container-fluid pt-3">
        <!-- Cabeçalho -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body py-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                                        <i class="fas fa-user fa-lg text-primary"></i>
                                    </div>
                                </div>
                                <div>
                                    <h2 class="h4 mb-1 fw-bold"><?= Html::encode($model->username) ?></h2>
                                    <div class="text-muted">
                                        <small>ID: <?= $model->id ?> • <?= $model->email ?></small>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                <?= Html::a('<i class="fas fa-arrow-left"></i>', ['index'], [
                                    'class' => 'btn btn-outline-secondary btn-sm',
                                    'title' => 'Voltar'
                                ]) ?>
                                <?= Html::a('<i class="fas fa-edit"></i>', ['update', 'id' => $model->id], [
                                    'class' => 'btn btn-primary btn-sm',
                                    'title' => 'Editar'
                                ]) ?>
                            </div>
                        </div>

                        <!-- Status e Role -->
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="d-flex align-items-center">
                                    <span class="me-2 text-muted">Status:</span>
                                    <?php if ($model->status === $model::STATUS_ACTIVE): ?>
                                        <span class="badge bg-success">
                                            <i class="fas fa-check-circle me-1"></i>Ativo
                                        </span>
                                    <?php elseif ($model->status === $model::STATUS_INACTIVE): ?>
                                        <span class="badge bg-warning">
                                            <i class="fas fa-clock me-1"></i>Inativo
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">
                                            <i class="fas fa-times-circle me-1"></i>Eliminado
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-md-6 mt-2 mt-md-0">
                                <div class="d-flex align-items-center">
                                    <span class="me-2 text-muted">Permissões:</span>
                                    <?= $roleDisplay ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Informações principais -->
        <div class="row">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="mb-0 fw-bold">
                            <i class="fas fa-info-circle text-primary me-2"></i>
                            Detalhes do Utilizador
                        </h5>
                    </div>
                    <div class="card-body">
                        <?= DetailView::widget([
                            'model' => $model,
                            'options' => [
                                'class' => 'table table-borderless mb-0'
                            ],
                            'attributes' => [
                                [
                                    'label' => 'Username',
                                    'value' => Html::tag('span', Html::encode($model->username), ['class' => 'fw-semibold']),
                                    'format' => 'raw',
                                ],
                                [
                                    'label' => 'Email',
                                    'value' => Html::a(Html::encode($model->email), 'mailto:' . $model->email, [
                                        'class' => 'text-decoration-none'
                                    ]),
                                    'format' => 'raw',
                                ],
                                [
                                    'label' => 'Criado em',
                                    'value' => Yii::$app->formatter->asDatetime($model->created_at, 'dd/MM/yyyy HH:mm'),
                                ],
                                [
                                    'label' => 'Atualizado em',
                                    'value' => Yii::$app->formatter->asDatetime($model->updated_at, 'dd/MM/yyyy HH:mm'),
                                ],
                            ],
                        ]) ?>
                    </div>
                </div>
            </div>

            <!-- Ações -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="mb-0 fw-bold">
                            <i class="fas fa-cogs text-primary me-2"></i>
                            Ações
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <?= Html::a('<i class="fas fa-edit me-2"></i> Editar Utilizador', ['update', 'id' => $model->id], [
                                'class' => 'btn btn-primary btn-block'
                            ]) ?>

                            <?php if ($model->id !== Yii::$app->user->id): ?>
                                <?php if ($model->status === $model::STATUS_ACTIVE): ?>
                                    <?= Html::a('<i class="fas fa-user-slash me-2"></i> Desativar Utilizador', ['delete', 'id' => $model->id], [
                                        'class' => 'btn btn-outline-danger btn-block',
                                        'data' => [
                                            'confirm' => 'Tem a certeza que deseja desativar este utilizador?',
                                            'method' => 'post',
                                        ],
                                    ]) ?>
                                <?php else: ?>
                                    <?= Html::a('<i class="fas fa-user-check me-2"></i> Ativar Utilizador', ['restore', 'id' => $model->id], [
                                        'class' => 'btn btn-outline-success btn-block',
                                        'data' => [
                                            'confirm' => 'Tem a certeza que deseja ativar este utilizador?',
                                            'method' => 'post',
                                        ],
                                    ]) ?>
                                <?php endif; ?>
                            <?php else: ?>
                                <button class="btn btn-outline-secondary btn-block" disabled>
                                    <i class="fas fa-user me-2"></i> É o seu próprio perfil
                                </button>
                            <?php endif; ?>

                            <?= Html::a('<i class="fas fa-list me-2"></i> Ver Todos Utilizadores', ['index'], [
                                'class' => 'btn btn-outline-secondary btn-block'
                            ]) ?>
                        </div>
                    </div>
                </div>

                <!-- Informação adicional -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="mb-0 fw-bold">
                            <i class="fas fa-clock text-primary me-2"></i>
                            Atividade
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="me-3">
                                <i class="fas fa-calendar-plus fa-lg text-muted"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block">Criado há</small>
                                <span class="fw-semibold"><?= Yii::$app->formatter->asRelativeTime($model->created_at) ?></span>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="fas fa-calendar-check fa-lg text-muted"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block">Última atualização</small>
                                <span class="fw-semibold"><?= Yii::$app->formatter->asRelativeTime($model->updated_at) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .card {
        border-radius: 10px;
    }
    .card-header {
        border-radius: 10px 10px 0 0 !important;
    }
    .btn-block {
        width: 100%;
    }
    .bg-primary.bg-opacity-10 {
        background-color: rgba(var(--bs-primary-rgb), 0.1) !important;
    }
    .table-borderless td, .table-borderless th {
        border: none !important;
        padding: 12px 8px;
    }
    .table-borderless tr:not(:last-child) {
        border-bottom: 1px solid #dee2e6;
    }
</style>