<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "sala".
 *
 * @property int $id
 * @property string $nome
 * @property string $estado
 * @property int $bloco_id
 *
 * @property Bloco $bloco
 */
class Sala extends \yii\db\ActiveRecord
{
    /**
     * ENUM field values
     */
    const ESTADO_LIVRE = 'Livre';
    const ESTADO_EM_USO = 'EmUso';
    const ESTADO_MANUTENCAO = 'Manutencao';
    const ESTADO_DESATIVADA = 'Desativada';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'sala';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['nome', 'bloco_id'], 'required'],
            [['bloco_id'], 'integer'],
            [['nome'], 'string', 'max' => 100],
            [['estado'], 'string', 'max' => 20],
            [['estado'], 'default', 'value' => self::ESTADO_LIVRE],
            ['estado', 'in', 'range' => array_keys(self::optsEstado())],
            [['bloco_id'], 'exist', 'skipOnError' => true, 'targetClass' => Bloco::class, 'targetAttribute' => ['bloco_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'nome' => 'Nome',
            'estado' => 'Estado',
            'bloco_id' => 'Bloco',
            'blocoName' => 'Bloco',
        ];
    }

    /**
     * Gets query for [[Bloco]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBloco()
    {
        return $this->hasOne(Bloco::class, ['id' => 'bloco_id']);
    }

    /**
     * Get bloco name
     */
    public function getBlocoName()
    {
        return $this->bloco ? $this->bloco->nome : 'N/A';
    }

    /**
     * column estado ENUM value labels
     * @return string[]
     */
    public static function optsEstado()
    {
        return [
            self::ESTADO_LIVRE => 'Livre',
            self::ESTADO_EM_USO => 'Em Uso',
            self::ESTADO_MANUTENCAO => 'Manutenção',
            self::ESTADO_DESATIVADA => 'Desativada',
        ];
    }

    /**
     * @return string
     */
    public function getEstadoLabel()
    {
        return self::optsEstado()[$this->estado] ?? 'Desconhecido';
    }

    /**
     * @return bool
     */
    public function isEstadoLivre()
    {
        return $this->estado === self::ESTADO_LIVRE;
    }

    public function setEstadoToLivre()
    {
        $this->estado = self::ESTADO_LIVRE;
    }

    /**
     * @return bool
     */
    public function isEstadoEmUso()
    {
        return $this->estado === self::ESTADO_EM_USO;
    }

    public function setEstadoToEmUso()
    {
        $this->estado = self::ESTADO_EM_USO;
    }

    /**
     * @return bool
     */
    public function isEstadoManutencao()
    {
        return $this->estado === self::ESTADO_MANUTENCAO;
    }

    public function setEstadoToManutencao()
    {
        $this->estado = self::ESTADO_MANUTENCAO;
    }

    /**
     * @return bool
     */
    public function isEstadoDesativada()
    {
        return $this->estado === self::ESTADO_DESATIVADA;
    }

    public function setEstadoToDesativada()
    {
        $this->estado = self::ESTADO_DESATIVADA;
    }

    /**
     * Get all salas count by estado
     */
    public static function getCountByEstado()
    {
        return self::find()
            ->select(['estado', 'COUNT(*) as count'])
            ->groupBy('estado')
            ->indexBy('estado')
            ->column();
    }
}