<?php

namespace backend\tests\unit\models;

use common\models\Sala;
use common\models\Bloco;

class SalaTest extends \Codeception\Test\Unit
{
    /**
     * @var \backend\tests\UnitTester
     */
    protected $tester;

    protected function _before()
    {
        // Criar um bloco para usar nos testes
        $this->blocoId = $this->tester->haveRecord('common\models\Bloco', [
            'nome' => 'Bloco para Testes ' . uniqid(),
            'estado' => 'ativo',
        ]);
    }

    protected function _after()
    {
        // Limpar se necessário
    }

    public function testValidacaoCamposObrigatorios()
    {
        $sala = new Sala();

        $this->assertFalse($sala->validate());
        $this->assertArrayHasKey('nome', $sala->errors);
        $this->assertArrayHasKey('bloco_id', $sala->errors);
    }

    public function testValidacaoEstadoValido()
    {
        $sala = new Sala();
        $sala->nome = 'Sala 101';
        $sala->bloco_id = $this->blocoId; // Usar bloco real

        // Usar um estado que definitivamente não existe
        $sala->estado = 'ESTADO_INVALIDO_XYZ';

        $this->assertFalse($sala->validate());
        $this->assertArrayHasKey('estado', $sala->errors);
    }

    public function testValidacaoEstadosConhecidos()
    {
        $estadosValidos = ['Livre', 'EmUso', 'Manutencao', 'Desativada'];

        foreach ($estadosValidos as $estado) {
            // Criar uma nova sala para cada teste
            $sala = new Sala([
                'nome' => 'Sala Teste ' . $estado . ' ' . uniqid(),
                'bloco_id' => $this->blocoId, // Usar bloco real
                'estado' => $estado,
            ]);

            $this->assertTrue($sala->validate(),
                "Estado '$estado' deveria ser válido. Erros: " . print_r($sala->errors, true));
        }
    }

    public function testModeloValido()
    {
        $sala = new Sala([
            'nome' => 'Sala Teste ' . uniqid(),
            'bloco_id' => $this->blocoId, // Usar bloco real
            'estado' => 'Livre',
        ]);

        $this->assertTrue($sala->validate(),
            'Erros: ' . print_r($sala->errors, true));
    }

    public function testMetodosEstaticos()
    {
        $opts = Sala::optsEstado();

        $this->assertIsArray($opts);
        $this->assertGreaterThan(0, count($opts));

        // Verificar estados esperados
        $estadosEsperados = ['Livre', 'EmUso', 'Manutencao', 'Desativada'];
        foreach ($estadosEsperados as $estado) {
            $this->assertArrayHasKey($estado, $opts,
                "Estado '$estado' deveria estar nas opções");
        }
    }

    public function testMetodosDeEstado()
    {
        $sala = new Sala();

        // Testar métodos getEstadoLabel com diferentes estados
        $sala->estado = 'Livre';
        $this->assertEquals('Livre', $sala->getEstadoLabel());

        $sala->estado = 'EmUso';
        $this->assertEquals('Em Uso', $sala->getEstadoLabel());

        $sala->estado = 'Manutencao';
        $this->assertEquals('Em Manutenção', $sala->getEstadoLabel());
    }
}