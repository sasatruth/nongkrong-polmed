<?php
namespace App\Controllers;

class Ulasan extends BaseController
{
    protected string $supabaseUrl;
    protected string $supabaseKey;

    public function __construct()
    {
        $this->supabaseUrl = env('SUPABASE_URL');
        $this->supabaseKey = env('SUPABASE_KEY');
    }

    // ─────────────────────────────────────────────────────────
    // POST /ulasan/simpan
    // ─────────────────────────────────────────────────────────
    public function simpan()
    {
        $tempatId       = (int) $this->request->getPost('tempat_nongkrong_id');
        $namaPengunjung = trim($this->request->getPost('nama_pengunjung'));
        $rating         = (int) $this->request->getPost('rating');
        $isiUlasan      = trim($this->request->getPost('isi_ulasan'));

        // ── Validasi input ────────────────────────────────────
        $errors = [];
        if (empty($namaPengunjung)) $errors[] = 'Nama pengunjung tidak boleh kosong.';
        if ($rating < 1 || $rating > 5) $errors[] = 'Rating harus antara 1 sampai 5.';
        if (empty($isiUlasan))      $errors[] = 'Isi ulasan tidak boleh kosong.';
        if ($tempatId <= 0)         $errors[] = 'ID tempat tidak valid.';

        if (!empty($errors)) {
            session()->setFlashdata('error', implode(' ', $errors));
            return redirect()->to('/tempat/' . $tempatId);
        }

        $client = \Config\Services::curlrequest();

        // ── 1. Insert ulasan ke Supabase ──────────────────────
        try {
            $payload = json_encode([
                'tempat_nongkrong_id' => $tempatId,
                'nama_pengunjung'     => $namaPengunjung,
                'rating'              => $rating,
                'isi_ulasan'          => $isiUlasan,
            ]);

            $client->request('POST',
                $this->supabaseUrl . '/rest/v1/ulasan',
                [
                    'headers' => [
                        'apikey'        => $this->supabaseKey,
                        'Authorization' => 'Bearer ' . $this->supabaseKey,
                        'Content-Type'  => 'application/json',
                        'Prefer'        => 'return=minimal',
                    ],
                    'body' => $payload,
                ]
            );
        } catch (\Exception $e) {
            log_message('error', 'Insert ulasan error: ' . $e->getMessage());
            session()->setFlashdata('error', 'Gagal menyimpan ulasan. Silakan coba lagi.');
            return redirect()->to('/tempat/' . $tempatId);
        }

        // ── 2. Update rata-rata rating di tabel tempat (opsional) ──
        try {
            // Ambil semua ulasan untuk tempat ini
            $res = $client->request('GET',
                $this->supabaseUrl
                    . '/rest/v1/ulasan?tempat_nongkrong_id=eq.' . $tempatId
                    . '&select=rating',
                [
                    'headers' => [
                        'apikey'        => $this->supabaseKey,
                        'Authorization' => 'Bearer ' . $this->supabaseKey,
                    ]
                ]
            );
            $allUlasan = json_decode($res->getBody(), true) ?? [];

            if (!empty($allUlasan)) {
                $avg = round(
                    array_sum(array_column($allUlasan, 'rating')) / count($allUlasan),
                    1
                );

                // PATCH rating di tabel tempat_nongkrong
                $client->request('PATCH',
                    $this->supabaseUrl . '/rest/v1/tempat_nongkrong?id=eq.' . $tempatId,
                    [
                        'headers' => [
                            'apikey'        => $this->supabaseKey,
                            'Authorization' => 'Bearer ' . $this->supabaseKey,
                            'Content-Type'  => 'application/json',
                            'Prefer'        => 'return=minimal',
                        ],
                        'body' => json_encode(['rating' => $avg]),
                    ]
                );
            }
        } catch (\Exception $e) {
            // Update rating gagal — tidak kritis, lanjut saja
            log_message('warning', 'Update avg rating error: ' . $e->getMessage());
        }

        session()->setFlashdata('success', 'Ulasan berhasil disimpan! Terima kasih, ' . htmlspecialchars($namaPengunjung) . '.');
        return redirect()->to('/tempat/' . $tempatId);
    }
}