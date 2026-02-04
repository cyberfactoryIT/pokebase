<?php
// Test: Lista episodes
$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => "https://cardmarket-api-tcg.p.rapidapi.com/pokemon/episodes",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        "x-rapidapi-host: cardmarket-api-tcg.p.rapidapi.com",
        "x-rapidapi-key: 4549717005msh02dfff5f9c87208p1a081fjsnb6ed6ac3cc89"
    ],
]);
$response = curl_exec($curl);
curl_close($curl);
echo $response;
