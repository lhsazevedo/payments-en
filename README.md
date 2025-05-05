# Payments App

[![CI - Payments service](https://github.com/lhsazevedo/payments/actions/workflows/ci-payments.yml/badge.svg)](https://github.com/lhsazevedo/payments/actions/workflows/ci-payments.yml)
[![CI - Users service](https://github.com/lhsazevedo/payments/actions/workflows/ci-users.yml/badge.svg)](https://github.com/lhsazevedo/payments/actions/workflows/ci-users.yml)
[![CI - Notifications service](https://github.com/lhsazevedo/payments/actions/workflows/ci-notification.yml/badge.svg)](https://github.com/lhsazevedo/payments/actions/workflows/ci-notification.yml)

Serviço de pagamentos simplificado de alta performance, usando PHP, Swoole e
Nano, PostgreSQL e RabbitMQ.

## Introdução
Esta API de pagamentos prioriza taxa de requisições e baixa latência, aproveitando o processamento concorrente do Swoole. O projeto é composto por dois microsserviços:
- Payments: expõe o endpoint de transferência e lida com autorizações e persistência.
- Notification: Consome mensagens da fila RabbitMQ e envia notificações via gateway externo.
- Users: Armazena perfil e dados pessoais dos usuários.

Essa separação isola falhas de notificações e garante que picos ou quedas nos serviços de SMS/e‑mail não degradem o processamento de pagamentos.

## Características
- Camada de domínio desacoplada do framework.
- Arquitetura simplificada para o contexto de microserviços.
- Resolução de dependências pelo container de serviços.
- Locking pessimista para evitar double spending.
- Transações com rollback automático em caso de falha.
- Controle de qualidade do código com PHP-CS-Fixer, PHPMD e PHPStan no nível 9.
- Testes unitários com cobertura das regras de negócio.
- Ambiente de desenvolvimento local baseado em Docker e Dev Containers.
- Timestamps armazenadas com milissegundos para precisão de logs e auditoria.

### Flow Chart
![Flow Chart](./flow-chart.svg)

### Sequence Diagram
![Sequence Diagram](./sequence-diagram.svg)

## Funcionalidades Implementadas
- **Transferência de valores entre usuários**
  - Validação de saldo
  - Chamada ao autorizador externo
  - Atualização de saldos em uma transação
- **Serviço de notificações**
  - Consome eventos da fila
  - Invoca o endpoint de notificação

## Próximos passos
Ideias que tenho para os próximos passos.

- [ ] Implementar serviço de notificações.
- [ ] Revisitar modos de falha e implementar soluções como retry e backoff.
- [ ] Escrever testes de integração.
- [ ] Criar script de configuração do RabbitMQ.
- [ ] Adicionar métodos ausentes nos repositórios.
- [ ] Implementar métodos aritméticos no Value Object `Amount()`.
- [ ] Implementar hidratação de entidades usando Reflection
- [ ] Permitir controle do endereço do serviço autorizador de pagamentos usando variáveis de ambiente.
- [ ] Registrar pagamentos não autorizados.
- [ ] Considerar separar usuários e contas em entidades diferentes.

## Configuração

**1. Clonar e rodar**  
Após clonar o repositório, suba os serviços usando:
```bash
docker compose up
```

**2. Executar migrations e seeders**  
Abra um novo terminal e execute o seguinte comando para criar o schema no BD do serviço de pagamentos e populá-lo com algumas contas:
```bash
docker compose exec payments php payments-server.php migrate --seed
```
Se você obtiver a saída abaixo, nosso banco de dados já estará preparado.
```
[INFO] Migration table created successfully.
Migrating: 2025_04_16_200006_create_users_table
Migrated:  2025_04_16_200006_create_users_table
Migrating: 2025_04_16_200233_create_payments_table
Migrated:  2025_04_16_200233_create_payments_table
Seed: AccountSeeder
Seeded: AccountSeeder
```

Faça o mesmo para o serviço de usuários:
```bash
docker compose exec users php users-server.php migrate --seed
```
```
$ docker compose exec users php users-server.php migrate --seed
[INFO] Migration table created successfully.
Migrating: 2025_04_16_200006_create_users_table
Migrated:  2025_04_16_200006_create_users_table
Seed: UserSeeder
Seeded: UserSeeder
```

_Nota:_ Se tiver problemas para executar as migrações, experimente fazer um drop das tabelas do banco manualmente primeiro.

**3. Fazer uma requisição e acompanhar os serviços**  
Agora que ambas as aplicações já estão rodando, podemos fazer uma requisição para o endpoint `POST /transfer` usando o `curl`.

```bash
curl -X POST \
    -H "Content-Type: application/json" \
    --data '{"payer_id": 1, "payee_id": 2, "amount": 1000}' \
    0.0.0.0:9501/transfer
```

Dependendo da resposta do serviço autorizador, você obterá uma das duas
respostas abaixo:
```json
{
  "status": "success",
  "data": {
    "message": "Transferred successfully"
  }
}
```
```json
{
  "status": "fail",
  "data": {
    "message": "Transfer was not authorized by external service"
  }
}
```

Além disso, nos logs do serviço the notificações, você verá logs das notificações que seriam enviadas:

```
notification-1  | Sending a SMS to 21987654321:
notification-1  | "You received R$ 1,00 from Bob."
notification-1  | 
notification-1  | Sending an email to alice@example.com:
notification-1  | "<h1>You received R$ 1,00 from Bob.</h1>"
notification-1  | 
notification-1  | Sending a SMS to 21912345678:
notification-1  | "You sent R$ 1,00 to Alice."
notification-1  | 
notification-1  | Sending an email to bob@example.com:
notification-1  | "<h1>You sent R$ 1,00 to Alice.</h1>"
notification-1  | 
```

Experimente trocar os valores no payload JSON para explorar as respostas da API.
Para experimentar com um lojista, você pode usar o usuário com ID 3.

_Nota:_ Para formatar a saída JSON do curl, você pode usar a ferramenta `jq`.
