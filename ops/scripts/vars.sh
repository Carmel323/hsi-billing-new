#!/bin/sh
set -eu

export APP_NAME=hsi-isp-billing
export GIT_COMMIT=${BITBUCKET_COMMIT:-latest}
export CL_BUILD_VERSION=${BITBUCKET_COMMIT:-latest}
export GIT_ESCAPED_BRANCH="$(echo ${BITBUCKET_BRANCH:-master} | sed -e 's/[]\/$*.^[]/_/g')"
