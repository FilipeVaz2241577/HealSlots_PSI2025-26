<?php

/** @var yii\web\View $this */
/** @var common\models\TipoEquipamento[] $tiposEquipamento */
/** @var string $search */

use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;

// Definir imagens para cada tipo
$imagens = [
        1 => 'mesacirurgica.jpg', // Equipamentos Móveis
        2 => 'monitores.jpg',     // Equipamentos de Monitorização
        3 => 'instrumentos_cirugicos.jpg', // Instrumentos Cirúrgicos
        4 => 'materias_de_consumo.jpg',    // Materiais de Consumo
];

// Definir cores de badge para cada tipo
$coresBadge = [
        1 => 'primary',
        2 => 'success',
        3 => 'warning',
        4 => 'info',
];

// Definir ícones para cada tipo
$icones = [
        1 => 'fa-procedures',
        2 => 'fa-heartbeat',
        3 => 'fa-syringe',
        4 => 'fa-boxes',
];

$this->title = 'Tipos de Equipamento';
$this->params['breadcrumbs'][] = $this->title;

// Calcular estatísticas totais
$totalTipos = count($tiposEquipamento);
$totalEquipamentos = 0;
$totalOperacionais = 0;
$totalManutencao = 0;
$totalEmUso = 0;

foreach ($tiposEquipamento as $tipo) {
    $totalEquipamentos += $tipo->quantidadeEquipamentos ?? 0;
    $totalOperacionais += $tipo->operacionais ?? 0;
    $totalManutencao += $tipo->em_manutencao ?? 0;
    $totalEmUso += $tipo->em_uso ?? 0;
}
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
                        <?php $form = ActiveForm::begin([
                                'method' => 'get',
                                'action' => ['site/tiposequipamento'],
                                'options' => ['class' => 'search-form']
                        ]); ?>

                        <div class="input-group input-group-lg">
                            <?= Html::textInput('search', $search, [
                                    'class' => 'form-control',
                                    'placeholder' => 'Pesquisar tipos de equipamento...',
                                    'aria-label' => 'Pesquisar tipos de equipamento'
                            ]) ?>
                            <button class="btn btn-primary" type="submit" title="Pesquisar">
                                <i class="fas fa-search"></i>
                            </button>
                            <?php if ($search): ?>
                                <?= Html::a('<i class="fas fa-times"></i>', ['site/tiposequipamento'], [
                                        'class' => 'btn btn-outline-secondary',
                                        'title' => 'Limpar pesquisa'
                                ]) ?>
                            <?php endif; ?>
                        </div>

                        <?php ActiveForm::end(); ?>
                    </div>
                </div>

                <!-- Lista de Tipos de Equipamento -->
                <div class="row">
                    <?php if (empty($tiposEquipamento)): ?>
                        <div class="col-12 text-center py-5">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <h4 class="text-muted">
                                <?= $search ? 'Nenhum tipo de equipamento encontrado para "' . Html::encode($search) . '"' : 'Nenhum tipo de equipamento cadastrado' ?>
                            </h4>
                            <?php if ($search): ?>
                                <p class="text-muted">Tente pesquisar com outros termos</p>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <?php foreach ($tiposEquipamento as $tipo): ?>
                            <?php
                            $quantidade = $tipo->quantidadeEquipamentos ?? 0;
                            $operacionais = $tipo->operacionais ?? 0;
                            $emManutencao = $tipo->em_manutencao ?? 0;
                            $emUso = $tipo->em_uso ?? 0;

                            $corBadge = isset($coresBadge[$tipo->id]) ? $coresBadge[$tipo->id] : 'secondary';
                            $icone = isset($icones[$tipo->id]) ? $icones[$tipo->id] : 'fa-tag';
                            ?>

                            <div class="col-md-6 mb-4">
                                <div class="equipment-card h-100">
                                    <div class="equipment-image-container">
                                        <?php
                                        $imagem = isset($imagens[$tipo->id]) ? $imagens[$tipo->id] : 'default-equipment.jpg';
                                        $imagemPath = Yii::getAlias('@web/img/' . $imagem);

                                        // Verificar se a imagem existe, senão usar placeholder
                                        $imagemFullPath = Yii::getAlias('@frontend/web/img/' . $imagem);
                                        if (!file_exists($imagemFullPath)) {
                                            $imagemPath = 'https://via.placeholder.com/400x200?text=' . urlencode($tipo->nome);
                                        }
                                        ?>
                                        <img src="<?= $imagemPath ?>"
                                             alt="<?= Html::encode($tipo->nome) ?>"
                                             class="equipment-image">
                                        <div class="equipment-overlay">
                                            <i class="fas fa-search-plus"></i>
                                        </div>
                                    </div>
                                    <div class="equipment-content">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <h5 class="text-primary fw-bold mb-0">
                                                <i class="fas <?= $icone ?> me-2"></i>
                                                <?= Html::encode($tipo->nome) ?>
                                            </h5>
                                        </div>

                                        <!-- Barra de progresso para status -->
                                        <?php if ($quantidade > 0): ?>
                                            <div class="mb-3">
                                                <div class="d-flex justify-content-between mb-1">
                                                    <small>Operacionais: <?= $operacionais ?></small>
                                                    <small><?= $quantidade > 0 ? round(($operacionais / $quantidade) * 100) : 0 ?>%</small>
                                                </div>
                                                <div class="progress" style="height: 6px;">
                                                    <div class="progress-bar bg-success" role="progressbar"
                                                         style="width: <?= $quantidade > 0 ? ($operacionais / $quantidade) * 100 : 0 ?>%">
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>

                                        <!-- Ações -->
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <?= Html::a('<i class="fas fa-eye me-1"></i> Ver Equipamentos',
                                                        ['site/equipamentos', 'tipo' => $tipo->id],
                                                        ['class' => 'btn btn-outline-primary btn-sm']) ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
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
            overflow: hidden;
            background: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            height: 100%;
        }

        .equipment-card:hover {
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            transform: translateY(-5px);
        }

        .equipment-image-container {
            position: relative;
            overflow: hidden;
            height: 180px;
            background-color: #f8f9fa;
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
            cursor: pointer;
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

        .equipment-stats .border {
            border-color: #e9ecef !important;
        }

        .equipment-stats .border:hover {
            border-color: #dee2e6 !important;
            background-color: #f8f9fa;
        }

        .badge {
            min-width: 40px;
            text-align: center;
        }

        .stat-card {
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .input-group-lg .form-control {
            border-radius: 8px 0 0 8px;
            border-right: 0;
        }

        .input-group-lg .btn {
            border-radius: 0 8px 8px 0;
        }

        .btn-outline-primary {
            border-radius: 6px;
            padding: 6px 12px;
            font-size: 0.875rem;
            transition: all 0.3s ease;
        }

        .btn-outline-primary:hover {
            background-color: #007bff;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 123, 255, 0.3);
        }

        .search-form .btn-outline-secondary {
            border-radius: 8px;
            margin-left: 5px;
        }

        .progress {
            background-color: #e9ecef;
            border-radius: 3px;
        }

        .progress-bar {
            border-radius: 3px;
        }
    </style>

<?php
// Adicionar JS para funcionalidades extras
$this->registerJs(<<<JS
    // Efeito de clique no overlay da imagem
    $(document).on('click', '.equipment-overlay', function(e) {
        e.preventDefault();
        var card = $(this).closest('.equipment-card');
        var link = card.find('.btn-outline-primary');
        if (link.length) {
            window.location.href = link.attr('href');
        }
    });
    
    // Focar no campo de busca ao carregar a página
    $('input[name="search"]').focus();
    
    // Animar as barras de progresso
    $(document).ready(function() {
        $('.progress-bar').each(function() {
            var width = $(this).attr('style').match(/width: (.*?)%/);
            if (width) {
                $(this).css('width', '0%').animate({
                    width: width[1] + '%'
                }, 1000);
            }
        });
    });
JS);