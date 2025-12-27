<?php

namespace backend\tests\unit\models;

use common\models\TipoEquipamento;
use common\models\Equipamento;

class TipoEquipamentoTest extends \Codeception\Test\Unit
{
    /**
     * @var \backend\tests\UnitTester
     */
    protected $tester;

    public function testValidacaoNomeObrigatorio()
    {
        $tipo = new TipoEquipamento();

        $this->assertFalse($tipo->validate());
        $this->assertArrayHasKey('nome', $tipo->errors);
    }

    public function testValidacaoNomeMaximoCaracteres()
    {
        $tipo = new TipoEquipamento();
        $tipo->nome = str_repeat('a', 101); // 101 caracteres

        $this->assertFalse($tipo->validate());
        $this->assertArrayHasKey('nome', $tipo->errors);
    }

    public function testModeloValido()
    {
        $tipo = new TipoEquipamento([
            'nome' => 'Tipo de Teste ' . uniqid(),
        ]);

        $this->assertTrue($tipo->validate());
    }

    public function testCriarTipoEquipamento()
    {
        $nomeUnico = 'Tipo ' . uniqid();
        $id = $this->tester->haveRecord('common\models\TipoEquipamento', [
            'nome' => $nomeUnico,
        ]);

        $tipo = TipoEquipamento::findOne($id);
        $this->assertNotNull($tipo);
        $this->assertEquals($nomeUnico, $tipo->nome);

        return $id;
    }

    /**
     * @depends testCriarTipoEquipamento
     */
    public function testRelacaoComEquipamentos($tipoId)
    {
        $tipo = TipoEquipamento::findOne($tipoId);
        $this->assertNotNull($tipo);

        // Verificar que a relação equipamentos existe
        $this->assertInstanceOf(\yii\db\ActiveQuery::class, $tipo->getEquipamentos());
    }

    public function testMetodosEstaticos()
    {
        // Primeiro, criar alguns tipos para teste
        $this->tester->haveRecord('common\models\TipoEquipamento', [
            'nome' => 'Monitorização',
        ]);

        $this->tester->haveRecord('common\models\TipoEquipamento', [
            'nome' => 'Cirurgia',
        ]);

        // Testar getTiposArray()
        $tiposArray = TipoEquipamento::getTiposArray();

        $this->assertIsArray($tiposArray);
        $this->assertGreaterThan(0, count($tiposArray));

        // Verificar estrutura do array [id => nome]
        foreach ($tiposArray as $id => $nome) {
            $this->assertIsNumeric($id);
            $this->assertIsString($nome);
            $this->assertNotEmpty($nome);
        }
    }

    public function testAtributosLabels()
    {
        $tipo = new TipoEquipamento();
        $labels = $tipo->attributeLabels();

        $this->assertIsArray($labels);
        $this->assertArrayHasKey('id', $labels);
        $this->assertArrayHasKey('nome', $labels);

        $this->assertEquals('ID', $labels['id']);
        $this->assertEquals('Nome', $labels['nome']);
    }

    public function testRegrasValidacao()
    {
        $tipo = new TipoEquipamento();
        $rules = $tipo->rules();

        $this->assertIsArray($rules);

        // Verificar regra de nome obrigatório
        $temNomeObrigatorio = false;
        foreach ($rules as $rule) {
            if (isset($rule[0]) && isset($rule[1])) {
                $atributos = (array)$rule[0];
                $tipoRegra = $rule[1];

                if (in_array('nome', $atributos) && $tipoRegra === 'required') {
                    $temNomeObrigatorio = true;
                    break;
                }
            }
        }

        $this->assertTrue($temNomeObrigatorio, 'Deveria ter regra de nome obrigatório');

        // Verificar regra de tamanho máximo
        $temTamanhoMaximo = false;
        foreach ($rules as $rule) {
            if (isset($rule[0]) && isset($rule[1])) {
                $atributos = (array)$rule[0];
                $tipoRegra = $rule[1];

                if (in_array('nome', $atributos) && $tipoRegra === 'string' && isset($rule['max'])) {
                    $temTamanhoMaximo = true;
                    break;
                }
            }
        }

        $this->assertTrue($temTamanhoMaximo, 'Deveria ter regra de tamanho máximo para nome');
    }
}