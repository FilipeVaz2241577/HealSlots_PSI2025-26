<?php

/** @var yii\web\View $this */
/** @var common\models\User $model */
/** @var yii\bootstrap5\ActiveForm $form */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

?>

<div class="user-form">
    <?php $form = ActiveForm::begin([
        'id' => 'user-form',
        'options' => ['class' => 'form-horizontal'],
        'fieldConfig' => [
            'template' => "{label}\n{input}\n{error}",
            'labelOptions' => ['class' => 'form-label'],
            'inputOptions' => ['class' => 'form-control'],
            'errorOptions' => ['class' => 'invalid-feedback'],
        ],
    ]); ?>

    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'username')->textInput([
                'maxlength' => true,
                'placeholder' => 'Digite o nome de utilizador'
            ]) ?>
        </div>

        <div class="col-md-6">
            <?= $form->field($model, 'email')->textInput([
                'maxlength' => true,
                'placeholder' => 'Digite o email',
                'type' => 'email'
            ]) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'role')->dropDownList(
                $rolesList ?? [],
                [
                    'prompt' => 'Selecione o perfil',
                    'class' => 'form-select'
                ]
            ) ?>
        </div>

        <div class="col-md-6">
            <?= $form->field($model, 'status')->dropDownList([
                $model::STATUS_ACTIVE => 'Ativo',
                $model::STATUS_INACTIVE => 'Inativo'
            ], [
                'class' => 'form-select'
            ]) ?>
        </div>
    </div>

    <?php if ($model->scenario === $model::SCENARIO_CREATE || !empty($model->password)): ?>
        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, 'password')->passwordInput([
                    'maxlength' => true,
                    'placeholder' => $model->isNewRecord ? 'Digite a password' : 'Deixe em branco para manter a password atual',
                    'value' => ''
                ]) ?>
                <small class="form-text text-muted">
                    <?= $model->isNewRecord ? 'Password para o novo utilizador' : 'Deixe em branco se não quiser alterar a password' ?>
                </small>
            </div>

            <div class="col-md-6">
                <?= $form->field($model, 'password_repeat')->passwordInput([
                    'maxlength' => true,
                    'placeholder' => 'Confirme a password'
                ]) ?>
            </div>
        </div>
    <?php endif; ?>

    <hr>

    <div class="form-group">
        <div class="d-flex justify-content-between">
            <?= Html::a('<i class="fa fa-times me-2"></i>Cancelar', ['index'], [
                'class' => 'btn btn-secondary',
            ]) ?>

            <div>
                <?php if (!$model->isNewRecord && $model->id !== Yii::$app->user->id): ?>
                    <?php if ($model->status === $model::STATUS_ACTIVE): ?>
                        <?= Html::a('<i class="fa fa-user-slash me-2"></i>Desativar', ['delete', 'id' => $model->id], [
                            'class' => 'btn btn-danger',
                            'data' => [
                                'confirm' => 'Tem a certeza que deseja desativar este utilizador?',
                                'method' => 'post',
                            ],
                        ]) ?>
                    <?php else: ?>
                        <?= Html::a('<i class="fa fa-user-check me-2"></i>Ativar', ['restore', 'id' => $model->id], [
                            'class' => 'btn btn-success',
                            'data' => [
                                'confirm' => 'Tem a certeza que deseja ativar este utilizador?',
                                'method' => 'post',
                            ],
                        ]) ?>
                    <?php endif; ?>
                <?php endif; ?>

                <?= Html::submitButton('<i class="fa fa-save me-2"></i>' . ($model->isNewRecord ? 'Criar Utilizador' : 'Guardar Alterações'), [
                    'class' => 'btn btn-primary',
                    'name' => 'submit-button'
                ]) ?>
            </div>
        </div>
    </div>

    <?php ActiveForm::end(); ?>
</div>