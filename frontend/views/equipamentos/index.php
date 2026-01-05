<?php

/** @var yii\web\View $this - Objeto da view do Yii */
/** @var common\models\TipoEquipamento[] $tiposEquipamento - Array de tipos de equipamento */
/** @var string $search - Termo de pesquisa atual */

use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;

// Definir imagens para cada tipo de equipamento
$imagens = [
        1 => 'mesacirurgica.jpg',         // Equipamentos Móveis
        2 => 'monitores.jpg',             // Equipamentos de Monitorização
        3 => 'instrumentos_cirugicos.jpg', // Instrumentos Cirúrgicos
        4 => 'materias_de_consumo.jpg',    // Materiais de Consumo
];

// Definir cores de badge (indicador visual) para cada tipo
$coresBadge = [
        1 => 'primary',   // Azul para equipamentos móveis
        2 => 'success',   // Verde para equipamentos de monitorização
        3 => 'warning',   // Amarelo para instrumentos cirúrgicos
        4 => 'info',      // Azul para materiais de consumo
];

// Definir ícones FontAwesome para cada tipo
$icones = [
        1 => 'fa-procedures',   // Ícone para equipamentos móveis
        2 => 'fa-heartbeat',    // Ícone para equipamentos de monitorização
        3 => 'fa-syringe',      // Ícone para instrumentos cirúrgicos
        4 => 'fa-boxes',        // Ícone para materiais de consumo
];

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
                <!-- Cabeçalho da página -->
                <div class="text-center mb-5">
                    <h1 class="display-5 text-primary mb-2"><?= Html::encode($this->title) ?></h1>
                    <p class="lead text-muted">Gerencie todas as categorias de equipamentos médicos</p>
                </div>

                <!-- Secção de filtros e pesquisa -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <?php $form = ActiveForm::begin([
                                'method' => 'get', // Formulário GET para pesquisa
                                'action' => ['site/tiposequipamento'], // Ação para onde envia a pesquisa
                                'options' => ['class' => 'search-form']
                        ]); ?>

                        <div class="input-group input-group-lg">
                            <!-- Campo de pesquisa de tipos de equipamento -->
                            <?= Html::textInput('search', $search, [
                                    'class' => 'form-control',
                                    'placeholder' => 'Pesquisar tipos de equipamento...',
                                    'aria-label' => 'Pesquisar tipos de equipamento'
                            ]) ?>
                            <!-- Botão de pesquisa -->
                            <button class="btn btn-primary" type="submit" title="Pesquisar">
                                <i class="fas fa-search"></i>
                            </button>
                            <!-- Botão para limpar pesquisa (apenas aparece se houver pesquisa) -->
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
                    <!-- Mensagem quando não há tipos de equipamento -->
                    <?php if (empty($tiposEquipamento)): ?>
                        <div class="col-12 text-center py-5">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <h4 class="text-muted">
                                <?= $search
                                        ? 'Nenhum tipo de equipamento encontrado como "' . Html::encode($search) . '"'
                                        : 'Nenhum tipo de equipamento cadastrado'
                                ?>
                            </h4>
                            <?php if ($search): ?>
                                <p class="text-muted">Tente pesquisar com outros filtros</p>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <!-- Loop através de cada tipo de equipamento -->
                        <?php foreach ($tiposEquipamento as $tipo): ?>
                            <?php
                            // Extrair estatísticas do tipo de equipamento
                            $quantidade = $tipo->quantidadeEquipamentos ?? 0;
                            $operacionais = $tipo->operacionais ?? 0;
                            $emManutencao = $tipo->em_manutencao ?? 0;
                            $emUso = $tipo->em_uso ?? 0;

                            // Determinar cor e ícone baseado no ID do tipo
                            $corBadge = isset($coresBadge[$tipo->id]) ? $coresBadge[$tipo->id] : 'secondary';
                            $icone = isset($icones[$tipo->id]) ? $icones[$tipo->id] : 'fa-tag';
                            ?>

                            <div class="col-md-6 mb-4">
                                <!-- Cartão para cada tipo de equipamento -->
                                <div class="equipment-card h-100">
                                    <!-- Container da imagem -->
                                    <div class="equipment-image-container">
                                        <?php
                                        $imagem = isset($imagens[$tipo->id]) ? $imagens[$tipo->id] : 'default-equipment.jpg';
                                        $imagemPath = Yii::getAlias('@web/img/' . $imagem);

                                        // Verificar se a imagem existe fisicamente, senão usar placeholder
                                        $imagemFullPath = Yii::getAlias('@frontend/web/img/' . $imagem);
                                        if (!file_exists($imagemFullPath)) {
                                            $imagemPath = 'https://via.placeholder.com/400x200?text=' . urlencode($tipo->nome);
                                        }
                                        ?>
                                        <!-- Imagem do tipo de equipamento -->
                                        <img src="<?= $imagemPath ?>"
                                             alt="<?= Html::encode($tipo->nome) ?>"
                                             class="equipment-image">
                                        <!-- Overlay para efeito visual ao passar o mouse -->
                                        <div class="equipment-overlay">
                                            <i class="fas fa-search-plus"></i>
                                        </div>
                                    </div>

                                    <!-- Conteúdo do cartão -->
                                    <div class="equipment-content">
                                        <!-- Cabeçalho do cartão: nome e ícone -->
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <h5 class="text-primary fw-bold mb-0">
                                                <i class="fas <?= $icone ?> me-2"></i>
                                                <?= Html::encode($tipo->nome) ?>
                                            </h5>
                                        </div>

                                        <!-- Barra de progresso para status (apenas se houver equipamentos) -->
                                        <?php if ($quantidade > 0): ?>
                                            <div class="mb-3">
                                                <div class="d-flex justify-content-between mb-1">
                                                    <!-- Contagem de equipamentos operacionais -->
                                                    <small>Operacionais: <?= $operacionais ?></small>
                                                    <!-- Percentagem de equipamentos operacionais -->
                                                    <small><?= $quantidade > 0 ? round(($operacionais / $quantidade) * 100) : 0 ?>%</small>
                                                </div>
                                                <!-- Barra de progresso visual -->
                                                <div class="progress" style="height: 6px;">
                                                    <div class="progress-bar bg-success" role="progressbar"
                                                         style="width: <?= $quantidade > 0 ? ($operacionais / $quantidade) * 100 : 0 ?>%">
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>

                                        <!-- Ações disponíveis -->
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <!-- Botão para ver equipamentos deste tipo -->
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
        /* Estilos para os cartões de equipamento */
        .equipment-card {
            border: 1px solid #e9ecef;
            border-radius: 12px;
            padding: 0;
            transition: all 0.3s ease; /* Transição suave para hover */
            overflow: hidden;
            background: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            height: 100%; /* Altura total para alinhamento */
        }

        /* Efeito de hover nos cartões */
        .equipment-card:hover {
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            transform: translateY(-5px); /* Leve elevação ao passar o mouse */
        }

        /* Container para a imagem do equipamento */
        .equipment-image-container {
            position: relative;
            overflow: hidden;
            height: 180px;
            background-color: #f8f9fa; /* Cor de fundo padrão */
        }

        /* Estilo da imagem do equipamento */
        .equipment-image {
            width: 100%;
            height: 100%;
            object-fit: cover; /* Mantém proporções da imagem */
            transition: all 0.3s ease;
        }

        /* Efeito de zoom na imagem ao passar o mouse */
        .equipment-card:hover .equipment-image {
            transform: scale(1.05);
        }

        /* Overlay semi-transparente para efeito visual */
        .equipment-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(52, 152, 219, 0.8); /* Azul semi-transparente */
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0; /* Inicialmente invisível */
            transition: all 0.3s ease;
            cursor: pointer;
        }

        /* Mostra overlay ao passar o mouse */
        .equipment-card:hover .equipment-overlay {
            opacity: 1;
        }

        /* Estilo do ícone no overlay */
        .equipment-overlay i {
            color: white;
            font-size: 2rem;
        }

        /* Espaçamento interno do conteúdo do cartão */
        .equipment-content {
            padding: 20px;
        }

        /* Estilos para estatísticas (se usadas futuramente) */
        .equipment-stats .border {
            border-color: #e9ecef !important;
        }

        .equipment-stats .border:hover {
            border-color: #dee2e6 !important;
            background-color: #f8f9fa;
        }

        /* Estilização de badges (indicadores visuais) */
        .badge {
            min-width: 40px;
            text-align: center;
        }

        /* Efeitos para cartões de estatísticas */
        .stat-card {
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        /* ESTILOS UNIFICADOS PARA BARRA DE PESQUISA - CONSISTENTE EM TODO O SISTEMA */
        .search-form .form-control {
            border-radius: 8px 0 0 8px; /* Bordas arredondadas apenas do lado esquerdo */
            border-right: 0; /* Remove borda direita para integrar com botão */
            font-size: 1.1rem;
            font-weight: 400;
            color: #495057;
            padding: 0.75rem 1rem;
        }

        /* Estilo do placeholder do campo de pesquisa */
        .search-form .form-control::placeholder {
            font-size: 1.1rem;
            font-weight: 400;
            color: #6c757d;
            opacity: 0.7;
        }

        /* Estilo do botão de pesquisa */
        .search-form .btn-primary {
            border-radius: 0 8px 8px 0; /* Bordas arredondadas apenas do lado direito */
            padding: 0 20px;
            font-size: 1.1rem;
            font-weight: 500;
            height: auto;
            display: flex; /* Layout flex para centralizar ícone */
            align-items: center; /* Centraliza verticalmente */
            justify-content: center; /* Centraliza horizontalmente */
        }

        /* Estilo do botão para limpar pesquisa */
        .search-form .btn-outline-secondary {
            border-radius: 8px;
            margin-left: 5px; /* Pequeno espaçamento do botão de pesquisa */
            padding: 0 18px;
            font-size: 1.1rem;
            height: auto;
            display: flex; /* Layout flex para centralizar ícone */
            align-items: center; /* Centraliza verticalmente */
            justify-content: center; /* Centraliza horizontalmente */
        }

        /* Estilo para barras de progresso */
        .progress {
            background-color: #e9ecef;
            border-radius: 3px;
        }

        .progress-bar {
            border-radius: 3px;
        }
    </style>

<?php
// JavaScript para funcionalidades extras
$this->registerJs(<<<JS
    // Efeito de clique no overlay da imagem - redireciona para ver equipamentos
    $(document).on('click', '.equipment-overlay', function(e) {
        e.preventDefault();
        var card = $(this).closest('.equipment-card');
        var link = card.find('.btn-outline-primary');
        if (link.length) {
            window.location.href = link.attr('href');
        }
    });
    
    // Focar automaticamente no campo de busca ao carregar a página
    $('input[name="search"]').focus();
    
    // Animação das barras de progresso ao carregar a página
    $(document).ready(function() {
        $('.progress-bar').each(function() {
            var width = $(this).attr('style').match(/width: (.*?)%/);
            if (width) {
                $(this).css('width', '0%').animate({
                    width: width[1] + '%'
                }, 1000); // Animação de 1 segundo
            }
        });
    });
JS);