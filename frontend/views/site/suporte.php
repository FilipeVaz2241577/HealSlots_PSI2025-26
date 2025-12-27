<?php

/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */
/** @var \frontend\models\ContactForm $model */

use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;

$this->title = 'Suporte';
?>
<div class="site-suporte">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-body p-5">
                        <div class="text-center mb-5">
                            <h1 class="display-5 text-primary mb-2"><?= Html::encode($this->title) ?></h1>
<<<<<<< HEAD
                            <p class="lead">Entre em contacto connosco para qualquer questão ou suporte</p>
=======
                            <p class="lead">Entre em contacto connosco </p>
>>>>>>> origin/filipe
                        </div>

                        <!-- Formulário de Contacto Centralizado -->
                        <div class="contact-form-wrapper">
<<<<<<< HEAD
                            <h4 class="text-primary mb-4 text-center">Envie-nos uma mensagem</h4>
=======
>>>>>>> origin/filipe

                            <?php $form = ActiveForm::begin([
                                    'id' => 'contact-form',
                                    'options' => ['class' => 'needs-validation']
                            ]); ?>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <?= $form->field($model, 'name')->textInput([
                                            'autofocus' => true,
                                            'class' => 'form-control form-control-lg',
<<<<<<< HEAD
                                            'placeholder' => 'Seu nome'
=======
                                            'placeholder' => 'Nome'
>>>>>>> origin/filipe
                                    ])->label('Nome') ?>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <?= $form->field($model, 'email')->textInput([
                                            'class' => 'form-control form-control-lg',
<<<<<<< HEAD
                                            'placeholder' => 'seu@email.com'
=======
                                            'placeholder' => 'Email'
>>>>>>> origin/filipe
                                    ])->label('Email') ?>
                                </div>
                            </div>

                            <div class="mb-3">
                                <?= $form->field($model, 'subject')->textInput([
                                        'class' => 'form-control form-control-lg',
                                        'placeholder' => 'Assunto da mensagem'
                                ])->label('Assunto') ?>
                            </div>

                            <div class="mb-3">
                                <?= $form->field($model, 'body')->textarea([
                                        'rows' => 5,
                                        'class' => 'form-control form-control-lg',
<<<<<<< HEAD
                                        'placeholder' => 'Escreva a sua mensagem aqui...'
=======
                                        'placeholder' => 'Escreve a tua mensagem aqui...'
>>>>>>> origin/filipe
                                ])->label('Mensagem') ?>
                            </div>

                            <div class="d-grid">
                                <?= Html::submitButton('Enviar Mensagem', [
                                        'class' => 'btn btn-primary btn-lg py-3',
                                        'name' => 'contact-button'
                                ]) ?>
                            </div>

                            <?php ActiveForm::end(); ?>
                        </div>

                        <!-- Serviços de Suporte -->
                        <div class="row mt-5">
                            <div class="col-12">
                                <h4 class="text-primary mb-4 text-center">📋 Serviços de Suporte</h4>
                                <div class="row text-center justify-content-center">
                                    <div class="col-md-3 mb-3">
                                        <div class="p-3 border rounded">
                                            <i class="fas fa-tools fa-2x text-primary mb-2"></i>
                                            <h6>Suporte Técnico</h6>
                                            <small class="text-muted">Equipamentos médicos</small>
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <div class="p-3 border rounded">
                                            <i class="fas fa-calendar-check fa-2x text-primary mb-2"></i>
                                            <h6>Agendamentos</h6>
                                            <small class="text-muted">Assistência com marcações</small>
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <div class="p-3 border rounded">
                                            <i class="fas fa-cogs fa-2x text-primary mb-2"></i>
                                            <h6>Problemas Sistema</h6>
                                            <small class="text-muted">Resolução de problemas</small>
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <div class="p-3 border rounded">
                                            <i class="fas fa-question-circle fa-2x text-primary mb-2"></i>
                                            <h6>Dúvidas</h6>
                                            <small class="text-muted">Esclarecimento de funcionalidades</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>