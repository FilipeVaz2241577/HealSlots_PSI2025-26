<?php

namespace backend\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Equipamento;

/**
 * Modelo de pesquisa para Equipamentos
 * Extende o modelo Equipamento para adicionar funcionalidades de pesquisa e filtragem
 * Inclui capacidade de pesquisar por tipo de equipamento através de JOIN
 */
class EquipamentoSearch extends Equipamento
{
    /**
     * @var string Nome do tipo de equipamento para pesquisa
     * Atributo virtual que permite filtrar pelo nome do tipo de equipamento
     */
    public $tipoEquipamentoName;

    /**
     * Define regras de validação para os parâmetros de pesquisa
     * @return array Regras de validação
     */
    public function rules()
    {
        return [
            [['id', 'tipoEquipamento_id'], 'integer'], // ID e tipoEquipamento_id devem ser inteiros
            [['numeroSerie', 'equipamento', 'estado', 'tipoEquipamentoName'], 'safe'], // Campos seguros para pesquisa
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
     * Inclui JOIN com tipoEquipamento para permitir filtragem por nome do tipo
     * @param array $params Parâmetros de pesquisa do formulário
     * @return ActiveDataProvider Provedor de dados com os resultados filtrados
     */
    public function search($params)
    {
        // Consulta base com JOIN para incluir dados do tipo de equipamento
        $query = Equipamento::find()->joinWith(['tipoEquipamento']);

        // Configuração do provedor de dados
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['id' => SORT_DESC], // Ordenação padrão: ID mais recente primeiro
            ],
            'pagination' => [
                'pageSize' => 20, // 20 registos por página
            ],
        ]);

        // Configurar ordenação para o campo virtual tipoEquipamentoName
        // Mapeia a ordenação do atributo virtual para o campo real na tabela relacionada
        $dataProvider->sort->attributes['tipoEquipamentoName'] = [
            'asc' => ['tipoEquipamento.nome' => SORT_ASC],   // Ordenação ascendente
            'desc' => ['tipoEquipamento.nome' => SORT_DESC], // Ordenação descendente
        ];

        // Carrega os parâmetros de pesquisa no modelo
        $this->load($params);

        // Se a validação falhar, retorna o provedor de dados sem filtros
        if (!$this->validate()) {
            // Se houver erro de validação, remover JOIN para evitar ambiguidade de colunas
            $query->joinWith(['tipoEquipamento' => function($q) {
                $q->where('0=1'); // JOIN vazio que não afeta os resultados
            }]);
            return $dataProvider;
        }

        // Aplica filtros à consulta, especificando tabelas para evitar ambiguidade
        $query->andFilterWhere([
            'equipamento.id' => $this->id, // Filtro por ID exato (especificar tabela)
            'equipamento.tipoEquipamento_id' => $this->tipoEquipamento_id, // Filtro por tipo de equipamento
            'equipamento.estado' => $this->estado, // Filtro por estado exato
        ]);

        // Filtros por correspondência parcial (LIKE)
        $query->andFilterWhere(['like', 'equipamento.numeroSerie', $this->numeroSerie])  // Filtro por número de série
        ->andFilterWhere(['like', 'equipamento.equipamento', $this->equipamento])    // Filtro por nome do equipamento
        ->andFilterWhere(['like', 'tipoEquipamento.nome', $this->tipoEquipamentoName]); // Filtro por nome do tipo de equipamento

        return $dataProvider;
    }
}