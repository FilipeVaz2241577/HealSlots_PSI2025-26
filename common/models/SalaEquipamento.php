<?php

namespace common\models;

use Yii;

/**
 * Esta é a classe de modelo para a tabela "sala_equipamento".
 * Representa a tabela de ligação (junction table) entre salas e equipamentos.
 * Esta tabela permite uma relação muitos-para-muitos entre salas e equipamentos.
 *
 * @property int $idSala - ID da sala (chave estrangeira para tabela sala)
 * @property int $idEquipamento - ID do equipamento (chave estrangeira para tabela equipamento)
 *
 * @property Sala $sala - Relação com a sala associada
 * @property Equipamento $equipamento - Relação com o equipamento associado
 */
class SalaEquipamento extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     * Retorna o nome da tabela associada a este modelo
     */
    public static function tableName()
    {
        return '{{%sala_equipamento}}'; // Nome da tabela de ligação na base de dados (com prefixo se aplicável)
    }

    /**
     * {@inheritdoc}
     * Define as regras de validação para os atributos do modelo
     */
    public function rules()
    {
        return [
            // Ambos os campos são obrigatórios
            [['idSala', 'idEquipamento'], 'required'],
            // Ambos os campos devem ser inteiros
            [['idSala', 'idEquipamento'], 'integer'],
            // A combinação idSala + idEquipamento deve ser única
            // Evita duplicação do mesmo equipamento na mesma sala
            [['idSala', 'idEquipamento'], 'unique', 'targetAttribute' => ['idSala', 'idEquipamento']],
            // Validação de existência da chave estrangeira para sala
            [['idSala'], 'exist', 'skipOnError' => true, 'targetClass' => Sala::class, 'targetAttribute' => ['idSala' => 'id']],
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
            'idSala' => 'Sala', // Rótulo para a sala
            'idEquipamento' => 'Equipamento', // Rótulo para o equipamento
        ];
    }

    /**
     * Obtém a relação com a sala associada
     * @return \yii\db\ActiveQuery - Query para obter a sala
     */
    public function getSala()
    {
        return $this->hasOne(Sala::class, ['id' => 'idSala']);
    }

    /**
     * Obtém a relação com o equipamento associado
     * @return \yii\db\ActiveQuery - Query para obter o equipamento
     */
    public function getEquipamento()
    {
        return $this->hasOne(Equipamento::class, ['id' => 'idEquipamento']);
    }

    /**
     * Remove todas as associações de uma sala específica
     * @param int $salaId - ID da sala
     * @return int - Número de registos eliminados
     */
    public static function removeAllBySala($salaId)
    {
        return self::deleteAll(['idSala' => $salaId]);
    }

    /**
     * Remove todas as associações de um equipamento específico
     * @param int $equipamentoId - ID do equipamento
     * @return int - Número de registos eliminados
     */
    public static function removeAllByEquipamento($equipamentoId)
    {
        return self::deleteAll(['idEquipamento' => $equipamentoId]);
    }

    /**
     * Verifica se uma associação específica entre sala e equipamento já existe
     * @param int $salaId - ID da sala
     * @param int $equipamentoId - ID do equipamento
     * @return bool - True se a associação existir
     */
    public static function associationExists($salaId, $equipamentoId)
    {
        return self::find()
            ->where(['idSala' => $salaId, 'idEquipamento' => $equipamentoId])
            ->exists(); // Retorna true se encontrar pelo menos um registo
    }

    /**
     * Obtém todos os equipamentos associados a uma sala específica
     * @param int $salaId - ID da sala
     * @return Equipamento[] - Array de objetos Equipamento
     */
    public static function getEquipamentosBySala($salaId)
    {
        return Equipamento::find()
            ->innerJoin('sala_equipamento', 'equipamento.id = sala_equipamento.idEquipamento')
            ->where(['sala_equipamento.idSala' => $salaId])
            ->all(); // Retorna todos os equipamentos
    }

    /**
     * Obtém todas as salas associadas a um equipamento específico
     * @param int $equipamentoId - ID do equipamento
     * @return Sala[] - Array de objetos Sala
     */
    public static function getSalasByEquipamento($equipamentoId)
    {
        return Sala::find()
            ->innerJoin('sala_equipamento', 'sala.id = sala_equipamento.idSala')
            ->where(['sala_equipamento.idEquipamento' => $equipamentoId])
            ->all(); // Retorna todas as salas
    }
}