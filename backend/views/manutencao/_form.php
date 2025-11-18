<?php

/** @var yii\web\View $this */
/** @var common\models\Manutencao $model */
/** @var yii\bootstrap5\ActiveForm $form */
/** @var array $tecnicosList */
/** @var array $equipamentosList */
/** @var array $salasList */

use yii\bootstrap5\ActiveForm;
use yii\helpers\ArrayHelper;
use common\models\Manutencao;
?>

<?php $form = ActiveForm::begin([
    'id' => 'manutencao-form',
    'fieldConfig' => [
        'options' => ['class' => 'mb-4'],
        'inputOptions' => ['class' => 'form-control form-control-lg'],
        'labelOptions' => ['class' => 'form-label fw-bold text-dark'],
    ],
]); ?>

<!-- Informações Básicas -->
<div class="row mb-4">
    <div class="col-12">
        <h4 class="text-primary mb-3">
            <i class="fa fa-info-circle me-2"></i>Informações Básicas
        </h4>
    </div>

    <div class="col-md-6">
        <?= $form->field($model, 'equipamento_id')
            ->dropDownList($equipamentosList, [
                'class' => 'form-select form-select-lg',
                'prompt' => '-- Selecione o equipamento --'
            ])
            ->label('Equipamento <span class="text-danger">*</span>')
            ->hint('Equipamento que necessita de manutenção', ['class' => 'form-text text-muted small'])
        ?>
    </div>

    <div class="col-md-6">
        <?= $form->field($model, 'sala_id')
            ->dropDownList($salasList, [
                'class' => 'form-select form-select-lg',
                'prompt' => '-- Selecione a sala --'
            ])
            ->label('Sala')
            ->hint('Sala onde se encontra o equipamento', ['class' => 'form-text text-muted small'])
        ?>
    </div>
</div>

<!-- Datas e Técnico -->
<div class="row mb-4">
    <div class="col-12">
        <h4 class="text-primary mb-3">
            <i class="fa fa-calendar me-2"></i>Agendamento
        </h4>
    </div>

    <div class="col-md-4">
        <?= $form->field($model, 'dataInicio')
            ->input('datetime-local')
            ->label('Data Início <span class="text-danger">*</span>')
            ->hint('Data e hora de início da manutenção', ['class' => 'form-text text-muted small'])
        ?>
    </div>

    <div class="col-md-4">
        <?= $form->field($model, 'dataFim')
            ->input('datetime-local')
            ->label('Data Fim')
            ->hint('Data e hora de conclusão da manutenção', ['class' => 'form-text text-muted small'])
        ?>
    </div>

    <div class="col-md-4">
        <?= $form->field($model, 'user_id')
            ->dropDownList($tecnicosList, [
                'class' => 'form-select form-select-lg',
                'prompt' => '-- Selecione o técnico --'
            ])
            ->label('Técnico Responsável')
            ->hint('Técnico que irá executar a manutenção', ['class' => 'form-text text-muted small'])
        ?>
    </div>
</div>

<!-- Estado e Descrição -->
<div class="row mb-4">
    <div class="col-12">
        <h4 class="text-primary mb-3">
            <i class="fa fa-cogs me-2"></i>Detalhes
        </h4>
    </div>

    <div class="col-md-4">
        <?= $form->field($model, 'status')
            ->dropDownList(Manutencao::getStatusOptions(), [
                'class' => 'form-select form-select-lg'
            ])
            ->label('Estado')
            ->hint('Estado atual da manutenção', ['class' => 'form-text text-muted small'])
        ?>
    </div>

    <div class="col-md-8">
        <?= $form->field($model, 'descricao')
            ->textarea([
                'rows' => 4,
                'placeholder' => 'Descreva detalhadamente a manutenção a ser realizada...'
            ])
            ->label('Descrição')
            ->hint('Descrição detalhada dos trabalhos a realizar', ['class' => 'form-text text-muted small'])
        ?>
    </div>
</div>

<!-- Botões de Ação -->
<div class="row mt-5">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center border-top pt-4">
            <?= \yii\bootstrap5\Html::a('<i class="fa fa-times me-2"></i>Cancelar', ['index'], [
                'class' => 'btn btn-lg btn-outline-secondary'
            ]) ?>

            <?= \yii\bootstrap5\Html::submitButton('<i class="fa fa-save me-2"></i>' . ($model->isNewRecord ? 'Criar Manutenção' : 'Atualizar Manutenção'), [
                'class' => 'btn btn-lg btn-primary px-4',
                'name' => 'save-button'
            ]) ?>
        </div>
    </div>
</div>

<?php ActiveForm::end(); ?>

<!-- CSS adicional -->
<style>
    .form-label {
        color: #2c3e50;
        font-weight: 600;
        font-size: 0.95rem;
    }
    .form-control:focus, .form-select:focus {
        border-color: #3498db;
        box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
    }
    .form-control-lg {
        padding: 0.75rem 1rem;
        font-size: 1rem;
    }
    .form-select-lg {
        padding: 0.75rem 2.5rem 0.75rem 1rem;
    }
    .btn-lg {
        min-width: 180px;
        padding: 0.75rem 2rem;
    }
    .text-danger {
        color: #e74c3c !important;
    }
    h4.text-primary {
        border-bottom: 2px solid #3498db;
        padding-bottom: 0.5rem;
    }
    .form-text {
        font-size: 0.85rem;
    }
</style>