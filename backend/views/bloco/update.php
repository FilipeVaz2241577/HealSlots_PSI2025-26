<?php
use yii\bootstrap5\Html;
/** @var yii\web\View $this */
/** @var common\models\Bloco $model */

$this->title = 'Atualizar Bloco: ' . $model->nome;
$this->params['breadcrumbs'][] = ['label' => 'Gestão de Blocos Operatórios', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->nome, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Atualizar';
?>

<div class="bloco-update">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card card-primary">

                    <!-- Cabeçalho da página -->
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

                    <!-- Conteúdo do formulário -->
                    <div class="card-body">
                        <!-- Alert informativo -->
                        <div class="alert alert-warning">
                            <h6><i class="fas fa-exclamation-triangle me-2"></i>Atenção</h6>
                            <p class="mb-0">
                                Ao alterar o estado do bloco para <strong>"Inativo"</strong>
                                todas as salas deste bloco ficarão indisponíveis para requisições.
                            </p>
                        </div>

                        <!-- Estatísticas rápidas -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="small-box bg-info">
                                    <div class="inner">
                                        <h3><?= $model->salas ? count($model->salas) : 0 ?></h3>
                                        <p>Total de Salas</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-door-open"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="small-box bg-success">
                                    <div class="inner">
                                        <h3>
                                            <?php
                                            $livres = 0;
                                            if ($model->salas) {
                                                foreach ($model->salas as $sala) {
                                                    if ($sala->estado === 'Livre') $livres++;
                                                }
                                            }
                                            echo $livres;
                                            ?>
                                        </h3>
                                        <p>Salas Livres</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-check-circle"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="small-box bg-danger">
                                    <div class="inner">
                                        <h3>
                                            <?php
                                            $ocupadas = 0;
                                            if ($model->salas) {
                                                foreach ($model->salas as $sala) {
                                                    if ($sala->estado === 'Ocupada') $ocupadas++;
                                                }
                                            }
                                            echo $ocupadas;
                                            ?>
                                        </h3>
                                        <p>Salas Ocupadas</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-times-circle"></i>
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

<!-- CSS adicional -->
<style>
    .small-box {
        border-radius: 0.25rem;
        box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
        display: block;
        margin-bottom: 20px;
        position: relative;
    }
    .small-box > .inner {
        padding: 10px;
    }
    .small-box h3 {
        font-size: 2.2rem;
        font-weight: bold;
        margin: 0 0 10px 0;
        white-space: nowrap;
        padding: 0;
    }
    .small-box p {
        font-size: 0.9rem;
        margin: 0;
    }
    .small-box .icon {
        position: absolute;
        top: 10px;
        right: 10px;
        z-index: 0;
        font-size: 70px;
        color: rgba(0,0,0,0.15);
        transition: all .3s linear;
    }
    .small-box:hover .icon {
        font-size: 75px;
    }
    .bg-info { background-color: #17a2b8 !important; color: white; }
    .bg-success { background-color: #28a745 !important; color: white; }
    .bg-danger { background-color: #dc3545 !important; color: white; }
    .bg-warning { background-color: #ffc107 !important; color: #212529; }
</style>