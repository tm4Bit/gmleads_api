<?php

return [
    'token' => getenv('GITHUB_API_TOKEN'),
    'owner' => getenv('GITHUB_REPO_OWNER'),
    'repo'  => getenv('GITHUB_REPO_NAME'),
    'base_uri' => 'https://api.github.com/',
];