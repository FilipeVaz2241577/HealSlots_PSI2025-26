<?php

use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;
use common\models\Sala;

/** @var yii\web\View $this */
/** @var common\models\Sala $model */
/** @var array $blocos */
/** @var yii\bootstrap5\ActiveForm $form */
?>

<div class="sala-form">

    <?php $form = ActiveForm::begin([
        'options' => ['class' => 'form-horizontal'],
        'fieldConfig' => [
            'template' => "{label}\n{input}\n{error}",
            'labelOptions' => ['class' => 'form-label'],
            'inputOptions' => ['class' => 'form-control'],
            'errorOptions' => ['class' => 'invalid-feedback'],
        ],
    ]); ?>

    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-<?= $model->isNewRecord ? 'plus' : 'edit' ?> me-2"></i>
                <?= $model->isNewRecord ? 'Adicionar Nova Sala' : 'Editar Sala: ' . Html::encode($model->nome) ?>
            </h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'nome')->textInput([
                        'maxlength' => true,
                        'placeholder' => 'Ex: Sala 1, Sala de Emergência...'
                    ]) ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'bloco_id')->dropDownList(
                        $blocos ?? [],
                        [
                            'class' => 'form-control',
                            'prompt' => 'Selecione o bloco...'
                        ]
                    ) ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'estado')->dropDownList(
                        Sala::optsEstado(),
                        [
                            'class' => 'form-control',
                            'prompt' => 'Selecione o estado...'
                        ]
                    ) ?>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <div class="row">
                <div class="col-md-6">
                    <?= Html::a('<i class="fas fa-arrow-left me-1"></i> Voltar', ['index'], [
                        'class' => 'btn btn-secondary'
                    ]) ?>
                </div>
                <div class="col-md-6 text-end">
                    <?= Html::submitButton(
                        $model->isNewRecord ?
                            '<i class="fas fa-save me-1"></i> Criar Sala' :
                            '<i class="fas fa-save me-1"></i> Atualizar Sala',
                        ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']
                    ) ?>
                </div>
            </div>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<style>
    .card {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }
    .form-label {
        font-weight: 600;
        margin-bottom: 0.5rem;
    }
    .invalid-feedback {
        display: block;
    }
</style>