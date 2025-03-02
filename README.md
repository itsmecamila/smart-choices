![Gif from movie Ratatoille](.github/surprise_me.gif)

# 🎲 Smart Choices

Decisões inteligentes no código, escolhas sábias no seu bolso. 



## 🪄 Funcionalidades

- **CRUD de Registros**
    - Criação de registros
    - Busca de registros por ID
    - Busca de todos registros
    - Exclusão de registro por ID
    - Atualização de registro (PATCH) por ID
- **Script de setup para banco de dados** 
    - Via requisição GET
- **Cálculo de saldo dinâmico** 
    - Via requisição GET
    - Período total
    - Período específico (passagem por parâmetros)
- **Geração de relatórios com base em período de tempo**
    - Formatos disponíveis: CSV, PDF e XLSL
    - Via requisição GET
    - Via CLI (Command Line Interface)

## 🧬 Stacks

**Back-End:** 
- PHP
- MySQL
- CodeIgniter

**Bibliotecas**: 
- DomPDF 
- PHPSpreadSheet


## 🫀 Documentação da API

### Estrutura da tabela do banco

| Campo   | Tipo       | Descrição                                   |
| :---------- | :--------- | :------------------------------------------ |
| `id`      | `int` | **Obrigatório**. O ID do registro |
| `title`      | `string` | **Obrigatório**. Título do registro |
| `value`      | `decimal` | **Obrigatório**. Valor do registro |
| `type`      | `enum` | **Obrigatório**. Tipo de registro: `income` / `expense`
| `date`      | `timestamp` | **Obrigatório**. Data real do registro|
| `created_at`      | `timestamp` | **Automático**. Data de criação do registro
| `updated_at`      | `timestamp` | **Automático**. Data da última atualização

### Rotas

#### Script de setup do banco

```http
GET /setup-database
```
#### Criar um registro

```http
POST /create-register
```

##### Exemplo:
```json
{
    "title": "Bilhete da loteria",
    "value": 2500000,
    "type": "income",
    "date":"2025-03-02 12:30:00"
}
```
#### Buscar todos registros
```http
GET  /registers
```

#### Buscar registro por ID
```http
GET  /registers/id
```
| Parâmetro   | Tipo       | Descrição                                   |
| :---------- | :--------- | :------------------------------------------ |
| `id`      | `int` | **Obrigatório**. O ID do registro |

#### Atualizar registro por ID (apenas um campo ou todos campos do exemplo)
```http
PATCH  /update/id
```
| Parâmetro   | Tipo       | Descrição                                   |
| :---------- | :--------- | :------------------------------------------ |
| `id`      | `int` | **Obrigatório**. O ID do registro |

##### Exemplo:
```json
{
    "title": "Bilhete da loteria",
    "value": 2500000,
    "type": "income",
    "date":"2025-03-02 12:30:00"
}
```

#### Excluir registro por ID
```http
DELETE  /delete/id
```
| Parâmetro   | Tipo       | Descrição                                   |
| :---------- | :--------- | :------------------------------------------ |
| `id`      | `int` | **Obrigatório**. O ID do registro |

#### Consultar saldo total atual
```http
GET /balance
```
#### Consultar saldo por período
```http
GET /balance?start_date=YYYY-MM-DD&end_date=YYYY-MM-DD
```
| Parâmetro   | Tipo       | Descrição                                   |
| :---------- | :--------- | :------------------------------------------ |
| `id`      | `int` | **Obrigatório**. O ID do registro |

#### Gerar relatório por formato de arquivo e período
```http
GET  /report?format=type&start_date=YYYY-MM-DD&end_date=YYYY-MM-DD
```
| Parâmetro   | Tipo       | Descrição                                   |
| :---------- | :--------- | :------------------------------------------ |
| `start_date`      | `string` | **Obrigatório**. Data inicial no formato YYYY-MM-DD |
| `end_date`      | `string` | **Obrigatório**. Data final no formato YYYY-MM-DD |
| `format`      | `string` | **Obrigatório**. Tipo de relatório: `csv` / `pdf` / `excel` 







## ⚙️ Instruções de uso

Clone o projeto

```bash
  git clone https://github.com/itsmecamila/smart-choices/
```

Entre no diretório do projeto

```bash
  cd smart-choices
```

Instale as dependências

```bash
  composer install
```

Renomeie o arquivo env 

```bash
  mv env .env
```

Edite o arquivo com suas configurações do banco de dados

```env
  database.default.hostname = localhost
  database.default.database = nome_do_seu_banco
  database.default.username = seu_username
  database.default.password = sua_senha
```

Inicie o servidor

```bash
  php spark serve
```

Execute a rota de setup para criar a tabela do banco 

```http
GET /setup-database
```
Verifique se o arquivo setup_done.txt foi criado na pasta writable para confirmar criação da tabela

```bash
  ls writable
```

## 🍷 CLI para  gerar relatórios

Para gerar relatórios via *Command Line Interface*, execute esse comando e siga as instruções no terminal

```bash
  php spark report:generate
```
Os relatórios serão armazenados no diretório *reports* que será criado no diretório *writable*

```bash
  ls writable/reports
```


## 🐭 Chef

- [@itsmecamila](https://www.github.com/itsmecamila)

