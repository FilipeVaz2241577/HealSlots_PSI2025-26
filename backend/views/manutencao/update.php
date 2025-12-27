<?php

/** @var yii\web\View $this */
/** @var common\models\Manutencao $model */

use yii\bootstrap5\Html;

$this->title = 'Atualizar Manutenção: #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Gestão de Manutenções', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => 'Manutenção #' . $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Atualizar';
?>

<div class="manutencao-update">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?= Html::encode($this->title) ?></h1>
        <?= Html::a('<i class="fa fa-arrow-left me-2"></i>Voltar', ['view', 'id' => $model->id], ['class' => 'btn btn-secondary']) ?>
    </div>

    <div class="container-fluid pt-4 px-4">
        <div class="bg-light rounded p-4">
<<<<<<< HEAD
=======
            <div class="alert alert-warning">
                <i class="fa fa-exclamation-triangle me-2"></i>
                <strong>Atenção:</strong> Ao atualizar, apenas são mostrados equipamentos/salas disponíveis.
                O item atual está marcado como "(atual)" para permitir mantê-lo.
            </div>

>>>>>>> origin/filipe
            <?= $this->render('_form', [
                'model' => $model,
                'tecnicosList' => $tecnicosList,
                'equipamentosList' => $equipamentosList,
                'salasList' => $salasList,
            ]) ?>
        </div>
    </div>
</div>