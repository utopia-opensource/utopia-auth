# Utopia-auth
Example of user authorization on your web service in Utopia Network.

## Scheme of work
![scheme](https://sagleft.ru/projects/utopia/auth/uauth_en.png)

### Requirements
* PHP >=7.0;
* Apache >=2.4;
* MariaDB / MySQL 5.6;
* Composer;

### Install
```
git clone https://github.com/Sagleft/utopia-auth.git
cd utopia-auth
mkdir view/cache
chmod 777 view/cache
cp .env.example .env
cp composer.json.example composer.json
composer update
cd controller/public_html
cp example.htaccess .htaccess
```

apache dir: ```controller/public_html```

### Copyright

Copyright (c) 2019 Sagleft.
