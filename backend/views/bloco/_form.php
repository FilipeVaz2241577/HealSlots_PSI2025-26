<?php

/** @var yii\web\View $this */
/** @var common\models\Bloco $model */
/** @var yii\bootstrap5\ActiveForm $form */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

?>

<div class="bloco-form">
    <?php $form = ActiveForm::begin([
        'id' => 'bloco-form',
        'options' => ['class' => 'form-horizontal'],
        'fieldConfig' => [
            'template' => "{label}\n{input}\n{error}",
            'labelOptions' => ['class' => 'form-label fw-bold'],
            'inputOptions' => ['class' => 'form-control'],
            'errorOptions' => ['class' => 'invalid-feedback'],
        ],
    ]); ?>

    <div class="row">
        <!-- Campo Nome do Bloco -->
        <div class="col-md-8">
            <?= $form->field($model, 'nome')->textInput([
                'maxlength' => true,
                'placeholder' => 'Digite o nome do bloco operatório'
            ])->label('Nome do Bloco <span class="text-danger">*</span>') ?>
        </div>

        <!-- Campo Estado -->
        <div class="col-md-4">
            <?= $form->field($model, 'estado')->dropDownList([
                'ativo' => 'Ativo',
                'inativo' => 'Inativo',
                'manutencao' => 'Em Manutenção'
            ], [
                'prompt' => 'Selecione o estado',
                'class' => 'form-select'
            ])->label('Estado <span class="text-danger">*</span>') ?>
        </div>
    </div>

    <!-- Informações sobre Estados -->
    <div class="row mt-3">
        <div class="col-12">
            <div class="alert alert-info">
                <h6 class="alert-heading"><i class="fas fa-info-circle me-2"></i>Informação sobre Estados:</h6>
                <div class="row mt-2">
                    <div class="col-md-4">
                        <strong class="text-success">• Ativo:</strong> Bloco disponível para uso
                    </div>
                    <div class="col-md-4">
                        <strong class="text-warning">• Em Manutenção:</strong> Bloco temporariamente indisponível
                    </div>
                    <div class="col-md-4">
                        <strong class="text-danger">• Inativo:</strong> Bloco permanentemente desativado
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Estatísticas (apenas para update) -->
    <?php if (!$model->isNewRecord): ?>
        <div class="row mt-4">
            <div class="col-12">
                <h5 class="text-primary mb-3">
                    <i class="fas fa-chart-bar me-2"></i>Estatísticas do Bloco
                </h5>
            </div>

            <div class="col-md-3">
                <div class="card border-0 bg-light">
                    <div class="card-body text-center">
                        <i class="fas fa-door-open fa-2x text-primary mb-2"></i>
                        <h6 class="card-title">Total Salas</h6>
                        <p class="card-text h4 text-dark"><?= $model->salas ? count($model->salas) : 0 ?></p>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card border-0 bg-light">
                    <div class="card-body text-center">
                        <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                        <h6 class="card-title">Salas Livres</h6>
                        <p class="card-text h4 text-dark">
                            <?php
                            $livres = 0;
                            if ($model->salas) {
                                foreach ($model->salas as $sala) {
                                    if ($sala->estado === 'Livre') $livres++;
                                }
                            }
                            echo $livres;
                            ?>
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card border-0 bg-light">
                    <div class="card-body text-center">
                        <i class="fas fa-times-circle fa-2x text-danger mb-2"></i>
                        <h6 class="card-title">Salas Ocupadas</h6>
                        <p class="card-text h4 text-dark">
                            <?php
                            $ocupadas = 0;
                            if ($model->salas) {
                                foreach ($model->salas as $sala) {
                                    if ($sala->estado === 'Ocupada') $ocupadas++;
                                }
                            }
                            echo $ocupadas;
                            ?>
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card border-0 bg-light">
                    <div class="card-body text-center">
                        <i class="fas fa-tools fa-2x text-warning mb-2"></i>
                        <h6 class="card-title">Salas Manutenção</h6>
                        <p class="card-text h4 text-dark">
                            <?php
                            $manutencao = 0;
                            if ($model->salas) {
                                foreach ($model->salas as $sala) {
                                    if ($sala->estado === 'Em Manutencao') $manutencao++;
                                }
                            }
                            echo $manutencao;
                            ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <hr class="my-4">

    <!-- Botões de ação -->
    <div class="form-group">
        <div class="d-flex justify-content-between">
            <?= Html::a('<i class="fas fa-arrow-left me-2"></i>Voltar à Lista', ['index'], [
                'class' => 'btn btn-secondary',
            ]) ?>

            <div>
                <?php if (!$model->isNewRecord): ?>
                    <?= Html::a('<i class="fas fa-door-open me-2"></i>Gerir Salas', ['sala/index', 'SalaSearch[bloco_id]' => $model->id], [
                        'class' => 'btn btn-info me-2',
                    ]) ?>

                    <?= Html::a('<i class="fas fa-trash me-2"></i>Eliminar', ['delete', 'id' => $model->id], [
                        'class' => 'btn btn-danger me-2',
                        'data' => [
                            'confirm' => 'Tem a certeza que deseja eliminar este bloco operatório?',
                            'method' => 'post',
                        ],
                    ]) ?>
                <?php endif; ?>

                <?= Html::submitButton('<i class="fas fa-save me-2"></i>' . ($model->isNewRecord ? 'Criar Bloco' : 'Guardar Alterações'), [
                    'class' => 'btn btn-primary',
                    'name' => 'submit-button'
                ]) ?>
            </div>
        </div>
    </div>

    <?php ActiveForm::end(); ?>
</div>

<!-- CSS adicional -->
<style>
    .form-label {
        color: #2c3e50;
        margin-bottom: 0.5rem;
    }
    .form-control:focus, .form-select:focus {
        border-color: #3498db;
        box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
    }
    .invalid-feedback {
        display: block;
    }
    .card {
        transition: all 0.3s ease;
    }
    .card:hover {
        transform: translateY(-2px);
    }
    .alert-info {
        background-color: #d1ecf1;
        border-color: #bee5eb;
        color: #0c5460;
    }
</style>