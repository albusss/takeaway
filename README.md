# Takeaway challenge
Hi

##### Request example
```json
{
    "phone": "31755173843",
    "restaurant_title": "Tasty pizza", 
    "delivery_time": "2019-06-04 12:10:00",
    "idempotency_key": "196fef60-98d9-11e3-a5e2-080020219aa5"
}
```

## Installation
```bash
git clone git@github.com:albusss/takeaway.git

docker-compose up
```

Specify `MESSAGEBIRD_API_TOKEN` in `docker-compose.yml` for message sending.  
## Tests
Run `php bin/phpinut` inside _message_sender_ docker-container for tests.

## Web interface
There is web interface on _http://localhost:80_ where you can see a log of the last 50 sent messages and at the messages 
that returned any other status then delivered in the last 24 hours (`new` and `error`).

Also, there is RabbitMQ management interface on _http://localhost:15672/_. Login: `rabbitmq`, password: `rabbitmq`.
