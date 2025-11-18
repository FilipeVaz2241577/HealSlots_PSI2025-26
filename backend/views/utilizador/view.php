<?php

/** @var yii\web\View $this */
/** @var common\models\User $model */

use yii\widgets\DetailView;
use yii\bootstrap5\Html;

$this->title = $model->username;
$this->params['breadcrumbs'][] = ['label' => 'Utilizadores', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

// Definir atributos dinamicamente
$attributes = [
    'id',
    'username',
    'email:email',
];

// Verificar se a propriedade role existe
if (property_exists($model, 'role') && $model->role !== null) {
    $attributes[] = [
        'attribute' => 'role',
        'value' => function($model) {
            $roles = [
                'Admin' => 'Administrador',
                'TecnicoSaude' => 'Técnico de Saúde',
                'User' => 'Utilizador'
            ];
            return $roles[$model->role] ?? $model->role;
        }
    ];
}

// Adicionar status
$attributes[] = [
    'attribute' => 'status',
    'value' => function($model) {
        $statuses = [
            $model::STATUS_ACTIVE => 'Ativo',
            $model::STATUS_INACTIVE => 'Inativo'
        ];
        return $statuses[$model->status] ?? $model->status;
    }
];

// Adicionar timestamps
$attributes[] = 'created_at:datetime';
$attributes[] = 'updated_at:datetime';

?>

<div class="utilizador-view">
    <div class="container-fluid pt-4 px-4">
        <div class="row g-4">
            <div class="col-12">
                <div class="bg-light rounded h-100 p-4">

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="mb-0">Detalhes do Utilizador: <?= Html::encode($model->username) ?></h4>
                        <div>
                            <?= Html::a('<i class="fa fa-arrow-left me-2"></i>Voltar', ['index'], ['class' => 'btn btn-secondary']) ?>
                            <?= Html::a('<i class="fa fa-edit me-2"></i>Editar', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
                            <?= Html::a('<i class="fa fa-trash me-2"></i>Eliminar', ['delete', 'id' => $model->id], [
                                'class' => 'btn btn-danger',
                                'data' => [
                                    'confirm' => 'Tem a certeza que deseja eliminar este utilizador?',
                                    'method' => 'post',
                                ],
                            ]) ?>
                        </div>
                    </div>

                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => $attributes,
                        'options' => ['class' => 'table table-striped'],
                    ]) ?>

                </div>
            </div>
        </div>
    </div>
</div>