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
<<<<<<< HEAD
    const ESTADO_INATIVO = 'inativo';
    const ESTADO_MANUTENCAO = 'manutencao';
=======
    const ESTADO_DESATIVADO = 'desativado';
>>>>>>> origin/filipe

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
<<<<<<< HEAD
            [['nome'], 'required'],
            [['estado'], 'string'],
            [['nome'], 'string', 'max' => 100],
            ['estado', 'in', 'range' => array_keys(self::optsEstado())],
=======
            [['nome'], 'required', 'message' => 'O nome do bloco é obrigatório.'],
            [['estado'], 'string'],
            [['nome'], 'string', 'max' => 100, 'tooLong' => 'O nome não pode exceder 100 caracteres.'],
            ['estado', 'in', 'range' => array_keys(self::optsEstado()), 'message' => 'Estado inválido.'],
            // Validação de unicidade
            [['nome'], 'unique', 'message' => 'Já existe um bloco com este nome. Por favor, escolha outro nome.'],
>>>>>>> origin/filipe
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
<<<<<<< HEAD
            self::ESTADO_INATIVO => 'Inativo',
            self::ESTADO_MANUTENCAO => 'Manutenção',
=======
            self::ESTADO_DESATIVADO => 'Desativado',
>>>>>>> origin/filipe
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
<<<<<<< HEAD
=======
     * Sobrescrevendo a validação beforeSave para garantir unicidade
     */
    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        // Verificar unicidade antes de salvar
        if ($this->isNewRecord) {
            $exists = self::find()->where(['nome' => $this->nome])->exists();
            if ($exists) {
                $this->addError('nome', 'Já existe um bloco com este nome.');
                return false;
            }
        } else {
            $exists = self::find()
                ->where(['nome' => $this->nome])
                ->andWhere(['!=', 'id', $this->id])
                ->exists();
            if ($exists) {
                $this->addError('nome', 'Já existe um bloco com este nome.');
                return false;
            }
        }

        return true;
    }

    /**
>>>>>>> origin/filipe
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
<<<<<<< HEAD
    public function isEstadoInativo()
    {
        return $this->estado === self::ESTADO_INATIVO;
    }

    public function setEstadoToInativo()
    {
        $this->estado = self::ESTADO_INATIVO;
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
=======
    public function isEstadoDesativado()
    {
        return $this->estado === self::ESTADO_DESATIVADO;
    }

    public function setEstadoToDesativado()
    {
        $this->estado = self::ESTADO_DESATIVADO;
    }

>>>>>>> origin/filipe
}