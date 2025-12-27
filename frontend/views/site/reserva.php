<?php

/** @var yii\web\View $this */
/** @var common\models\Sala $sala */
/** @var common\models\Equipamento[] $equipamentosDisponiveis */
/** @var common\models\Equipamento[] $equipamentosSala */

use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;
use yii\helpers\ArrayHelper;

$this->title = 'Reservar Sala: ' . $sala->nome;
$this->params['breadcrumbs'][] = ['label' => 'Blocos', 'url' => ['site/blocos']];
$this->params['breadcrumbs'][] = ['label' => $sala->bloco->nome, 'url' => ['site/salas', 'bloco' => $sala->bloco_id]];
$this->params['breadcrumbs'][] = ['label' => $sala->nome, 'url' => ['site/detalhe-sala', 'id' => $sala->id]];
$this->params['breadcrumbs'][] = 'Reservar';

// Mapear cores para estados - baseado no modelo Sala
$coresEstadoSala = [
        'Livre' => 'success',
        'EmUso' => 'primary',        // ← USAR APENAS 'EmUso'
        'Manutencao' => 'warning',
        'Inativa' => 'secondary'
];

// Usar o método do modelo para obter o label correto
$estadoTextoSala = $sala->getEstadoLabel();
$corBadgeSala = isset($coresEstadoSala[$sala->estado]) ? $coresEstadoSala[$sala->estado] : 'secondary';
?>

    <div class="site-reservar">
        <div class="container">
            <div class="card shadow">
                <div class="card-body p-4">
                    <!-- Cabeçalho -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h1 class="display-6 text-primary mb-2">
                                <i class="fas fa-calendar-check me-2"></i>
                                Reservar Sala
                            </h1>
                            <p class="lead text-muted mb-0">
                                Sala: <strong><?= Html::encode($sala->nome) ?></strong> |
                                Bloco: <strong><?= Html::encode($sala->bloco->nome) ?></strong> |
                                Estado: <span class="badge bg-<?= $corBadgeSala ?>"><?= $estadoTextoSala ?></span>
                            </p>
                        </div>
                        <div>
                            <?= Html::a('<i class="fas fa-arrow-left me-2"></i>Voltar',
                                    ['site/detalhe-sala', 'id' => $sala->id],
                                    ['class' => 'btn btn-outline-secondary']) ?>
                        </div>
                    </div>

                    <?php if (Yii::$app->session->hasFlash('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <?= Yii::$app->session->getFlash('error') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Adicione esta seção após o cabeçalho -->
                    <div class="alert alert-info mb-4">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Importante:</strong> O sistema verificará automaticamente conflitos de horários para a sala e equipamentos selecionados.
                        <?php if ($sala->estado === \common\models\Sala::ESTADO_EM_USO): ?>
                            <div class="mt-2">
                                <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                                <strong>Atenção:</strong> Esta sala está atualmente marcada como <?= $estadoTextoSala ?>. Pode ainda ser possível fazer uma nova reserva se não houver conflitos de horário.
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="row">
                        <!-- Formulário de Reserva -->
                        <div class="col-lg-8">
                            <div class="card mb-4">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Formulário de Reserva</h5>
                                </div>
                                <div class="card-body">
                                    <?php $form = ActiveForm::begin([
                                            'id' => 'reserva-form',
                                            'options' => ['class' => 'needs-validation'],
                                    ]); ?>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Sala</label>
                                                <input type="text" class="form-control" value="<?= Html::encode($sala->nome) ?>" readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Bloco</label>
                                                <input type="text" class="form-control" value="<?= Html::encode($sala->bloco->nome) ?>" readonly>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="data_reserva" class="form-label fw-bold">Data da Reserva *</label>
                                                <input type="date"
                                                       class="form-control"
                                                       id="data_reserva"
                                                       name="data_reserva"
                                                       required
                                                       min="<?= date('Y-m-d') ?>"
                                                       value="<?= date('Y-m-d') ?>">
                                                <div class="invalid-feedback">
                                                    Por favor, selecione uma data.
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="hora_inicio" class="form-label fw-bold">Hora de Início *</label>
                                                <input type="time"
                                                       class="form-control"
                                                       id="hora_inicio"
                                                       name="hora_inicio"
                                                       required
                                                       value="09:00">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="hora_fim" class="form-label fw-bold">Hora de Fim *</label>
                                                <input type="time"
                                                       class="form-control"
                                                       id="hora_fim"
                                                       name="hora_fim"
                                                       required
                                                       value="10:00">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="observacoes" class="form-label fw-bold">Observações</label>
                                        <textarea class="form-control"
                                                  id="observacoes"
                                                  name="observacoes"
                                                  rows="3"
                                                  placeholder="Adicione observações sobre a reserva..."></textarea>
                                    </div>

                                    <!-- Seção de Equipamentos -->
                                    <div class="mb-4">
                                        <label class="form-label fw-bold d-flex justify-content-between align-items-center">
                                            <span>Selecione Equipamentos</span>
                                            <small class="text-muted">(Opcional)</small>
                                        </label>

                                        <?php if (!empty($equipamentosDisponiveis)): ?>
                                            <div class="equipment-selection">
                                                <div class="d-flex justify-content-between align-items-center mb-3">
                                                    <small class="text-muted">
                                                        <i class="fas fa-info-circle me-1"></i>
                                                        Selecione os equipamentos que deseja incluir na reserva
                                                    </small>
                                                    <button type="button" class="btn btn-sm btn-outline-secondary" id="check-all-equip">
                                                        <i class="fas fa-check-square me-2"></i>Selecionar Todos
                                                    </button>
                                                </div>
                                                <div class="row">
                                                    <?php foreach ($equipamentosDisponiveis as $equipamento): ?>
                                                        <div class="col-md-6 mb-3">
                                                            <div class="form-check equipment-card p-3 border rounded">
                                                                <input class="form-check-input"
                                                                       type="checkbox"
                                                                       name="equipamentos[]"
                                                                       value="<?= $equipamento->id ?>"
                                                                       id="equip_<?= $equipamento->id ?>">
                                                                <label class="form-check-label w-100" for="equip_<?= $equipamento->id ?>">
                                                                    <div class="d-flex justify-content-between align-items-center">
                                                                        <div>
                                                                            <strong><?= Html::encode($equipamento->equipamento) ?></strong>
                                                                            <div class="text-muted small">
                                                                                <i class="fas fa-tag me-1"></i>
                                                                                <?= Html::encode($equipamento->tipoEquipamento->nome ?? 'N/A') ?>
                                                                            </div>
                                                                        </div>
                                                                        <div class="text-end">
                                                                            <span class="badge bg-success">Operacional</span>
                                                                            <div class="text-muted small">
                                                                                <i class="fas fa-barcode me-1"></i>
                                                                                <?= Html::encode($equipamento->numeroSerie) ?>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </label>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <div class="alert alert-info">
                                                <i class="fas fa-info-circle me-2"></i>
                                                Não há equipamentos disponíveis para seleção.
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="d-grid gap-2">
                                        <?= Html::submitButton('<i class="fas fa-calendar-check me-2"></i> Confirmar Reserva',
                                                ['class' => 'btn btn-success btn-lg']) ?>

                                        <?= Html::a('<i class="fas fa-times me-2"></i> Cancelar',
                                                ['site/detalhe-sala', 'id' => $sala->id],
                                                ['class' => 'btn btn-outline-danger']) ?>
                                    </div>

                                    <?php ActiveForm::end(); ?>
                                </div>
                            </div>
                        </div>

                        <!-- Informações e Equipamentos da Sala -->
                        <div class="col-lg-4">
                            <!-- Resumo da Sala -->
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Resumo da Sala</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <h5 class="text-primary"><?= Html::encode($sala->nome) ?></h5>
                                        <p class="text-muted mb-2">
                                            <i class="fas fa-building me-2"></i>
                                            Bloco: <?= Html::encode($sala->bloco->nome) ?>
                                        </p>
                                        <p class="mb-2">
                                            <i class="fas fa-circle me-2 text-<?= $corBadgeSala ?>"></i>
                                            Estado: <?= $estadoTextoSala ?>
                                        </p>
                                        <p class="mb-0">
                                            <i class="fas fa-tools me-2"></i>
                                            Equipamentos na sala: <?= count($equipamentosSala) ?>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Equipamentos já na Sala -->
                            <?php if (!empty($equipamentosSala)): ?>
                                <div class="card">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0"><i class="fas fa-tools me-2"></i>Equipamentos na Sala</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="equipment-list">
                                            <?php foreach ($equipamentosSala as $equipamento): ?>
                                                <div class="equipment-item mb-2 p-2 border rounded position-relative">
                                                    <!-- BOTÃO SEM RBAC - APARECE PARA TODOS -->
                                                    <div class="position-absolute top-0 end-0 p-1">
                                                        <?= Html::a(
                                                                '<i class="fas fa-trash text-danger"></i>',
                                                                ['site/remove-equipamento', 'sala_id' => $sala->id, 'equipamento_id' => $equipamento->id],
                                                                [
                                                                        'class' => 'btn btn-sm btn-outline-danger border-0',
                                                                        'title' => 'Remover este equipamento',
                                                                        'data' => [
                                                                                'confirm' => 'Tem certeza que deseja remover este equipamento da sala?',
                                                                                'method' => 'post',
                                                                        ],
                                                                        'style' => 'padding: 2px 6px;'
                                                                ]
                                                        ) ?>
                                                    </div>

                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <strong class="small"><?= Html::encode($equipamento->equipamento) ?></strong>
                                                            <div class="text-muted" style="font-size: 0.8rem;">
                                                                <?= Html::encode($equipamento->numeroSerie) ?>
                                                            </div>
                                                            <div class="text-muted small">
                                                                <i class="fas fa-tag me-1"></i>
                                                                <?= Html::encode($equipamento->tipoEquipamento->nome ?? 'N/A') ?>
                                                            </div>
                                                        </div>
                                                        <?php
                                                        $corEquipamento = 'secondary';
                                                        if ($equipamento->estado === 'Operacional') {
                                                            $corEquipamento = 'success';
                                                        } elseif ($equipamento->estado === 'Em Uso') {
                                                            $corEquipamento = 'primary';
                                                        } elseif ($equipamento->estado === 'Em Manutenção') {
                                                            $corEquipamento = 'warning';
                                                        }
                                                        ?>
                                                        <span class="badge bg-<?= $corEquipamento ?> small"><?= Html::encode($equipamento->estado) ?></span>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .equipment-card:hover {
            background-color: #f8f9fa;
            border-color: #007bff;
            cursor: pointer;
        }

        .equipment-card .form-check-input:checked + label {
            background-color: #e7f3ff;
        }

        .equipment-item {
            transition: all 0.3s ease;
        }

        .equipment-item:hover {
            background-color: #f8f9fa;
        }

        .invalid-feedback {
            display: none;
        }

        .was-validated .form-control:invalid ~ .invalid-feedback {
            display: block;
        }

        .btn-lg {
            padding: 12px 24px;
            font-size: 1.1rem;
        }

        .form-check-label {
            cursor: pointer;
        }

        .form-check-input {
            margin-top: 0.3rem;
        }
    </style>

<?php
$js = <<<JS
// Validação do formulário
(function() {
    'use strict';
    
    // Validar data e hora
    const dataReservaInput = document.getElementById('data_reserva');
    const horaInicioInput = document.getElementById('hora_inicio');
    const horaFimInput = document.getElementById('hora_fim');
    
    // Definir data mínima como hoje
    const today = new Date().toISOString().split('T')[0];
    dataReservaInput.min = today;
    
    // Se a data selecionada for hoje, hora mínima deve ser hora atual
    dataReservaInput.addEventListener('change', function() {
        if (this.value === today) {
            const now = new Date();
            const currentHour = now.getHours().toString().padStart(2, '0');
            const currentMinute = now.getMinutes().toString().padStart(2, '0');
            horaInicioInput.min = currentHour + ':' + currentMinute;
            
            // Se a hora atual for maior que a hora selecionada, atualizar
            if (horaInicioInput.value < horaInicioInput.min) {
                horaInicioInput.value = horaInicioInput.min;
            }
        } else {
            horaInicioInput.min = '00:00';
        }
    });
    
    // Validar que hora de fim é maior que hora de início
    horaInicioInput.addEventListener('change', function() {
        if (horaFimInput.value && this.value >= horaFimInput.value) {
            horaFimInput.setCustomValidity('Hora de fim deve ser posterior à hora de início');
            horaFimInput.value = '';
        } else {
            horaFimInput.setCustomValidity('');
        }
    });
    
    horaFimInput.addEventListener('change', function() {
        if (horaInicioInput.value && this.value <= horaInicioInput.value) {
            this.setCustomValidity('Hora de fim deve ser posterior à hora de início');
        } else {
            this.setCustomValidity('');
        }
    });
    
    // Form submission
    const form = document.getElementById('reserva-form');
    form.addEventListener('submit', function(event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        
        form.classList.add('was-validated');
        
        // Verificar se pelo menos uma data e hora foram selecionadas
        if (!dataReservaInput.value || !horaInicioInput.value || !horaFimInput.value) {
            event.preventDefault();
            alert('Por favor, preencha todos os campos obrigatórios.');
            return false;
        }
        
        // Verificar se hora de fim é maior que hora de início
        if (horaInicioInput.value >= horaFimInput.value) {
            event.preventDefault();
            alert('A hora de fim deve ser posterior à hora de início.');
            return false;
        }
        
        return true;
    });
    
    // Marcar/desmarcar todos os equipamentos
    const checkAllBtn = document.getElementById('check-all-equip');
    if (checkAllBtn) {
        checkAllBtn.addEventListener('click', function() {
            const checkboxes = document.querySelectorAll('input[name="equipamentos[]"]');
            const allChecked = Array.from(checkboxes).every(cb => cb.checked);
            
            checkboxes.forEach(cb => {
                cb.checked = !allChecked;
                cb.dispatchEvent(new Event('change'));
            });
            
            this.innerHTML = allChecked ? 
                '<i class="fas fa-check-square me-2"></i>Selecionar Todos' :
                '<i class="fas fa-square me-2"></i>Desselecionar Todos';
        });
    }
    
    // Verificar se data é hoje para definir hora mínima
    if (dataReservaInput.value === today) {
        const now = new Date();
        const currentHour = now.getHours().toString().padStart(2, '0');
        const currentMinute = now.getMinutes().toString().padStart(2, '0');
        horaInicioInput.min = currentHour + ':' + currentMinute;
        
        if (horaInicioInput.value < horaInicioInput.min) {
            horaInicioInput.value = horaInicioInput.min;
        }
    }
})();

// Alternar visualização dos equipamentos
function toggleEquipmentView() {
    const gridView = document.getElementById('equipment-grid');
    const listView = document.getElementById('equipment-list');
    const toggleBtn = document.getElementById('toggle-view-btn');
    
    if (gridView.style.display === 'none') {
        gridView.style.display = 'block';
        listView.style.display = 'none';
        toggleBtn.innerHTML = '<i class="fas fa-list me-2"></i>Ver em Lista';
    } else {
        gridView.style.display = 'none';
        listView.style.display = 'block';
        toggleBtn.innerHTML = '<i class="fas fa-th-large me-2"></i>Ver em Grade';
    }
}
JS;

$this->registerJs($js);