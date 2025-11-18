<?php

/** @var yii\web\View $this */
/** @var string $sala */

use yii\bootstrap5\Html;

$this->title = "Detalhes da Sala $sala - Bloco A";
?>

<div class="site-detalhe-sala">
    <div class="container">
        <div class="card shadow">
            <div class="card-body p-5">
                <!-- Cabeçalho -->
                <div class="d-flex justify-content-between align-items-center mb-5">
                    <div>
                        <h1 class="display-5 text-primary mb-2"><?= Html::encode($this->title) ?></h1>
                        <p class="lead text-muted">Informações detalhadas da sala</p>
                    </div>
                    <div>
                        <?= Html::a('Voltar às Salas', ['site/salas'], ['class' => 'btn btn-outline-secondary']) ?>
                    </div>
                </div>

                <!-- Detalhes da Sala -->
                <div class="row">
                    <!-- Informações Principais -->
                    <div class="col-md-8">
                        <div class="room-info-card mb-4">
                            <div class="card">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0">Informações da Sala</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p><strong>Nome:</strong> <?= Html::encode($sala) ?></p>
                                            <p><strong>Bloco:</strong> Bloco A</p>
                                            <p><strong>Capacidade:</strong> 30 pessoas</p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Estado:</strong>
                                                <span class="text-success"><strong>Disponível</strong></span>
                                            </p>
                                            <p><strong>Tipo:</strong> Sala de Reunião</p>
                                            <p><strong>Área:</strong> 45 m²</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Equipamentos Disponíveis -->
                        <div class="equipment-card mb-4">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">Equipamentos da Sala</h5>
                                </div>
                                <div class="card-body">
                                    <div class="equipment-list" id="equipmentList">
                                        <div class="list-group">
                                            <div class="list-group-item equipment-item">
                                                <div class="d-flex w-100 justify-content-between align-items-center">
                                                    <div class="flex-grow-1">
                                                        <h6 class="mb-1">Projetor Epson EB-X41</h6>
                                                        <p class="mb-1 text-muted">Projetor LCD XGA 3.300 lumens</p>
                                                        <small class="text-muted">Nº Série: PRJ2024001</small>
                                                    </div>
                                                    <div class="ms-3">
                                                        <span class="badge bg-success me-2">Disponível</span>
                                                        <button class="btn btn-primary btn-sm add-to-list" data-equipment='{"name":"Projetor Epson EB-X41","serial":"PRJ2024001","status":"Disponível"}'>
                                                            Adicionar à Lista
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="list-group-item equipment-item">
                                                <div class="d-flex w-100 justify-content-between align-items-center">
                                                    <div class="flex-grow-1">
                                                        <h6 class="mb-1">Sistema de Som JBL</h6>
                                                        <p class="mb-1 text-muted">Sistema de áudio 2.1 com 500W</p>
                                                        <small class="text-muted">Nº Série: AUD2024002</small>
                                                    </div>
                                                    <div class="ms-3">
                                                        <span class="badge bg-success me-2">Disponível</span>
                                                        <button class="btn btn-primary btn-sm add-to-list" data-equipment='{"name":"Sistema de Som JBL","serial":"AUD2024002","status":"Disponível"}'>
                                                            Adicionar à Lista
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="list-group-item equipment-item">
                                                <div class="d-flex w-100 justify-content-between align-items-center">
                                                    <div class="flex-grow-1">
                                                        <h6 class="mb-1">Ar Condicionado Daikin</h6>
                                                        <p class="mb-1 text-muted">Split 12.000 BTU</p>
                                                        <small class="text-muted">Nº Série: AC2024003</small>
                                                    </div>
                                                    <div class="ms-3">
                                                        <span class="badge bg-warning me-2">Em Manutenção</span>
                                                        <button class="btn btn-primary btn-sm add-to-list" data-equipment='{"name":"Ar Condicionado Daikin","serial":"AC2024003","status":"Em Manutenção"}'>
                                                            Adicionar à Lista
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="list-group-item equipment-item">
                                                <div class="d-flex w-100 justify-content-between align-items-center">
                                                    <div class="flex-grow-1">
                                                        <h6 class="mb-1">Quadro Interativo Smart</h6>
                                                        <p class="mb-1 text-muted">Quadro 86" touch screen</p>
                                                        <small class="text-muted">Nº Série: QI2024004</small>
                                                    </div>
                                                    <div class="ms-3">
                                                        <span class="badge bg-success me-2">Disponível</span>
                                                        <button class="btn btn-primary btn-sm add-to-list" data-equipment='{"name":"Quadro Interativo Smart","serial":"QI2024004","status":"Disponível"}'>
                                                            Adicionar à Lista
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Lista de Equipamentos Selecionados -->
                        <div class="selected-equipment-card mb-4">
                            <div class="card">
                                <div class="card-header bg-info text-white">
                                    <h5 class="mb-0">Lista de Equipamentos Selecionados</h5>
                                </div>
                                <div class="card-body">
                                    <div id="selectedEquipmentList" class="selected-list">
                                        <p class="text-muted mb-0">Nenhum equipamento selecionado</p>
                                    </div>
                                    <div class="mt-3 d-none" id="listActions">
                                        <?= Html::a('Reservar Equipamentos', ['#'], ['class' => 'btn btn-success me-2']) ?>
                                        <?= Html::a('Limpar Lista', ['#'], ['class' => 'btn btn-outline-danger', 'id' => 'clearList']) ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar com Ações Rápidas -->
                    <div class="col-md-4">
                        <div class="action-sidebar">
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">Ações Rápidas</h6>
                                </div>
                                <div class="card-body">
                                    <?= Html::a('Reservar Sala', ['#'], ['class' => 'btn btn-success w-100 mb-2']) ?>
                                    <?= Html::a('Reportar Problema', ['#'], ['class' => 'btn btn-warning w-100 mb-2']) ?>
                                    <?= Html::a('Solicitar Manutenção', ['#'], ['class' => 'btn btn-danger w-100']) ?>
                                </div>
                            </div>

                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">Estatísticas</h6>
                                </div>
                                <div class="card-body">
                                    <div class="text-center mb-3">
                                        <h4 class="text-primary">85%</h4>
                                        <small class="text-muted">Taxa de Ocupação</small>
                                    </div>
                                    <div class="text-center mb-3">
                                        <h4 class="text-success">12</h4>
                                        <small class="text-muted">Reservas este Mês</small>
                                    </div>
                                    <div class="text-center">
                                        <h4 class="text-warning">2</h4>
                                        <small class="text-muted">Manutenções Pendentes</small>
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

<style>
    .room-info-card .card-header {
        background: linear-gradient(135deg, #007bff, #0056b3);
    }

    .equipment-item {
        border: none;
        border-left: 4px solid #007bff;
        margin-bottom: 8px;
        border-radius: 4px;
        transition: all 0.3s ease;
    }

    .equipment-item:hover {
        background-color: #f8f9fa;
        border-left-color: #0056b3;
    }

    .selected-equipment-card .card-header {
        background: linear-gradient(135deg, #17a2b8, #138496);
    }

    .selected-equipment-item {
        border: none;
        border-left: 4px solid #17a2b8;
        margin-bottom: 8px;
        border-radius: 4px;
        background-color: #f8f9fa;
    }

    .action-sidebar .btn {
        border-radius: 6px;
        padding: 10px;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .action-sidebar .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    }

    .add-to-list {
        transition: all 0.3s ease;
    }

    .add-to-list:hover {
        transform: translateY(-1px);
    }

    .remove-from-list {
        transition: all 0.3s ease;
    }

    .remove-from-list:hover {
        transform: scale(1.1);
    }

    .text-success { color: #28a745 !important; }
    .text-warning { color: #ffc107 !important; }
    .text-danger { color: #dc3545 !important; }
    .text-info { color: #17a2b8 !important; }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectedEquipmentList = document.getElementById('selectedEquipmentList');
        const listActions = document.getElementById('listActions');
        const clearListBtn = document.getElementById('clearList');
        let selectedEquipment = [];

        // Função para adicionar equipamento à lista
        function addEquipmentToSelected(equipmentData) {
            // Verificar se o equipamento já está na lista
            if (selectedEquipment.some(eq => eq.serial === equipmentData.serial)) {
                alert('Este equipamento já está na lista!');
                return;
            }

            // Adicionar à lista
            selectedEquipment.push(equipmentData);
            updateSelectedEquipmentList();
        }

        // Função para remover equipamento da lista
        function removeEquipmentFromSelected(serial) {
            selectedEquipment = selectedEquipment.filter(eq => eq.serial !== serial);
            updateSelectedEquipmentList();
        }

        // Função para atualizar a lista visual
        function updateSelectedEquipmentList() {
            if (selectedEquipment.length === 0) {
                selectedEquipmentList.innerHTML = '<p class="text-muted mb-0">Nenhum equipamento selecionado</p>';
                listActions.classList.add('d-none');
            } else {
                selectedEquipmentList.innerHTML = selectedEquipment.map(equipment => `
                    <div class="selected-equipment-item p-3">
                        <div class="d-flex w-100 justify-content-between align-items-center">
                            <div class="flex-grow-1">
                                <h6 class="mb-1">${equipment.name}</h6>
                                <p class="mb-1 text-muted">Nº Série: ${equipment.serial}</p>
                                <span class="badge ${equipment.status === 'Disponível' ? 'bg-success' : 'bg-warning'}">${equipment.status}</span>
                            </div>
                            <button class="btn btn-outline-danger btn-sm remove-from-list ms-3" data-serial="${equipment.serial}">
                                Remover
                            </button>
                        </div>
                    </div>
                `).join('');
                listActions.classList.remove('d-none');
            }
        }

        // Event listeners para os botões "Adicionar à Lista"
        document.querySelectorAll('.add-to-list').forEach(button => {
            button.addEventListener('click', function() {
                const equipmentData = JSON.parse(this.getAttribute('data-equipment'));
                addEquipmentToSelected(equipmentData);
            });
        });

        // Event listener para remover equipamentos (usando event delegation)
        selectedEquipmentList.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-from-list')) {
                const serial = e.target.getAttribute('data-serial');
                removeEquipmentFromSelected(serial);
            }
        });

        // Event listener para limpar a lista
        clearListBtn.addEventListener('click', function(e) {
            e.preventDefault();
            if (confirm('Tem a certeza que deseja limpar a lista de equipamentos?')) {
                selectedEquipment = [];
                updateSelectedEquipmentList();
            }
        });

        // Event listener para mostrar detalhes do equipamento ao clicar no item
        document.querySelectorAll('.equipment-item').forEach(item => {
            item.addEventListener('click', function(e) {
                // Só mostra detalhes se não clicar no botão
                if (!e.target.classList.contains('add-to-list') && !e.target.closest('.add-to-list')) {
                    const equipmentName = this.querySelector('h6').textContent;
                    const status = this.querySelector('.badge').textContent;
                    const serial = this.querySelector('small').textContent.replace('Nº Série: ', '');

                    alert(`Equipamento: ${equipmentName}\nNº Série: ${serial}\nStatus: ${status}`);
                }
            });
        });
    });
</script>