<?php

use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;
use yii\helpers\ArrayHelper;

$this->title = 'Nova Requisição';
$this->params['breadcrumbs'][] = ['label' => 'Gestão de Requisições', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

// Carregar salas disponíveis
$salasDisponiveis = \common\models\Sala::find()
    ->joinWith(['bloco'])
    ->where(['sala.estado' => 'Livre'])
    ->andWhere(['bloco.estado' => 'ativo'])
    ->orderBy(['bloco.nome' => SORT_ASC, 'sala.nome' => SORT_ASC])
    ->all();

$salasList = ArrayHelper::map($salasDisponiveis, 'id', function($sala) {
    return $sala->bloco->nome . ' - ' . $sala->nome . ' (ID: ' . $sala->id . ')';
});
?>

<div class="requisicao-create">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card card-primary">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h3 class="card-title">
                                <i class="fas fa-calendar-plus me-2"></i>
                                <?= Html::encode($this->title) ?>
                            </h3>
                            <?= Html::a('<i class="fas fa-arrow-left me-2"></i>Voltar à Lista', ['index'], ['class' => 'btn btn-secondary btn-sm']) ?>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <h6><i class="fas fa-info-circle me-2"></i>Informação Importante</h6>
                            <p class="mb-0">
                                • As requisições só podem ser criadas para salas com estado "Livre"<br>
                                • O bloco da sala deve estar "Ativo"<br>
                                • Verifique a disponibilidade da sala antes de submeter<br>
                                • Ao criar a requisição, o estado da sala mudará para "Em Uso"
                            </p>
                        </div>

                        <?php if (empty($salasDisponiveis)): ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Atenção:</strong> Não há salas disponíveis no momento. Todas as salas estão em uso, em manutenção ou desativadas.
                                <?= Html::a('Ver estado das salas', ['sala/index'], ['class' => 'alert-link ms-2']) ?>
                            </div>
                        <?php endif; ?>

                        <?php $form = ActiveForm::begin([
                            'id' => 'requisicao-form',
                            'fieldConfig' => [
                                'options' => ['class' => 'mb-4'],
                                'inputOptions' => ['class' => 'form-control'],
                                'labelOptions' => ['class' => 'form-label fw-bold'],
                            ],
                        ]); ?>

                        <div class="row">
                            <div class="col-md-6">
                                <?= $form->field($model, 'sala_id')->dropDownList(
                                    $salasList,
                                    [
                                        'prompt' => '-- Selecione uma sala --',
                                        'class' => 'form-select',
                                        'required' => true,
                                        'disabled' => empty($salasDisponiveis)
                                    ]
                                )->label('Sala <span class="text-danger">*</span>') ?>
                            </div>
                            <div class="col-md-3">
                                <?= $form->field($model, 'dataInicio')->textInput([
                                    'type' => 'datetime-local',
                                    'required' => true
                                ])->label('Data de Início <span class="text-danger">*</span>') ?>
                            </div>
                            <div class="col-md-3">
                                <?= $form->field($model, 'dataFim')->textInput([
                                    'type' => 'datetime-local'
                                ])->label('Data de Fim') ?>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card border-0 bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title mb-3">
                                            <i class="fas fa-clock me-2"></i>Informação sobre Horários
                                        </h6>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="text-center">
                                                    <i class="fas fa-hourglass-start fa-2x text-primary mb-2"></i>
                                                    <h6 class="text-dark">Data de Início</h6>
                                                    <p class="text-muted small mb-0">Quando a requisição começa</p>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="text-center">
                                                    <i class="fas fa-hourglass-end fa-2x text-success mb-2"></i>
                                                    <h6 class="text-dark">Data de Fim</h6>
                                                    <p class="text-muted small mb-0">Quando a requisição termina (opcional)</p>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="text-center">
                                                    <i class="fas fa-calendar-check fa-2x text-warning mb-2"></i>
                                                    <h6 class="text-dark">Sem Data de Fim</h6>
                                                    <p class="text-muted small mb-0">Requisição contínua até ser cancelada</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-warning mt-4">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Nota:</strong> Ao submeter esta requisição, o estado da sala será automaticamente alterado para <span class="badge bg-danger">Em Uso</span>.
                        </div>

                        <hr class="my-4">

                        <div class="form-group">
                            <div class="d-flex justify-content-between">
                                <?= Html::a('<i class="fas fa-times me-2"></i>Cancelar', ['index'], [
                                    'class' => 'btn btn-lg btn-outline-secondary'
                                ]) ?>

                                <?php if (!empty($salasDisponiveis)): ?>
                                    <?= Html::submitButton('<i class="fas fa-save me-2"></i>Criar Requisição', [
                                        'class' => 'btn btn-lg btn-primary px-4',
                                        'name' => 'create-button'
                                    ]) ?>
                                <?php else: ?>
                                    <?= Html::button('<i class="fas fa-ban me-2"></i>Sem Salas Disponíveis', [
                                        'class' => 'btn btn-lg btn-secondary px-4',
                                        'disabled' => true
                                    ]) ?>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php ActiveForm::end(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .form-label {
        color: #2c3e50;
        font-weight: 600;
    }
    .form-control:focus, .form-select:focus {
        border-color: #3498db;
        box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
    }
    .btn-lg {
        min-width: 180px;
        padding: 0.75rem 2rem;
    }
    .card {
        transition: all 0.3s ease;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Definir data mínima como agora
        const dataInicioInput = document.getElementById('requisicao-datainicio');

        if (dataInicioInput) {
            const now = new Date();
            // Remover segundos e milissegundos
            now.setSeconds(0);
            now.setMilliseconds(0);
            const minDate = now.toISOString().slice(0, 16);
            dataInicioInput.value = minDate;
            dataInicioInput.min = minDate;
        }
    });
</script>