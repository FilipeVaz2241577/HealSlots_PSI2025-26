<?php

/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */
/** @var \frontend\models\SignupForm $model */

use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;

$this->title = 'Registo';
?>
<div class="site-signup">
    <div class="row justify-content-center">
        <div class="col-md-4">
            <div class="card mt-5">
                <div class="card-body">
                    <h1 class="text-center mb-4"><?= Html::encode($this->title) ?></h1>

                    <?php $form = ActiveForm::begin(['id' => 'form-signup']); ?>

                    <?= $form->field($model, 'username')->textInput(['autofocus' => true, 'placeholder' => 'Nome de utilizador']) ?>

                    <?= $form->field($model, 'email')->textInput(['placeholder' => 'Email']) ?>

                    <?= $form->field($model, 'password')->passwordInput(['placeholder' => 'Palavra-passe']) ?>

                    <div class="form-group">
                        <?= Html::submitButton('Registar', ['class' => 'btn btn-primary w-100', 'name' => 'signup-button']) ?>
                    </div>


                    <div class="text-center mt-2">
                        <p class="text-center mb-0">Já tem conta? <?= Html::a('Faça login aqui', ['site/login'])?></p>
                    </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
    </div>
</div>