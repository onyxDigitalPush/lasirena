<?php
return [
    "driver" => "smtp",
    "host" => EMAIL_HOST,
    "port" => EMAIL_PORT,
    "from" => array(
        "address" => EMAIL_SENDER_ADDRESS,
        "name" => EMAIL_SENDER_NAME
    ),
    "username" => EMAIL_SMTP_AUTH_USER,
    "password" => EMAIL_SMTP_AUTH_PASSWORD,
    "sendmail" => "/usr/sbin/sendmail -bs"
];