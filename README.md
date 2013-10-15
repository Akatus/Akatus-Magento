<p align="left">
    <img src="https://site.akatus.com/wp-content/uploads/2012/12/logo.gif" alt="Akatus" title="Akatus" />
</p>

# Módulo Akatus para Magento (1.4.2 ou superior)

## Instalação

Após finalizar o download, descompacte e realize os seguintes passos:

1 - Transfira o conteúdo para a raiz da instalação Magento, sobrescrevendo os arquivos.
2 - Transfira o conteúdo de skin/frontend/default/default para skin/frontend/base/default.

*Atenção:* Se você utiliza um tema personalizado, vá até __app/design/frontend/default/default/template__ e copie a pasta akatus para dentro da pasta template do seu tema.

Antes de configurar o módulo, vá ao Gerenciador de Cache, clique em selecionar todos, escolha a ação "Atualizar" e aplique. Em seguida, vá em Configurações e localize a opção "Métodos de Pagamento". Lá o módulo Akatus aparecerá para ser configurado.

## Configurações do Método de Pagamento

Habilitado (Enabled) - Ativa/Desativa o módulo
Modo - Produção ou Sandbox (para a realização de testes)
Título - Texto que será exibido para o cliente
Número Máximo de Parcelas - O máximo de parcelas para cartão de crédito
Token NIP - Código gerado no painel da conta Akatus (menu Integração > Chaves de Segurança)
API Key - Código gerado no painel da conta Akatus (menu Integração > Chaves de Segurança)
E-mail - E-mail de cadastro da conta Akatus

*Atenção:* Ao instalar o módulo, por padrão ele estará no modo Sandbox. Para utilizá-lo, preencha as informações de sua conta desenvolvedor, cadastrada a partir de [https://dev.akatus.com](https://dev.akatus.com)

## Notificação Instantânea de Pagamento (NIP)

Para receber as notificações de mudanças no status das transações é necessário:

1 - Acessar o menu Integração > Notificações, dentro da sua conta Akatus.
2 - Habilitar as notificações e inserir a URL no padrão: *http://www.sualoja.com.br/akatus_response.php*

*Atenção:* É interessante utilizar HTTPS para o NIP, porém é necessário que o servidor esteja corretamente configurado e com certificado SSL válido. 

## Detectando e Resolvendo Problemas Comuns de Integração

Habilite os logs do Magento (Sistema > Configuração > Desenvolvedor > Configurações de Log) e verifique o arquivo __var/log/system.log__ da instalação Magento. Lá irá constar os XMLs que estão sendo enviados e recebidos na comunicação com a Akatus.
