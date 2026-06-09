<?php
namespace App\Controllers;

class Home extends BaseController {
    public function index() {
        $data['anggota'] = [
            ['nama' => 'Alya Syahrani', 'peran' => 'Web Developer',  'foto' => 'alyak.JPEG'],
            ['nama' => 'Fattah',        'peran' => 'Web Developer',  'foto' => 'fattah.JPEG'],
            ['nama' => 'Salsa',         'peran' => 'Mobile Developer','foto' => 'salsa.JPEG'],
            ['nama' => 'Muhammad Farid','peran' => 'GIS Specialist',  'foto' => 'farid.JPEG'],
            ['nama' => 'Ariel',         'peran' => 'System Analyst',  'foto' => 'ariel.JPEG'],
            ['nama' => 'Ayu',           'peran' => 'System Analyst',  'foto' => 'ayu.JPEG'],
        ];

        // Hit API Supabase
        $url = env('SUPABASE_URL') . '/rest/v1/tempat_nongkrong?select=*&order=rating.desc';
        $client = \Config\Services::curlrequest();
        try {
            $response = $client->request('GET', $url, [
                'headers' => [
                    'apikey'        => env('SUPABASE_KEY'),
                    'Authorization' => 'Bearer ' . env('SUPABASE_KEY'),
                    'Content-Type'  => 'application/json',
                ]
            ]);
            $data['tempat_nongkrong'] = json_decode($response->getBody(), true) ?? [];
        } catch (\Exception $e) {
            $data['tempat_nongkrong'] = [];
            log_message('error', 'Supabase error: ' . $e->getMessage());
        }

        // ─── DATA SPASIAL DINAMIS + RUTE REALISTIS ────────────────────────────
        $kampusCenter = [3.5626, 98.6569];

        // Koordinat rute realistis (jalan-jalan sekitar Polmed)
        $data['spatial_data'] = [
            'kampus_center' => $kampusCenter,
            'points'        => $data['tempat_nongkrong'],
            
            // RUTE REALISTIS: path jalan yang melengkung alami
            'routes' => [
                // Route 1: Menuju area kafe (jalan selatan)
                'kafe_route' => [
                    [3.5626, 98.6569],    // Kampus
                    [3.5618, 98.6572],    // Pintu keluar kampus
                    [3.5612, 98.6578],    // Belok kiri jalan utama
                    [3.5605, 98.6585],    // Jalan lurus
                    [3.5598, 98.6592],    // Dekat kafe cluster
                ],
                // Route 2: Menuju warkop (jalan timur)
                'warkop_route' => [
                    [3.5626, 98.6569],    // Kampus
                    [3.5631, 98.6575],    // Pintu timur
                    [3.5638, 98.6581],    // Jalan kecil
                    [3.5645, 98.6589],    // Persimpangan
                    [3.5652, 98.6597],    // Area warkop
                ],
                // Route 3: Menuju resto (jalan barat)
                'resto_route' => [
                    [3.5626, 98.6569],    // Kampus
                    [3.5621, 98.6563],    // Pintu barat
                    [3.5615, 98.6557],    // Jalan besar
                    [3.5609, 98.6551],    // Menuju resto
                ],
                // Route 4: Menuju taman (jalan utara)
                'taman_route' => [
                    [3.5626, 98.6569],    // Kampus
                    [3.5634, 98.6565],    // Pintu utara
                    [3.5642, 98.6561],    // Jalan hijau
                    [3.5650, 98.6557],    // Taman kota
                ],
            ],

            'line_colors' => [
                'kafe'   => '#FF6B35',
                'warkop' => '#4ECDC4',
                'resto'  => '#45B7D1',
                'taman'  => '#96CEB4',
            ],

            'polygon_colors' => [
                'kafe'   => ['stroke' => '#FF6B35', 'fill' => '#FF6B3530'],
                'warkop' => ['stroke' => '#4ECDC4', 'fill' => '#4ECDC430'],
                'resto'  => ['stroke' => '#45B7D1', 'fill' => '#45B7D130'],
                'taman'  => ['stroke' => '#96CEB4', 'fill' => '#96CEB430'],
            ],
        ];
        // ──────────────────────────────────────────────────────────────────────

        // Statistik fasilitas dengan ikon
        $fasilitasStats = ['wifi' => 0, 'ac' => 0, 'colokan' => 0, 'outdoor' => 0, 'musholla' => 0, 'parkir_mobil' => 0, 'toilet' => 0];
        foreach ($data['tempat_nongkrong'] as $t) {
            foreach ($fasilitasStats as $key => $val) {
                if (!empty($t[$key])) $fasilitasStats[$key]++;
            }
        }
        $data['fasilitas_stats'] = $fasilitasStats;
        $data['total_tempat']    = count($data['tempat_nongkrong']);

        return view('landing_page', $data);
    }
}