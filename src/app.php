<?php
require __DIR__ . '/../vendor/autoload.php';
use Slim\Factory\AppFactory;
$app = AppFactory::create();
$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();
$categorias = [
    ['id' => 1, 'nome' => 'Salario', 'tipo' => 'receita'],
    ['id' => 2, 'nome' => 'Auxilio', 'tipo' => 'receita'],
    ['id' => 3, 'nome' => 'Aluguel', 'tipo' => 'despesa'],
    ['id' => 4, 'nome' => 'Curso', 'tipo' => 'despesa'],
];
$lancamentos = [
    [
        'id' => 1,
        'descricao' => 'Salario',
        'valor' => 3000.00,
        'data' => '2026-11-01',
        'tipo' => 'receita',
        'id_categoria' => 1
    ],
    [
        'id' => 2,
        'descricao' => 'Aluguel',
        'valor' => 1200.00,
        'data' => '2026-11-05',
        'tipo' => 'despesa',
        'id_categoria' => 3
    ]
];
 

$app->get('/categorias', function ($request, $response) use ($categorias) {
    $response->getBody()->write(json_encode($categorias));
    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(200);
});
$app->get('/categorias/{id}', function ($request, $response, $args) use ($categorias) {
    $id = (int)$args['id']; 
    foreach ($categorias as $categoria) {
        if ($categoria['id'] == $id) {
            $response->getBody()->write(json_encode($categoria));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(200);
        }
    }
 
 
    $response->getBody()->write(json_encode([
        'erro' => 'Categoria não encontrada'
    ]));
    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(404);
});
 
 
$app->post('/categorias', function ($request, $response) use (&$categorias) {
    $body = $request->getParsedBody();
      // Verifica se os campos obrigatórios foram preenchidos
    if (empty($body['nome']) || empty($body['tipo'])) {
        $response->getBody()->write(json_encode([
            "erro" => "Campos obrigatórios ausentes"
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }
// Verifica se o tipo informado é válido
    if (!in_array($body['tipo'], ['receita', 'despesa'])) {
        $response->getBody()->write(json_encode([
            "erro" => "Tipo inválido. Informe receita ou despesa"
        ]));
 
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }
 
    // Verifica se já existe uma categoria com o mesmo nome
    foreach ($categorias as $categoria) {
        if (strtolower(trim($categoria['nome'])) === strtolower(trim($body['nome']))) {
            $response->getBody()->write(json_encode([
                "erro" => "Já existe uma categoria com esse nome"
            ]));
 
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
    }
 
    // Gera o próximo ID automaticamente
    $proximoId = $categorias ? max(array_column($categorias, 'id')) + 1 : 1;
 
    // Cria a nova categoria
    $nova = [
        "id" => $proximoId,
        "nome" => trim($body['nome']),
        "tipo" => $body['tipo']
    ];
 
    // Adiciona a categoria ao array
    $categorias[] = $nova;
 
    $response->getBody()->write(json_encode($nova));
 
    return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
});

 
 
$app->put('/categorias/{id}', function ($request, $response, $args) use (&$categorias) {
    $id = (int) $args['id'];
    $body = $request->getParsedBody();
 
    // Busca a categoria pelo ID
    $indiceEncontrado = null;
 
    foreach ($categorias as $key => $categoria) {
        if ($categoria['id'] === $id) {
            $indiceEncontrado = $key;
            break;
        }
    }
 
    // Verifica se a categoria existe
    if ($indiceEncontrado === null) {
        $response->getBody()->write(json_encode([
            "erro" => "Categoria não encontrada"
        ]));
 
        return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
    }
 
    // Verifica se os campos obrigatórios foram preenchidos
    if (empty($body['nome']) || empty($body['tipo'])) {
        $response->getBody()->write(json_encode([
            "erro" => "Campos obrigatórios ausentes"
        ]));
 
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }
 
    // Verifica se o tipo informado é válido
    if (!in_array($body['tipo'], ['receita', 'despesa'])) {
        $response->getBody()->write(json_encode([
            "erro" => "Tipo inválido. Informe receita ou despesa"
        ]));
 
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }
 
    // Verifica se o nome já pertence a outra categoria
    foreach ($categorias as $categoria) {
        if (
            $categoria['id'] !== $id &&
            strtolower(trim($categoria['nome'])) === strtolower(trim($body['nome']))
        ) {
            $response->getBody()->write(json_encode([
                "erro" => "Já existe uma categoria com esse nome"
            ]));
 
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
    }
 
    // Atualiza a categoria
    $categorias[$indiceEncontrado] = [
        "id" => $id,
        "nome" => trim($body['nome']),
        "tipo" => $body['tipo']
    ];
 
    $response->getBody()->write(json_encode($categorias[$indiceEncontrado]));
 
    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
});
 
 
 
$app->delete('/categorias/{id}', function ($request, $response, $args) use (&$categorias, &$lancamentos) {
 
    $id = (int) $args['id'];
 
    $indiceEncontrado = null;
 
    foreach ($categorias as $key => $categoria) {
        if ($categoria['id'] === $id) {
            $indiceEncontrado = $key;
            break;
        }
    }
 
    if ($indiceEncontrado === null) {
        $response->getBody()->write(json_encode([
            'erro' => 'Categoria não encontrada'
        ]));
 
        return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
    }
 
    // Verifica se existem lançamentos vinculados à categoria
    foreach ($lancamentos as $lancamento) {
        if ($lancamento['id_categoria'] === $id) {
            $response->getBody()->write(json_encode([
                'erro' => 'Não é possível excluir uma categoria que possui lançamentos vinculados'
            ]));
 
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
    }
 
    unset($categorias[$indiceEncontrado]);
 
    return $response->withStatus(204);
});
 
 
$app->get('/lancamentos', function ($request, $response) use ($lancamentos, $categorias) {
 
    $filtros = $request->getQueryParams();
 
    $resultado = $lancamentos;
 
    // Filtro por período
    if (isset($filtros['data_inicial']) || isset($filtros['data_final'])) {
 
        if (empty($filtros['data_inicial']) || empty($filtros['data_final'])) {
            $response->getBody()->write(json_encode([
                'erro' => 'Informe a data inicial e a data final'
            ]));
 
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
 
        $inicial = DateTime::createFromFormat('!Y-m-d', $filtros['data_inicial']);
        $final = DateTime::createFromFormat('!Y-m-d', $filtros['data_final']);
 
        if (
            !$inicial || !$final ||
            $inicial->format('Y-m-d') !== $filtros['data_inicial'] ||
            $final->format('Y-m-d') !== $filtros['data_final'] ||
            $filtros['data_inicial'] > $filtros['data_final']
        ) {
            $response->getBody()->write(json_encode([
                'erro' => 'Período informado inválido'
            ]));
 
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
 
        $resultado = array_filter($resultado, function ($lancamento) use ($filtros) {
            return $lancamento['data'] >= $filtros['data_inicial']
&& $lancamento['data'] <= $filtros['data_final'];
        });
    }
 
    // Filtro por categoria
    if (isset($filtros['id_categoria'])) {
 
        $categoriaExiste = false;
 
        foreach ($categorias as $categoria) {
            if ($categoria['id'] == $filtros['id_categoria']) {
                $categoriaExiste = true;
                break;
            }
        }
 
        if (!$categoriaExiste) {
            $response->getBody()->write(json_encode([
                'erro' => 'Categoria não encontrada'
            ]));
 
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }
 
        $resultado = array_filter($resultado, function ($lancamento) use ($filtros) {
            return $lancamento['id_categoria'] == $filtros['id_categoria'];
        });
    }
 
    // Filtro por tipo
    if (isset($filtros['tipo'])) {
 
        if (!in_array($filtros['tipo'], ['receita', 'despesa'])) {
            $response->getBody()->write(json_encode([
                'erro' => 'Tipo inválido. Informe receita ou despesa'
            ]));
 
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
 
        $resultado = array_filter($resultado, function ($lancamento) use ($filtros) {
            return $lancamento['tipo'] === $filtros['tipo'];
        });
    }
 
    $response->getBody()->write(json_encode(array_values($resultado)));
 
    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
});
 
 
$app->get('/lancamentos/{id}', function ($request, $response, $args) use ($lancamentos) {
 
    $id = (int) $args['id'];
 
    foreach ($lancamentos as $lancamento) {
        if ($lancamento['id'] === $id) {
 
            $response->getBody()->write(json_encode($lancamento));
 
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        }
    }
 
    $response->getBody()->write(json_encode([
        'erro' => 'Lançamento não encontrado'
    ]));
 
    return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
});
 
 
$app->put('/lancamentos/{id}', function ($request, $response, $args) use (&$lancamentos, &$categorias) {
 
    $id = (int) $args['id'];
    $body = $request->getParsedBody();
 
    // Busca o lançamento
    $indiceEncontrado = null;
 
    foreach ($lancamentos as $key => $lancamento) {
        if ($lancamento['id'] === $id) {
            $indiceEncontrado = $key;
            break;
        }
    }
 
    if ($indiceEncontrado === null) {
        $response->getBody()->write(json_encode([
            'erro' => 'Lançamento não encontrado'
        ]));
 
        return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
    }
 
    // Verifica os campos obrigatórios
    if (
        empty($body['descricao']) ||
        !isset($body['valor']) ||
        empty($body['data']) ||
        empty($body['tipo']) ||
        !isset($body['id_categoria'])
    ) {
        $response->getBody()->write(json_encode([
            'erro' => 'Campos obrigatórios ausentes'
        ]));
 
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }
 
    // Verifica o valor
    if (!is_numeric($body['valor']) || $body['valor'] <= 0) {
        $response->getBody()->write(json_encode([
            'erro' => 'O valor deve ser maior que zero'
        ]));
 
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }
 
    // Verifica o tipo
    if (!in_array($body['tipo'], ['receita', 'despesa'])) {
        $response->getBody()->write(json_encode([
            'erro' => 'Tipo inválido. Informe receita ou despesa'
        ]));
 
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }
 
    // Verifica a data
    $data = DateTime::createFromFormat('!Y-m-d', $body['data']);
 
    if (!$data || $data->format('Y-m-d') !== $body['data']) {
        $response->getBody()->write(json_encode([
            'erro' => 'Data inválida. Utilize o formato AAAA-MM-DD'
        ]));
 
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }
 
    // Busca a categoria
    $categoriaEncontrada = null;
 
    foreach ($categorias as $categoria) {
        if ($categoria['id'] == $body['id_categoria']) {
            $categoriaEncontrada = $categoria;
            break;
        }
    }
 
    if ($categoriaEncontrada === null) {
        $response->getBody()->write(json_encode([
            'erro' => 'Categoria não encontrada'
        ]));
 
        return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
    }
 
    // Verifica se o tipo combina com a categoria
    if ($categoriaEncontrada['tipo'] !== $body['tipo']) {
        $response->getBody()->write(json_encode([
            'erro' => 'O tipo do lançamento deve ser compatível com a categoria'
        ]));
 
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }
 
    // Atualiza o lançamento
    $lancamentos[$indiceEncontrado] = [
        'id' => $id,
        'descricao' => trim($body['descricao']),
        'valor' => (float) $body['valor'],
        'data' => $body['data'],
        'tipo' => $body['tipo'],
        'id_categoria' => (int) $body['id_categoria']
    ];
 
    $response->getBody()->write(json_encode($lancamentos[$indiceEncontrado]));
 
    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
});
 
 
$app->post('/lancamentos', function ($request, $response) use (&$lancamentos, &$categorias) {
 
    $body = $request->getParsedBody();
 
    // Verifica os campos obrigatórios
    if (
        empty($body['descricao']) ||
        !isset($body['valor']) ||
        empty($body['data']) ||
        empty($body['tipo']) ||
        !isset($body['id_categoria'])
    ) {
        $response->getBody()->write(json_encode([
            'erro' => 'Campos obrigatórios ausentes'
        ]));
 
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }
 
    // Verifica se o valor é maior que zero
    if (!is_numeric($body['valor']) || $body['valor'] <= 0) {
        $response->getBody()->write(json_encode([
            'erro' => 'O valor deve ser maior que zero'
        ]));
 
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }
 
    // Verifica se o tipo é válido
    if (!in_array($body['tipo'], ['receita', 'despesa'])) {
        $response->getBody()->write(json_encode([
            'erro' => 'Tipo inválido. Informe receita ou despesa'
        ]));
 
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }
 
    // Verifica se a data é válida
    $data = DateTime::createFromFormat('!Y-m-d', $body['data']);
 
    if (!$data || $data->format('Y-m-d') !== $body['data']) {
        $response->getBody()->write(json_encode([
            'erro' => 'Data inválida. Utilize o formato AAAA-MM-DD'
        ]));
 
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }
 
    // Busca a categoria informada
    $categoriaEncontrada = null;
 
    foreach ($categorias as $categoria) {
        if ($categoria['id'] == $body['id_categoria']) {
            $categoriaEncontrada = $categoria;
            break;
        }
    }
 
    // Verifica se a categoria existe
    if ($categoriaEncontrada === null) {
        $response->getBody()->write(json_encode([
            'erro' => 'Categoria não encontrada'
        ]));
 
        return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
    }
 
    // Verifica se o tipo do lançamento combina com o tipo da categoria
    if ($categoriaEncontrada['tipo'] !== $body['tipo']) {
        $response->getBody()->write(json_encode([
            'erro' => 'O tipo do lançamento deve ser compatível com a categoria'
        ]));
 
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }
 
    // Gera o próximo ID
    $proximoId = $lancamentos ? max(array_column($lancamentos, 'id')) + 1 : 1;
 
    // Cria o lançamento
    $novo = [
        'id' => $proximoId,
        'descricao' => trim($body['descricao']),
        'valor' => (float) $body['valor'],
        'data' => $body['data'],
        'tipo' => $body['tipo'],
        'id_categoria' => (int) $body['id_categoria']
    ];
 
    $lancamentos[] = $novo;
 
    $response->getBody()->write(json_encode($novo));
 
    return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
});
 
 
$app->delete('/lancamentos/{id}', function ($request, $response, $args) use (&$lancamentos) {
 
    $id = (int) $args['id'];
 
    $indiceEncontrado = null;
 
    foreach ($lancamentos as $key => $lancamento) {
        if ($lancamento['id'] === $id) {
            $indiceEncontrado = $key;
            break;
        }
    }
 
    if ($indiceEncontrado === null) {
        $response->getBody()->write(json_encode([
            'erro' => 'Lançamento não encontrado'
        ]));
 
        return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
    }
 
    unset($lancamentos[$indiceEncontrado]);
 
    return $response->withStatus(204);
});
 
return $app;
 
