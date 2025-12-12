<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "bloco".
 *
 * @property int $id
 * @property string $nome
 * @property string|null $estado
 *
 * @property Sala[] $salas
 */
class Bloco extends \yii\db\ActiveRecord
{

    /**
     * ENUM field values
     */
    const ESTADO_ATIVO = 'ativo';
    const ESTADO_DESATIVADO = 'desativado';
    const ESTADO_MANUTENCAO = 'manutencao';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'bloco';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['estado'], 'default', 'value' => 'ativo'],
            [['nome'], 'required'],
            [['estado'], 'string'],
            [['nome'], 'string', 'max' => 100],
            ['estado', 'in', 'range' => array_keys(self::optsEstado())],
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
        ];
    }

    /**
     * Gets query for [[Salas]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSalas()
    {
        return $this->hasMany(Sala::class, ['bloco_id' => 'id']);
    }

    /**
     * column estado ENUM value labels
     * @return string[]
     */
    public static function optsEstado()
    {
        return [
            self::ESTADO_ATIVO => 'Ativo',
            self::ESTADO_DESATIVADO => 'Desativado',
            self::ESTADO_MANUTENCAO => 'Manutenção',
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
    public function isEstadoAtivo()
    {
        return $this->estado === self::ESTADO_ATIVO;
    }

    public function setEstadoToAtivo()
    {
        $this->estado = self::ESTADO_ATIVO;
    }

    /**
     * @return bool
     */
    public function isEstadoDesativado()
    {
        return $this->estado === self::ESTADO_DESATIVADO;
    }

    public function setEstadoToDesativado()
    {
        $this->estado = self::ESTADO_DESATIVADO;
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

}