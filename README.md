# CopyCon - NF-e

Geração de NF-e com base nas respostas dos serviços:

- [ x ] PagSeguro
- [ x ] Eduzz
- [ x ] PayPal

## Instalação

Crie os arquivos de configuração:

```
# config/eduzz.yml
eduzz:
  public_key: XXXX
  api_key: ZZZZ
  
# config/nfe.yml
nfe:
  token: 'nononono'
  company: 'nononono'
  
# config/pagseguro.yml
pagseguro:
  email: 'nonono@nono.ono.no'
  token: 'ONONONONO'
```

Instale as dependências:

```
composer install
```

Configure os serviços de notificação para enviar a chamada para: seuhost/pagseguro, seuhost/eduzz, seuhost/paypal


### Licença
MIT

