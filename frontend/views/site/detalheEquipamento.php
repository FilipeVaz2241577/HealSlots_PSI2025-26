<?php

/** @var yii\web\View $this */
/** @var string $equipamento */
/** @var string $categoria */

use yii\bootstrap5\Html;

// Mapear categorias para títulos
$titulosCategorias = [
    'monitorizacao' => 'Equipamentos de Monitorização',
    'moveis' => 'Equipamentos Móveis',
    'cirurgicos' => 'Instrumentos Cirúrgicos',
    'consumo' => 'Materiais de Consumo'
];

$tituloCategoria = $titulosCategorias[$categoria] ?? 'Equipamentos';
$this->title = "Detalhes - $equipamento";
?>

<div class="site-detalhe-equipamento">
    <div class="container">
        <div class="card shadow">
            <div class="card-body p-5">
                <!-- Cabeçalho -->
                <div class="d-flex justify-content-between align-items-center mb-5">
                    <div>
                        <h1 class="display-5 text-primary mb-2"><?= Html::encode($this->title) ?></h1>
                        <p class="lead text-muted">Informações detalhadas do equipamento</p>
                    </div>
                    <div>
                        <?= Html::a('Voltar aos Equipamentos', ['site/equipamentos', 'categoria' => $categoria], ['class' => 'btn btn-outline-secondary']) ?>
                    </div>
                </div>

                <div class="row">
                    <!-- Informações Principais -->
                    <div class="col-md-8">
                        <!-- Caixa com Texto sobre o Equipamento -->
                        <div class="card mb-4">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">Descrição do Equipamento</h5>
                            </div>
                            <div class="card-body">
                                <?php if ($equipamento === 'Monitor de Sinais Vitais DX-1000'): ?>
                                    <p class="mb-3">O <strong>Monitor de Sinais Vitais DX-1000</strong> é um equipamento de monitorização multiparamétrico de última geração, projetado para fornecer medições precisas e contínuas dos sinais vitais dos pacientes em ambientes hospitalares.</p>

                                    <h6 class="text-muted mb-3">Características Principais:</h6>
                                    <ul class="mb-3">
                                        <li>Monitorização contínua de ECG, SpO2, pressão arterial não invasiva</li>
                                        <li>Tela colorida de 12.1 polegadas com interface touchscreen</li>
                                        <li>Bateria com autonomia de 8 horas</li>
                                        <li>Armazenamento de dados para até 72 horas</li>
                                        <li>Conectividade Wi-Fi e Ethernet</li>
                                        <li>Alarmes visuais e sonoros configuráveis</li>
                                    </ul>

                                    <h6 class="text-muted mb-3">Especificações Técnicas:</h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p><strong>Marca:</strong> MedTech Solutions</p>
                                            <p><strong>Modelo:</strong> DX-1000</p>
                                            <p><strong>Peso:</strong> 4.2 kg</p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Dimensões:</strong> 32 x 28 x 12 cm</p>
                                            <p><strong>Alimentação:</strong> 100-240V AC / Bateria</p>
                                            <p><strong>Garantia:</strong> 36 meses</p>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <p class="text-muted mb-0">Descrição detalhada do equipamento <?= Html::encode($equipamento) ?>.</p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Localizações e Estado -->
                        <div class="card">
                            <div class="card-header bg-light">
                                <h5 class="mb-0">Distribuição e Localização</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                        <tr>
                                            <th>Localização</th>
                                            <th>Estado</th>
                                            <th>Número de Série</th>
                                            <th>Última Manutenção</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr>
                                            <td>Bloco A - Sala 101</td>
                                            <td><span class="badge bg-success">Disponível</span></td>
                                            <td>SN-MON-2024-001</td>
                                            <td>15/01/2024</td>
                                        </tr>
                                        <tr>
                                            <td>Bloco B - Urgências</td>
                                            <td><span class="badge bg-warning">Em Uso</span></td>
                                            <td>SN-MON-2024-005</td>
                                            <td>10/02/2024</td>
                                        </tr>
                                        <tr>
                                            <td>Bloco C - UCI</td>
                                            <td><span class="badge bg-warning">Em Uso</span></td>
                                            <td>SN-MON-2024-008</td>
                                            <td>05/02/2024</td>
                                        </tr>
                                        <tr>
                                            <td>Departamento Manutenção</td>
                                            <td><span class="badge bg-danger">Em Manutenção</span></td>
                                            <td>SN-MON-2024-012</td>
                                            <td>20/02/2024</td>
                                        </tr>
                                        <tr>
                                            <td>Bloco A - Sala 102</td>
                                            <td><span class="badge bg-success">Disponível</span></td>
                                            <td>SN-MON-2024-003</td>
                                            <td>18/01/2024</td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar com Estatísticas -->
                    <div class="col-md-4">
                        <!-- Estatísticas de Quantidade -->
                        <div class="card mb-4">
                            <div class="card-header bg-info text-white">
                                <h5 class="mb-0">Estatísticas do Equipamento</h5>
                            </div>
                            <div class="card-body">
                                <div class="text-center mb-4">
                                    <h2 class="text-primary">15</h2>
                                    <p class="text-muted mb-0">Total de Unidades</p>
                                </div>

                                <div class="row text-center">
                                    <div class="col-6 mb-3">
                                        <div class="border rounded p-3">
                                            <h4 class="text-success">8</h4>
                                            <small class="text-muted">Disponíveis</small>
                                        </div>
                                    </div>
                                    <div class="col-6 mb-3">
                                        <div class="border rounded p-3">
                                            <h4 class="text-warning">5</h4>
                                            <small class="text-muted">Em Uso</small>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="border rounded p-3">
                                            <h4 class="text-danger">2</h4>
                                            <small class="text-muted">Manutenção</small>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="border rounded p-3">
                                            <h4 class="text-secondary">0</h4>
                                            <small class="text-muted">Reservados</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Gráfico de Distribuição -->
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h5 class="mb-0">Distribuição por Estado</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="statusChart" width="100%" height="200"></canvas>
                            </div>
                        </div>

                        <!-- Ações Rápidas -->
                        <div class="card">
                            <div class="card-header bg-light">
                                <h5 class="mb-0">Ações Rápidas</h5>
                            </div>
                            <div class="card-body">
                                <?= Html::a('Reservar Equipamento', ['#'], ['class' => 'btn btn-success w-100 mb-2']) ?>
                                <?= Html::a('Solicitar Manutenção', ['#'], ['class' => 'btn btn-warning w-100 mb-2']) ?>
                                <?= Html::a('Reportar Problema', ['#'], ['class' => 'btn btn-danger w-100 mb-2']) ?>
                                <?= Html::a('Histórico Completo', ['#'], ['class' => 'btn btn-outline-primary w-100']) ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Informações Adicionais -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header bg-light">
                                <h5 class="mb-0">Informações Adicionais</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <h6 class="text-muted">Fornecedor</h6>
                                        <p>MedTech Solutions, Lda.</p>
                                        <p><small class="text-muted">Contacto: +351 123 456 789</small></p>
                                    </div>
                                    <div class="col-md-4">
                                        <h6 class="text-muted">Próxima Manutenção Preventiva</h6>
                                        <p>15/03/2024</p>
                                        <p><small class="text-muted">Faltam 23 dias</small></p>
                                    </div>
                                    <div class="col-md-4">
                                        <h6 class="text-muted">Documentação</h6>
                                        <?= Html::a('Manual de Utilização', ['#'], ['class' => 'd-block text-primary mb-1']) ?>
                                        <?= Html::a('Ficha Técnica', ['#'], ['class' => 'd-block text-primary mb-1']) ?>
                                        <?= Html::a('Certificado de Garantia', ['#'], ['class' => 'd-block text-primary']) ?>
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
    .card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .card-header {
        border-radius: 12px 12px 0 0 !important;
        font-weight: 600;
    }

    .table th {
        border-top: none;
        font-weight: 600;
        color: #495057;
        background-color: #f8f9fa;
    }

    .badge {
        font-size: 0.75rem;
        padding: 6px 10px;
        border-radius: 20px;
    }

    .border.rounded {
        transition: all 0.3s ease;
    }

    .border.rounded:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .text-success { color: #28a745 !important; }
    .text-warning { color: #ffc107 !important; }
    .text-danger { color: #dc3545 !important; }
    .text-info { color: #17a2b8 !important; }
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Gráfico de distribuição por estado
        const ctx = document.getElementById('statusChart').getContext('2d');
        const statusChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Disponíveis', 'Em Uso', 'Em Manutenção'],
                datasets: [{
                    data: [8, 5, 2],
                    backgroundColor: [
                        '#28a745',
                        '#ffc107',
                        '#dc3545'
                    ],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true
                        }
                    }
                },
                cutout: '60%'
            }
        });
    });
</script>