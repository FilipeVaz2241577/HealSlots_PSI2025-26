<?php

/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */
/** @var \common\models\LoginForm $model */

use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;

$this->title = 'Login';
?>
<div class="site-login">
    <div class="row justify-content-center">
        <div class="col-md-4">
            <h1 class="text-center mb-4"><?= Html::encode($this->title) ?></h1>

            <?php $form = ActiveForm::begin(['id' => 'login-form']); ?>

            <?= $form->field($model, 'username')->textInput(['autofocus' => true, 'placeholder' => 'Usuário']) ?>

            <?= $form->field($model, 'password')->passwordInput(['placeholder' => 'Senha']) ?>

            <?= $form->field($model, 'rememberMe')->checkbox() ?>

            <div class="form-group">
                <?= Html::submitButton('Entrar', ['class' => 'btn btn-primary w-100', 'name' => 'login-button']) ?>
            </div>

            <div class="text-center mb-3">
                <?= Html::a('Não tem conta? Registe-se aqui', ['site/signup']) ?>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>