use password hash to generate password for definations.js


Creating rabitmq image

```
docker image build -t rabitmq-image .
```

Docker run command to start rabitmq container

```
docker run --detach -p 15672:15672 -p 5672:5672 --name rabitmq-container \
-v ./rabbitmq.config:/etc/rabbitmq/rabbitmq.config:ro \
-v ./definitions.json:/etc/rabbitmq/definitions.json:ro \
-v ./rabbitmq:/var/lib/rabbitmq \
rabitmq-image
```