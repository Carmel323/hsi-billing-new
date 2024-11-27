COMPOSER_AUTH_BASE64 ?= `cat auth.json | base64`
APP_NAME ?= hsi-isp-billing
CL_BUILD_VERSION ?= latest
ENVIRONMENT_NAME ?= staging
SOURCE_COMMIT = `git rev-parse --short HEAD`

.PHONY: default build local local-down test test-automated test-automated-predeploy push env-validate test-cfn cfn cfn-pipeline chamber migrations

.DEFAULT:
	@echo 'Invalid target.'
	@echo
	@echo '    nginx                    build nginx docker image'
	@echo '    fpm                  	build fpm docker image'
	@echo '    local                    build and run local environment'
	@echo '    local-down               tear down local environment'
	@echo '    test                     run PHPUnit tests'

default: .DEFAULT


DOCKER_BUILD = docker build \
							--build-arg COMPOSER_AUTH_BASE64="$(COMPOSER_AUTH_BASE64)" \
							--build-arg ENVIRONMENT_NAME="$(ENVIRONMENT_NAME)" \
							--build-arg SOURCE_COMMIT="$(SOURCE_COMMIT)"

set_vars:
	@. ops/scripts/vars.sh

nginx: set_vars
	@$(DOCKER_BUILD) --target nginx -t ${APP_NAME}:nginx -f ops/docker/Dockerfile .

fpm: set_vars
	@$(DOCKER_BUILD) --target build -t ${APP_NAME}:fpm -f ops/docker/Dockerfile .

local: set_vars
	docker compose up -d ${APP_NAME}

local-down:
	docker compose down

composer-install:
	docker exec lms-move-app composer install

setup-local: fpm nginx local composer-install create-db migrate seed

create-db:
	docker exec lms-move-mysql mysql -u "root" -e 'create database if not exists move_lms;'

migrate:
	docker exec lms-move-app php artisan migrate

seed:
	docker exec lms-move-app php artisan db:seed

# test: # Implement PHPUnit tests

configure-production: set_vars
	@cat ops/production/vars.sh

configure-staging: set_vars
	@cat ops/staging/vars.sh


## Ops Targets
ENV_VARS = $$(cat ops/${ENVIRONMENT_NAME}/vars.sh | sed -E 's/export ([^=]+)=.*/-e \1/')

test:
	@echo ${PIPELINE}

CFN_ARGS ?= --auto
PIPELINE_IMAGE ?= "public.ecr.aws/cldevops/pipeline-tools:21"
PIPELINE_RUN = docker run -w /app -v $$(pwd)/:/app
PIPELINE_BASE = $(PIPELINE_RUN) -v $(HOME)/.aws:/root/.aws
PIPELINE = ${PIPELINE_BASE} \
	-e "AWS_PROFILE=$(AWS_PROFILE)" \
	-e "AWS_REGION=$(AWS_REGION)" \
	-e "AWS_DEFAULT_REGION=$(AWS_REGION)" \
  	-e "ENVIRONMENT_NAME=$(ENVIRONMENT_NAME)" \
  	-e "APP_NAME=$(APP_NAME)" \
  	-e "CL_BUILD_VERSION=$(CL_BUILD_VERSION)" \
  	-e AWS_ACCESS_KEY_ID \
  	-e AWS_SECRET_ACCESS_KEY \
  	-e AWS_SESSION_TOKEN \
  	-e "CHAMBER_KMS_KEY_ALIAS=alias/$(APP_NAME)-$(ENVIRONMENT_NAME)-app-ps-key" \
	$(ENV_VARS) \
  	$(PIPELINE_IMAGE)

test-cfn:
	${PIPELINE_BASE} $(PIPELINE_IMAGE) cfn-lint /app/ops/cloudformation/*.yml

env-validate:
	@test $(ENVIRONMENT_NAME) || (echo "no ENVIRONMENT_NAME"; exit 1)
	@test $(AWS_REGION) || (echo "no AWS_REGION"; exit 1)
	@echo " AWS_REGION=$(AWS_REGION)\n\
	 ENVIRONMENT_NAME=$(ENVIRONMENT_NAME)"
	@${PIPELINE} aws sts get-caller-identity
	@read -p "Continue? (y/n): " continue; \
	if [ "$$continue" != "y" ]; then exit 1; fi;

cfn: env-validate test-cfn
	${PIPELINE} cops env update-stack app $(ENVIRONMENT_NAME) -v

cfn-pipeline: env-validate test-cfn
	${PIPELINE} cops env update-stack pipeline $(ENVIRONMENT_NAME) -v

chamber: env-validate
	${PIPELINE} chamber import app/$(APP_NAME)/$(ENVIRONMENT_NAME)/run ops/$(ENVIRONMENT_NAME)/env.secrets.json
	${PIPELINE} chamber import app/$(APP_NAME)/$(ENVIRONMENT_NAME)/pre-deploy ops/$(ENVIRONMENT_NAME)/env.predeploysecrets.json

get-secrets:
	${PIPELINE} chamber export app/$(APP_NAME)/$(ENVIRONMENT_NAME)/run -f dotenv > ops/$(ENVIRONMENT_NAME)/secrets.env
	${PIPELINE} chamber export app/$(APP_NAME)/$(ENVIRONMENT_NAME)/pre-deploy -f dotenv > ops/$(ENVIRONMENT_NAME)/predeploy-secrets.env

chamber-export: env-validate
	${PIPELINE} chamber export app/$(APP_NAME)/$(ENVIRONMENT_NAME)/run | jq > ops/$(ENVIRONMENT_NAME)/env.secrets.json
	${PIPELINE} chamber export app/$(APP_NAME)/$(ENVIRONMENT_NAME)/pre-deploy | jq > ops/$(ENVIRONMENT_NAME)/env.predeploysecrets.json

trigger-deploy:
	@. ops/${ENVIRONMENT_NAME}/vars.sh; \
	unset AWS_PROFILE; \
	/usr/local/bin/pipeline-scripts/artifacts.sh

deploy-config:
	@. ops/${ENVIRONMENT_NAME}/vars.sh; \
    unset AWS_PROFILE; \
	aws s3 cp ops/${ENVIRONMENT_NAME}/basic.env  s3://$(CODEPIPELINE_BUCKET)/EnvFiles/$(ENVIRONMENT_NAME)/basic.env; \
	aws s3 cp ops/${ENVIRONMENT_NAME}/predeploybasic.env s3://$(CODEPIPELINE_BUCKET)/EnvFiles/$(ENVIRONMENT_NAME)/predeploybasic.env

restart-services:
	@. ops/${ENVIRONMENT_NAME}/vars.sh; \
    unset AWS_PROFILE; \
    echo "Restarting services in $$CLUSTER_NAME"; \
	aws ecs list-services --cluster $$CLUSTER_NAME --region $$AWS_REGION | jq -r '.serviceArns[]' | grep -i ${APP_NAME} | xargs -I {} aws ecs update-service --cluster $$CLUSTER_NAME --service {} --force-new-deployment --region $$AWS_REGION

push: fpm nginx
	docker tag $(APP_NAME):fpm $(REGISTRY):fpm-$(CL_BUILD_VERSION)
	docker tag $(APP_NAME):nginx $(REGISTRY):nginx-$(CL_BUILD_VERSION)

	# This is used by the pre-deploy task (migrations, etc). Needs to be a predictable tag, since
	# it needs to be created by the pipeline script.
	docker tag $(APP_NAME):fpm $(REGISTRY):fpm-pre-deploy

	docker push $(REGISTRY):fpm-$(CL_BUILD_VERSION)
	docker push $(REGISTRY):nginx-$(CL_BUILD_VERSION)
	docker push $(REGISTRY):fpm-pre-deploy

