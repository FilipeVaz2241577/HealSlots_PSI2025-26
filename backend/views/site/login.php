<?php
use yii\helpers\Html;
use yii\bootstrap4\ActiveForm;
use yii\bootstrap4\Alert;
?>

<div class="login-container">
    <div class="login-card">
        <!-- Header Simples -->
        <div class="login-header">
            <div class="logo">
                <i class="fas fa-hospital-alt"></i>
                <h1>HealSlots</h1>
            </div>
            <p class="system-name">Sistema de Gestão</p>
        </div>

        <div class="login-body">
            <!-- Mensagem de erro geral -->
            <?php if (Yii::$app->session->hasFlash('error')): ?>
                <div class="alert-message">
                    <?= Alert::widget([
                        'body' => Yii::$app->session->getFlash('error'),
                        'options' => [
                            'class' => 'alert-danger',
                            'style' => 'margin-bottom: 20px;'
                        ]
                    ]) ?>
                </div>
            <?php endif; ?>

            <?php $form = ActiveForm::begin([
                'id' => 'login-form',
                'fieldConfig' => [
                    'errorOptions' => ['class' => 'error-message']
                ]
            ]) ?>

            <!-- Campo Username -->
            <div class="form-group">
                <div class="input-wrapper">
                    <i class="fas fa-user input-icon"></i>
                    <?= $form->field($model, 'username')
                        ->label(false)
                        ->textInput([
                            'placeholder' => 'Utilizador',
                            'class' => 'form-control',
                            'autofocus' => true
                        ]) ?>
                </div>
                <div class="error-container">
                    <?= Html::error($model, 'username', ['class' => 'error-message']) ?>
                </div>
            </div>

            <!-- Campo Password -->
            <div class="form-group">
                <div class="input-wrapper">
                    <i class="fas fa-lock input-icon"></i>
                    <?= $form->field($model, 'password')
                        ->label(false)
                        ->passwordInput([
                            'placeholder' => 'Password',
                            'class' => 'form-control',
                            'id' => 'password-field'
                        ]) ?>
                    <button type="button" class="password-toggle" id="password-toggle">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <div class="error-container">
                    <?= Html::error($model, 'password', ['class' => 'error-message']) ?>
                </div>
            </div>

            <!-- Lembrar-me -->
            <div class="form-group remember-me">
                <?= $form->field($model, 'rememberMe')->checkbox([
                    'label' => 'Manter sessão iniciada'
                ]) ?>
            </div>

            <!-- Botão de Login -->
            <div class="form-group">
                <?= Html::submitButton('Entrar', [
                    'class' => 'btn btn-primary btn-block btn-login',
                    'name' => 'login-button'
                ]) ?>
            </div>

            <?php ActiveForm::end(); ?>

        </div>
    </div>
</div>

<style>
    .login-container {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f8f9fa;
        padding: 20px;
    }

    .login-card {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        width: 100%;
        max-width: 400px;
        border: 1px solid #dee2e6;
    }

    .login-header {
        padding: 30px 30px 20px;
        text-align: center;
        border-bottom: 1px solid #e9ecef;
        background: #fff;
    }

    .logo {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        margin-bottom: 8px;
    }

    .logo i {
        font-size: 2rem;
        color: #007bff;
    }

    .logo h1 {
        font-size: 1.5rem;
        font-weight: 600;
        color: #343a40;
        margin: 0;
    }

    .system-name {
        color: #6c757d;
        font-size: 0.9rem;
        margin: 0;
    }

    .login-body {
        padding: 30px;
    }

    .alert-message {
        width: 100%;
        max-width: 300px;
        margin: 0 auto 20px;
    }

    .alert-danger {
        background-color: #f8d7da;
        border-color: #f5c6cb;
        color: #721c24;
        padding: 12px 15px;
        border-radius: 4px;
        font-size: 0.9rem;
        text-align: center;
    }

    .form-group {
        margin-bottom: 1rem;
        display: flex;
        flex-direction: column;
        align-items: center;
        width: 100%;
    }

    .input-wrapper {
        position: relative;
        display: flex;
        align-items: center;
        width: 100%;
        max-width: 300px;
        margin-bottom: 4px;
    }

    .input-icon {
        position: absolute;
        left: 12px;
        color: #6c757d;
        z-index: 3;
    }

    .form-control {
        padding-left: 40px;
        padding-right: 40px;
        height: 45px;
        border: 1px solid #ced4da;
        border-radius: 4px;
        width: 100%;
    }

    .form-control:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
    }

    .password-toggle {
        position: absolute;
        right: 12px;
        background: none;
        border: none;
        color: #6c757d;
        cursor: pointer;
        z-index: 3;
    }

    .password-toggle:hover {
        color: #495057;
    }

    .error-container {
        width: 100%;
        max-width: 300px;
        display: flex;
        justify-content: center;
    }

    .error-message {
        color: #dc3545;
        font-size: 0.8rem;
        margin-top: 2px;
        display: block;
        text-align: center;
        width: 100%;
    }

    .remember-me {
        margin-bottom: 1.5rem;
        justify-content: center;
    }

    .remember-me .checkbox {
        margin: 0;
    }

    .btn-login {
        height: 45px;
        font-weight: 500;
        background: #007bff;
        border: none;
        border-radius: 4px;
        max-width: 300px;
        margin: 0 auto;
        width: 100%;
    }

    .login-footer {
        text-align: center;
        margin-top: 1.5rem;
        padding-top: 1.5rem;
        border-top: 1px solid #e9ecef;
    }

    .help-link {
        color: #6c757d;
        font-size: 0.9rem;
        text-decoration: none;
    }

    .help-link:hover {
        color: #007bff;
        text-decoration: underline;
    }

    /* Estados de loading */
    .btn-loading {
        position: relative;
        color: transparent;
    }

    .btn-loading::after {
        content: '';
        position: absolute;
        width: 16px;
        height: 16px;
        top: 50%;
        left: 50%;
        margin-left: -8px;
        margin-top: -8px;
        border: 2px solid transparent;
        border-top: 2px solid #fff;
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle password visibility
        const passwordToggle = document.getElementById('password-toggle');
        const passwordField = document.getElementById('password-field');

        if (passwordToggle && passwordField) {
            passwordToggle.addEventListener('click', function() {
                const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordField.setAttribute('type', type);
                const icon = this.querySelector('i');
                icon.classList.toggle('fa-eye');
                icon.classList.toggle('fa-eye-slash');
            });
        }

        // Loading state no botão
        const loginForm = document.getElementById('login-form');
        const loginButton = document.querySelector('.btn-login');

        if (loginForm && loginButton) {
            loginForm.addEventListener('submit', function() {
                loginButton.classList.add('btn-loading');
                loginButton.disabled = true;
            });
        }
    });
</script>