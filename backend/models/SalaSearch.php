<?php

namespace backend\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Sala;

/**
 * Modelo de pesquisa para Salas
 * Extende o modelo Sala para adicionar funcionalidades de pesquisa e filtragem
 * Segue uma abordagem simplificada com JOIN condicional para melhor performance
 */
class SalaSearch extends Sala
{
    /**
     * @var string Nome do bloco para pesquisa
     * Atributo virtual que permite filtrar pelo nome do bloco relacionado
     */
    public $blocoName;

    /**
     * Define regras de validação para os parâmetros de pesquisa
     * @return array Regras de validação
     */
    public function rules()
    {
        return [
            [['id', 'bloco_id'], 'integer'],              // IDs devem ser inteiros
            [['nome', 'estado', 'blocoName'], 'safe'],    // Campos seguros para pesquisa
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
     * Usa uma abordagem otimizada com JOIN condicional para melhor performance
     * @param array $params Parâmetros de pesquisa do formulário
     * @return ActiveDataProvider Provedor de dados com os resultados filtrados
     */
    public function search($params)
    {
        // Consulta SIMPLES sem JOIN inicial - otimização de performance
        // O JOIN só será adicionado se necessário (filtro por nome do bloco)
        $query = Sala::find();

        // Configuração do provedor de dados
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['nome' => SORT_ASC], // Ordenação padrão: nome alfabético
            ],
            'pagination' => [
                'pageSize' => 20, // 20 registos por página
            ],
        ]);

        // Carrega os parâmetros de pesquisa no modelo
        $this->load($params);

        // Se a validação falhar, retorna o provedor de dados sem filtros
        if (!$this->validate()) {
            return $dataProvider;
        }

        // Aplica filtros básicos à consulta
        // Nota: Não é necessário prefixo de tabela pois não há JOINs ainda
        $query->andFilterWhere([
            'id' => $this->id,                // Filtro por ID exato
            'bloco_id' => $this->bloco_id,    // Filtro por bloco
            'estado' => $this->estado,        // Filtro por estado
        ]);

        // Filtro por correspondência parcial no nome da sala
        $query->andFilterWhere(['like', 'nome', $this->nome]);

        // JOIN CONDICIONAL: Adicionar JOIN apenas se precisar filtrar por nome do bloco
        // Esta otimização evita JOINs desnecessários quando não são precisos
        if (!empty($this->blocoName)) {
            // Adiciona o JOIN dinamicamente apenas quando o filtro é utilizado
            $query->joinWith(['bloco']);
            // Aplica filtro por nome do bloco
            $query->andFilterWhere(['like', 'bloco.nome', $this->blocoName]);

            // NOTA: Agora que temos JOIN, poderiam ocorrer conflitos de colunas
            // Mas como o JOIN é adicionado no fim, os filtros anteriores já foram aplicados
            // e usaram apenas a tabela 'sala', evitando ambiguidade
        }

        return $dataProvider;
    }
}