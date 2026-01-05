<?php

/** @var yii\web\View $this - Objeto da view do Yii */
/** @var yii\bootstrap5\ActiveForm $form - Objeto do formulário Bootstrap 5 */
/** @var \common\models\LoginForm $model - Modelo do formulário de login */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

// Definir título da página
$this->title = 'Login';
?>

<div class="site-login">
    <!-- Container responsivo centrado -->
    <div class="row justify-content-center">
        <div class="col-md-4">
            <!-- Cartão para o formulário de login -->
            <div class="card mt-5">
                <div class="card-body">
                    <!-- Título da página -->
                    <h1 class="text-center mb-4"><?= Html::encode($this->title) ?></h1>

                    <!-- Início do formulário de login -->
                    <?php $form = ActiveForm::begin(['id' => 'login-form']); ?>

                    <!-- Campo para nome de utilizador -->
                    <?= $form->field($model, 'username')->textInput([
                            'autofocus' => true, // Foco automático neste campo
                            'placeholder' => 'Nome de utilizador' // Texto de placeholder
                    ]) ?>

                    <!-- Campo para palavra-passe -->
                    <?= $form->field($model, 'password')->passwordInput([
                            'placeholder' => 'Palavra-passe' // Texto de placeholder
                    ]) ?>

                    <!-- Checkbox para lembrar credenciais -->
                    <?= $form->field($model, 'rememberMe')->checkbox() ?>

                    <!-- Botão de submissão do formulário -->
                    <div class="form-group">
                        <?= Html::submitButton('Login', [
                                'class' => 'btn btn-primary w-100', // Botão primário com largura total
                                'name' => 'login-button' // Nome do botão
                        ]) ?>
                    </div>

                    <!-- Link para página de registo -->
                    <div class="text-center mt-2">
                        <p class="text-center mb-0">Não tens uma conta? <?= Html::a('Crie uma', ['/site/signup']) ?></p>
                    </div>

                    <?php ActiveForm::end(); ?>
                    <!-- Fim do formulário de login -->
                </div>
            </div>
        </div>
    </div>
</div>