# cqrs-es-2018-ws

# Requisiti

- Docker

# Avvio applicazione

- `docker-compose up -d`
- `docker-compose exec php ./idephix.phar build`

# Regole di dominio

- non si può prenotare il campo in uno slot che è già prenotato
- la prenotazione deve essere di almeno un'ora e massimo tre ore
- il campo è prenotabile dalle 9 alle 23
- la decima prenotazione fatta dall'utente è gratuita
- la conferma deve essere fatta via email e via sms

# Tools

- Adminer: http://localhost:8081/
    - server: mysql
    - user: dev
    - password: dev
