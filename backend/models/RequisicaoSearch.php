<?php

namespace backend\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Requisicao;

class RequisicaoSearch extends Requisicao
{
    public $sala_nome;
    public $user_name;
    public $bloco_nome;
    // Remover as propriedades range e manter apenas as datas simples
    // public $dataInicio_range;
    // public $dataFim_range;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'user_id', 'sala_id'], 'integer'],
            [['status'], 'string'],
            [['dataInicio', 'dataFim', 'sala_nome', 'user_name', 'bloco_nome'], 'safe'],
            // Remover range das regras
            // [['dataInicio_range', 'dataFim_range'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Requisicao::find()
            ->joinWith(['sala', 'user'])
            ->leftJoin('bloco', 'sala.bloco_id = bloco.id');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['dataInicio' => SORT_DESC],
                'attributes' => [
                    'id',
                    'dataInicio',
                    'dataFim',
                    'status',
                    'sala_nome' => [
                        'asc' => ['sala.nome' => SORT_ASC],
                        'desc' => ['sala.nome' => SORT_DESC],
                    ],
                    'user_name' => [
                        'asc' => ['user.username' => SORT_ASC],
                        'desc' => ['user.username' => SORT_DESC],
                    ],
                    'bloco_nome' => [
                        'asc' => ['bloco.nome' => SORT_ASC],
                        'desc' => ['bloco.nome' => SORT_DESC],
                    ],
                ],
            ],
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        // Filtros normais
        $query->andFilterWhere([
            'requisicao.id' => $this->id,
            'requisicao.user_id' => $this->user_id,
            'requisicao.sala_id' => $this->sala_id,
            'requisicao.status' => $this->status,
        ]);

        // Filtro por data de início específica - converter dd/mm/aaaa para aaaa-mm-dd
        if ($this->dataInicio) {
            $dataFormatada = $this->converterDataParaMySQL($this->dataInicio);
            if ($dataFormatada) {
                $query->andFilterWhere(['DATE(requisicao.dataInicio)' => $dataFormatada]);
            }
        }

        // Filtro por data de fim específica - converter dd/mm/aaaa para aaaa-mm-dd
        if ($this->dataFim) {
            $dataFormatada = $this->converterDataParaMySQL($this->dataFim);
            if ($dataFormatada) {
                $query->andFilterWhere(['DATE(requisicao.dataFim)' => $dataFormatada]);
            }
        }

        // Filtros de texto
        $query->andFilterWhere(['like', 'sala.nome', $this->sala_nome])
            ->andFilterWhere(['like', 'user.username', $this->user_name])
            ->andFilterWhere(['like', 'bloco.nome', $this->bloco_nome]);

        return $dataProvider;
    }

    /**
     * Converte data de dd/mm/aaaa para aaaa-mm-dd
     * @param string $data Data no formato dd/mm/aaaa
     * @return string|null Data no formato aaaa-mm-dd ou null se inválida
     */
    private function converterDataParaMySQL($data)
    {
        if (empty($data)) {
            return null;
        }

        // Verificar se já está no formato aaaa-mm-dd
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $data)) {
            return $data;
        }

        // Tentar converter de dd/mm/aaaa para aaaa-mm-dd
        $partes = explode('/', $data);
        if (count($partes) === 3) {
            $dia = $partes[0];
            $mes = $partes[1];
            $ano = $partes[2];

            // Validar se são números
            if (is_numeric($dia) && is_numeric($mes) && is_numeric($ano)) {
                // Garantir 2 dígitos para dia e mês
                $dia = str_pad($dia, 2, '0', STR_PAD_LEFT);
                $mes = str_pad($mes, 2, '0', STR_PAD_LEFT);

                // Verificar se é uma data válida
                if (checkdate((int)$mes, (int)$dia, (int)$ano)) {
                    return $ano . '-' . $mes . '-' . $dia;
                }
            }
        }

        return null;
    }
}