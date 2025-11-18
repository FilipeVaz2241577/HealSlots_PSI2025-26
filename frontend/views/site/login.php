<?php

/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */
/** @var \common\models\LoginForm $model */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

$this->title = 'Login';
?>

<div class="site-login">
    <div class="row justify-content-center">
        <div class="col-md-4">
            <div class="card mt-5">
                <div class="card-body">
                    <h1 class="text-center mb-4"><?= Html::encode($this->title) ?></h1>

                    <?php $form = ActiveForm::begin(['id' => 'login-form']); ?>

                    <?= $form->field($model, 'username')->textInput(['autofocus' => true, 'placeholder' => 'Nome de utilizador']) ?>

                    <?= $form->field($model, 'password')->passwordInput(['placeholder' => 'Palavra-passe']) ?>

                    <?= $form->field($model, 'rememberMe')->checkbox() ?>

                    <div class="form-group">
                        <?= Html::submitButton('Login', ['class' => 'btn btn-primary w-100', 'name' => 'login-button']) ?>
                    </div>


                    <div class="text-center mt-2">
                        <p class="text-center mb-0">Não tens uma conta? <?= Html::a('Crie uma', ['/site/signup']) ?></p>
                    </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
    </div>
</div>