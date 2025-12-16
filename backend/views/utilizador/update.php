<?php
use yii\bootstrap5\Html;

/** @var yii\web\View $this */
/** @var common\models\User $model */

$this->title = 'Atualizar Utilizador: ' . $model->username;
$this->params['breadcrumbs'][] = ['label' => 'Utilizadores', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->username, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Atualizar';

// Determinar o badge do status atual
$statusBadge = '';
if ($model->status === $model::STATUS_ACTIVE) {
    $statusBadge = '<span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Ativo</span>';
} elseif ($model->status === $model::STATUS_INACTIVE) {
    $statusBadge = '<span class="badge bg-warning"><i class="fas fa-clock me-1"></i>Inativo</span>';
} else {
    $statusBadge = '<span class="badge bg-danger"><i class="fas fa-times-circle me-1"></i>Eliminado</span>';
}
?>

<div class="utilizador-update">
    <div class="container-fluid pt-3">
        <div class="row justify-content-center">
            <div class="col-12 col-xl-10">
                <!-- Cabeçalho -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <div class="bg-primary bg-opacity-10 rounded-circle p-2">
                                        <i class="fas fa-user-edit fa-lg text-primary"></i>
                                    </div>
                                </div>
                                <div>
                                    <h2 class="h4 mb-1 fw-bold"><?= Html::encode($this->title) ?></h2>
                                    <div class="text-muted">
                                        <small>ID: <?= $model->id ?> • Atualize os dados do utilizador</small>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                <?= Html::a('<i class="fas fa-arrow-left"></i>', ['index'], [
                                    'class' => 'btn btn-outline-secondary btn-sm',
                                    'title' => 'Voltar à lista'
                                ]) ?>
                                <?= Html::a('<i class="fas fa-eye"></i>', ['view', 'id' => $model->id], [
                                    'class' => 'btn btn-outline-info btn-sm',
                                    'title' => 'Ver detalhes'
                                ]) ?>
                            </div>
                        </div>

                        <!-- Indicador de status atual -->
                        <div class="alert alert-light border py-2 mb-0">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center">
                                        <span class="me-2 text-muted">Estado:</span>
                                        <?= $statusBadge ?>
                                    </div>
                                </div>
                                <div class="col-md-6 mt-2 mt-md-0">
                                    <div class="text-muted">
                                        <small>
                                            <i class="fas fa-calendar-alt me-1"></i>
                                            Última atualização: <?= Yii::$app->formatter->asRelativeTime($model->updated_at) ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Formulário -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <?= $this->render('_form', [
                            'model' => $model,
                            'rolesList' => $rolesList,
                        ]) ?>
                    </div>
                </div>

                <!-- Informações úteis -->
                <div class="mt-4">
                    <div class="alert alert-info border-0 bg-info-subtle">
                        <div class="d-flex align-items-start">
                            <div class="flex-shrink-0">
                                <i class="fas fa-lightbulb text-info fa-lg mt-1"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="alert-heading mb-2 fw-bold">Notas importantes</h6>
                                <ul class="mb-0 small ps-3">
                                    <li>Altere apenas os campos necessários</li>
                                    <li>Mudanças no status afetam o acesso imediato do utilizador</li>
                                    <?php if ($model->id === Yii::$app->user->id): ?>
                                        <li class="fw-bold text-warning"><i class="fas fa-exclamation-triangle me-1"></i> Está a editar o seu próprio perfil</li>
                                    <?php endif; ?>
                                </ul>
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
    .bg-primary.bg-opacity-10 {
        background-color: rgba(var(--bs-primary-rgb), 0.1) !important;
    }
    .alert-light {
        background-color: #f8f9fa;
    }
    .btn-sm {
        padding: 0.375rem 0.75rem;
    }
</style>