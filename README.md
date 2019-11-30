# Chat

OpenTHC Chat System.
Tools for deploying and managing Mattermost integrated into ... stuff.

Chat is Mattermost service running behind an Nginx proxy.


## Install Mattermost

Follow their instructions, get the latest build and install the binary.


## Configure Nginx

Use nginx as a proxy, following the example configuration.
It's default configuration is to forward all traffic, except for PHP stuff, to Mattermost.

With this configuration, we need to dump the Mattermost home page to our webroot:

    curl http://127.0.0.1:8065/ > webroot/index.html

This file will need to be updated after every upgrade.
Or you can customize it.
