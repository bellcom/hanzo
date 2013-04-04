#
# Hanzo / Pompdelux production deploy.
#

set :deploy_to,   "/var/www/pompdelux"

# default environment, used by default functions
set :symfony_env_prod, "prod_dk"
set :symfony_env_prods, ["prod_fi", "prod_se", "prod_no", "prod_com", "prod_nl", "prod_dk", "prod_fi_consultant", "prod_se_consultant", "prod_no_consultant", "prod_nl_consultant", "prod_dk_consultant"]

set :adminserver, "pdladmin"
set :staticserver, "pdlstatic1"

set :branch, "master"

# list of servers to deploy to
role :app, 'pdlfront-dk3', 'pdlfront-dk2', 'pdlfront-dk1', 'pdlfront-no1', 'pdlfront-se1', 'pdlfront-nl1', 'pdlfront-fi1', 'pdlfront-dk4', 'pdlfront-dk5', 'pdladmin', 'pdlkons-dk1', 'pdlstatic1'

# :apache should contain our apache servers. Used in reload_apache and apcclear
role :apache, 'pdlfront-dk3', 'pdlfront-dk2', 'pdlfront-dk1', 'pdlfront-no1', 'pdlfront-se1', 'pdlfront-nl1', 'pdlfront-fi1', 'pdlfront-dk4', 'pdlfront-dk5', 'pdladmin', 'pdlkons-dk1'

# our redis server. clear cache here
role :redis, adminserver, :primary => true

# our static server. run assets dumps here
role :static, staticserver, :primary => true

# where to run migrations. :db is also a default and may be used internally.
role :db, adminserver, :primary => true  # where to run migrations

# only notify New Relic on production deplos
after 'deploy:send_email', 'deploy:newrelic_notify'

# own tasks. copy config
namespace :deploy do
  desc "Copy default parameters.ini and hanzo.yml to shared dir"
  task :copy_prod_config do
    run("mkdir -p #{shared_path}/app/config/ && wget -q --output-document=#{shared_path}/app/config/parameters.ini http://tools.bellcom.dk/hanzo/parameters.ini && wget -q --output-document=#{shared_path}/app/config/hanzo.yml http://tools.bellcom.dk/hanzo/hanzo.yml")
  end
# own tasks. copy vhost
  desc "Copy default vhost from stat"
  task :copy_vhost, :roles => :apache do
    run("sudo wget -q --output-document=/etc/apache2/sites-available/pompdelux http://tools.bellcom.dk/hanzo/pompdelux-vhost.txt")
  end
end
