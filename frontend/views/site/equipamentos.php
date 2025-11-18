<?php

/** @var yii\web\View $this */
/** @var string $categoria */

use yii\bootstrap5\Html;

// Mapear categorias para títulos
$titulosCategorias = [
        'monitorizacao' => 'Equipamentos de Monitorização',
        'moveis' => 'Equipamentos Móveis',
        'cirurgicos' => 'Instrumentos Cirúrgicos',
        'consumo' => 'Materiais de Consumo'
];

$titulo = $titulosCategorias[$categoria] ?? 'Equipamentos';
$this->title = $titulo;
?>

<div class="site-equipamentos">
    <div class="container">
        <div class="card shadow">
            <div class="card-body p-5">
                <!-- Cabeçalho -->
                <div class="d-flex justify-content-between align-items-center mb-5">
                    <div>
                        <h1 class="display-5 text-primary mb-2"><?= Html::encode($this->title) ?></h1>
                        <p class="lead text-muted">Lista detalhada de todos os equipamentos</p>
                    </div>
                    <div>
                        <?= Html::a('Voltar às Categorias', ['site/tiposequipamento'], ['class' => 'btn btn-outline-secondary']) ?>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="input-group">
                            <input type="text" class="form-control" placeholder="Pesquisar equipamentos...">
                            <button class="btn btn-outline-primary" type="button">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <select class="form-select">
                            <option value="">Todos os estados</option>
                            <option value="disponivel">Disponível</option>
                            <option value="ocupado">Ocupado</option>
                            <option value="manutencao">Em Manutenção</option>
                            <option value="defeito">Com Defeito</option>
                        </select>
                    </div>
                </div>

                <!-- Tabela de Equipamentos -->
                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead class="table-primary">
                        <tr>
                            <th>Nome do Equipamento</th>
                            <th>Estado</th>
                            <th>Número de Série</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if ($categoria === 'monitorizacao'): ?>
                            <!-- Equipamentos de Monitorização -->
                            <tr>
                                <td>Monitor de Sinais Vitais DX-1000</td>
                                <td><span class="badge bg-success">Disponível</span></td>
                                <td>SN-MON-2024-001</td>
                            </tr>
                            <tr>
                                <td>Electrocardíógrafo ECG-200</td>
                                <td><span class="badge bg-warning">Ocupado</span></td>
                                <td>SN-ECG-2024-002</td>
                            </tr>
                            <tr>
                                <td>Oxímetro de Pulso OP-50</td>
                                <td><span class="badge bg-danger">Em Manutenção</span></td>
                                <td>SN-OXI-2024-003</td>
                            </tr>
                            <tr>
                                <td>Monitor de Pressão Arterial PA-300</td>
                                <td><span class="badge bg-success">Disponível</span></td>
                                <td>SN-PA-2024-004</td>
                            </tr>

                        <?php elseif ($categoria === 'moveis'): ?>
                            <!-- Equipamentos Móveis -->
                            <tr>
                                <td>Cama Hospitalar Elétrica CH-500</td>
                                <td><span class="badge bg-success">Disponível</span></td>
                                <td>SN-CAM-2024-001</td>
                            </tr>
                            <tr>
                                <td>Maca de Emergência ME-100</td>
                                <td><span class="badge bg-warning">Ocupado</span></td>
                                <td>SN-MAC-2024-002</td>
                            </tr>
                            <tr>
                                <td>Carro de Anestesia CA-200</td>
                                <td><span class="badge bg-success">Disponível</span></td>
                                <td>SN-CAR-2024-003</td>
                            </tr>

                        <?php elseif ($categoria === 'cirurgicos'): ?>
                            <!-- Instrumentos Cirúrgicos -->
                            <tr>
                                <td>Kit Cirúrgico Básico KCB-100</td>
                                <td><span class="badge bg-success">Disponível</span></td>
                                <td>SN-KIT-2024-001</td>
                            </tr>
                            <tr>
                                <td>Autoclave AC-3000</td>
                                <td><span class="badge bg-danger">Em Manutenção</span></td>
                                <td>SN-AUT-2024-002</td>
                            </tr>
                            <tr>
                                <td>Bisturi Elétrico BE-200</td>
                                <td><span class="badge bg-success">Disponível</span></td>
                                <td>SN-BIS-2024-003</td>
                            </tr>

                        <?php elseif ($categoria === 'consumo'): ?>
                            <!-- Materiais de Consumo -->
                            <tr>
                                <td>Luvas Cirúrgicas Estéreis</td>
                                <td><span class="badge bg-success">Disponível</span></td>
                                <td>LOTE-LUV-2024-001</td>
                            </tr>
                            <tr>
                                <td>Seringas 10ml</td>
                                <td><span class="badge bg-info">Stock Baixo</span></td>
                                <td>LOTE-SER-2024-002</td>
                            </tr>
                            <tr>
                                <td>Agulhas 21G</td>
                                <td><span class="badge bg-success">Disponível</span></td>
                                <td>LOTE-AGU-2024-003</td>
                            </tr>

                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="text-center text-muted py-4">
                                    <i class="fas fa-exclamation-circle fa-2x mb-3"></i>
                                    <br>
                                    Categoria não encontrada
                                </td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Estatísticas -->
                <div class="row mt-5">
                    <div class="col-md-3">
                        <div class="stat-card text-center p-3">
                            <h3 class="text-primary" id="totalEquipamentos">0</h3>
                            <p class="text-muted mb-0">Total</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card text-center p-3">
                            <h3 class="text-success" id="disponiveis">0</h3>
                            <p class="text-muted mb-0">Disponíveis</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card text-center p-3">
                            <h3 class="text-warning" id="ocupados">0</h3>
                            <p class="text-muted mb-0">Ocupados</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card text-center p-3">
                            <h3 class="text-danger" id="manutencao">0</h3>
                            <p class="text-muted mb-0">Manutenção</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .table th {
        border-top: none;
        font-weight: 600;
        color: #495057;
    }

    .stat-card {
        border: 1px solid #e9ecef;
        border-radius: 8px;
        background: white;
        transition: all 0.3s ease;
    }

    .stat-card:hover {
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        transform: translateY(-3px);
    }

    .badge {
        font-size: 0.75rem;
        padding: 6px 10px;
        border-radius: 20px;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Calcular estatísticas
        const total = document.querySelectorAll('tbody tr').length - 1; // -1 para excluir a linha de "não encontrado"
        const disponiveis = document.querySelectorAll('.badge.bg-success').length;
        const ocupados = document.querySelectorAll('.badge.bg-warning').length;
        const manutencao = document.querySelectorAll('.badge.bg-danger').length;

        document.getElementById('totalEquipamentos').textContent = total;
        document.getElementById('disponiveis').textContent = disponiveis;
        document.getElementById('ocupados').textContent = ocupados;
        document.getElementById('manutencao').textContent = manutencao;
    });
</script>

