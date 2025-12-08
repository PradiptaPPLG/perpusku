<?php
// Set header supaya respons yang dikirimkan ke client berbentuk JSON
header('Content-Type: application/json');

// Ambil data JSON yang dikirim lewat body request dan ubah jadi array PHP
$input = json_decode(file_get_contents("php://input"), true);

// Ambil pesan dari user, kalau kosong isinya default "Halo"
$userMessage = $input['message'] ?? 'Halo';

// Isi API key kamu di sini, pastikan jangan dibagikan ke publik
$apiKey = 'sk-...'; // Ganti dengan API Key kamu

// Siapkan data yang mau dikirim ke API OpenAI
$data = [
    "model" => "gpt-3.5-turbo", // Model yang dipakai
    "messages" => [["role" => "user", "content" => $userMessage]] // Pesan dari user
];

// Mulai setup CURL untuk kirim request ke API OpenAI
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://api.openai.com/v1/chat/completions");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Authorization: Bearer $apiKey" // Masukkan API Key di header
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data)); // Kirim data dalam format JSON

// Jalankan CURL dan ambil hasilnya
$result = curl_exec($ch);

// Kalau CURL error (misalnya koneksi gagal), balikin pesan error
if (curl_errno($ch)) {
    echo json_encode(["reply" => "CURL Error: " . curl_error($ch)]);
    curl_close($ch);
    exit;
}
curl_close($ch);

// Ubah hasil dari API yang berupa JSON jadi array PHP
$response = json_decode($result, true);

// Pesan info langganan kalau AI nggak ngasih respons balik
$subscriptionMessage = "✨ Fitur AI Assistant Premium ✨\n\n"
    . "Untuk mengaktifkan fitur chat AI ini, Anda perlu berlangganan layanan OpenAI API.\n\n"
    . "📌 Cara berlangganan:\n"
    . "- Kunjungi platform.openai.com\n"
    . "- Buat akun dan API Key\n"
    . "- Isi saldo kredit API\n"
    . "- Masukkan API Key Anda di sistem kami\n\n"
    . "Dengan berlangganan, Anda akan mendapatkan:\n"
    . "✅ Bantuan AI 24/7\n"
    . "✅ Jawaban instan untuk pertanyaan perpustakaan\n"
    . "✅ Rekomendasi buku pintar";

// Ambil jawaban dari AI kalau ada, kalau nggak ya pakai pesan langganan
$reply = $response['choices'][0]['message']['content'] ?? $subscriptionMessage;

// Kirim balasan ke client dalam format JSON
echo json_encode(["reply" => $reply]);
?>
