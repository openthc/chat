# Chat

OpenTHC Chat System.
Tools for deploying and managing Mattermost integrated into ... stuff.

Chat is Mattermost service running behind an Nginx proxy.


## Create Database

```sql
create user openthc_chat with encrypted password 'openthc_chat';
create database openthc_chat with owner openthc_chat;
```


## Install Mattermost

- [Download](https://mattermost.com/download/)
- [Install Tarball](https://docs.mattermost.com/install/install-tar.html)

```
mkdir tmp
cd tmp
wget https://releases.mattermost.com/10.6.1/mattermost-10.6.1-linux-amd64.tar.gz
mv mattermost ../mattermost-10.6.1
cd ..
ln -s mattermost-10.6.1 mattermost
```

Then configure it.
Use our example configuration file and change all the `openthc.example.com` to something good.

## Create Users

```
./bin/mmctl user create --email test@openthc.dev --username 'test565' --password 'passweed'
./bin/mmctl user create --email mbw@openthc.dev --username 'mbw' --password 'passweed'
./bin/mmctl user create --email djb@openthc.dev --username 'djb' --password 'passweed'
```

## Create Teams

```
./bin/mmctl team create --name 'usa-wa' --display-name "USA/Washington" --email 'root@openthc.dev'
./bin/mmctl team create --name 'usa-nm' --display-name "USA/New Mexico" --email 'root@openthc.dev'
./bin/mmctl team users add usa-wa test@openthc.dev
./bin/mmctl team users add usa-or mbw@openthc.dev
./bin/mmctl team users add usa-nm mbw@openthc.dev
./bin/mmctl team users add usa-ny mbw@openthc.dev
./bin/mmctl team users add usa-ny test@openthc.dev
./bin/mmctl team users add usa-nm test@openthc.dev
./bin/mmctl team users add usa-or test@openthc.dev
./bin/mmctl team users add usa-or djb@openthc.dev
./bin/mmctl team users add usa-nm djb@openthc.dev
./bin/mmctl team users add usa-ny djb@openthc.dev

```

Then you have to go into each Team and upload files and configure the other nice things in there, manually.
Not sure how to automatically trigger that in the database/filesystem.

### Legacy Install

@note These are wrong for >=8.0

Follow their instructions, get the latest build and install the binary.

* https://docs.mattermost.com/administration/upgrade.html

```shell
wget 'https://releases.mattermost.com/5.35.1/mattermost-5.35.1-linux-amd64.tar.gz'
mkdir mattermost-5.35.1
tar -zxf mattermost-5.35.1-linux-amd64.tar.gz -C mattermost-5.35.1 --strip-components=1
# upgrade copy data
cp ./mattermost/config/config.json ./mattermost-5.35.1/config/config.json
rsync -av --dry-run ./mattermost/data/ ./mattermost-5.35.1/data/
# rsync -av --dry-run ./mattermost/plugins/ ./mattermost-5.35.1/plugins/
# rsync -av --dry-run ./mattermost/client/plugins/ ./mattermost-5.35.1/client/plugins/
rm mattermost
ln -s ./mattermost-5.35.1 ./mattermost
./mattermost/bin/mattermost user password 'root@openthc.com' $SECRET_PASSWORD
```

## Configure Caddy - v1

Simple as this

```
chat.openthc.dev {

	handle /auth/open {
		root * /opt/openthc/chat/webroot/
		import common404
		file_server
		php_fastcgi unix//run/php/php7.4-fpm.sock {
			try_files {path} {path}/index.html {path}/index.php main.php
		}
	}

	handle {
		# root * /opt/openthc/chat/webroot/
		import common404
		reverse_proxy 127.0.0.1:8065
	}

}
```

## Configure Nginx - v0

Use nginx as a proxy, following the example configuration.
It's default configuration is to forward all traffic, except for PHP stuff, to Mattermost.
Check out ./etc/nginx.conf and symlink into `/etc/nginx/sites-enabled/`

- https://docs.mattermost.com/install/config-proxy-nginx.html

## Configure Certbot

 * @see https://eff-certbot.readthedocs.io/en/stable/using.html#setting-up-automated-renewal

Add this so Nginx restarts when it gets a new certificate

```
cat <<<EOF > /etc/letsencrypt/renewal-hooks/deploy/nginx.sh
#!/bin/sh

systemctl restart nginx
EOF
chmod 0755 /etc/letsencrypt/renewal-hooks/deploy/nginx.sh
```

## Upgrade

```
#
#
#
OLD_VERSION="6.1.0"
NEW_VERSION="8.1.2"


  971  wget https://releases.mattermost.com/8.1.2/mattermost-8.1.2-linux-amd64.tar.gz
  976  tar -zxf mattermost-8.1.2-linux-amd64.tar.gz --transform='s,^[^/]\+,\0-8.1.2,'


  985  kill $(pidof mattermost)
  986  pidof mattermost

  989  cd mattermost-6.1.0/
  990  rsync -av mattermost-6.1.0/config/ mattermost-8.1.2/config/
  991  rsync -av mattermost-6.1.0/data/   mattermost-8.1.2/data/
  992  rsync -av mattermost-6.1.0/data/   mattermost-8.1.2/data/

  996  rm mattermost
  997  ln -s mattermost-8.1.2 mattermost

  999  bash -x ./mattermost.sh
 1002  tail -f mattermost/mattermost.log

```


## Upgrade Cleanup

Use this to find duplicate files in the old directory.
Then use perl to cleanup and generate some rm commands.

```
diff -qrs ./new ./old > same.txt
perl -e '($f1, $f2) = m/Files (.+) and (.+) are identical/; print "rm \"$f2\"\n";' -n ./same.txt > rm-same.sh
```

## Integrate

- https://developers.mattermost.com/integrate/reference/server/server-reference/

Our ingetration uses some PHP scripts to boot-strap the authentication from our universe.
