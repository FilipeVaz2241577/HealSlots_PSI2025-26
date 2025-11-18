<?php

/** @var yii\web\View $this */

use yii\bootstrap5\Html;

$this->title = 'Blocos Hospitalares';
?>

<div class="site-blocos">
    <div class="container">
        <div class="card shadow">
            <div class="card-body p-5">
                <div class="text-center mb-5">
                    <h1 class="display-5 text-primary mb-2"><?= Html::encode($this->title) ?></h1>
                    <p class="lead text-muted">Gerencie todos os blocos hospitalares</p>
                </div>

                <!-- Lista de Blocos -->
                <div class="row">
                    <!-- Bloco A -->
                    <div class="col-md-6 mb-4">
                        <div class="block-card">
                            <div class="block-content p-4">
                                <h3 class="text-primary mb-2">Bloco A</h3>
                                <p class="mb-3">
                                    <span class="text-muted">Estado:</span>
                                    <span class="text-success"><strong>Ativo</strong></span>
                                </p>
                                <div class="text-start">
                                    <?= Html::a('Ver Salas', ['site/salas'], ['class' => 'btn btn-outline-primary btn-sm']) ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Bloco B -->
                    <div class="col-md-6 mb-4">
                        <div class="block-card">
                            <div class="block-content p-4">
                                <h3 class="text-primary mb-2">Bloco B</h3>
                                <p class="mb-3">
                                    <span class="text-muted">Estado:</span>
                                    <span class="text-success"><strong>Ativo</strong></span>
                                </p>
                                <div class="text-start">
                                    <button class="btn btn-outline-primary btn-sm">
                                        Ver Salas
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Bloco C -->
                    <div class="col-md-6 mb-4">
                        <div class="block-card">
                            <div class="block-content p-4">
                                <h3 class="text-primary mb-2">Bloco C</h3>
                                <p class="mb-3">
                                    <span class="text-muted">Estado:</span>
                                    <span class="text-warning"><strong>Manutenção</strong></span>
                                </p>
                                <div class="text-start">
                                    <button class="btn btn-outline-primary btn-sm">
                                        Ver Salas
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Bloco D -->
                    <div class="col-md-6 mb-4">
                        <div class="block-card">
                            <div class="block-content p-4">
                                <h3 class="text-primary mb-2">Bloco D</h3>
                                <p class="mb-3">
                                    <span class="text-muted">Estado:</span>
                                    <span class="text-success"><strong>Ativo</strong></span>
                                </p>
                                <div class="text-start">
                                    <button class="btn btn-outline-primary btn-sm">
                                        Ver Salas
                                    </button>
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
    .block-card {
        border: 1px solid #e9ecef;
        border-radius: 12px;
        padding: 0;
        transition: all 0.3s ease;
        height: 100%;
        overflow: hidden;
        background: white;
    }

    .block-card:hover {
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        transform: translateY(-5px);
    }

    .block-content {
        padding: 40px 30px;
    }

    .text-success {
        color: #28a745 !important;
    }

    .text-warning {
        color: #ffc107 !important;
    }

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