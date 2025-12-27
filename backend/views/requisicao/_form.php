<?php

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

?>

<div class="requisicao-form">
    <?php $form = ActiveForm::begin([
        'id' => 'requisicao-form',
        'fieldConfig' => [
            'template' => "{label}\n{input}\n{error}",
            'labelOptions' => ['class' => 'form-label fw-bold'],
            'inputOptions' => ['class' => 'form-control'],
            'errorOptions' => ['class' => 'invalid-feedback'],
        ],
    ]); ?>

    <?php if ($model->isNewRecord): ?>
        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, 'sala_id')->dropDownList(
                    \yii\helpers\ArrayHelper::map(
                        \common\models\Sala::find()
                            ->joinWith(['bloco'])
                            ->where(['sala.estado' => 'Livre'])
                            ->andWhere(['bloco.estado' => 'ativo'])
                            ->all(),
                        'id',
                        function($sala) {
                            return $sala->bloco->nome . ' - ' . $sala->nome . ' (ID: ' . $sala->id . ')';
                        }
                    ),
                    ['prompt' => '-- Selecione uma sala --', 'class' => 'form-select']
                )->label('Sala <span class="text-danger">*</span>') ?>
            </div>
        </div>
    <?php else: ?>
        <?= $form->field($model, 'sala_id')->hiddenInput()->label(false) ?>
        <div class="alert alert-info">
            <strong>Sala:</strong> <?= $model->sala->bloco->nome ?? '-' ?> - <?= $model->sala->nome ?? '-' ?> (ID: <?= $model->sala_id ?>)
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'dataInicio')->textInput([
                'type' => 'datetime-local'
            ])->label('Data de Início <span class="text-danger">*</span>') ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'dataFim')->textInput([
                'type' => 'datetime-local'
            ])->label('Data de Fim') ?>
        </div>
    </div>

    <?php if (!$model->isNewRecord): ?>
        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, 'status')->dropDownList(
                    \common\models\Requisicao::optsStatus(),
                    ['class' => 'form-select']
                )->label('Estado <span class="text-danger">*</span>') ?>
            </div>
        </div>
    <?php else: ?>
        <?= $form->field($model, 'status')->hiddenInput(['value' => 'Ativa'])->label(false) ?>
    <?php endif; ?>

    <hr class="my-4">

    <div class="form-group">
        <div class="d-flex justify-content-between">
            <?= Html::a('<i class="fas fa-arrow-left me-2"></i>Voltar à Lista', ['index'], [
                'class' => 'btn btn-secondary',
            ]) ?>

            <div>
                <?php if (!$model->isNewRecord): ?>
                    <?php if ($model->isAtiva()): ?>
                        <?= Html::a('<i class="fas fa-check me-2"></i>Concluir', ['marcar-concluida', 'id' => $model->id], [
                            'class' => 'btn btn-success me-2',
                            'data' => [
                                'confirm' => 'Tem a certeza que deseja marcar esta requisição como concluída?',
                                'method' => 'post',
                            ],
                        ]) ?>
                        <?= Html::a('<i class="fas fa-times me-2"></i>Cancelar', ['marcar-cancelada', 'id' => $model->id], [
                            'class' => 'btn btn-danger me-2',
                            'data' => [
                                'confirm' => 'Tem a certeza que deseja cancelar esta requisição?',
                                'method' => 'post',
                            ],
                        ]) ?>
                    <?php endif; ?>

                    <?= Html::a('<i class="fas fa-trash me-2"></i>Eliminar', ['delete', 'id' => $model->id], [
                        'class' => 'btn btn-danger me-2',
                        'data' => [
                            'confirm' => 'Tem a certeza que deseja eliminar esta requisição?',
                            'method' => 'post',
                        ],
                    ]) ?>
                <?php endif; ?>

                <?= Html::submitButton('<i class="fas fa-save me-2"></i>' . ($model->isNewRecord ? 'Criar Requisição' : 'Guardar Alterações'), [
                    'class' => 'btn btn-primary',
                    'name' => 'submit-button'
                ]) ?>
            </div>
        </div>
    </div>

    <?php ActiveForm::end(); ?>
</div>

<style>
    .form-label {
        color: #2c3e50;
        margin-bottom: 0.5rem;
    }
    .form-control:focus, .form-select:focus {
        border-color: #3498db;
        box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
    }
    .invalid-feedback {
        display: block;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Formatar datas para o formato datetime-local (YYYY-MM-DDTHH:mm)
        function formatDateForInput(dateStr) {
            if (!dateStr) return '';
            const date = new Date(dateStr);
            return date.toISOString().slice(0, 16);
        }

        // Aplicar formatação aos campos existentes
        const dataInicioInput = document.getElementById('requisicao-datainicio');
        const dataFimInput = document.getElementById('requisicao-datafim');

        if (dataInicioInput && dataInicioInput.value) {
            dataInicioInput.value = formatDateForInput(dataInicioInput.value);
        }

        if (dataFimInput && dataFimInput.value) {
            dataFimInput.value = formatDateForInput(dataFimInput.value);
        }

        // Definir data mínima como agora para novas requisições
        if (dataInicioInput && !dataInicioInput.value) {
            const now = new Date();
            // Remover segundos e milissegundos
            now.setSeconds(0);
            now.setMilliseconds(0);
            dataInicioInput.value = now.toISOString().slice(0, 16);
            dataInicioInput.min = now.toISOString().slice(0, 16);
        }

        // Verificar disponibilidade quando alterar datas ou sala
        function verificarDisponibilidade() {
            const salaId = document.getElementById('requisicao-sala_id')?.value;
            const dataInicio = document.getElementById('requisicao-datainicio')?.value;
            const dataFim = document.getElementById('requisicao-datafim')?.value;

            if (!salaId || !dataInicio || !dataFim) {
                return;
            }

            // Verificação básica no frontend
            const inicio = new Date(dataInicio);
            const fim = new Date(dataFim);

            if (fim <= inicio) {
                mostrarMensagemDisponibilidade('danger', 'A data de fim deve ser posterior à data de início');
                return;
            }

            // Aqui poderia fazer uma chamada AJAX para verificar no servidor
            mostrarMensagemDisponibilidade('info', 'Verificando disponibilidade...');
        }

        function mostrarMensagemDisponibilidade(tipo, mensagem) {
            let statusDiv = document.getElementById('disponibilidade-status');
            if (!statusDiv) {
                statusDiv = document.createElement('div');
                statusDiv.id = 'disponibilidade-status';
                const form = document.querySelector('#requisicao-form');
                form.insertBefore(statusDiv, form.querySelector('.form-group'));
            }

            const classes = {
                'success': 'alert-success',
                'danger': 'alert-danger',
                'info': 'alert-info',
                'warning': 'alert-warning'
            };

            statusDiv.innerHTML = `
            <div class="alert ${classes[tipo]} mt-2">
                <i class="fas fa-${tipo === 'success' ? 'check' : 'exclamation'}-circle me-2"></i>${mensagem}
            </div>
        `;
        }

        // Adicionar event listeners
        const salaSelect = document.getElementById('requisicao-sala_id');
        const dataInicioField = document.getElementById('requisicao-datainicio');
        const dataFimField = document.getElementById('requisicao-datafim');

        if (salaSelect) salaSelect.addEventListener('change', verificarDisponibilidade);
        if (dataInicioField) dataInicioField.addEventListener('change', verificarDisponibilidade);
        if (dataFimField) dataFimField.addEventListener('change', verificarDisponibilidade);
    });
</script>