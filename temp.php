<?php
$token = bin2hex(openssl_random_pseudo_bytes(8));
echo $token;
