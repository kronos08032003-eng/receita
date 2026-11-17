 
<?php
 
use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\ServerRequestFactory;
 
class LancamentosTest extends TestCase
{
    private function getApp()
    {
        return require __DIR__ . '/../src/app.php';
    }
 
    // Função auxiliar para enviar as requisições
    private function enviarRequisicao($metodo, $rota, $dados = null)
    {
        $app = $this->getApp();
 
        $request = (new ServerRequestFactory())
            ->createServerRequest($metodo, $rota)
            ->withHeader('Content-Type', 'application/json');
 
        if ($dados !== null) {
            $request->getBody()->write(json_encode($dados));
            $request->getBody()->rewind();
 
            $request = $request->withParsedBody($dados);
        }
 
        return $app->handle($request);
    }
 
    // Dados válidos para cadastrar um lançamento
    private function dadosLancamento()
    {
        return [
            'descricao' => 'Transporte',
            'valor' => 150.00,
            'data' => '2026-11-10',
            'tipo' => 'despesa',
            'id_categoria' => 3
        ];
    }
 
    // 1. GET - Listar todos os lançamentos
    public function test_deve_listar_todos_os_lancamentos(): void
    {
        $response = $this->enviarRequisicao('GET', '/lancamentos');
 
        $this->assertEquals(200, $response->getStatusCode());
 
        $corpo = json_decode((string) $response->getBody(), true);
 
        $this->assertIsArray($corpo);
        $this->assertCount(2, $corpo);
    }
 
    // 2. GET - Buscar lançamento por ID
    public function test_deve_buscar_lancamento_por_id(): void
    {
        $response = $this->enviarRequisicao('GET', '/lancamentos/1');
 
        $this->assertEquals(200, $response->getStatusCode());
 
        $corpo = json_decode((string) $response->getBody(), true);
 
        $this->assertEquals(1, $corpo['id']);
        $this->assertEquals('Salario', $corpo['descricao']);
    }
 
    // 3. POST - Cadastrar lançamento
    public function test_deve_cadastrar_lancamento(): void
    {
        $dados = $this->dadosLancamento();
 
        $response = $this->enviarRequisicao('POST', '/lancamentos', $dados);
 
        $this->assertEquals(201, $response->getStatusCode());
 
        $corpo = json_decode((string) $response->getBody(), true);
 
        $this->assertEquals('Transporte', $corpo['descricao']);
        $this->assertEquals(150.00, $corpo['valor']);
        $this->assertArrayHasKey('id', $corpo);
    }
 
    // 4. PUT - Atualizar lançamento
    public function test_deve_atualizar_lancamento(): void
    {
        $dados = [
            'descricao' => 'Salario atualizado',
            'valor' => 3500.00,
            'data' => '2026-11-15',
            'tipo' => 'receita',
            'id_categoria' => 1
        ];
 
        $response = $this->enviarRequisicao('PUT', '/lancamentos/1', $dados);
 
        $this->assertEquals(200, $response->getStatusCode());
 
        $corpo = json_decode((string) $response->getBody(), true);
 
        $this->assertEquals('Salario atualizado', $corpo['descricao']);
        $this->assertEquals(3500.00, $corpo['valor']);
    }
 
    // 5. DELETE - Excluir lançamento
    public function test_deve_deletar_lancamento(): void
    {
        $response = $this->enviarRequisicao('DELETE', '/lancamentos/2');
 
        $this->assertEquals(204, $response->getStatusCode());
    }
 
    // 6. POST - Tentar cadastrar sem campos obrigatórios
    public function test_nao_deve_cadastrar_sem_campos_obrigatorios(): void
    {
        $dados = [
            'valor' => 150,
            'data' => '2026-11-10',
            'tipo' => 'despesa',
            'id_categoria' => 3
        ];
 
        $response = $this->enviarRequisicao('POST', '/lancamentos', $dados);
 
        $this->assertEquals(400, $response->getStatusCode());
 
        $corpo = json_decode((string) $response->getBody(), true);
 
        $this->assertEquals('Campos obrigatórios ausentes', $corpo['erro']);
    }
 
    // 7. POST - Tentar cadastrar valor igual a zero
    public function test_nao_deve_cadastrar_valor_menor_ou_igual_a_zero(): void
    {
        $dados = $this->dadosLancamento();
        $dados['valor'] = 0;
 
        $response = $this->enviarRequisicao('POST', '/lancamentos', $dados);
 
        $this->assertEquals(400, $response->getStatusCode());
 
        $corpo = json_decode((string) $response->getBody(), true);
 
        $this->assertEquals('O valor deve ser maior que zero', $corpo['erro']);
    }
 
    // 8. POST - Tentar cadastrar com tipo inválido
    public function test_nao_deve_cadastrar_com_tipo_invalido(): void
    {
        $dados = $this->dadosLancamento();
        $dados['tipo'] = 'investimento';
 
        $response = $this->enviarRequisicao('POST', '/lancamentos', $dados);
 
        $this->assertEquals(400, $response->getStatusCode());
 
        $corpo = json_decode((string) $response->getBody(), true);
 
        $this->assertEquals('Tipo inválido. Informe receita ou despesa', $corpo['erro']);
    }
 
    // 9. POST - Tentar cadastrar com data inválida
    public function test_nao_deve_cadastrar_com_data_invalida(): void
    {
        $dados = $this->dadosLancamento();
        $dados['data'] = '2026-15-40';
 
        $response = $this->enviarRequisicao('POST', '/lancamentos', $dados);
 
        $this->assertEquals(400, $response->getStatusCode());
 
        $corpo = json_decode((string) $response->getBody(), true);
 
        $this->assertEquals('Data inválida. Utilize o formato AAAA-MM-DD', $corpo['erro']);
    }
 
    // 10. POST - Tentar cadastrar com categoria inexistente
    public function test_nao_deve_cadastrar_com_categoria_inexistente(): void
    {
        $dados = $this->dadosLancamento();
        $dados['id_categoria'] = 999;
 
        $response = $this->enviarRequisicao('POST', '/lancamentos', $dados);
 
        $this->assertEquals(404, $response->getStatusCode());
 
        $corpo = json_decode((string) $response->getBody(), true);
 
        $this->assertEquals('Categoria não encontrada', $corpo['erro']);
    }
 
    // 11. POST - Tentar cadastrar com tipo incompatível com a categoria
    public function test_nao_deve_cadastrar_com_tipo_incompativel_com_categoria(): void
    {
        $dados = $this->dadosLancamento();
        $dados['tipo'] = 'receita';
 
        $response = $this->enviarRequisicao('POST', '/lancamentos', $dados);
 
        $this->assertEquals(400, $response->getStatusCode());
 
        $corpo = json_decode((string) $response->getBody(), true);
 
        $this->assertEquals(
            'O tipo do lançamento deve ser compatível com a categoria',
            $corpo['erro']
        );
    }
 
    // 12. GET - Tentar buscar lançamento inexistente
    public function test_deve_retornar_erro_ao_buscar_lancamento_inexistente(): void
    {
        $response = $this->enviarRequisicao('GET', '/lancamentos/999');
 
        $this->assertEquals(404, $response->getStatusCode());
 
        $corpo = json_decode((string) $response->getBody(), true);
 
        $this->assertEquals('Lançamento não encontrado', $corpo['erro']);
    }
 
    // 13. PUT - Tentar atualizar lançamento inexistente
    public function test_deve_retornar_erro_ao_atualizar_lancamento_inexistente(): void
    {
        $dados = $this->dadosLancamento();
 
        $response = $this->enviarRequisicao('PUT', '/lancamentos/999', $dados);
 
        $this->assertEquals(404, $response->getStatusCode());
 
        $corpo = json_decode((string) $response->getBody(), true);
 
        $this->assertEquals('Lançamento não encontrado', $corpo['erro']);
    }
 
    // 14. PUT - Tentar atualizar com valor inválido
    public function test_nao_deve_atualizar_com_valor_invalido(): void
    {
        $dados = $this->dadosLancamento();
        $dados['valor'] = -100;
 
        $response = $this->enviarRequisicao('PUT', '/lancamentos/2', $dados);
 
        $this->assertEquals(400, $response->getStatusCode());
 
        $corpo = json_decode((string) $response->getBody(), true);
 
        $this->assertEquals('O valor deve ser maior que zero', $corpo['erro']);
    }
 
    // 15. DELETE - Tentar excluir lançamento inexistente
    public function test_deve_retornar_erro_ao_deletar_lancamento_inexistente(): void
    {
        $response = $this->enviarRequisicao('DELETE', '/lancamentos/999');
 
        $this->assertEquals(404, $response->getStatusCode());
 
        $corpo = json_decode((string) $response->getBody(), true);
 
        $this->assertEquals('Lançamento não encontrado', $corpo['erro']);
    }
 
    // 16. GET - Filtrar lançamentos por período
    public function test_deve_filtrar_lancamentos_por_periodo(): void
    {
        $response = $this->enviarRequisicao(
            'GET',
            '/lancamentos?data_inicial=2026-11-01&data_final=2026-11-03'
        );
 
        $this->assertEquals(200, $response->getStatusCode());
 
        $corpo = json_decode((string) $response->getBody(), true);
 
        $this->assertCount(1, $corpo);
        $this->assertEquals('Salario', $corpo[0]['descricao']);
    }
 
    // 17. GET - Filtrar lançamentos por categoria
    public function test_deve_filtrar_lancamentos_por_categoria(): void
    {
        $response = $this->enviarRequisicao(
            'GET',
            '/lancamentos?id_categoria=3'
        );
 
        $this->assertEquals(200, $response->getStatusCode());
 
        $corpo = json_decode((string) $response->getBody(), true);
 
        $this->assertCount(1, $corpo);
        $this->assertEquals(3, $corpo[0]['id_categoria']);
    }
 
    // 18. GET - Filtrar lançamentos por tipo
    public function test_deve_filtrar_lancamentos_por_tipo(): void
    {
        $response = $this->enviarRequisicao(
            'GET',
            '/lancamentos?tipo=receita'
        );
 
        $this->assertEquals(200, $response->getStatusCode());
 
        $corpo = json_decode((string) $response->getBody(), true);
 
        $this->assertCount(1, $corpo);
        $this->assertEquals('receita', $corpo[0]['tipo']);
    }
 
    // 19. GET - Tentar filtrar com tipo inválido
    public function test_nao_deve_filtrar_com_tipo_invalido(): void
    {
        $response = $this->enviarRequisicao(
            'GET',
            '/lancamentos?tipo=investimento'
        );
 
        $this->assertEquals(400, $response->getStatusCode());
 
        $corpo = json_decode((string) $response->getBody(), true);
 
        $this->assertEquals('Tipo inválido. Informe receita ou despesa', $corpo['erro']);
    }
 
    // 20. GET - Tentar filtrar por categoria inexistente
    public function test_nao_deve_filtrar_por_categoria_inexistente(): void
    {
        $response = $this->enviarRequisicao(
            'GET',
            '/lancamentos?id_categoria=999'
        );
 
        $this->assertEquals(404, $response->getStatusCode());
 
        $corpo = json_decode((string) $response->getBody(), true);
 
        $this->assertEquals('Categoria não encontrada', $corpo['erro']);
    }
 
    // 21. GET - Tentar filtrar com período inválido
    public function test_nao_deve_filtrar_com_periodo_invalido(): void
    {
        $response = $this->enviarRequisicao(
            'GET',
            '/lancamentos?data_inicial=2026-11-20&data_final=2026-11-01'
        );
 
        $this->assertEquals(400, $response->getStatusCode());
 
        $corpo = json_decode((string) $response->getBody(), true);
 
        $this->assertEquals('Período informado inválido', $corpo['erro']);
    }
}
 
