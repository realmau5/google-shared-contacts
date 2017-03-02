# Google Shared Contacts Manager

Use this tool to manage shared contacts on a Google for Work / Google App domain. You can use this to share the contact details of vendors, emergency contacts, janitorial services, colleagues or other important persons with the members of your Google Apps-domain.

This tool does NOT work with your regular GMail account, and you need to be domain admin for this tool to work. Contacts shared in this fashion will show up in _most_ phones. 

It is a self-hosted tool, meaning that you should download the source code, install the dependencies and host it yourself. This is the most privacy-friendly option.

## Installation

Installations instructions for Ubuntu 16.04 with LAMP and SSH enabled. All instructions as root.

### Update
```bash
apt update && apt upgrade -y
```

### Install Composer

```bash
cd /root
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php -r "if (hash_file('SHA384', 'composer-setup.php') === '55d6ead61b29c7bdee5cccfb50076874187bd9f21f65d8991d46ec5cc90518f447387fb9f76ebae1fbbacf329e583e30') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
php composer-setup.php
php -r "unlink('composer-setup.php');"
```

If it ran with no errors, you’ll end up with composer.phar file in the root directory

### Get software and unzip

Copy and unzip google-contacts-manager-master.zip file in `/var/www`
```bash
cd /var/www
wget https://github.com/JC5/google-shared-contacts/archive/master.zip
unzip google-shared-contacts-master.zip
mv /var/www/google-shared-contacts-master /var/www/google-shared-contacts
```

### Permissions

Give permissions to the directory

```bash
chown -R www-data:www-data /var/www/google-shared-contacts/
```

### Copy composer

Copy composer.phar to that directory from root

```bash
cd /root
cp composer.phar /var/www/google-shared-contacts/
```

### Install php-mbstring for Composer

```bash
apt install php-mbstring
```

### Run the Google-Shared-Contacts installer

```bash
cd /var/www/google-shared-contacts
php composer.phar install --no-dev
```
This should install all dependencies and exit normally, with no errors.

### Config file for GSC

```bash
cp .env.example .env
```

### Generate key for config file. 
```bash
php artisan key:generate
```

This will automatically put the generated key in the .env file as below:

```
APP_ENV=local
APP_DEBUG=true
APP_KEY=base64:blablablabalblablablabalblablablabalblablablabalblablablabalblablablabal
APP_URL=http://localhost
```

### Setup Google API key

You need an API key from Google to be allowed to touch other people’s (even your own) Shared Contacts API. Open the .env file. You’ll notice these three fields:

```
GOOGLE_ID=
GOOGLE_SECRET=
GOOGLE_REDIRECT=
```
Browse to [the cloud console](https://console.cloud.google.com/apis/credentials), login if necessary and you should be able to see a button “create credentials”. Select OAuth Client ID, then select Web application. The name should be akin to “Google Shared Contacts”. You can leave the Javascript URL empty but the Authorised redirect URL’s should have these entries:

`http://your-server's-fqdn/oauth2callback`

The important part is the `/oauth2callback`.

If you click create you will see the ID and the secret. They match the config value from the .env file. So fill them in (no quotes necessary) and also fill in one redirect URL:

```
GOOGLE_ID=blablabla.apps.googleusercontent.com
GOOGLE_SECRET=blablabla
GOOGLE_REDIRECT=http://your-server's-fqdn/oauth2callback
```

### Browse to the tool

The root of the tool is in /public/. So either browse to /public/ or make /public/ the root of the subdomain.

If the tool gives weird 404’s, set Apache to AllowOverride All, at least for the subdomain.

### Setup apache with SSL (advanced)

You’ll need your SSL certificate in PEM format and it's private key file. Place them in directories as per the config file below and rename them appropriately:

```bash
nano /etc/apache2/sites-available/000-default.conf
```


```
<VirtualHost *:80>
ServerAdmin webmaster@localhost
<Directory /var/www/google-shared-contacts/public>
        Require all granted
        AllowOverride All
   </Directory>
    DocumentRoot /var/www/google-shared-contacts/public
    ServerName your-server's-fqdn
    Redirect permanent / https://your-server's-fqdn/
        ErrorLog /var/log/apache2/gsc.error.log
        CustomLog /var/log/apache2/access.log combined
</VirtualHost>
<VirtualHost *:443>
ServerAdmin webmaster@localhost
<Directory /var/www/google-shared-contacts/public>
        Require all granted
        AllowOverride All
   </Directory>
    DocumentRoot /var/www/google-shared-contacts/public
    ServerName your-server's-fqdn
        SSLEngine on
        SSLCertificateFile /etc/ssl/certs/your-cert.crt
        SSLCertificateKeyFile /etc/ssl/private/your-key.key
        ErrorLog /var/log/apache2/gsc.error.log
        CustomLog /var/log/apache2/access.log combined
</VirtualHost>
```

### Modules and restart
Run following commands to enable all the required modules for apache and restart the service

```bash
a2enmod rewrite
a2dismod autoindex
a2enmod speling 
a2enmod ssl
service apache2 restart
```

Browse to the `https://your-server's-fqdn/` - you should see GSC login page.

**DONE!**

## Try it / use it

There are currently two instances publicly available. You can use these freely.

1. [My personal website](https://contacts.nder.be/).
2. [Hosted by the Brunswick School in Greenwich CT](https://gsc.brunswickschool.org/)
