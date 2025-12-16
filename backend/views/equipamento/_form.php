<?php

use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;
use common\models\Equipamento;

/** @var yii\web\View $this */
/** @var common\models\Equipamento $model */
/** @var yii\bootstrap5\ActiveForm $form */
/** @var array $tiposEquipamento */
?>

<div class="equipamento-form">

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
                <?= $model->isNewRecord ? 'Adicionar Novo Equipamento' : 'Editar Equipamento: ' . Html::encode($model->equipamento) ?>
            </h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'numeroSerie')->textInput([
                        'maxlength' => true,
                        'placeholder' => 'Número de série do equipamento'
                    ]) ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'equipamento')->textInput([
                        'maxlength' => true,
                        'placeholder' => 'Nome descritivo do equipamento'
                    ]) ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'tipoEquipamento_id')->dropDownList(
                        $tiposEquipamento ?? [],
                        [
                            'class' => 'form-control',
                            'prompt' => 'Selecione o tipo de equipamento...'
                        ]
                    ) ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'estado')->dropDownList(
                        Equipamento::optsEstado(),
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
                            '<i class="fas fa-save me-1"></i> Criar Equipamento' :
                            '<i class="fas fa-save me-1"></i> Atualizar Equipamento',
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