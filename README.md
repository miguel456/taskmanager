# Projeto II PASSI - Aplicação de Gestão de Projetos e Tarefas em PHP

### Versão 1.0.0-release

## Tecnologias usadas:
 - PHP 8.4 com PDO (extensão obrigatória)
 - Bootstrap 5 com FontAwesome 6

## Instruções de instalação

A versão de lançamento anexada já vem com o Composer pronto. NÃO é preciso fazer mais nada neste sentido.

 - Importar a estrutura ``config/projectary.sql`` para uma base de dados fresca
 - Configurar a aplicação: Copiar ``config/database.example.ini`` para ``config/database.ini`` e alterar os valores com as credenciais respetivas;
 - Apontar o servidor web para a raíz do projeto
   - Garantir que todos os ficheiros no diretório ``config`` não são de leitura pública (ou usar o .htaccess incluído)
 - Criar primeira conta e testar 

## Dependências usadas:

 - Nenhumas
 - Composer para Autoload de todas as classes e funções