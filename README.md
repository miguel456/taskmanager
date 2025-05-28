# Projeto II PASSI - Aplicação de Gestão de Projetos e Tarefas em PHP

### Versão 0.1.0

## Tecnologias usadas:
 - PHP 8.4 com PDO (extensão obrigatória)
 - Bootstrap 5 com FontAwesome 6

## Instruções de uso

 - Importar BD anexada se necessário (ou apenas a estrutura: ``config/taskprojects.sql``);
 - Instalar dependências do Composer (``composer install --no-dev``)
 - Garantir que o autoloader é gerado (``composer dump-autoload -o``) ou o projeto não vai funcionar corretamente 
 - Configurar a aplicação: Copiar ``config/database.example.ini`` para ``config/database.ini`` e alterar os valores com as credenciais respetivas;
 - Apontar o servidor web para a raíz do projeto
   - Garantir que todos os ficheiros no diretório ``config`` não são de leitura pública (ou usar o .htaccess incluído)
 - Profit

**Alternativamente**:
 - Importar a BD com o método/ficheiro escolhido;
 - Extraír a versão de lançamento já pronta e apontar o servidor para a raíz
 - Configurar a aplicação: Copiar ``config/database.example.ini`` para ``config/database.ini`` e alterar os valores com as credenciais respetivas;
 - Profit

## Dependências usadas:

 - ``vlucas/phpdotenv`` (futuramente) para gestão de configuração