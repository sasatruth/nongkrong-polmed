<?php
namespace App\Models;
use CodeIgniter\Model;

class TempatNongkrongModel extends Model {
    protected $supabaseUrl;
    protected $supabaseKey;

    public function __construct() {
        parent::__construct();
        $this->supabaseUrl = env('SUPABASE_URL');
        $this->supabaseKey = env('SUPABASE_KEY');
    }

    public function getAllPlaces() {
        $url = $this->supabaseUrl . '/rest/v1/tempat_nongkrong?select=*';
        $client = \Config\Services::curlrequest();
        try {
            $response = $client->request('GET', $url, [
                'headers' => [
                    'apikey' => $this->supabaseKey,
                    'Authorization' => 'Bearer ' . $this->supabaseKey,
                ]
            ]);
            return json_decode($response->getBody(), true);
        } catch (\Exception $e) { return []; }
    }
}