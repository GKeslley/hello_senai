# Hello Senai | REST API

## Introdução

Esta API foi desenvolvida usando Laravel com o intuito de desenvolver um sistema para alunos do curso técnico de Desenvolvimento de Sistemas do Senai
chamado <strong><a href="https://github.com/GKeslley/hellosenai_front">Hello Senai</a></strong>.

## Recursos
É permitido alunos:
  - Poster projetos <br>
  - Convider colaboradores para o desenvolvimento de projetos <br>
  - Comentar em projetos <br>
  - Realizar desafios <br>
  - Alterar dados pessoais e o avatar <br>
  - Desativar a conta <br>
  - Denunciar postagens <br>

É permitido professores:  
- Criar desafios <br>

É permitido administradores:  
- Autenticar um novo professor <br>
- Visualizar denúncias <br>
- Registrar um novo administrador <br>

## Requisitos

- PHP 7.4 ou superior
- Laravel 10.28 ou superior
- MySQL (10.4.^-MariaDB)
- Node 20.10 ou superior
- Composer 2.6 ou superior
  
## Instalação

1. Clone este repositório para a sua máquina local.
2. Configure o arquivo ".env" para atender as configurações do seu banco de dados.
3. Rode as migrations.
4. Instale as dependências.
5. Inicie o servidor local
6. Utilize o <strong><a href="https://github.com/GKeslley/hellosenai_front">front-end do projeto</a></strong> para melhor interação. 

## Execução

```
# Clone este repositório
$ git clone https://github.com/GKeslley/hello_senai.git

# Acesse a pasta do projeto no seu terminal/cmd
$ cd hello_senai

#Instale as dependências
$ composer install

#Rode as migrations
$ php artisan migrate

#Inicie o servidor local
$ php artisan serve

```
## Testes

Aqui estão alguns exemplos de como você pode testar a API:

### Usuário - Crie uma nova conta, busque outros usuários ou atualize suas informações

- **POST /usuario**: Registro / Login de um usuário
- **PUT /usuario**: Atualizar informações do usuário logado
- **PUT /usuario/senha/modificar**: Modifique sua senha
- **PUT /usuario/conta/desativar**: Desative sua conta
- **PUT /avatar**: Atualize seu avatar
- **GET /usuario/{nickname}**: Listar informações de outro usuário a partir do apelido

### Projetos - Crie um novo projeto, exiba as informações de um projeto, atualize seus dados ou desative o projeto

- **POST /projeto**: Postar um novo projeto
- **POST /projeto/{slug}/comentar**: Comentar em um projeto
- **POST /projeto/{slug}/denunciar**: Denunciar um projeto
- **PUT /projeto/{slug}**: Editar um projeto existente
- **PUT /projeto/{slug}/desativar**: Desativar um projeto
- **PUT /projeto/{slug}/reativar**: Reativar um projeto
- **GET /projeto**: Listar todos os projetos cadastrados
- **GET /projeto/{slug}**: Listar um projeto específico

### Convites - Crie um novo convite, atualize suas informações ou delete

- **POST /convite**: Criar um novo convite
- **PUT /convite/{slug}**: Editar um convite
- **DELETE /convite/{slug}**: Deletar um convite
- **GET /convite**: Listar todos os convites

### Desafios - Crie um novo desafio, atualize suas informações ou delete

- **POST /desafio**: Criar um novo desafio
- **PUT /desafio/{slug}**: Editar um desafio
- **DELETE /desafio/{slug}**: Deletar um desafio
- **GET /desafio**: Listar todos os desafios
- **GET /desafio/{slug}**: Listar um desafio específico

### Professores - Autentique um novo professor

- **POST /professor**: Listar todos os professores cadastrados
- **PUT /professor/autenticar**: Autenticar um novo professor
- **GET /professor**: Listar os professores autenticados
- **GET /professor/{nickname}**: Listar os desafios criados de um professor
- **GET /professor/invalidos**: Listar os professores ainda não autenticados

### Autenticação - Dados do usuário logado

- **POST /login**: Realizar login na plataforma e retornar um token de autenticação
- **POST /logout**: Deslogar do sistema
- **GET /profile**: Retornar dados do usuário logado e autenticado
  
## Contribuição
Se você deseja contribuir para o projeto, siga as etapas abaixo:

1. Faça um fork deste repositório.
2. Crie uma nova branch com sua contribuição: git checkout -b minha-contribuicao
3. Faça commit das suas alterações: git commit -m 'Adicionando minha contribuição'
4. Envie suas alterações: git push origin minha-contribuicao
5. Abra um pull request.

## Licença
Este projeto está licenciado sob a licença MIT - consulte o arquivo LICENSE.md para obter detalhes.
