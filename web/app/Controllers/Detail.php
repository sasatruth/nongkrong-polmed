<?php
namespace App\Controllers;

class Detail extends BaseController
{
    protected string $supabaseUrl;
    protected string $supabaseKey;

    public function __construct()
    {
        $this->supabaseUrl = env('SUPABASE_URL');
        $this->supabaseKey = env('SUPABASE_KEY');
    }

    // ─────────────────────────────────────────────────────────
    // GET /tempat/{id}
    // ─────────────────────────────────────────────────────────
    public function index(int $id)
    {
        $client = \Config\Services::curlrequest();

        // 1. Ambil data tempat berdasarkan ID
        $tempat = [];
        try {
            $res = $client->request('GET',
                $this->supabaseUrl . '/rest/v1/tempat_nongkrong?id=eq.' . $id . '&select=*',
                $this->supabaseHeaders()
            );
            $result = json_decode($res->getBody(), true);
            $tempat = $result[0] ?? [];
        } catch (\Exception $e) {
            log_message('error', 'Supabase tempat error: ' . $e->getMessage());
        }

        // Jika tempat tidak ditemukan, redirect ke home
        if (empty($tempat)) {
            return redirect()->to('/')->with('error', 'Tempat tidak ditemukan.');
        }

        // 2. Ambil semua ulasan berdasarkan tempat_nongkrong_id (terbaru di atas)
        $ulasan = [];
        try {
            $res = $client->request('GET',
                $this->supabaseUrl
                    . '/rest/v1/ulasan?tempat_nongkrong_id=eq.' . $id
                    . '&order=created_at.desc&select=*',
                $this->supabaseHeaders()
            );
            $ulasan = json_decode($res->getBody(), true) ?? [];
        } catch (\Exception $e) {
            log_message('error', 'Supabase ulasan error: ' . $e->getMessage());
        }

        // 3. Hitung rata-rata rating dari ulasan
        $avgRating = 0;
        if (!empty($ulasan)) {
            $sum = array_sum(array_column($ulasan, 'rating'));
            $avgRating = round($sum / count($ulasan), 1);
        }

        // 4. Flash message dari redirect setelah submit
        $successMsg = session()->getFlashdata('success');
        $errorMsg   = session()->getFlashdata('error');

        return view('detail_tempat', [
            'tempat'     => $tempat,
            'ulasan'     => $ulasan,
            'avgRating'  => $avgRating,
            'successMsg' => $successMsg,
            'errorMsg'   => $errorMsg,
        ]);
    }

    // ─────────────────────────────────────────────────────────
    // Helper: Header Supabase
    // ─────────────────────────────────────────────────────────
    private function supabaseHeaders(): array
    {
        return [
            'headers' => [
                'apikey'        => $this->supabaseKey,
                'Authorization' => 'Bearer ' . $this->supabaseKey,
                'Content-Type'  => 'application/json',
            ]
        ];
    }
}