<?php

namespace common\models;

use Yii;

/**
 * Esta é a classe de modelo para a tabela "requisicao_equipamento".
 * Representa a tabela de ligação (junction table) entre requisições e equipamentos.
 * Esta tabela permite uma relação muitos-para-muitos entre requisições e equipamentos.
 *
 * @property int $idRequisicao - ID da requisição (chave estrangeira para tabela requisicao)
 * @property int $idEquipamento - ID do equipamento (chave estrangeira para tabela equipamento)
 *
 * @property Equipamento $idEquipamento0 - Relação com o equipamento associado
 * @property Requisicao $idRequisicao0 - Relação com a requisição associada
 */
class RequisicaoEquipamento extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     * Retorna o nome da tabela associada a este modelo
     */
    public static function tableName()
    {
        return 'requisicao_equipamento'; // Nome da tabela de ligação na base de dados
    }

    /**
     * {@inheritdoc}
     * Define as regras de validação para os atributos do modelo
     */
    public function rules()
    {
        return [
            // Ambos os campos são obrigatórios
            [['idRequisicao', 'idEquipamento'], 'required'],
            // Ambos os campos devem ser inteiros
            [['idRequisicao', 'idEquipamento'], 'integer'],
            // A combinação idRequisicao + idEquipamento deve ser única
            // Evita duplicação do mesmo equipamento na mesma requisição
            [['idRequisicao', 'idEquipamento'], 'unique', 'targetAttribute' => ['idRequisicao', 'idEquipamento']],
            // Validação de existência da chave estrangeira para requisição
            [['idRequisicao'], 'exist', 'skipOnError' => true, 'targetClass' => Requisicao::class, 'targetAttribute' => ['idRequisicao' => 'id']],
            // Validação de existência da chave estrangeira para equipamento
            [['idEquipamento'], 'exist', 'skipOnError' => true, 'targetClass' => Equipamento::class, 'targetAttribute' => ['idEquipamento' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     * Define os rótulos para os atributos (usados em formulários)
     */
    public function attributeLabels()
    {
        return [
            'idRequisicao' => 'ID Requisição', // Rótulo para o ID da requisição
            'idEquipamento' => 'ID Equipamento', // Rótulo para o ID do equipamento
        ];
    }

    /**
     * Obtém a relação com o equipamento associado
     * @return \yii\db\ActiveQuery - Query para obter o equipamento
     */
    public function getIdEquipamento0()
    {
        return $this->hasOne(Equipamento::class, ['id' => 'idEquipamento']);
    }

    /**
     * Obtém a relação com a requisição associada
     * @return \yii\db\ActiveQuery - Query para obter a requisição
     */
    public function getIdRequisicao0()
    {
        return $this->hasOne(Requisicao::class, ['id' => 'idRequisicao']);
    }
}