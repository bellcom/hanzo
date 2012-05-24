set :domain,      "pdlfront-dk1" # hf@bellcom.dk: _Skal_ være en af dem som er defineret i rollerne
set :deploy_to,   "/var/www/testpompdelux.dk" 
set :symfony_env_prod, "prod"

# Your HTTP server, Apache/etc
role(:web) do
   frontend_list
end
# This may be the same as your `Web` server
role(:app) do
   frontend_list
end

def frontend_list
  contentsArray = Array.new
  contentsArray = File.readlines("tools/deploy/frontend_list.txt")
end

role :db, domain, :primary => true  # This is where Rails migrations will run

namespace :deploy do
  desc "Copy default parameters.ini and hanzo.yml to shared dir"
  task :copy_prod_config do
    run("mkdir -p #{shared_path}/app/config/ && wget -q --output-document=#{shared_path}/app/config/parameters.ini http://tools.bellcom.dk/hanzo/parameters.ini && wget -q --output-document=#{shared_path}/app/config/hanzo.yml http://tools.bellcom.dk/hanzo/hanzo.yml")
  end
  desc "Roll out apc-clear.php"
  task :copy_apcclear do
    run("wget -q --output-document=/var/www/apc-clear.php http://tools.bellcom.dk/hanzo/apc-clear.php.txt")
  end
  desc "Clear apc cache on the local server"
  task :apcclear do
    run("wget -q -O /dev/null http://localhost/apc-clear.php")
  end
end


