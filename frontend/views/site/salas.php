<?php

/** @var yii\web\View $this */

use yii\bootstrap5\Html;

$this->title = 'Salas - Bloco A';
?>

<div class="site-salas">
    <div class="container">
        <div class="card shadow">
            <div class="card-body p-5">
                <!-- Cabeçalho -->
                <div class="d-flex justify-content-between align-items-center mb-5">
                    <div>
                        <h1 class="display-5 text-primary mb-2"><?= Html::encode($this->title) ?></h1>
                        <p class="lead text-muted">Gerencie todas as salas do Bloco A</p>
                    </div>
                    <div>
                        <?= Html::a('Voltar aos Blocos', ['site/blocos'], ['class' => 'btn btn-outline-secondary']) ?>
                    </div>
                </div>

                <!-- Lista de Salas Simplificada -->
                <div class="row">
                    <!-- Sala A101 -->
                    <div class="col-md-4 mb-4">
                        <div class="room-card">
                            <div class="room-content p-4">
                                <h4 class="text-primary mb-3">Sala A101</h4>
                                <p class="mb-3">
                                    <span class="text-muted">Estado:</span>
                                    <span class="text-success"><strong>Disponível</strong></span>
                                </p>
                                <div class="text-start">
                                    <?= Html::a('Ver Detalhes', ['#'], ['class' => 'btn btn-outline-primary btn-sm']) ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sala A102 -->
                    <div class="col-md-4 mb-4">
                        <div class="room-card">
                            <div class="room-content p-4">
                                <h4 class="text-primary mb-3">Sala A102</h4>
                                <p class="mb-3">
                                    <span class="text-muted">Estado:</span>
                                    <span class="text-warning"><strong>Ocupada</strong></span>
                                </p>
                                <div class="text-start">
                                    <?= Html::a('Ver Detalhes', ['#'], ['class' => 'btn btn-outline-primary btn-sm']) ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sala A103 -->
                    <div class="col-md-4 mb-4">
                        <div class="room-card">
                            <div class="room-content p-4">
                                <h4 class="text-primary mb-3">Sala A103</h4>
                                <p class="mb-3">
                                    <span class="text-muted">Estado:</span>
                                    <span class="text-success"><strong>Disponível</strong></span>
                                </p>
                                <div class="text-start">
                                    <?= Html::a('Ver Detalhes', ['#'], ['class' => 'btn btn-outline-primary btn-sm']) ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sala A104 -->
                    <div class="col-md-4 mb-4">
                        <div class="room-card">
                            <div class="room-content p-4">
                                <h4 class="text-primary mb-3">Sala A104</h4>
                                <p class="mb-3">
                                    <span class="text-muted">Estado:</span>
                                    <span class="text-danger"><strong>Manutenção</strong></span>
                                </p>
                                <div class="text-start">
                                    <?= Html::a('Ver Detalhes', ['#'], ['class' => 'btn btn-outline-primary btn-sm']) ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sala A105 -->
                    <div class="col-md-4 mb-4">
                        <div class="room-card">
                            <div class="room-content p-4">
                                <h4 class="text-primary mb-3">Sala A105</h4>
                                <p class="mb-3">
                                    <span class="text-muted">Estado:</span>
                                    <span class="text-success"><strong>Disponível</strong></span>
                                </p>
                                <div class="text-start">
                                    <?= Html::a('Ver Detalhes', ['#'], ['class' => 'btn btn-outline-primary btn-sm']) ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sala A106 -->
                    <div class="col-md-4 mb-4">
                        <div class="room-card">
                            <div class="room-content p-4">
                                <h4 class="text-primary mb-3">Sala A106</h4>
                                <p class="mb-3">
                                    <span class="text-muted">Estado:</span>
                                    <span class="text-info"><strong>Reservada</strong></span>
                                </p>
                                <div class="text-start">
                                    <?= Html::a('Ver Detalhes', ['#'], ['class' => 'btn btn-outline-primary btn-sm']) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Estatísticas Rápidas -->
                <div class="row mt-5">
                    <div class="col-md-3">
                        <div class="stat-card text-center p-3">
                            <h3 class="text-primary">6</h3>
                            <p class="text-muted mb-0">Total de Salas</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card text-center p-3">
                            <h3 class="text-success">3</h3>
                            <p class="text-muted mb-0">Disponíveis</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card text-center p-3">
                            <h3 class="text-warning">1</h3>
                            <p class="text-muted mb-0">Ocupadas</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card text-center p-3">
                            <h3 class="text-danger">1</h3>
                            <p class="text-muted mb-0">Em Manutenção</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .room-card {
        border: 1px solid #e9ecef;
        border-radius: 12px;
        padding: 0;
        transition: all 0.3s ease;
        height: 100%;
        overflow: hidden;
        background: white;
    }

    .room-card:hover {
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        transform: translateY(-5px);
    }

    .room-content {
        padding: 30px 25px;
        text-align: center;
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

    .text-success { color: #28a745 !important; }
    .text-warning { color: #ffc107 !important; }
    .text-danger { color: #dc3545 !important; }
    .text-info { color: #17a2b8 !important; }

    .btn-outline-primary {
        border-radius: 6px;
        padding: 8px 16px;
        font-size: 0.875rem;
        transition: all 0.3s ease;
    }

    .btn-outline-primary:hover {
        background-color: #007bff;
        color: white;
        transform: translateY(-2px);
    }
</style>