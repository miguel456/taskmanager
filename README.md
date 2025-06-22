## Project Archival Notice: although this is not marked as pre-release software, this project is effectively a university project and should not be used in production. Feel free to spin up a copy to get a look around, but don't expect it to work as nicely as a production-grade task management app. For instance, rolling your own authentication is a security anti-pattern, and that's what I did in this project (assignment requirements). PRs accepted but be aware that this repo will be archived soon.

## Future support

No security releases will be offered. No bugs will be patched. No more features will be developed. This project is now only for reference
and archival purposes.

-----------------

**I might spin up my own proper task management project soon tho (without them pesky uni assignment constraints)! *

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
