<?php

/** @var yii\web\View $this */
/** @var common\models\Bloco $model */
/** @var yii\bootstrap5\ActiveForm $form */

use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;

$this->title = 'Adicionar Bloco Operatório';
$this->params['breadcrumbs'][] = ['label' => 'Gestão de Blocos Operatórios', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="bloco-create">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?= Html::encode($this->title) ?></h1>
        <?= Html::a('<i class="fas fa-arrow-left me-2"></i>Voltar', ['index'], ['class' => 'btn btn-secondary']) ?>
    </div>

    <div class="container-fluid">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-hospital me-2"></i>Novo Bloco Operatório
                </h3>
            </div>
            <div class="card-body">

                <?php $form = ActiveForm::begin([
                    'id' => 'create-bloco-form',
                    'fieldConfig' => [
                        'options' => ['class' => 'mb-4'],
                        'inputOptions' => ['class' => 'form-control form-control-lg'],
                        'labelOptions' => ['class' => 'form-label fw-bold text-dark'],
                        'hintOptions' => ['class' => 'form-text text-muted small'],
                    ],
                    'enableAjaxValidation' => false, // Desativar validação AJAX se estiver causando problemas
                ]); ?>

                <!-- Informações do Bloco -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h4 class="text-primary mb-3">
                            <i class="fas fa-building me-2"></i>Informações do Bloco
                        </h4>
                    </div>

                    <div class="col-md-8">
                        <?= $form->field($model, 'nome')
                            ->textInput([
                                'maxlength' => true,
                                'placeholder' => 'ex: Bloco A - Cirurgia Geral',
                                'autocomplete' => 'off' // Adicionar para evitar preenchimento automático
                            ])
                            ->label('Nome do Bloco <span class="text-danger">*</span>')
                            ->hint('O nome do bloco deve ser único. Verifique se não existe outro bloco com o mesmo nome.', ['class' => 'form-text text-muted small'])
                        ?>
                    </div>

                    <div class="col-md-4">
                        <?= $form->field($model, 'estado')
                            ->dropDownList([
                                'ativo' => 'Ativo',
                                'inativo' => 'Inativo',
                                'manutencao' => 'Em Manutenção'
                            ], [
                                'class' => 'form-select form-select-lg',
                                'prompt' => '-- Selecione o estado --'
                            ])
                            ->label('Estado <span class="text-danger">*</span>')
                            ->hint('Estado atual do bloco operatório', ['class' => 'form-text text-muted small'])
                        ?>
                    </div>
                </div>

                <!-- Informações Adicionais -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h4 class="text-primary mb-3">
                            <i class="fas fa-info-circle me-2"></i>Informações Adicionais
                        </h4>
                    </div>

                    <div class="col-12">
                        <div class="card border-0 bg-light">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="text-center">
                                            <i class="fas fa-door-open fa-2x text-primary mb-2"></i>
                                            <h6 class="text-dark">Salas</h6>
                                            <p class="text-muted mb-0">0 salas inicialmente</p>
                                            <small class="text-muted">Pode adicionar salas após criar o bloco</small>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="text-center">
                                            <i class="fas fa-microchip fa-2x text-success mb-2"></i>
                                            <h6 class="text-dark">Equipamentos</h6>
                                            <p class="text-muted mb-0">0 equipamentos</p>
                                            <small class="text-muted">Equipamentos serão associados às salas</small>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="text-center">
                                            <i class="fas fa-calendar-check fa-2x text-warning mb-2"></i>
                                            <h6 class="text-dark">Requisições</h6>
                                            <p class="text-muted mb-0">0 requisições</p>
                                            <small class="text-muted">Requisições futuras para este bloco</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Estados do Bloco -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h4 class="text-primary mb-3">
                            <i class="fas fa-tasks me-2"></i>Estados do Bloco
                        </h4>
                    </div>

                    <div class="col-12">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="card border-success h-100">
                                    <div class="card-body text-center">
                                        <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                                        <h6 class="card-title text-success">Ativo</h6>
                                        <p class="card-text small text-muted">
                                            Bloco disponível para uso normal. Salas podem ser requisitadas.
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card border-warning h-100">
                                    <div class="card-body text-center">
                                        <i class="fas fa-tools fa-2x text-warning mb-2"></i>
                                        <h6 class="card-title text-warning">Em Manutenção</h6>
                                        <p class="card-text small text-muted">
                                            Bloco em manutenção. Não disponível para requisições temporariamente.
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card border-danger h-100">
                                    <div class="card-body text-center">
                                        <i class="fas fa-times-circle fa-2x text-danger mb-2"></i>
                                        <h6 class="card-title text-danger">Inativo</h6>
                                        <p class="card-text small text-muted">
                                            Bloco desativado. Não disponível para requisições de forma permanente.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botões de Ação -->
                <div class="row mt-5">
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center border-top pt-4">
                            <?= Html::a('<i class="fas fa-times me-2"></i>Cancelar', ['index'], [
                                'class' => 'btn btn-lg btn-outline-secondary'
                            ]) ?>

                            <?= Html::submitButton('<i class="fas fa-save me-2"></i>Criar Bloco', [
                                'class' => 'btn btn-lg btn-primary px-4',
                                'name' => 'create-button'
                            ]) ?>
                        </div>
                    </div>
                </div>

                <?php ActiveForm::end(); ?>

            </div>
        </div>
    </div>
</div>

<!-- CSS adicional -->
<style>
    .form-label {
        color: #2c3e50;
        font-weight: 600;
        font-size: 0.95rem;
    }
    .form-control:focus, .form-select:focus {
        border-color: #3498db;
        box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
    }
    .form-control-lg {
        padding: 0.75rem 1rem;
        font-size: 1rem;
    }
    .form-select-lg {
        padding: 0.75rem 2.5rem 0.75rem 1rem;
    }
    .btn-lg {
        min-width: 180px;
        padding: 0.75rem 2rem;
    }
    .text-danger {
        color: #e74c3c !important;
    }
    .card {
        transition: all 0.3s ease;
    }
    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    h4.text-primary {
        border-bottom: 2px solid #3498db;
        padding-bottom: 0.5rem;
    }
    .form-text {
        font-size: 0.85rem;
    }
    .border-success, .border-warning, .border-danger {
        border-width: 2px !important;
    }
</style>