#!/bin/bash
#
#
#

set -o errexit
set -o errtrace
set -o nounset
set -o pipefail

BIN_PATH=$( cd -- "$( dirname -- "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )
APP_ROOT=$( dirname -- "${BIN_PATH}")

cd "$APP_ROOT"

OPENTHC_CHAT_ORIGIN="${OPENTHC_CHAT_ORIGIN:-}"
if [ -z "$OPENTHC_CHAT_ORIGIN" ]
then
	echo "Set Env"
	exit 1
fi


OPENTHC_CHAT_ORIGIN="${OPENTHC_CHAT_ORIGIN:-}"
if [ -z "$OPENTHC_CHAT_ORIGIN" ]
then
	echo "Set Env"
	exit 1
fi

# using --local means we don't have to authenticate

mattermost/bin/mmctl --local user create --email ${OPENTHC_CHAT_CONTACT0_USERNAME} --username 'root' --password ${OPENTHC_CHAT_CONTACT0_PASSWORD}
# mattermost/bin/mmctl --local user change-password ${OPENTHC_CHAT_CONTACT0_USERNAME} --password HASHED_PASSWORD --hashed

mattermost/bin/mmctl --local team create --name openthc --display-name "OpenTHC" --private
mattermost/bin/mmctl --local team users add openthc root

# Teams
mattermost/bin/mmctl --local team create --name public --display-name "Public"
mattermost/bin/mmctl --local team create --name usa-me --display-name "USA/Maine" --private
mattermost/bin/mmctl --local team create --name usa-mi --display-name "USA/Michigan" --private
mattermost/bin/mmctl --local team create --name usa-nm --display-name "USA/New Mexico" --private
mattermost/bin/mmctl --local team create --name usa-ny --display-name "USA/New York" --private
mattermost/bin/mmctl --local team create --name usa-vt --display-name "USA/Vermont" --private
mattermost/bin/mmctl --local team create --name usa-wa --display-name "USA/Washington" --private
mattermost/bin/mmctl --local team create --name bra --display-name "Brazil" --private
mattermost/bin/mmctl --local team create --name ecu --display-name "Ecuador" --private
mattermost/bin/mmctl --local team create --name pan --display-name "Panama" --private
mattermost/bin/mmctl --local team create --name per --display-name "Peru" --private
mattermost/bin/mmctl --local team create --name ury --display-name "Uruguay" --private
mattermost/bin/mmctl --local team create --name zaf --display-name "South Africa" --private
