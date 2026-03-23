# Requisitos do Sistema

## Contexto e Objetivo

O ChefePDV é um sistema de ponto de venda para pequeno e médio varejo, desenvolvido em PHP puro sem frameworks externos. A restrição tecnológica é intencional: o perfil de sistema legado em PHP com jQuery é comum no mercado brasileiro, e o objetivo é demonstrar que é possível construir algo seguro e bem arquitetado dentro dessas limitações.

O sistema deve cobrir o ciclo completo de uma operação de caixa — da abertura ao fechamento — com controle de estoque, relatórios gerenciais e gestão de usuários por perfil de acesso.

---

## Stakeholders

| Perfil | Responsabilidade |
|---|---|
| Administrador | Configuração do sistema, gestão de usuários, acesso total |
| Gerente | Operação diária, relatórios, cadastros de produtos e clientes |
| Operador de caixa | Frente de caixa e consulta/cadastro de clientes |

---

## Requisitos Funcionais

### Autenticação e Controle de Acesso
- RF01 — O sistema deve autenticar usuários por e-mail e senha
- RF02 — Tentativas de login devem ser limitadas por IP (máx. 5 tentativas em 15 minutos)
- RF03 — Cada usuário deve ter um perfil: `admin`, `gerente` ou `operador`
- RF04 — O acesso a páginas e endpoints deve ser validado pelo perfil — não apenas pela existência de sessão

### Caixa
- RF05 — O operador deve abrir o caixa informando o saldo inicial antes de qualquer venda
- RF06 — O sistema deve bloquear vendas enquanto o caixa estiver fechado
- RF07 — O fechamento deve registrar o saldo real informado e calcular automaticamente o saldo esperado para comparação

### Frente de Caixa
- RF08 — Produtos devem ser buscados por nome ou código
- RF09 — A venda deve aceitar as formas de pagamento: dinheiro, crédito, débito e PIX
- RF10 — Pagamentos em dinheiro devem validar que o valor recebido cobre o total antes de concluir
- RF11 — Deve ser possível aplicar desconto em reais no total da venda
- RF12 — A finalização deve decrementar o estoque de forma atômica, com lock de linha para evitar race condition em múltiplos terminais
- RF13 — O cancelamento deve estornar o estoque de todos os itens sem apagar o registro da venda

### Cadastros
- RF14 — Produtos devem ter código, preço, estoque atual, estoque mínimo e categoria
- RF15 — Clientes podem ser vinculados opcionalmente a uma venda
- RF16 — O endereço do cliente deve ser preenchível automaticamente via CEP (integração ViaCEP)

### Relatórios
- RF17 — O relatório geral deve exibir faturamento, ticket médio, cancelamentos, breakdown por pagamento e situação do estoque
- RF18 — Relatórios de vendas, produtos e pagamentos devem ser filtráveis por período
- RF19 — Deve ser possível exportar relatórios individualmente por aba ou combinando múltiplas seções em um único PDF

### Gestão de Usuários
- RF20 — Somente o admin pode criar, editar e desativar usuários
- RF21 — Um usuário não pode excluir ou desativar a própria conta

---

## Requisitos Não Funcionais

- RNF01 — Todas as queries ao banco devem usar prepared statements com `EMULATE_PREPARES = false`
- RNF02 — Formulários POST devem ser protegidos com token CSRF
- RNF03 — Senhas devem ser armazenadas com `password_hash(PASSWORD_BCRYPT)`
- RNF04 — A sessão deve usar `httponly`, `samesite: Lax` e regeneração de ID no login
- RNF05 — O sistema deve rodar em PHP 7.4+ sem dependência de frameworks externos
- RNF06 — O ambiente de desenvolvimento deve ser reproduzível via Docker Compose em um único comando

---

## Fora do Escopo

- Emissão de nota fiscal (NF-e / NFC-e)
- Integração com maquininhas de cartão
- Aplicativo mobile
- Suporte a múltiplas lojas
- Módulo financeiro (contas a pagar/receber)
