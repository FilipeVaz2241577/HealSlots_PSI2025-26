<?php

/** @var yii\web\View $this */
/** @var common\models\Bloco[] $blocos - Array de blocos a serem exibidos */
/** @var string $search - Termo de pesquisa atual */
/** @var int $totalBlocos - Número total de blocos */
/** @var int $totalSalas - Número total de salas em todos os blocos */
/** @var int $blocosAtivos - Número de blocos ativos */
/** @var int $blocosDesativados - Número de blocos desativados */
/** @var int $blocosUso - Número de blocos em uso */

use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;

// Mapear cores para estados (usando as constantes do modelo Bloco)
$coresEstado = [
        \common\models\Bloco::ESTADO_ATIVO => 'success',      // Verde para ativo
        \common\models\Bloco::ESTADO_DESATIVADO => 'secondary', // Cinza para desativado
];
?>

<div class="site-blocos">
    <div class="container">
        <div class="card shadow">
            <div class="card-body p-5">
                <!-- Cabeçalho da página -->
                <div class="d-flex justify-content-between align-items-center mb-5">
                    <div class="text-center w-100">
                        <h1 class="display-5 text-primary mb-2"><?= Html::encode($this->title) ?></h1>
                        <p class="lead text-muted">Gerencie todos os blocos hospitalares</p>
                    </div>

                    <div>
                        <?php if (Yii::$app->user->can('createBloco')): ?>
                            <!-- Botão para criar novo bloco (apenas para utilizadores com permissão) -->
                            <?= Html::a('<i class="fas fa-plus me-1"></i> Novo Bloco', ['bloco/create'], ['class' => 'btn btn-primary']) ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Secção de filtros e pesquisa -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <?php $form = ActiveForm::begin([
                                'method' => 'get', // Formulário GET para pesquisa
                                'action' => ['site/blocos'], // Ação para onde envia a pesquisa
                                'options' => ['class' => 'search-form']
                        ]); ?>

                        <div class="input-group input-group-lg">
                            <!-- Campo de pesquisa -->
                            <?= Html::textInput('search', $search, [
                                    'class' => 'form-control',
                                    'placeholder' => 'Pesquisar blocos...',
                                    'aria-label' => 'Pesquisar blocos',
                                    'autofocus' => true, // Foco automático no campo
                            ]) ?>
                            <!-- Botão de pesquisa -->
                            <button class="btn btn-primary" type="submit" title="Pesquisar">
                                <i class="fas fa-search"></i>
                            </button>
                            <!-- Botão para limpar pesquisa (apenas aparece se houver pesquisa) -->
                            <?php if ($search): ?>
                                <?= Html::a('<i class="fas fa-times"></i>', ['site/blocos'], [
                                        'class' => 'btn btn-outline-secondary',
                                        'title' => 'Limpar pesquisa'
                                ]) ?>
                            <?php endif; ?>
                        </div>

                        <?php ActiveForm::end(); ?>
                    </div>
                </div>

                <!-- Mensagem quando não há blocos para mostrar -->
                <?php if (empty($blocos)): ?>
                    <div class="col-12 text-center py-5">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <h4 class="text-muted">
                            <?= $search
                                    ? 'Nenhum bloco encontrado como "' . Html::encode($search) . '"'
                                    : 'Não existem blocos cadastrados'
                            ?>
                        </h4>
                        <?php if ($search): ?>
                            <p class="text-muted">Tente pesquisar com outros filtros</p>
                        <?php endif; ?>
                        <?php if (!$search && Yii::$app->user->can('createBloco')): ?>
                            <?= Html::a('Clique aqui para adicionar um novo bloco', ['bloco/create'], ['class' => 'btn btn-link']) ?>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <!-- Grid de blocos -->
                <div class="row">
                    <?php foreach ($blocos as $bloco): ?>
                        <?php
                        // Calcula estatísticas para cada bloco
                        $salasCount = $bloco->getSalas()->count();
                        $corBadge = isset($coresEstado[$bloco->estado]) ? $coresEstado[$bloco->estado] : 'secondary';
                        $estadoTexto = $bloco->getEstadoLabel();

                        // Contagem de salas por estado
                        $salasLivres = $bloco->getSalas()->where(['estado' => \common\models\Sala::ESTADO_LIVRE])->count();
                        $salasEmUso = $bloco->getSalas()->where(['estado' => \common\models\Sala::ESTADO_EM_USO])->count();
                        $salasManutencao = $bloco->getSalas()->where(['estado' => \common\models\Sala::ESTADO_MANUTENCAO])->count();
                        $salasDesativadas = $bloco->getSalas()->where(['estado' => \common\models\Sala::ESTADO_DESATIVADA])->count();
                        ?>
                        <div class="col-md-4 mb-4">
                            <!-- Cartão de cada bloco -->
                            <div class="block-card h-100">
                                <div class="block-content p-2">
                                    <!-- Cabeçalho do cartão: nome e estado -->
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <h3 class="text-primary mb-0"><?= Html::encode($bloco->nome) ?></h3>
                                        <span class="badge bg-<?= $corBadge ?>">
                                            <?= Html::encode($estadoTexto) ?>
                                        </span>
                                    </div>

                                    <!-- Informações do bloco -->
                                    <div class="mb-3">
                                        <p class="mb-2">
                                            <i class="fas fa-door-open text-primary me-2"></i>
                                            <strong><?= $salasCount ?></strong> sala(s)
                                        </p>
                                        <p class="mb-0">
                                            <i class="fas fa-calendar-alt text-primary me-2"></i>
                                            <small class="text-muted">
                                                <!-- Distribuição das salas por estado -->
                                                <span class="text-success"><?= $salasLivres ?> Livres</span> |
                                                <span class="text-danger"><?= $salasEmUso ?> Em Uso</span><br>
                                                <span class="text-warning"><?= $salasManutencao ?> Manutenção</span> |
                                                <span class="text-secondary"><?= $salasDesativadas ?> Desativadas</span>
                                            </small>
                                        </p>
                                    </div>

                                    <!-- Botões de ação -->
                                    <div class="d-flex justify-content-between align-items-center mt-4">
                                        <div>
                                            <!-- Botão para ver salas do bloco -->
                                            <?= Html::a('Ver Salas',
                                                    ['site/salas', 'bloco' => $bloco->id],
                                                    ['class' => 'btn btn-outline-primary btn-sm']) ?>
                                        </div>
                                        <div class="text-end">
                                            <?php if (Yii::$app->user->can('updateBloco')): ?>
                                                <!-- Botão para editar bloco (com permissão) -->
                                                <?= Html::a('<i class="fas fa-edit"></i>',
                                                        ['bloco/update', 'id' => $bloco->id],
                                                        [
                                                                'class' => 'btn btn-outline-warning btn-sm me-1',
                                                                'title' => 'Editar bloco'
                                                        ]) ?>
                                            <?php endif; ?>

                                            <?php if (Yii::$app->user->can('createSala')): ?>
                                                <!-- Botão para adicionar sala ao bloco (com permissão) -->
                                                <?= Html::a('<i class="fas fa-plus"></i>',
                                                        ['sala/create', 'bloco_id' => $bloco->id],
                                                        [
                                                                'class' => 'btn btn-outline-success btn-sm',
                                                                'title' => 'Adicionar sala'
                                                        ]) ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Informações adicionais e estatísticas -->
                <?php if (!empty($blocos)): ?>
                    <div class="alert alert-light mt-4">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-muted mb-3"><i class="fas fa-info-circle me-2"></i>Informações</h6>
                                <ul class="list-unstyled">
                                    <li>
                                        <!-- Badge para blocos ativos -->
                                        <span class="badge bg-success me-2">&nbsp;</span>
                                        <strong><?= $blocosAtivos ?></strong> bloco(s) ativo(s)
                                    </li>
                                    <li>
                                        <!-- Badge para blocos desativados -->
                                        <span class="badge bg-secondary me-2">&nbsp;</span>
                                        <strong><?= $blocosDesativados ?></strong> bloco(s) desativado(s)
                                    </li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <div class="alert alert-info">
                                    <!-- Resumo total de salas e blocos -->
                                    <i class="fas fa-building me-2"></i>
                                    Total de <strong><?= $totalSalas ?></strong> sala(s) distribuídas por <strong><?= $totalBlocos ?></strong> bloco(s)
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
    /* Estilos para os cartões de bloco */
    .block-card {
        border: 1px solid #e9ecef;
        border-radius: 12px;
        padding: 0;
        transition: all 0.3s ease; /* Transição suave para hover */
        overflow: hidden;
        background: white;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        height: 100%;
    }

    /* Efeito de hover nos cartões */
    .block-card:hover {
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        transform: translateY(-5px); /* Leve elevação ao passar o mouse */
    }

    /* Espaçamento interno do conteúdo dos cartões */
    .block-content {
        padding: 25px;
    }

    /* Estilos para cartões de estatísticas (se usados futuramente) */
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

    /* Estilização de badges (indicadores de estado) */
    .badge {
        font-size: 0.75rem;
        padding: 6px 10px;
        border-radius: 20px; /* Bordas arredondadas para badge oval */
    }

    /* Estilos para botões com contorno primário */
    .btn-outline-primary {
        border-radius: 6px;
        padding: 6px 12px;
        font-size: 0.875rem;
        transition: all 0.3s ease;
    }

    /* Efeito hover para botões primários */
    .btn-outline-primary:hover {
        background-color: #007bff;
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 123, 255, 0.3);
    }

    /* Estilos para botões com contorno de sucesso */
    .btn-outline-success {
        border-radius: 6px;
        padding: 6px 12px;
        font-size: 0.875rem;
        transition: all 0.3s ease;
    }

    /* Efeito hover para botões de sucesso */
    .btn-outline-success:hover {
        background-color: #28a745;
        color: white;
        transform: translateY(-2px);
    }

    /* Estilos para botões com contorno de aviso */
    .btn-outline-warning {
        border-radius: 6px;
        padding: 6px 12px;
        font-size: 0.875rem;
        transition: all 0.3s ease;
    }

    /* Efeito hover para botões de aviso */
    .btn-outline-warning:hover {
        background-color: #ffc107;
        color: #212529;
        transform: translateY(-2px);
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
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* Estilo do botão para limpar pesquisa */
    .search-form .btn-outline-secondary {
        border-radius: 8px;
        margin-left: 5px; /* Pequeno espaçamento do botão de pesquisa */
        padding: 0 18px;
        font-size: 1.1rem;
        height: auto;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* Ajuste do grupo de input para altura consistente */
    .search-form .input-group-lg {
        height: auto;
    }

    /* Estilo consistente para títulos dos cartões */
    .block-card h3,
    .block-card h3 * {
        font-weight: 500 !important;
        font-family: inherit !important;
    }
</style>