<?php

/** @var yii\web\View $this */
/** @var common\models\Manutencao $model */
/** @var yii\bootstrap5\ActiveForm $form */
/** @var array $tecnicosList */
/** @var array $equipamentosList */
/** @var array $salasList */
<<<<<<< HEAD
=======
/** @var int|null $equipamento_id */
/** @var int|null $sala_id */
>>>>>>> origin/filipe

use yii\bootstrap5\ActiveForm;
use yii\helpers\ArrayHelper;
use common\models\Manutencao;
<<<<<<< HEAD
=======

// Garantir que as variáveis existem
$equipamento_id = isset($equipamento_id) ? $equipamento_id : '';
$sala_id = isset($sala_id) ? $sala_id : '';
>>>>>>> origin/filipe
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
<<<<<<< HEAD
=======
        <div class="alert alert-info">
            <i class="fa fa-info-circle me-2"></i>
            Selecione <strong>pelo menos um</strong>: equipamento OU sala.<br>
            <small class="text-muted">Nota: Apenas são mostrados equipamentos/salas que não estão atualmente em manutenção.</small>
        </div>
>>>>>>> origin/filipe
    </div>

    <div class="col-md-6">
        <?= $form->field($model, 'equipamento_id')
            ->dropDownList($equipamentosList, [
                'class' => 'form-select form-select-lg',
<<<<<<< HEAD
                'prompt' => '-- Selecione o equipamento --'
            ])
            ->label('Equipamento <span class="text-danger">*</span>')
            ->hint('Equipamento que necessita de manutenção', ['class' => 'form-text text-muted small'])
=======
                'prompt' => '-- Selecione o equipamento (opcional) --',
                'id' => 'equipamento-select'
            ])
            ->label('Equipamento')
            ->hint('Equipamento disponível para manutenção (opcional)', ['class' => 'form-text text-muted small'])
>>>>>>> origin/filipe
        ?>
    </div>

    <div class="col-md-6">
        <?= $form->field($model, 'sala_id')
            ->dropDownList($salasList, [
                'class' => 'form-select form-select-lg',
<<<<<<< HEAD
                'prompt' => '-- Selecione a sala --'
            ])
            ->label('Sala')
            ->hint('Sala onde se encontra o equipamento', ['class' => 'form-text text-muted small'])
=======
                'prompt' => '-- Selecione a sala (opcional) --',
                'id' => 'sala-select'
            ])
            ->label('Sala')
            ->hint('Sala disponível para manutenção (opcional)', ['class' => 'form-text text-muted small'])
>>>>>>> origin/filipe
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
<<<<<<< HEAD
            ->label('Data Fim')
=======
            ->label('Data Fim (Opcional)')
>>>>>>> origin/filipe
            ->hint('Data e hora de conclusão da manutenção', ['class' => 'form-text text-muted small'])
        ?>
    </div>

    <div class="col-md-4">
        <?= $form->field($model, 'user_id')
            ->dropDownList($tecnicosList, [
                'class' => 'form-select form-select-lg',
                'prompt' => '-- Selecione o técnico --'
            ])
<<<<<<< HEAD
            ->label('Técnico Responsável')
=======
            ->label('Técnico Responsável (Opcional)')
>>>>>>> origin/filipe
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
<<<<<<< HEAD
                'placeholder' => 'Descreva detalhadamente a manutenção a ser realizada...'
            ])
            ->label('Descrição')
=======
                'placeholder' => 'Descreva detalhadamente a manutenção a ser realizada...',
                'id' => 'descricao-textarea'
            ])
            ->label('Descrição (Opcional)')
>>>>>>> origin/filipe
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
<<<<<<< HEAD
                'name' => 'save-button'
=======
                'name' => 'save-button',
                'id' => 'submit-button'
>>>>>>> origin/filipe
            ]) ?>
        </div>
    </div>
</div>

<?php ActiveForm::end(); ?>

<<<<<<< HEAD
=======
<!-- JavaScript para preenchimento automático -->
<?php
$js = <<<JS
$(document).ready(function() {
    // Se vier de um equipamento, seleciona automaticamente
    var equipamentoId = '$equipamento_id';
    
    if (equipamentoId && equipamentoId !== '') {
        $('#equipamento-select').val(equipamentoId);
        
        // Busca informações do equipamento via AJAX
        $.get('/Projeto/HealSlots/backend/web/manutencao/get-equipamento-info', {id: equipamentoId}, function(data) {
            if (data.success) {
                // Preenche a descrição com informações do equipamento
                var descricaoAtual = $('#descricao-textarea').val();
                var novaDescricao = 'MANUTENÇÃO DO EQUIPAMENTO:\\n';
                novaDescricao += '------------------------------\\n';
                novaDescricao += 'Equipamento: ' + data.equipamento.nome + '\\n';
                novaDescricao += 'Número de Série: ' + data.equipamento.numeroSerie + '\\n';
                novaDescricao += 'Tipo: ' + data.equipamento.tipo + '\\n';
                novaDescricao += 'Estado atual: ' + data.equipamento.estado + '\\n';
                if (data.sala) {
                    novaDescricao += 'Localização: ' + data.sala.nome + ' (' + data.sala.bloco + ')\\n';
                    // Preenche automaticamente a sala se o equipamento estiver em uma
                    $('#sala-select').val(data.sala.id);
                } else {
                    novaDescricao += 'Localização: Equipamento não está atribuído a nenhuma sala\\n';
                }
                novaDescricao += '\\nDESCRIÇÃO DA MANUTENÇÃO:\\n';
                novaDescricao += '------------------------------\\n';
                if (!descricaoAtual) {
                    descricaoAtual = 'Descreva aqui os trabalhos a realizar...';
                }
                novaDescricao += descricaoAtual;
                $('#descricao-textarea').val(novaDescricao);
            }
        });
    }
    
    // Se vier de uma sala, seleciona automaticamente
    var salaId = '$sala_id';
    if (salaId && salaId !== '') {
        $('#sala-select').val(salaId);
        
        // Preenche a descrição para sala
        var descricaoAtual = $('#descricao-textarea').val();
        if (!descricaoAtual) {
            var novaDescricao = 'MANUTENÇÃO DA SALA:\\n';
            novaDescricao += '------------------------------\\n';
            novaDescricao += 'Sala: [Nome da Sala]\\n';
            novaDescricao += 'Bloco: [Nome do Bloco]\\n';
            novaDescricao += '\\nDESCRIÇÃO DA MANUTENÇÃO:\\n';
            novaDescricao += '------------------------------\\n';
            novaDescricao += 'Descreva aqui os trabalhos a realizar na sala...';
            $('#descricao-textarea').val(novaDescricao);
        }
    }
    
    // Quando o equipamento é alterado, busca sua sala atual
    $('#equipamento-select').change(function() {
        var equipId = $(this).val();
        if (equipId) {
            $.get('/Projeto/HealSlots/backend/web/manutencao/get-equipamento-sala', {id: equipId}, function(data) {
                if (data.success && data.sala_id) {
                    $('#sala-select').val(data.sala_id);
                } else {
                    $('#sala-select').val('');
                }
            });
        }
    });
    
    // Validação: Pelo menos um campo preenchido
    $('#manutencao-form').on('submit', function(e) {
        var equipamento = $('#equipamento-select').val();
        var sala = $('#sala-select').val();
        
        if (!equipamento && !sala) {
            e.preventDefault();
            alert('Erro: Deve selecionar pelo menos um equipamento OU uma sala.');
            return false;
        }
        
        return true;
    });
});
JS;

$this->registerJs($js);
?>

>>>>>>> origin/filipe
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
<<<<<<< HEAD
=======
    .alert-info {
        background-color: #d1ecf1;
        border-color: #bee5eb;
        color: #0c5460;
    }
    #descricao-textarea {
        font-family: 'Courier New', monospace;
        font-size: 14px;
        line-height: 1.4;
    }
    .field {
        transition: all 0.3s ease;
    }
>>>>>>> origin/filipe
</style>