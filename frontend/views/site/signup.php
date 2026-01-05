<?php

/** @var yii\web\View $this - Objeto da view do Yii */
/** @var yii\bootstrap5\ActiveForm $form - Objeto do formulário Bootstrap 5 */
/** @var \frontend\models\SignupForm $model - Modelo do formulário de registo */

use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;

// Definir título da página
$this->title = 'Registo';
?>
<div class="site-signup">
    <!-- Container responsivo centrado -->
    <div class="row justify-content-center">
        <div class="col-md-4">
            <!-- Cartão para o formulário de registo -->
            <div class="card mt-5">
                <div class="card-body">
                    <!-- Título da página -->
                    <h1 class="text-center mb-4"><?= Html::encode($this->title) ?></h1>

                    <!-- Início do formulário de registo -->
                    <?php $form = ActiveForm::begin(['id' => 'form-signup']); ?>

                    <!-- Campo para nome de utilizador -->
                    <?= $form->field($model, 'username')->textInput([
                            'autofocus' => true, // Foco automático neste campo
                            'placeholder' => 'Nome de utilizador' // Texto de placeholder
                    ]) ?>

                    <!-- Campo para endereço de email -->
                    <?= $form->field($model, 'email')->textInput([
                            'placeholder' => 'Email' // Texto de placeholder
                    ]) ?>

                    <!-- Campo para palavra-passe -->
                    <?= $form->field($model, 'password')->passwordInput([
                            'placeholder' => 'Palavra-passe' // Texto de placeholder
                    ]) ?>

                    <!-- Botão de submissão do formulário -->
                    <div class="form-group">
                        <?= Html::submitButton('Registar', [
                                'class' => 'btn btn-primary w-100', // Botão primário com largura total
                                'name' => 'signup-button' // Nome do botão
                        ]) ?>
                    </div>

                    <!-- Link para página de login -->
                    <div class="text-center mt-2">
                        <p class="text-center mb-0">Já tem conta? <?= Html::a('Faça login aqui', ['site/login'])?></p>
                    </div>

                    <?php ActiveForm::end(); ?>
                    <!-- Fim do formulário de registo -->
                </div>
            </div>
        </div>
    </div>
</div>