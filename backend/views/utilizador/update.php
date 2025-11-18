<?php
use yii\bootstrap5\Html;
/** @var yii\web\View $this */
/** @var common\models\User $model */

// TODO: Definir título da página
$this->title = 'Atualizar Utilizador: ' . $model->username;
$this->params['breadcrumbs'][] = ['label' => 'Utilizadores', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->username, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Atualizar';
?>

<div class="utilizador-update">
    <div class="container-fluid pt-4 px-4">
        <div class="row g-4">
            <div class="col-12">
                <div class="bg-light rounded h-100 p-4">

                    <!-- TODO: Cabeçalho da página -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="mb-0"><?= Html::encode($this->title) ?></h4>
                        <div>
                            <?= Html::a('<i class="fa fa-arrow-left me-2"></i>Voltar', ['index'], ['class' => 'btn btn-secondary']) ?>
                            <?= Html::a('<i class="fa fa-eye me-2"></i>Ver Detalhes', ['view', 'id' => $model->id], ['class' => 'btn btn-info']) ?>
                        </div>
                    </div>

                    <!-- TODO: Formulário de atualização -->
                    <?= $this->render('_form', [
                        'model' => $model,
                    ]) ?>

                </div>
            </div>
        </div>
    </div>
</div>