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
- [Docker](https://docs.mattermost.com/install/install-docker.html)

Then configure it.
Use our [example configuration file](etc/mattermost-config.json) and change all the `openthc.example.com` to something good.


## Create Users

Have to have the config values TeamSettings.EnableUserCreation (maybe?) and  EmailSettings.EnableSignUpWithEmail both set to TRUE for setup.
Then disable after the first user is added,

https://chat.openthc.example.com/signup_user_complete

```
./bin/mmctl user create --email <SOMETHING> --username <SOMETHING> --password '<PASSWORD>'
```


## Create Teams

```
./bin/mmctl team create --name 'usa-wa' --display-name "USA/Washington" --email '<SOME_EMAIL>'
./bin/mmctl team users add <TEAM> <USER>
```

Then you have to go into each Team and upload files and configure the other nice things in there, manually.
Not sure how to automatically trigger that in the database/filesystem.

## Permissions

You have to go into this permissions page and configure them as you see fit.

https://chat.openthc.example.com/admin_console/user_management/permissions/system_scheme


## Caddy

See ./etc/Caddyfile-example


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

Our integration uses some PHP scripts to boot-strap the authentication from our universe.
