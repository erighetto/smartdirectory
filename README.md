
# Smartdirectory  
  

## Static website generation test

  This is a project where I tried to test the possibility of generate a static file web site using the Symfony 4 framework.

## Bootstrapping the project

    git clone git@github.com:erighetto/smartdirectory.git    
    cd smartdirectory   
    make start 
    make node 
    yarn install     
    yarn build 
    exit 
    make shell    
    composer install --prefer-dist    
    composer dump-env dev   
    php bin/console doctrine:schema:update --force    
    php bin/console doctrine:database:import .dumps/smartdirectory.sql
    exit

  
## Generate static file  

    make shell  
    ./build.sh

