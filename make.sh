#!/bin/bash
#
# Install Helper
#
# SPDX-License-Identifier: MIT
#

set -o errexit
set -o errtrace
set -o nounset
set -o pipefail

APP_ROOT=$( cd -- "$( dirname -- "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )

cd "$APP_ROOT"

OPENTHC_CHAT_ORIGIN="${OPENTHC_CHAT_ORIGIN:-}"
if [ -z "$OPENTHC_CHAT_ORIGIN" ]
then
	echo "Set Env"
	exit 1
fi

composer install --no-ansi --no-progress --classmap-authoritative


#
# Install Mattermost
MATTERMOST_VERSION="10.6.1"
if [ ! -d "mattermost-${MATTERMOST_VERSION}" ]
then
	mkdir tmp
	cd tmp
	wget https://releases.mattermost.com/${MATTERMOST_VERSION}/mattermost-${MATTERMOST_VERSION}-linux-amd64.tar.gz
	tar -zxf mattermost-${MATTERMOST_VERSION}-linux-amd64.tar.gz
	mv mattermost ../mattermost-${MATTERMOST_VERSION}
	cd ..
	rm -fr ./tmp

	# Make Link
	ln -s mattermost-${MATTERMOST_VERSION} mattermost

fi


#
# Configure Mattermost
# Maybe use jq?
diff -u etc/mattermost-config.json mattermost/config/config.json
# Update the Config....
cat etc/mattermost-config.json > new-config.json
F0="new-config.json"
F1="tmp-config.json"

jq --tab ".\"ServiceSettings\".\"SiteURL\" = \"${OPENTHC_CHAT_ORIGIN}\"" \
	"$F0" > "$F1" && mv "$F1" "$F0"

jq --tab '."ServiceSettings"."ListenAddress" = "127.0.0.1:8065"' \
	"$F0" > "$F1" && mv "$F1" "$F0"

# Enable mmctl --local
jq --tab '."ServiceSettings"."EnableLocalMode" = true' \
	"$F0" > "$F1" && mv "$F1" "$F0"

# Allow user to Create from the UI
jq --tab '."ServiceSettings"."EnableUserCreation" = false' \
	"$F0" > "$F1" && mv "$F1" "$F0"

#
# Start Mattermost
./mattermost.sh start
