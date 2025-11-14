<?php

/** @var yii\web\View $this */
/** @var common\models\User $model */
/** @var yii\bootstrap5\ActiveForm $form */

use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;
use yii\helpers\ArrayHelper;

$this->title = 'Adicionar Utilizador';
$this->params['breadcrumbs'][] = ['label' => 'Gestão de Utilizadores', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="utilizador-create">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?= Html::encode($this->title) ?></h1>
        <?= Html::a('<i class="fa fa-arrow-left me-2"></i>Voltar', ['index'], ['class' => 'btn btn-secondary']) ?>
    </div>

    <div class="container-fluid pt-4 px-4">
        <div class="bg-light rounded p-4">

            <?php $form = ActiveForm::begin([
                'id' => 'create-user-form',
                'fieldConfig' => [
                    'options' => ['class' => 'mb-4'],
                    'inputOptions' => ['class' => 'form-control form-control-lg'],
                    'labelOptions' => ['class' => 'form-label fw-bold text-dark'],
                ],
            ]); ?>

            <!-- Informações Básicas -->
            <div class="row mb-4">
                <div class="col-12">
                    <h4 class="text-primary mb-3">
                        <i class="fa fa-user me-2"></i>Informações Básicas
                    </h4>
                </div>

                <div class="col-md-6">
                    <?= $form->field($model, 'username')
                        ->textInput([
                            'maxlength' => true,
                            'placeholder' => 'ex: joao.silva'
                        ])
                        ->label('Nome de Utilizador <span class="text-danger">*</span>')
                        ->hint('Nome único para identificação no sistema', ['class' => 'form-text text-muted small'])
                    ?>
                </div>

                <div class="col-md-6">
                    <?= $form->field($model, 'email')
                        ->textInput([
                            'maxlength' => true,
                            'type' => 'email',
                            'placeholder' => 'exemplo@email.com'
                        ])
                        ->label('Email <span class="text-danger">*</span>')
                        ->hint('Email válido para contacto', ['class' => 'form-text text-muted small'])
                    ?>
                </div>
            </div>

            <!-- Permissões e Segurança -->
            <div class="row mb-4">
                <div class="col-12">
                    <h4 class="text-primary mb-3">
                        <i class="fa fa-shield me-2"></i>Permissões e Segurança
                    </h4>
                </div>

                <div class="col-md-6">
                    <?= $form->field($model, 'role')
                        ->dropDownList($rolesList, [
                            'class' => 'form-select form-select-lg',
                            'prompt' => '-- Selecione um role --'
                        ])
                        ->label('Role <span class="text-danger">*</span>')
                        ->hint('Nível de acesso e permissões do utilizador', ['class' => 'form-text text-muted small'])
                    ?>
                </div>

                <div class="col-md-6">
                    <div class="card h-100 border-0 bg-light">
                        <div class="card-body">
                            <h6 class="card-title text-dark mb-3">
                                <i class="fa fa-info-circle me-2 text-info"></i>Estado do Utilizador
                            </h6>
                            <p class="card-text text-success mb-2">
                                <i class="fa fa-check-circle me-2"></i>Status: <strong>Ativo</strong>
                            </p>
                            <small class="text-muted">
                                Todos os novos utilizadores são criados automaticamente como ativos.
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Password -->
            <div class="row mb-4">
                <div class="col-12">
                    <h4 class="text-primary mb-3">
                        <i class="fa fa-lock me-2"></i>Segurança da Conta
                    </h4>
                </div>

                <div class="col-md-6">
                    <?= $form->field($model, 'password')
                        ->passwordInput([
                            'maxlength' => true,
                            'placeholder' => '••••••'
                        ])
                        ->label('Password <span class="text-danger">*</span>')
                        ->hint('Mínimo 6 caracteres - recomendado usar letras, números e símbolos', ['class' => 'form-text text-muted small'])
                    ?>
                </div>

                <div class="col-md-6">
                    <?= $form->field($model, 'password_repeat')
                        ->passwordInput([
                            'maxlength' => true,
                            'placeholder' => '••••••'
                        ])
                        ->label('Confirmar Password <span class="text-danger">*</span>')
                        ->hint('Digite novamente a password para confirmação', ['class' => 'form-text text-muted small'])
                    ?>
                </div>
            </div>

            <!-- Campo hidden para status ativo -->
            <?= $form->field($model, 'status')->hiddenInput(['value' => 10])->label(false) ?>

            <!-- Botões de Ação -->
            <div class="row mt-5">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center border-top pt-4">
                        <?= Html::a('<i class="fa fa-times me-2"></i>Cancelar', ['index'], [
                            'class' => 'btn btn-lg btn-outline-secondary'
                        ]) ?>

                        <?= Html::submitButton('<i class="fa fa-save me-2"></i>Criar Utilizador', [
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
</style>