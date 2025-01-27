# Desenvolvimento de Teste Desenvolvedor PHP Laravel Pleno.
Pequena aplicação REST API com laravel 10;


### Para iniciar o laravel com o docker:


1. na raiz do projeto, ``` $ cd winx-api ```
2. ``` $ docker-compose up -d ```
3. ``` $ docker-compose exec winx_api bash ```
4. ``` $ cd winx-app ```
5. ``` $ composer update ```
6. ``` $ php artisan key:generate ```
7. ``` $ php artisan jwt:secret ```
8. ``` $ php artisan optimize:clear ```



#### Para popular o banco de dados:

1. na raiz do projeto, ``` $ cd winx-api ```
2. ``` $ docker-compose exec winx_api bash  ```
3. ``` $ cd winx-app ```
4. ``` $ php artisan migrate:fresh --seed ```


### Teste o acesso da api em http://localhost/


#### Para realizar os testes:
1. ``` $ php artisan test ```
2. Ou diretamente com PHPUnity: ``` $ vendor/bin/phpunit ```


#### Para gerar e visualizar o Swagger:
1. ``` $ php artisan l5-swagger:generate ```
2. Acesse a documentação no navegador: ``` http://localhost/api/documentation ```
2. Faça o login e salve o token no botão: ``` Authorize ```


#### Para executar os Jobs:
1. ``` $ php artisan queue:work ```


## Esta disponível o arquivo para api no postman na raiz do projeto!


## Esta disponível o arquivo csv para importar colaboradores na raiz do projeto!
``` Teste Winx - Página1.csv ```