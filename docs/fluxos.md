# Fluxos do Sistema

## Fluxo de Autenticação

```mermaid
flowchart TD
    A([Acessa o sistema]) --> B{Sessão ativa?}
    B -->|Sim| C[Redireciona para home]
    B -->|Não| D[Exibe tela de login]
    D --> E[Informa e-mail e senha]
    E --> F{IP bloqueado?}
    F -->|Sim| G[Exibe mensagem de bloqueio]
    F -->|Não| H{Credenciais válidas?}
    H -->|Não| I[Registra tentativa + exibe erro]
    I --> J{5 tentativas em 15 min?}
    J -->|Sim| K[Bloqueia IP por 15 min]
    J -->|Não| D
    H -->|Sim| L[Limpa tentativas do IP]
    L --> M[session_regenerate_id — novo ID de sessão]
    M --> N[Grava perfil na sessão]
    N --> O{Perfil do usuário?}
    O -->|operador| P[Redireciona → Frente de Caixa]
    O -->|gerente / admin| Q[Redireciona → Dashboard]
```

---

## Fluxo de Abertura de Caixa

```mermaid
flowchart TD
    A([Operador acessa Caixa]) --> B{Caixa já aberto?}
    B -->|Sim| C[Exibe resumo do turno atual]
    B -->|Não| D[Exibe formulário de abertura]
    D --> E[Informa saldo inicial]
    E --> F[Registra caixa com status = aberto]
    F --> G[Libera frente de caixa]
```

---

## Fluxo de uma Venda

```mermaid
flowchart TD
    A([Operador abre frente de caixa]) --> B{Caixa aberto?}
    B -->|Não| C[Bloqueia — exige abertura de caixa]
    B -->|Sim| D[Busca produto por nome ou código]
    D --> E{Produto encontrado?}
    E -->|Não| F[Exibe aviso]
    F --> D
    E -->|Sim| G[Adiciona ao carrinho]
    G --> H{Continuar adicionando?}
    H -->|Sim| D
    H -->|Não| I[Seleciona forma de pagamento]
    I --> J{Pagamento em dinheiro?}
    J -->|Sim| K{Valor recebido ≥ total?}
    K -->|Não| L[Bloqueia — exige valor suficiente]
    L --> I
    K -->|Sim| M
    J -->|Não| M[Confirma venda]
    M --> N[BEGIN TRANSACTION]
    N --> O[SELECT estoque FOR UPDATE]
    O --> P{Estoque suficiente\npara todos os itens?}
    P -->|Não| Q[ROLLBACK — exibe erro]
    P -->|Sim| R[INSERT vendas + venda_itens]
    R --> S[UPDATE estoque — decremento atômico]
    S --> T[COMMIT]
    T --> U[Exibe resumo + troco se houver]
```

---

## Fluxo de Cancelamento de Venda

```mermaid
flowchart TD
    A([Gerente acessa histórico]) --> B[Localiza venda]
    B --> C{Status da venda?}
    C -->|cancelada| D[Exibe aviso — já cancelada]
    C -->|concluida| E[Confirma cancelamento]
    E --> F[BEGIN TRANSACTION]
    F --> G[SELECT status FOR UPDATE]
    G --> H{Status ainda concluida?}
    H -->|Não| I[ROLLBACK — concorrência detectada]
    H -->|Sim| J[UPDATE vendas SET status = cancelada]
    J --> K[Recupera itens da venda]
    K --> L[UPDATE estoque — estorno por item]
    L --> M[COMMIT]
    M --> N[Venda marcada como cancelada\nEstoque restaurado]
```

---

## Fluxo de Fechamento de Caixa

```mermaid
flowchart TD
    A([Operador inicia fechamento]) --> B[Sistema calcula saldo esperado\ncom base nas vendas do turno]
    B --> C[Operador informa saldo real contado]
    C --> D[Sistema registra diferença]
    D --> E[Caixa fechado — status = fechado]
    E --> F{Diferença detectada?}
    F -->|Sim| G[Diferença fica visível no histórico\npara análise do gerente]
    F -->|Não| H[Fechamento sem divergência]
```

---

## Fluxo de Controle de Acesso por Rota

```mermaid
flowchart LR
    R[Requisição GET] --> I[index.php]
    I --> AC{Auth::can\nperfisPermitidos}
    AC -->|autorizado| V[Carrega view]
    AC -->|negado| Red[redirect /pos]

    R2[Requisição AJAX] --> API[api/*.php]
    API --> G1{Guard::\nrequireAjax}
    G1 -->|sem sessão| E1[HTTP 401]
    G1 -->|com sessão| G2{Guard::\nrequireRole}
    G2 -->|perfil negado| E2[HTTP 403]
    G2 -->|autorizado| P[Processa e retorna JSON]
```
