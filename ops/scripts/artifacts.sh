#!/bin/sh
set -eu

TRANSFORM_BASIC_ENV="${TRANSFORM_BASIC_ENV:-0}"

# partially to have some info during the later CodeBuild, partially to have
# a validation step for expected environment variables
cat << EOF > deploy_metadata.env
set -eau
GIT_COMMIT="$GIT_COMMIT"
APP_NAME="$APP_NAME"
ENVIRONMENT_NAME="$ENVIRONMENT_NAME"
CODEPIPELINE_BUCKET="$CODEPIPELINE_BUCKET"
EOF

cops cfn input -i $ENVIRONMENT_NAME/stacks.yml -T codepipeline -q .app > app.json

if [ $TRANSFORM_BASIC_ENV == "1" ]; then
    jqy -i $ENVIRONMENT_NAME/env.yml . > env.basic.json
fi

zip -r app.zip .

aws s3 cp app.zip s3://$CODEPIPELINE_BUCKET/$APP_NAME/$ENVIRONMENT_NAME/
