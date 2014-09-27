socat TCP4-LISTEN:5672,fork,reuseaddr TCP4:rabbitmq:5672 &
socat TCP4-LISTEN:15672,fork,reuseaddr TCP4:rabbitmq:15672 &