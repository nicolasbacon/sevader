<?php
namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class CommunesFrance
{
    private $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    public function getCommunesFrance(): array
    {
        $response = $this->client->request(
            'GET',
            'https://geo.api.gouv.fr/communes'
        );

        return $response->toArray();
    }

    public function getNomCommune() :array
    {
       $communes = 'https://geo.api.gouv.fr/communes'
       ;

        $json = file_get_contents($communes);
        $links = json_decode($json, TRUE);
$nomCommunes =[];


       foreach ($links as $key=>$val ){
           $nomCommunes[] = $val['nom'];
            //echo $nom ;
            //array_push($nomCommunes,$nom);
       }


       return $nomCommunes;
    }
}