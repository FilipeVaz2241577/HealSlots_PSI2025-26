<?php

namespace backend\tests\unit\models;

use common\models\Equipamento;

class EquipamentoTest extends \Codeception\Test\Unit
{
    /**
     * @var \backend\tests\UnitTester
     */
    protected $tester;

    public function testValidacaoCamposObrigatorios()
    {
        $equipamento = new Equipamento();

        $this->assertFalse($equipamento->validate());
        $this->assertArrayHasKey('numeroSerie', $equipamento->errors);
        $this->assertArrayHasKey('tipoEquipamento_id', $equipamento->errors);
        $this->assertArrayHasKey('equipamento', $equipamento->errors);
        $this->assertArrayHasKey('estado', $equipamento->errors);
    }

    public function testValidacaoEstadoValido()
    {
        $equipamento = new Equipamento();
        $equipamento->numeroSerie = 'SN12345';
        $equipamento->tipoEquipamento_id = 1;
        $equipamento->equipamento = 'Monitor';

        // Testar com estado realmente inválido
        // Primeiro, vamos ver quais são os estados válidos
        $estadosValidos = array_keys(Equipamento::optsEstado());

        // Vamos usar um estado que definitivamente não está na lista
        $equipamento->estado = 'ESTADO_INEXISTENTE_XYZ';

        $this->assertFalse($equipamento->validate(),
            'O estado "' . $equipamento->estado . '" deveria ser inválido. Estados válidos: ' . implode(', ', $estadosValidos));

        $this->assertArrayHasKey('estado', $equipamento->errors);
    }

    public function testValidacaoEstadoValidoComEstadosConhecidos()
    {
        $equipamento = new Equipamento();
        $equipamento->numeroSerie = 'SN12345';
        $equipamento->tipoEquipamento_id = 1;
        $equipamento->equipamento = 'Monitor';

        // Testar com cada estado válido
        $estadosValidos = [
            'Operacional',
            'Em Manutenção',
            'Em Uso'
        ];

        foreach ($estadosValidos as $estado) {
            $equipamento->estado = $estado;
            $this->assertTrue($equipamento->validate(),
                "Estado '$estado' deveria ser válido. Erros: " . print_r($equipamento->errors, true));
        }

        // Testar com estado inválido
        $equipamento->estado = 'EstadoInvalidoQualquer';
        $this->assertFalse($equipamento->validate());
        $this->assertArrayHasKey('estado', $equipamento->errors);
    }

    public function testModeloValido()
    {
        // Usar um número de série único para evitar conflitos com a regra 'unique'
        $numeroSerieUnico = 'SN-' . uniqid();

        $equipamento = new Equipamento([
            'numeroSerie' => $numeroSerieUnico,
            'tipoEquipamento_id' => 1,
            'equipamento' => 'Monitor Cardíaco',
            'estado' => 'Operacional',
        ]);

        $this->assertTrue($equipamento->validate(),
            'Erros: ' . print_r($equipamento->errors, true));
    }

    public function testMetodosEstaticos()
    {
        $opts = Equipamento::optsEstado();

        $this->assertIsArray($opts);
        $this->assertGreaterThan(0, count($opts), 'Deve ter pelo menos uma opção de estado');

        // Verificar se pelo menos um estado esperado existe
        $estadosEsperados = ['Operacional', 'Em Manutenção', 'Em Uso'];
        $encontrouEstado = false;

        foreach ($estadosEsperados as $estado) {
            if (isset($opts[$estado])) {
                $encontrouEstado = true;
                break;
            }
        }

        $this->assertTrue($encontrouEstado,
            'Nenhum dos estados esperados encontrado. Opções: ' . print_r(array_keys($opts), true));
    }
}