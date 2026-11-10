# API de Controle Financeiro Pessoal

Web service REST em PHP com Slim para cadastrar categorias e lançamentos financeiros, filtrar movimentações e consultar o resumo do saldo. Os dados são armazenados em um arquivo JSON local.

## Requisitos

- PHP 8.1 ou superior
- Composer

## Configuração e execução

Instale as dependências:

```sh
composer install
```

Inicie a API:

```sh
composer start
```

A API ficará disponível em `http://127.0.0.1:8000`. Por padrão, os dados são salvos em `storage/financeiro.json`. Para escolher outro arquivo, defina `FINANCEIRO_DATA_FILE` antes de iniciar o servidor.

## Testes

Execute a suíte automatizada:

```sh
composer test
```

Os testes criam um arquivo de dados temporário por cenário e não alteram os dados da API.

## Formato

Envie e receba JSON com `Content-Type: application/json`. Erros usam o formato:

```json
{
  "erro": "Descrição do problema."
}
```

Tipos aceitos: `receita` e `despesa`. Valores devem ser positivos. Datas usam o formato `AAAA-MM-DD`.

## Endpoints

### Categorias

| Método | Endpoint | Descrição |
|---|---|---|
| `GET` | `/categorias` | Lista as categorias |
| `POST` | `/categorias` | Cria categoria com `nome` e `tipo` |
| `GET` | `/categorias/{id}` | Consulta categoria |
| `PUT` | `/categorias/{id}` | Atualiza `nome` e `tipo` |
| `DELETE` | `/categorias/{id}` | Exclui categoria sem lançamentos vinculados |

Exemplo de categoria:

```json
{
  "nome": "Salário",
  "tipo": "receita"
}
```

### Lançamentos

| Método | Endpoint | Descrição |
|---|---|---|
| `GET` | `/lancamentos` | Lista lançamentos; aceita filtros |
| `POST` | `/lancamentos` | Cria lançamento |
| `GET` | `/lancamentos/{id}` | Consulta lançamento |
| `PUT` | `/lancamentos/{id}` | Atualiza lançamento |
| `DELETE` | `/lancamentos/{id}` | Exclui lançamento |

Filtros combináveis de `GET /lancamentos`:

- `dataInicial=AAAA-MM-DD`
- `dataFinal=AAAA-MM-DD`
- `categoriaId={id}`
- `tipo=receita` ou `tipo=despesa`

Exemplo de lançamento:

```json
{
  "descricao": "Pagamento",
  "valor": 3500.00,
  "data": "2026-11-10",
  "tipo": "receita",
  "categoriaId": 1
}
```

### Resumo financeiro

| Método | Endpoint | Descrição |
|---|---|---|
| `GET` | `/resumo` | Retorna total de receitas, despesas e saldo de todos os lançamentos |
| `GET` | `/resumo?dataInicial=AAAA-MM-DD&dataFinal=AAAA-MM-DD` | Retorna os totais no período informado |

O saldo é calculado como receitas menos despesas. As operações de criação retornam `201`, exclusões bem-sucedidas retornam `204`, validações retornam `422`, recursos inexistentes retornam `404` e tentativas de excluir categorias em uso retornam `409`.
