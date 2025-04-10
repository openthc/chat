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


Created my first users, via API like this


```shell
curl 'https://chat.openthc.example.com/api/v4/users' \
  -H 'accept: */*' \
  -H 'accept-language: en' \
  -H 'content-type: application/json' \
  -b 'rl_page_init_referrer=RudderEncrypt%3AU2FsdGVkX19SZB50SsRlGqF1jC1EFrZP0z5yC%2FlDhyo%3D; rl_page_init_referring_domain=RudderEncrypt%3AU2FsdGVkX1%2FD3Nf9TSlZLnr96w8Iav07c%2BUCHwkP0g4%3D; rl_anonymous_id=RudderEncrypt%3AU2FsdGVkX1%2BXCoN4nhwugi45%2B%2Fv72Wzl3s9hoU%2BCwkqp3M01L8nylct8E4YpyHLAuPfeQgvv9gO7OCePhuOxFQ%3D%3D; rl_group_id=RudderEncrypt%3AU2FsdGVkX18rYnk1bS3RJ8u8CaOuvoU0JjBZU65qwc0%3D; rl_group_trait=RudderEncrypt%3AU2FsdGVkX19VRuNpR0OvkEja0%2B0ivJU6XOSWdJmhPos%3D; rl_user_id=RudderEncrypt%3AU2FsdGVkX1%2B%2BJsfjpb8DXhU32DwD%2FQ0Ek8TLrCzNf0Anu3%2FBeUDCxxL5eQvkPGxm; rl_trait=RudderEncrypt%3AU2FsdGVkX1%2BynmKbjyw4fclhyHH22ZRoJszF7wKEguM%3D' \
  -H 'origin: https://chat.openthc.example.com' \
  -H 'priority: u=1, i' \
  -H 'sec-ch-ua: "Chromium";v="134", "Not:A-Brand";v="24", "Google Chrome";v="134"' \
  -H 'sec-ch-ua-mobile: ?0' \
  -H 'sec-ch-ua-platform: "Linux"' \
  -H 'sec-fetch-dest: empty' \
  -H 'sec-fetch-mode: cors' \
  -H 'sec-fetch-site: same-origin' \
  -H 'user-agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36' \
  -H 'x-requested-with: XMLHttpRequest' \
  --data-raw '{"email":"root@openthc.example.com","username":"root","password":"passweed"}'
```

And made the first team like:

```shell
curl 'https://chat.openthc.example.com/api/v4/teams' \
  -H 'accept: */*' \
  -H 'accept-language: en' \
  -H 'content-type: application/json' \
  -b 'rl_page_init_referrer=RudderEncrypt%3AU2FsdGVkX19SZB50SsRlGqF1jC1EFrZP0z5yC%2FlDhyo%3D; rl_page_init_referring_domain=RudderEncrypt%3AU2FsdGVkX1%2FD3Nf9TSlZLnr96w8Iav07c%2BUCHwkP0g4%3D; rl_anonymous_id=RudderEncrypt%3AU2FsdGVkX1%2BXCoN4nhwugi45%2B%2Fv72Wzl3s9hoU%2BCwkqp3M01L8nylct8E4YpyHLAuPfeQgvv9gO7OCePhuOxFQ%3D%3D; rl_group_id=RudderEncrypt%3AU2FsdGVkX18rYnk1bS3RJ8u8CaOuvoU0JjBZU65qwc0%3D; rl_group_trait=RudderEncrypt%3AU2FsdGVkX19VRuNpR0OvkEja0%2B0ivJU6XOSWdJmhPos%3D; rl_user_id=RudderEncrypt%3AU2FsdGVkX1%2B%2BJsfjpb8DXhU32DwD%2FQ0Ek8TLrCzNf0Anu3%2FBeUDCxxL5eQvkPGxm; rl_trait=RudderEncrypt%3AU2FsdGVkX1%2BynmKbjyw4fclhyHH22ZRoJszF7wKEguM%3D; MMAUTHTOKEN=udtwmj9t4bybdjp511zmczcs6c; MMUSERID=mfd9d7zfgjgo7rnqshoittbdzh; MMCSRF=s8gofxdufin4fy6wbxmi89c94y' \
  -H 'origin: https://chat.openthc.example.com' \
  -H 'priority: u=1, i' \
  -H 'sec-ch-ua: "Chromium";v="134", "Not:A-Brand";v="24", "Google Chrome";v="134"' \
  -H 'sec-ch-ua-mobile: ?0' \
  -H 'sec-ch-ua-platform: "Linux"' \
  -H 'sec-fetch-dest: empty' \
  -H 'sec-fetch-mode: cors' \
  -H 'sec-fetch-site: same-origin' \
  -H 'user-agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36' \
  -H 'x-csrf-token: s8gofxdufin4fy6wbxmi89c94y' \
  -H 'x-requested-with: XMLHttpRequest' \
  --data-raw '{"id":"","create_at":0,"update_at":0,"delete_at":0,"display_name":"OpenTHC","name":"openthc","description":"","email":"","type":"O","company_name":"","allowed_domains":"","invite_id":"","allow_open_invite":false,"scheme_id":"","group_constrained":false}'
```

Then it compleetes

```shell
curl 'https://chat.openthc.example.com/api/v4/system/onboarding/complete' \
  -H 'accept: */*' \
  -H 'accept-language: en' \
  -H 'content-type: application/json' \
  -b 'rl_page_init_referrer=RudderEncrypt%3AU2FsdGVkX19SZB50SsRlGqF1jC1EFrZP0z5yC%2FlDhyo%3D; rl_page_init_referring_domain=RudderEncrypt%3AU2FsdGVkX1%2FD3Nf9TSlZLnr96w8Iav07c%2BUCHwkP0g4%3D; rl_anonymous_id=RudderEncrypt%3AU2FsdGVkX1%2BXCoN4nhwugi45%2B%2Fv72Wzl3s9hoU%2BCwkqp3M01L8nylct8E4YpyHLAuPfeQgvv9gO7OCePhuOxFQ%3D%3D; rl_group_id=RudderEncrypt%3AU2FsdGVkX18rYnk1bS3RJ8u8CaOuvoU0JjBZU65qwc0%3D; rl_group_trait=RudderEncrypt%3AU2FsdGVkX19VRuNpR0OvkEja0%2B0ivJU6XOSWdJmhPos%3D; rl_user_id=RudderEncrypt%3AU2FsdGVkX1%2B%2BJsfjpb8DXhU32DwD%2FQ0Ek8TLrCzNf0Anu3%2FBeUDCxxL5eQvkPGxm; rl_trait=RudderEncrypt%3AU2FsdGVkX1%2BynmKbjyw4fclhyHH22ZRoJszF7wKEguM%3D; MMAUTHTOKEN=udtwmj9t4bybdjp511zmczcs6c; MMUSERID=mfd9d7zfgjgo7rnqshoittbdzh; MMCSRF=s8gofxdufin4fy6wbxmi89c94y' \
  -H 'origin: https://chat.openthc.example.com' \
  -H 'priority: u=1, i' \
  -H 'sec-ch-ua: "Chromium";v="134", "Not:A-Brand";v="24", "Google Chrome";v="134"' \
  -H 'sec-ch-ua-mobile: ?0' \
  -H 'sec-ch-ua-platform: "Linux"' \
  -H 'sec-fetch-dest: empty' \
  -H 'sec-fetch-mode: cors' \
  -H 'sec-fetch-site: same-origin' \
  -H 'user-agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36' \
  -H 'x-csrf-token: s8gofxdufin4fy6wbxmi89c94y' \
  -H 'x-requested-with: XMLHttpRequest' \
  --data-raw '{"organization":"OpenTHC","install_plugins":[]}'
```

```
./bin/mmctl user create --email <SOMETHING> --username <SOMETHING> --password '<PASSWORD>'
```

The email is folded by Mattermost into lowercase and then becomes case sensitive from CLI tools.

mattermost/bin/mmctl user create --email 'test+092T0800@openthc.example.com' --username 'test092T0800' --password 'passweed'
Becomes
mattermost/bin/mmctl team users add public 'test+092t0800@openthc.example.com'

Notice te lowercase 'T'

## Create Teams

```
./bin/mmctl team create --name 'usa-wa' --display-name "USA/Washington" --email '<SOME_EMAIL>'
./bin/mmctl team users add <TEAM> <USER>
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
chat.openthc.example.com {

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

Our integration uses some PHP scripts to boot-strap the authentication from our universe.
