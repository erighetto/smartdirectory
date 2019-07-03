
# Smartdirectory  
  

## Static website generation test

  This is a project where I tried to test the possibility of generate a static file web site using the Symfony 4 framework.

## Bootstrapping the project

    git clone git@github.com:erighetto/smartdirectory.git    
    cd smartdirectory    
    docker-compose up    
    docker exec -it -u application smartdirectory_php bash    
    composer install --prefer-dist    
    composer dump-env prod   
    php bin/console doctrine:schema:update --force    
    php bin/console doctrine:database:import .dumps/smartdirectory.sql   

  
## Compile assets  

    cd smartdirectory    
    yarn install    
    yarn build  

  
  
## Generate static file  

    docker exec -it -u application smartdirectory_php bash   
    ./build.sh

