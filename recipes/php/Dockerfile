FROM davert/roboci-base
RUN echo '/usr/bin/Xvfb :99 -ac -screen 0 1024x768x24 &' > /etc/init.d/xvfb
RUN chef-solo -o php::multi,composer -j travis.json
RUN curl -s http://getcomposer.org/installer | php
RUN mv composer.phar /usr/bin/composer
USER travis
ENV PATH $PATH:/home/travis/.phpenv/bin
RUN ["/bin/bash", "-l", "-c", "eval \"$(phpenv init -)\""]
RUN phpenv rehash 2>/dev/null