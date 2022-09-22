<?php

namespace App\Service;

use DateTimeImmutable;

class JWTService
{
    // On génère le Json Web Token - Voir https://jwt.io

    /**
     * Génération du JWT 
     * @param array $header
     * @param array $payload
     * @param string $secret
     * @param integer $validity
     * @return string
     */
    public function generate(array $header, array $payload, string $secret, int $validity = 10800): string
    {
        if($validity > 0){
            $now = new DateTimeImmutable();
            $exp = $now->getTimestamp() + $validity;
            // '$exp' poiur 'expiration'
    
            $payload['iat'] = $now->getTimestamp();  // maintenant
            //'iat' signifie 'issued at' 
            $payload['exp'] = $exp;
        }

      

        //On encode JSON puis en base64
        $base64Header = base64_encode(json_encode($header));
        $base64Payload = base64_encode(json_encode($payload));

        //On "nettoie" les valeurs encodées (retrait des +, / et =)
        // On remplace les + par des -, les / par des _ , et les = par des rien "" , car les token ne gere pas ses signes
        $base64Header = str_replace(['+', '/', '='], ['-', '_', ''], $base64Header);
        $base64Payload = str_replace(['+', '/', '='], ['-', '_', ''], $base64Payload);

        //On génère la signature , et pour ça il nous faut un secret 
        //(voir creation de JWT_SECRET dans le fichier 'env.local', et le parametrage dns le fichier 'service.yaml')
        $secret = base64_encode($secret);

        $signature = hash_hmac('sha256', $base64Header . '.' . $base64Payload, $secret, true);
        // hashage en 'sha256': format utilisé par les jwt.

        //on encode la signature en base64
        $base64Signature = base64_encode($signature);
        //on nettoie les valeurs encodées
        $base64Signature = str_replace(['+', '/', '='], ['-', '_', ''], $base64Signature);

        // On crée le token
        $jwt = $base64Header . '.' . $base64Payload . '.' . $base64Signature;

        return $jwt;
    }

    //On vérifie que le token est valide (correctement formé)
    public function isValid(string $token): bool
    {
        return preg_match(
            '/^[a-zA-Z0-9\-\_\=]+\.[a-zA-Z0-9\-\_\=]+\.[a-zA-Z0-9\-\_\=]+$/',
            $token
        ) === 1;
    }

    //On recupère le payload
    public function getPayload(string $token): array
    {
        //on démonte le token
        $array = explode('.', $token);
        //On décode le Payload
        $payload = json_decode(base64_decode($array[1]), true); //true signifie qu'on decode en tableau associatif

        return $payload;

    }

    //On recupère le Header
    public function getHeader(string $token): array
    {
        //on démonte le token
        $array = explode('.', $token);
        //On décode le Header
        $header = json_decode(base64_decode($array[0]), true); //true signifie qu'on decode en tableau associatif

        return $header;

    }

    //On verifie si le token est expiré
    public function isExpired(string $token): bool
    {
        $payload = $this->getPayload($token);

        $now = new DateTimeImmutable();

        return $payload['exp'] < $now->getTimestamp();
    }

    //On verifie la signature du token
    public function check(string $token, string $secret): bool
    {
        //On récupère le header et le payload
        $header = $this->getHeader($token);
        $payload = $this->getPayload($token);

        //On regenere un token
        $verifToken = $this->generate($header, $payload, $secret, 0);

        return $token === $verifToken;

    }
}