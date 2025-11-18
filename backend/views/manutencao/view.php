<?php

/** @var yii\web\View $this */
/** @var common\models\Manutencao $model */

use yii\bootstrap5\Html;
use yii\widgets\DetailView;

$this->title = 'Manutenção #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Gestão de Manutenções', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="manutencao-view">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?= Html::encode($this->title) ?></h1>
        <div>
            <?= Html::a('<i class="fa fa-arrow-left me-2"></i>Voltar', ['index'], ['class' => 'btn btn-secondary']) ?>
            <?= Html::a('<i class="fa fa-edit me-2"></i>Editar', ['update', 'id' => $model->id], ['class' => 'btn btn-warning']) ?>
            <?= Html::a('<i class="fa fa-trash me-2"></i>Eliminar', ['delete', 'id' => $model->id], [
                'class' => 'btn btn-danger',
                'data' => [
                    'confirm' => 'Tem a certeza que deseja eliminar esta manutenção?',
                    'method' => 'post',
                ],
            ]) ?>
        </div>
    </div>

    <div class="container-fluid pt-4 px-4">
        <div class="bg-light rounded p-4">

            <!-- Informações Principais -->
            <div class="row mb-5">
                <div class="col-md-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                <i class="fa fa-info-circle me-2"></i>Informações da Manutenção
                            </h5>
                        </div>
                        <div class="card-body">
                            <?= DetailView::widget([
                                'model' => $model,
                                'attributes' => [
                                    'id',
                                    [
                                        'attribute' => 'equipamento_id',
                                        'value' => $model->equipamento ? $model->equipamento->nome : '-',
                                    ],
                                    [
                                        'attribute' => 'sala_id',
                                        'value' => $model->sala ? $model->sala->nome : '-',
                                    ],
                                    [
                                        'attribute' => 'user_id',
                                        'value' => $model->user ? $model->user->username : '-',
                                    ],
                                    [
                                        'attribute' => 'dataInicio',
                                        'format' => ['datetime', 'php:d/m/Y H:i'],
                                    ],
                                    [
                                        'attribute' => 'dataFim',
                                        'format' => ['datetime', 'php:d/m/Y H:i'],
                                        'value' => $model->dataFim ? Yii::$app->formatter->asDatetime($model->dataFim, 'php:d/m/Y H:i') : '-',
                                    ],
                                    [
                                        'attribute' => 'status',
                                        'value' => $model->getStatusBadge(),
                                        'format' => 'raw',
                                    ],
                                ],
                            ]) ?>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0">
                                <i class="fa fa-history me-2"></i>Informações do Sistema
                            </h5>
                        </div>
                        <div class="card-body">
                            <?= DetailView::widget([
                                'model' => $model,
                                'attributes' => [
                                    [
                                        'attribute' => 'created_by',
                                        'value' => $model->createdBy ? $model->createdBy->username : '-',
                                    ],
                                    [
                                        'attribute' => 'created_at',
                                        'format' => ['datetime', 'php:d/m/Y H:i'],
                                    ],
                                    [
                                        'attribute' => 'updated_by',
                                        'value' => $model->updatedBy ? $model->updatedBy->username : '-',
                                    ],
                                    [
                                        'attribute' => 'updated_at',
                                        'format' => ['datetime', 'php:d/m/Y H:i'],
                                    ],
                                ],
                            ]) ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Descrição -->
            <?php if ($model->descricao): ?>
                <div class="row">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-secondary text-white">
                                <h5 class="mb-0">
                                    <i class="fa fa-file-alt me-2"></i>Descrição da Manutenção
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="p-3 bg-light rounded">
                                    <?= nl2br(Html::encode($model->descricao)) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<!-- CSS adicional -->
<style>
    .card {
        margin-bottom: 1rem;
    }
    .card-header {
        font-weight: 600;
    }
    .table th {
        width: 40%;
        background-color: #f8f9fa;
    }
</style>