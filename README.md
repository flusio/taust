# taust

taust is a simple monitoring tool.
It allows you to monitor:

- that your websites are up (only HTTPS);
- basic metrics of your servers (memory, CPU and disks space).

It is able to notify you about alerts by emails or SMS (only via the French Free Mobile operator).

It supports PHP 8.0+ and has very few dependencies.

taust is licensed under [AGPL 3](/LICENSE).

## Contributing

taust is a personal project and I consider it finished.
I do not intend to add new features, but I will maintain it to fix bugs, or to add support for new PHP versions.
By consequence, I am going to refuse your feature requests and also probably your Pull Requests.

## Credits

Favicon made by [Good Ware](https://www.flaticon.com/authors/good-ware) from [flaticon.com](https://www.flaticon.com/).

## Documentation

### Install on your server

First thing: **should you install taust on your server?**
I would say no.
taust is a personal project and I didn't designed it to be used by someone else.
That's being said: it's up to you to use it or not!
Just note that the documentation is not well detailed and it is expected you're at ease with managing a server.

You’ll need:

- PHP 8.0+ (with intl, gettext, pcntl and pdo\_pgsql extensions);
- a PostgreSQL database;
- a webserver (Nginx in this documentation).

Download taust:

```console
$ cd /var/www
$ git clone --recurse-submodules https://github.com/flusio/taust.git
$ cd taust
```

Create a `.env` file:

```console
$ cp env.sample .env
$ vim .env # or edit with nano or whatever editor you prefer
```

The environment file is commented so it should not be too complicated to setup correctly.

Initialize the database:

```console
$ php cli migrations setup --seed
```

Set correct permissions on the files:

```console
$ sudo chown -R www-data:www-data .
$ sudo chmod 400 .env
```

Then, configure your virtual host to serve PHP files from the `public/` directory.
You can inspire yourself with the Nginx configuration in [`docker/nginx.conf`](/docker/nginx.conf).
Note that taust must be served on HTTPS in production.
You can use [certbot](https://certbot.eff.org/) for that.

Enable the site:

```console
$ sudo ln -s /etc/nginx/sites-available/taust.conf /etc/nginx/sites-enabled/taust.conf
$ sudo systemctl reload nginx
```

The last step is to configure the asynchronous jobs.

You can use Systemd for that.
First, create a `taust.service` file under `/etc/systemd/system/`:

```systemd
[Unit]
Description=A job worker for taust

[Service]
ExecStart=php /var/www/taust/cli jobs watch
User=www-data
Group=www-data

Restart=on-failure
RestartSec=5s

[Install]
WantedBy=multi-user.target
```

Then, enable and start the service with:

```console
$ sudo systemctl enable taust.service
$ sudo systemctl start taust.service
```

This will start a Jobs Worker in background wich will monitor the domains and check/send alarms.

If you prefer, you can configure a Cron task instead. For instance, with `crontab -u www-data -e`:

```cron
* * * * * php /var/www/taust/cli jobs watch --stop-after=5 >>/var/log/taust-jobs.txt 2>&1
```

### Update taust

Fetch the new code with Git:

```console
$ git pull --recurse-submodules
```

Run the migrations:

```console
$ sudo -u www-data php cli migrations setup --seed
```

Restart the jobs worker:

```console
$ sudo systemctl restart taust.service
```

Optional last step that might be useful: verify that your instance is still online.

### Install for development

Download taust:

```console
$ git clone --recurse-submodules https://github.com/flusio/taust.git
$ cd taust
```

Install [Docker](https://docs.docker.com/engine/install/).

Install the development dependencies:

```console
$ make install
```

Start the application:

```console
$ make docker-start
```

Setup the database:

```console
$ make setup
```
