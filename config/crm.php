<?php

return [
    'endpoint' => (getenv('APP_ENV') === 'dev') ? 'https://siebelpp.sisgm.com/' : 'https://siebel.sisgm.com/',
    'email' => getenv('CRM_EMAIL'),
    'password' => getenv('CRM_PASSWORD'),
];
