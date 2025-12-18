<?php
/** @var yii\web\View $this */

use hail812\adminlte\widgets\Alert;
use hail812\adminlte\widgets\SmallBox;
use hail812\adminlte\widgets\InfoBox;
use yii\bootstrap5\Html;

// Adicionar Chart.js
$this->registerJsFile('https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js');

$this->title = 'Dashboard - HealSlots';
$this->params['breadcrumbs'] = [['label' => $this->title]];
?>

<div class="container-fluid">
    <!-- Alert de Boas-vindas -->
    <div class="row mb-4">
        <div class="col-12">
            <?= Alert::widget([
                'type' => 'info',
                'body' => '
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="fas fa-heartbeat fa-3x"></i>
                        </div>
                        <div>
                            <h3 class="mb-1">Bem-vindo ao HealSlots!</h3>
                            <p class="mb-0">Sistema de gestão hospitalar - Monitorização em tempo real</p>
                        </div>
                    </div>
                ',
            ]) ?>
        </div>
    </div>

    <!-- Estatísticas Principais -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <?= SmallBox::widget([
                'title' => $userCount ?? '0',
                'text' => 'Total Utilizadores',
                'icon' => 'fas fa-users',
                'theme' => 'info'
            ]) ?>
        </div>
        <div class="col-lg-3 col-6">
            <?= SmallBox::widget([
                'title' => $activeUsers ?? '0',
                'text' => 'Utilizadores Ativos',
                'icon' => 'fas fa-user-check',
                'theme' => 'success'
            ]) ?>
        </div>
        <div class="col-lg-3 col-6">
            <?= SmallBox::widget([
                'title' => $totalSalas ?? '0',
                'text' => 'Salas Operatórias',
                'icon' => 'fas fa-door-open',
                'theme' => 'primary'
            ]) ?>
        </div>
        <div class="col-lg-3 col-6">
            <?= SmallBox::widget([
                'title' => $requisicoesAtivas ?? '0',
                'text' => 'Requisições Ativas',
                'icon' => 'fas fa-calendar-check',
                'theme' => 'warning'
            ]) ?>
        </div>
    </div>

    <!-- Segunda Linha de Estatísticas -->
    <div class="row mb-4">
        <div class="col-lg-3 col-6">
            <?= InfoBox::widget([
                'text' => 'Administradores',
                'number' => $adminCount ?? '0',
                'icon' => 'fas fa-user-shield',
                'theme' => 'danger',
                'progress' => [
                    'width' => '70%',
                    'description' => '70% do total'
                ]
            ]) ?>
        </div>
        <div class="col-lg-3 col-6">
            <?= InfoBox::widget([
                'text' => 'Logins Hoje',
                'number' => $todayLogins ?? '0',
                'icon' => 'fas fa-sign-in-alt',
                'theme' => 'info',
                'progress' => [
                    'width' => '40%',
                    'description' => '40% do total'
                ]
            ]) ?>
        </div>
        <div class="col-lg-3 col-6">
            <?= InfoBox::widget([
                'text' => 'Salas Livres',
                'number' => $salasLivres ?? '0',
                'icon' => 'fas fa-circle-check',
                'theme' => 'success',
                'progress' => [
                    'width' => '60%',
                    'description' => '60% disponíveis'
                ]
            ]) ?>
        </div>
        <div class="col-lg-3 col-6">
            <?= InfoBox::widget([
                'text' => 'Em Manutenção',
                'number' => $salasManutencao ?? '0',
                'icon' => 'fas fa-tools',
                'theme' => 'warning',
                'progress' => [
                    'width' => '15%',
                    'description' => '15% do total'
                ]
            ]) ?>
        </div>
    </div>

    <!-- Gráficos e Visualizações -->
    <div class="row">
        <!-- Gráfico de Estado das Salas -->
        <div class="col-md-6">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-pie me-2"></i>
                        Estado das Salas Operatórias
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="salaStatusChart" height="250"></canvas>
                </div>
                <div class="card-footer">
                    <small class="text-muted">
                        <i class="fas fa-info-circle me-1"></i> Distribuição atual das salas por estado
                    </small>
                </div>
            </div>
        </div>

        <!-- Gráfico de Requisições por Mês -->
        <div class="col-md-6">
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-line me-2"></i>
                        Requisições por Mês
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="requisicoesChart" height="250"></canvas>
                </div>
                <div class="card-footer">
                    <small class="text-muted">
                        <i class="fas fa-info-circle me-1"></i> Tendência de requisições nos últimos 6 meses
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Informações em Tempo Real -->
    <div class="row">
        <!-- Últimas Requisições -->
        <div class="col-md-6">
            <div class="card card-warning">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-history me-2"></i>
                        Últimas Requisições
                    </h3>
                    <div class="card-tools">
                        <?= Html::a('Ver Todas', ['requisicao/index'], ['class' => 'btn btn-warning btn-sm']) ?>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">
                            <thead>
                            <tr>
                                <th style="width: 10px">#</th>
                                <th>Sala</th>
                                <th>Utilizador</th>
                                <th>Estado</th>
                                <th>Início</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!empty($ultimasRequisicoes)): ?>
                                <?php foreach ($ultimasRequisicoes as $requisicao): ?>
                                    <tr>
                                        <td><?= $requisicao['id'] ?? '' ?></td>
                                        <td><?= Html::encode($requisicao['sala_nome'] ?? '') ?></td>
                                        <td><?= Html::encode($requisicao['user_name'] ?? '') ?></td>
                                        <td>
                                            <?php
                                            $badge = match($requisicao['status'] ?? '') {
                                                'Ativa' => 'success',
                                                'Concluída' => 'secondary',
                                                'Cancelada' => 'danger',
                                                default => 'dark'
                                            };
                                            ?>
                                            <span class="badge bg-<?= $badge ?>">
                                                    <?= $requisicao['status'] ?? '' ?>
                                                </span>
                                        </td>
                                        <td><?= Yii::$app->formatter->asDatetime($requisicao['dataInicio'] ?? '', 'short') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-3">
                                        <i class="fas fa-info-circle me-2"></i>Nenhuma requisição recente
                                    </td>
                                </tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Acesso Rápido -->
        <div class="col-md-6">
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-bolt me-2"></i>
                        Acesso Rápido
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <a href="<?= Yii::$app->urlManager->createUrl(['requisicao/create']) ?>"
                               class="btn btn-primary btn-lg btn-block text-left">
                                <i class="fas fa-plus me-2"></i>
                                Nova Requisição
                            </a>
                        </div>
                        <div class="col-md-6 mb-3">
                            <a href="<?= Yii::$app->urlManager->createUrl(['sala/index']) ?>"
                               class="btn btn-success btn-lg btn-block text-left">
                                <i class="fas fa-door-open me-2"></i>
                                Gerir Salas
                            </a>
                        </div>
                        <div class="col-md-6 mb-3">
                            <a href="<?= Yii::$app->urlManager->createUrl(['requisicao/calendar']) ?>"
                               class="btn btn-warning btn-lg btn-block text-left">
                                <i class="fas fa-calendar-alt me-2"></i>
                                Calendário
                            </a>
                        </div>
                        <div class="col-md-6 mb-3">
                            <a href="<?= Yii::$app->urlManager->createUrl(['user/index']) ?>"
                               class="btn btn-danger btn-lg btn-block text-left">
                                <i class="fas fa-users me-2"></i>
                                Utilizadores
                            </a>
                        </div>
                    </div>

                    <!-- Status do Sistema -->
                    <div class="mt-4">
                        <h6 class="mb-3">
                            <i class="fas fa-server me-2"></i>Status do Sistema
                        </h6>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Database:</span>
                            <span class="badge bg-success">Online</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Cache:</span>
                            <span class="badge bg-success">Ativo</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Último Backup:</span>
                            <span class="text-muted">Hoje, 02:00</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Callouts Informativos -->
    <div class="row mt-4">
        <div class="col-md-4">
            <div class="callout callout-info">
                <h5><i class="fas fa-bell me-2"></i>Notificações</h5>
                <p>
                    <?= $notificacoesCount ?? '0' ?> notificações pendentes<br>
                    <?= $alertasCount ?? '0' ?> alertas do sistema
                </p>
                <a href="#" class="btn btn-info btn-sm">Ver Detalhes</a>
            </div>
        </div>
        <div class="col-md-4">
            <div class="callout callout-success">
                <h5><i class="fas fa-chart-bar me-2"></i>Estatísticas</h5>
                <p>
                    <?= $taxaOcupacao ?? '0' ?>% ocupação média<br>
                    <?= $tempoMedio ?? '0' ?>h tempo médio por requisição
                </p>
                <a href="#" class="btn btn-success btn-sm">Ver Relatório</a>
            </div>
        </div>
        <div class="col-md-4">
            <div class="callout callout-warning">
                <h5><i class="fas fa-exclamation-triangle me-2"></i>Avisos</h5>
                <p>
                    <?= $manutencoesPendentes ?? '0' ?> manutenções pendentes<br>
                    <?= $salasProblema ?? '0' ?> salas com problemas
                </p>
                <a href="#" class="btn btn-warning btn-sm">Resolver</a>
            </div>
        </div>
    </div>
</div>

<style>
    .btn-block {
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 10px;
        transition: all 0.3s ease;
        text-align: left;
        display: flex;
        align-items: center;
    }
    .btn-block:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    .callout {
        border-left-width: 5px;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 0;
    }
    .table-hover tbody tr:hover {
        background-color: rgba(0,0,0,0.02);
    }
    .card {
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        border: none;
        border-radius: 10px;
        margin-bottom: 20px;
    }
    .card:hover {
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Gráfico de Estado das Salas (Pie Chart)
        const salaCtx = document.getElementById('salaStatusChart').getContext('2d');
        const salaChart = new Chart(salaCtx, {
            type: 'pie',
            data: {
                labels: ['Livre', 'Em Uso', 'Manutenção', 'Desativada'],
                datasets: [{
                    data: [
                        <?= $salasLivres ?? 0 ?>,
                        <?= $salasEmUso ?? 0 ?>,
                        <?= $salasManutencao ?? 0 ?>,
                        <?= $salasDesativadas ?? 0 ?>
                    ],
                    backgroundColor: [
                        '#28a745', // Verde
                        '#dc3545', // Vermelho
                        '#ffc107', // Amarelo
                        '#6c757d'  // Cinza
                    ],
                    borderWidth: 1,
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
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                label += context.raw + ' salas';
                                return label;
                            }
                        }
                    }
                }
            }
        });

        // Gráfico de Requisições por Mês (Line Chart)
        const reqCtx = document.getElementById('requisicoesChart').getContext('2d');
        const meses = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
        const mesAtual = new Date().getMonth();
        const ultimosMeses = meses.slice(Math.max(mesAtual - 5, 0), mesAtual + 1);

        const requisicoesChart = new Chart(reqCtx, {
            type: 'line',
            data: {
                labels: ultimosMeses,
                datasets: [{
                    label: 'Requisições',
                    data: <?= json_encode($requisicoesPorMes ?? [0,0,0,0,0,0]) ?>,
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        },
                        title: {
                            display: true,
                            text: 'Número de Requisições'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Mês'
                        }
                    }
                }
            }
        });


    });
</script>