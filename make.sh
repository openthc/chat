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

composer install --no-ansi --no-progress --classmap-authoritative

if [ ! -f etc/config.php ]
then
	echo "Create etc/config.php"
	exit 1
fi

OPENTHC_CHAT_ORIGIN="${OPENTHC_CHAT_ORIGIN:-}"
if [ -z "$OPENTHC_CHAT_ORIGIN" ]
then
	echo "Set OPENTHC_CHAT_ORIGIN"
	exit 1
fi


#
# Install Mattermost
MATTERMOST_VERSION="10.7.0"
MATTERMOST_PACKAGE="mattermost-team-${MATTERMOST_VERSION}-linux-amd64.tar.gz"
if [ ! -d "mattermost-${MATTERMOST_VERSION}" ]
then

	echo "Downloading Server"

	mkdir tmp
	cd tmp
	wget --quiet https://releases.mattermost.com/${MATTERMOST_VERSION}/${MATTERMOST_PACKAGE}
	tar -zxf "${MATTERMOST_PACKAGE}"
	mv mattermost ../mattermost-${MATTERMOST_VERSION}
	cd ..
	rm -fr ./tmp

	# Make Link
	ln -s mattermost-${MATTERMOST_VERSION} mattermost

	#
	# Configure Mattermost
	diff -uw etc/mattermost-config.json mattermost/config/config.json || true

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

	d=$(date +%Y%m%d%H%M)
	cp mattermost/config/config.json mattermost/config/config.json.$d
	mv "$F0" mattermost/config/config.json

fi



#
# Start Mattermost
./mattermost.sh start

# Wait ?

if [ ! ~/.config/mmctl/config ]
then
	echo "Login with:"
	echo "  mattermost/bin/mmctl auth login ${OPENTHC_CHAT_ORIGIN}"
	echo "Then update etc/config.php"
fi
