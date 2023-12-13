# Sannlarm


## Prerequisites installation

The app requires PHP 8.3 and Supervisor

```bash
sudo apt update
sudo apt upgrade

sudo apt install software-properties-common
sudo add-apt-repository ppa:ondrej/php
sudo apt update
sudo apt install php8.3
sudo apt install php8.3-xml php8.3-mbstring php8.3-curl php8.3-gd php8.3-mysql php8.3-zip php8.3-intl
php -v

sudo apt install supervisor
wget https://get.symfony.com/cli/installer -O - | bash
```

Start and enable Supervisor to run on boot
```bash
sudo systemctl start supervisor
sudo systemctl enable supervisor
```

## Project installation

- Create .env.local file and populate values
- `composer install`
- Run instructions inside config/messenger-workers.ini
- Start webserver `symfony serve -d` this needs to be run each time the pi is rebooted