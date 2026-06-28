# Importador XML Sankhya - Render

Projeto PHP pronto para publicar gratuitamente no Render.

## O que ele faz

- Tela web moderna
- Upload de XML NF-e
- Leitura de parceiro e itens
- Simulação de consulta/cadastro no Sankhya
- Montagem do payload do pedido TOP 102
- Preparado para API real do Sankhya
- Deploy via Render com Docker
- Configuração por variáveis de ambiente

## Como testar localmente

```bash
cd importador_xml_sankhya_render
php -S localhost:8080 -t public
```

Abra:

```text
http://localhost:8080
```

## Como publicar no Render

### 1. Crie um repositório no GitHub

Crie um repositório, por exemplo:

```text
importador-xml-sankhya
```

Envie os arquivos:

```bash
git init
git add .
git commit -m "primeira versao importador xml sankhya"
git branch -M main
git remote add origin https://github.com/SEU_USUARIO/importador-xml-sankhya.git
git push -u origin main
```

### 2. Crie o serviço no Render

No Render:

```text
New
↓
Web Service
↓
Build and deploy from a Git repository
↓
Selecione o repositório
```

Como este projeto tem `render.yaml` e `Dockerfile`, o Render reconhecerá o deploy.

### 3. Variáveis de ambiente

No Render, em Environment, configure:

```text
APP_SANDBOX=true
SANKHYA_BASE_URL=https://api.sankhya.com.br
SANKHYA_TOKEN=seu_token
SANKHYA_APPKEY=sua_appkey
SANKHYA_CODEMP=1
SANKHYA_TOP=102
SANKHYA_CODTIPVENDA=1
SANKHYA_CODVEND=0
```

Para enviar de verdade para o Sankhya, altere:

```text
APP_SANDBOX=false
```

## Atenção

O projeto sobe em modo simulação por segurança.  
Enquanto `APP_SANDBOX=true`, ele não envia pedido para o Sankhya.

Antes de usar em produção, valide:

- Login da API no seu ambiente
- Serviço correto de cadastro de parceiro
- Serviço correto de consulta de produto
- Payload da TOP 102
- Regras de produto, empresa, vendedor, natureza e centro de resultado
