# Chat

OpenTHC Chat System.
Tools for deploying and managing Mattermost integrated into ... stuff.

Chat is Mattermost service running behind an Nginx proxy.


## Install Mattermost

Follow their instructions, get the latest build and install the binary.

* https://docs.mattermost.com/administration/upgrade.html

```
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

## Configure Nginx

Use nginx as a proxy, following the example configuration.
It's default configuration is to forward all traffic, except for PHP stuff, to Mattermost.

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

