extension = "newrelic.so"

[newrelic]
newrelic.enabled = true
newrelic.license = '{{getenv "NEWRELIC_KEY" "none"}}'
newrelic.appname = "AIM - Production"
newrelic.framework = "laravel"
newrelic.process_host.display_name = "AIM - Prod Host"

;; Tags
newrelic.labels = 'App:AIM;Environment:{{getenv "APP_ENV"}};Region:{{aws.EC2Region "none"}};Instance:{{aws.EC2Meta "instance-id" "none"}};Team:Insurance'

;; Daemon settings
newrelic.daemon.utilization.detect_azure = false
newrelic.daemon.utilization.detect_gcp = false
newrelic.daemon.utilization.detect_pcf = false

;; Custom settings
newrelic.transaction_tracer.enabled = true
newrelic.distributed_tracing_enabled = true
newrelic.transaction_tracer.detail = false
newrelic.browser_monitoring.auto_instrument = false
newrelic.error_collector.prioritize_api_errors = true

;; For full list of ini settings, go to https://docs.newrelic.com/docs/agents/php-agent/configuration/php-agent-configuration
