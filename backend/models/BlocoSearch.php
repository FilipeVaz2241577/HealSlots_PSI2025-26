<?php

namespace backend\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Bloco;

/**
 * Modelo de pesquisa para Blocos
 * Extende o modelo Bloco para adicionar funcionalidades de pesquisa e filtragem
 */
class BlocoSearch extends Bloco
{
    /**
     * Define regras de validação para os parâmetros de pesquisa
     * @return array Regras de validação
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],          // ID deve ser inteiro
            [['nome', 'estado'], 'safe'], // Nome e estado são campos seguros para pesquisa
        ];
    }

    /**
     * Define os cenários disponíveis para este modelo de pesquisa
     * Herda os cenários da classe base Model
     * @return array Cenários disponíveis
     */
    public function scenarios()
    {
        return Model::scenarios();
    }

    /**
     * Cria o provedor de dados com os filtros aplicados
     * @param array $params Parâmetros de pesquisa do formulário
     * @return ActiveDataProvider Provedor de dados com os resultados filtrados
     */
    public function search($params)
    {
        // Consulta base - todos os blocos
        $query = Bloco::find();

        // Configuração do provedor de dados
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['nome' => SORT_ASC]] // Ordenação padrão por nome (A-Z)
        ]);

        // Carrega os parâmetros de pesquisa no modelo
        $this->load($params);

        // Se a validação falhar, retorna o provedor de dados sem filtros
        if (!$this->validate()) {
            return $dataProvider;
        }

        // Aplica filtros à consulta
        $query->andFilterWhere([
            'id' => $this->id,          // Filtro por ID exato
            'estado' => $this->estado,   // Filtro por estado exato
        ]);

        // Filtro por nome (pesquisa parcial)
        $query->andFilterWhere(['like', 'nome', $this->nome]);

        return $dataProvider;
    }
}