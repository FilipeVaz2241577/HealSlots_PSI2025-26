<?php

use yii\bootstrap5\Html;

$this->title = 'Atualizar Requisição: #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Gestão de Requisições', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => 'Requisição #' . $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Atualizar';
?>

<div class="requisicao-update">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card card-primary">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h3 class="card-title">
                                <i class="fas fa-edit me-2"></i>
                                <?= Html::encode($this->title) ?>
                            </h3>
                            <div>
                                <?= Html::a('<i class="fas fa-arrow-left me-2"></i>Voltar à Lista', ['index'], ['class' => 'btn btn-secondary btn-sm']) ?>
                                <?= Html::a('<i class="fas fa-eye me-2"></i>Ver Detalhes', ['view', 'id' => $model->id], ['class' => 'btn btn-info btn-sm']) ?>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if ($model->isAtiva()): ?>
                            <div class="alert alert-warning">
                                <h6><i class="fas fa-exclamation-triangle me-2"></i>Atenção</h6>
                                <p class="mb-0">
                                    • Alterar datas pode causar conflitos com outras requisições<br>
                                    • Verifique a disponibilidade da sala antes de guardar alterações
                                </p>
                            </div>
                        <?php elseif ($model->isConcluida()): ?>
                            <div class="alert alert-secondary">
                                <h6><i class="fas fa-info-circle me-2"></i>Informação</h6>
                                <p class="mb-0">
                                    Esta requisição já está concluída. Apenas alguns campos podem ser editados.
                                </p>
                            </div>
                        <?php elseif ($model->isCancelada()): ?>
                            <div class="alert alert-danger">
                                <h6><i class="fas fa-ban me-2"></i>Requisição Cancelada</h6>
                                <p class="mb-0">
                                    Esta requisição foi cancelada e não pode ser editada.
                                </p>
                            </div>
                        <?php endif; ?>

                        <!-- Estatísticas da requisição -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="small-box bg-info">
                                    <div class="inner">
                                        <h6 class="text-white">Estado</h6>
                                        <h4>
                                            <?php
                                            $badges = [
                                                'Ativa' => '<span class="badge bg-success">Ativa</span>',
                                                'Concluída' => '<span class="badge bg-secondary">Concluída</span>',
                                                'Cancelada' => '<span class="badge bg-danger">Cancelada</span>'
                                            ];
                                            echo $badges[$model->status] ?? '-';
                                            ?>
                                        </h4>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-flag"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="small-box bg-primary">
                                    <div class="inner">
                                        <h6 class="text-white">Sala</h6>
                                        <h4><?= $model->sala->nome ?? '-' ?></h4>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-door-open"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="small-box bg-success">
                                    <div class="inner">
                                        <h6 class="text-white">Utilizador</h6>
                                        <h4><?= $model->user->username ?? '-' ?></h4>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-user"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="small-box bg-warning">
                                    <div class="inner">
                                        <h6 class="text-dark">Duração</h6>
                                        <h4 class="text-dark">
                                            <?php
                                            if ($model->dataFim) {
                                                $inicio = new DateTime($model->dataInicio);
                                                $fim = new DateTime($model->dataFim);
                                                $interval = $inicio->diff($fim);

                                                if ($interval->days > 0) {
                                                    echo $interval->days . ' dias';
                                                } elseif ($interval->h > 0) {
                                                    echo $interval->h . ' horas';
                                                } else {
                                                    echo $interval->i . ' minutos';
                                                }
                                            } else {
                                                echo 'Em curso';
                                            }
                                            ?>
                                        </h4>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-clock text-dark"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Formulário de atualização -->
                        <?= $this->render('_form', [
                            'model' => $model,
                        ]) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .small-box {
        border-radius: 0.25rem;
        box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
        display: block;
        margin-bottom: 20px;
        position: relative;
        height: 120px;
    }
    .small-box > .inner {
        padding: 15px;
    }
    .small-box h4 {
        font-size: 1.5rem;
        font-weight: bold;
        margin: 5px 0 0 0;
        white-space: nowrap;
        padding: 0;
    }
    .small-box h6 {
        font-size: 0.9rem;
        margin: 0;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .small-box .icon {
        position: absolute;
        top: 15px;
        right: 15px;
        z-index: 0;
        font-size: 70px;
        color: rgba(0,0,0,0.15);
        transition: all .3s linear;
    }
    .small-box:hover .icon {
        font-size: 75px;
    }
</style>