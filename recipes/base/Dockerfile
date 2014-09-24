FROM ubuntu:12.04
RUN apt-get update && apt-get install -y cron && apt-get install -y curl sudo git rsync build-essential wget ruby1.9.1 rubygems1.9.1 python-software-properties && curl -L https://www.opscode.com/chef/install.sh | bash && wget -O - https://github.com/travis-ci/travis-cookbooks/archive/master.tar.gz | tar -xz && mkdir -p /var/chef/cookbooks && cp -a travis-cookbooks-master/ci_environment/* /var/chef/cookbooks
RUN adduser travis --disabled-password --gecos ""
RUN mkdir /home/travis/builds
ADD travis.json travis.json
RUN locale-gen en_US.UTF-8
ENV LANG en_US.UTF-8
ENV LANGUAGE en_US:en
ENV LC_ALL en_US.UTF-8
RUN chmod 777 /tmp
RUN echo 'travis ALL=(ALL) NOPASSWD:ALL' >> /etc/sudoers