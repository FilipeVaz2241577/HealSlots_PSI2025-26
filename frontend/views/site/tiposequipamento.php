<?php

/** @var yii\web\View $this */

use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;

$this->title = 'Tipos de Equipamento';
?>

<div class="container">
    <div class="card shadow">
        <div class="card-body p-5">
            <div class="text-center mb-5">
                <h1 class="display-5 text-primary mb-2"><?= Html::encode($this->title) ?></h1>
                <p class="lead text-muted">Gerencie todas as categorias de equipamentos médicos</p>
            </div>

            <!-- Filtros e Busca -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="input-group input-group-lg">
                        <input type="text" class="form-control" placeholder="Pesquisar equipamentos...">
                        <button class="btn btn-primary" type="button">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Lista de Equipamentos -->
            <div class="row">
                <!-- Equipamento 1 -->
                <div class="col-md-6 mb-4">
                    <div class="equipment-card">
                        <div class="equipment-image-container">
                            <img src="<?= Yii::getAlias('@web/img/monitores.jpg') ?>" alt="Monitorização" class="equipment-image">
                            <div class="equipment-overlay">
                                <i class="fas fa-search-plus"></i>
                            </div>
                        </div>
                        <div class="equipment-content">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <h5 class="text-primary fw-bold">Equipamentos de monitorização</h5>
                            </div>
                            <div class="equipment-info">
                                <p class="mb-2">
                                    <i class="fas fa-heartbeat text-primary me-2"></i>
                                    <strong>Monitorização vital</strong>
                                </p>
                                <p class="mb-3">
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                    <small class="text-muted">Quantidade:</small>
                                </p>
                            </div>
                            <div class="text-start">
                                <?= Html::a('Ver Equipamentos', ['site/equipamentos', 'categoria' => 'monitorizacao'], ['class' => 'btn btn-outline-primary btn-sm']) ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Equipamento 2 -->
                <div class="col-md-6 mb-4">
                    <div class="equipment-card">
                        <div class="equipment-image-container">
                            <img src="<?= Yii::getAlias('@web/img/mesacirurgica.jpg') ?>" alt="Equipamentos Móveis" class="equipment-image">
                            <div class="equipment-overlay">
                                <i class="fas fa-search-plus"></i>
                            </div>
                        </div>
                        <div class="equipment-content">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <h5 class="text-primary fw-bold">Equipamentos móveis</h5>
                            </div>
                            <div class="equipment-info">
                                <p class="mb-2">
                                    <i class="fas fa-procedures text-primary me-2"></i>
                                    <strong>Mobilidade hospitalar</strong>
                                </p>
                                <p class="mb-3">
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                    <small class="text-muted">Quantidade:</small>
                                </p>
                            </div>
                            <div class="text-start">
                                <?= Html::a('Ver Equipamentos', ['site/equipamentos', 'categoria' => 'moveis'], ['class' => 'btn btn-outline-primary btn-sm']) ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Equipamento 3 -->
                <div class="col-md-6 mb-4">
                    <div class="equipment-card">
                        <div class="equipment-image-container">
                            <img src="<?= Yii::getAlias('@web/img/instrumentos_cirugicos.jpg') ?>" alt="Instrumentos Cirúrgicos" class="equipment-image">
                            <div class="equipment-overlay">
                                <i class="fas fa-search-plus"></i>
                            </div>
                        </div>
                        <div class="equipment-content">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <h5 class="text-primary fw-bold">Instrumentos cirúrgicos</h5>
                            </div>
                            <div class="equipment-info">
                                <p class="mb-2">
                                    <i class="fas fa-syringe text-primary me-2"></i>
                                    <strong>Procedimentos cirúrgicos</strong>
                                </p>
                                <p class="mb-3">
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                    <small class="text-muted">Quantidade:</small>
                                </p>
                            </div>
                            <div class="text-start">
                                <?= Html::a('Ver Equipamentos', ['site/equipamentos', 'categoria' => 'cirurgicos'], ['class' => 'btn btn-outline-primary btn-sm']) ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Equipamento 4 -->
                <div class="col-md-6 mb-4">
                    <div class="equipment-card">
                        <div class="equipment-image-container">
                            <img src="<?= Yii::getAlias('@web/img/materias_de_consumo.jpg') ?>" alt="Materiais de Consumo" class="equipment-image">
                            <div class="equipment-overlay">
                                <i class="fas fa-search-plus"></i>
                            </div>
                        </div>
                        <div class="equipment-content">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <h5 class="text-primary fw-bold">Materiais de consumo</h5>
                            </div>
                            <div class="equipment-info">
                                <p class="mb-2">
                                    <i class="fas fa-boxes text-primary me-2"></i>
                                    <strong>Consumíveis médicos</strong>
                                </p>
                                <p class="mb-3">
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                    <small class="text-muted">Quantidade:</small>
                                </p>
                            </div>
                            <div class="text-start">
                                <?= Html::a('Ver Equipamentos', ['site/equipamentos', 'categoria' => 'consumo'], ['class' => 'btn btn-outline-primary btn-sm']) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .equipment-card {
        border: 1px solid #e9ecef;
        border-radius: 12px;
        padding: 0;
        transition: all 0.3s ease;
        height: 100%;
        overflow: hidden;
        background: white;
    }

    .equipment-card:hover {
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        transform: translateY(-5px);
    }

    .equipment-image-container {
        position: relative;
        overflow: hidden;
        height: 200px;
    }

    .equipment-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: all 0.3s ease;
    }

    .equipment-card:hover .equipment-image {
        transform: scale(1.05);
    }

    .equipment-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(52, 152, 219, 0.8);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: all 0.3s ease;
    }

    .equipment-card:hover .equipment-overlay {
        opacity: 1;
    }

    .equipment-overlay i {
        color: white;
        font-size: 2rem;
    }

    .equipment-content {
        padding: 20px;
    }

    .equipment-info p {
        display: flex;
        align-items: center;
        margin-bottom: 8px;
    }

    .badge {
        font-size: 0.75rem;
        padding: 6px 10px;
        border-radius: 20px;
    }

    .input-group-lg .form-control {
        border-radius: 8px 0 0 8px;
    }

    .input-group-lg .btn {
        border-radius: 0 8px 8px 0;
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