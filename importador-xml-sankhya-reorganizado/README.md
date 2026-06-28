# Importador XML Sankhya

Projeto reorganizado para deploy no Render.

## Estrutura

- `public/`: arquivos acessíveis pelo navegador.
- `app/Core/`: bootstrap, configurações e helpers.
- `app/Services/`: parser XML, cliente Sankhya e serviço de importação.
- `storage/logs/`: logs da aplicação.
- `public/uploads/`: XMLs enviados.

## Deploy no Render

Use Docker. O serviço deve apontar para a raiz do repositório, onde está o `Dockerfile`.

## Variáveis de ambiente

Configure no Render:

```env
APP_SANDBOX=true
SANKHYA_BASE_URL=https://api.sankhya.com.br
SANKHYA_TOKEN=
SANKHYA_APPKEY=
SANKHYA_USERNAME=
SANKHYA_PASSWORD=
SANKHYA_CODTIPOPER=102
SANKHYA_CODEMP=1
SANKHYA_CODNAT=0
SANKHYA_CODCENCUS=0
SANKHYA_CODPROJ=0
```
