<?php
include "../../includes/header.php"
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Syarat dan Ketentuan</title>

    <style>
        :root {
        /* ── Surfaces ─────────────────────────────── */
        --white:                  #ffffff;
        --bg:                     #f7f9fb;
        --bg2:                    #f2f4f6;
        --bg3:                    #eceef0;
        --border:                 #e2e8f0;
        --border-strong:          #c2c6d6;
        --card-bg:                #ffffff;

        /* ── Text ────────────────────────────────── */
        --text:                   #191c1e;
        --muted:                  #424754;

        /* ── Brand / Primary ─────────────────────── */
        --blue:                   #0058be;
        --blue-dk:                #004395;
        --blue-lt:                #d8e2ff;
        --blue-container:         #2170e4;

        /* ── Semantic ────────────────────────────── */
        --green:                  #12b76a;
        --orange:                 #924700;

        /* ── Elevation / Shadow ──────────────────── */
        --shadow:                 0px 4px 12px rgba(0, 0, 0, 0.05);
        --shadow-lg:              0px 20px 25px rgba(0, 0, 0, 0.10);

        /* ── Shape ───────────────────────────────── */
        --radius-sm:              0.25rem;
        --radius:                 0.5rem;
        --radius-md:              0.75rem;
        --radius-lg:              1rem;
        --radius-xl:              1.5rem;
        --radius-full:            9999px;

        /* ── Spacing (8px base scale) ────────────── */
        --space-1:  8px;
        --space-2:  16px;
        --space-3:  24px;
        --space-4:  32px;
        --space-5:  40px;
        --space-6:  48px;
        --space-7:  56px;
        --space-8:  64px;
        --container: 1280px;
        --gutter:    24px;
        }

        body {
        font-family: 'Outfit', sans-serif;
        background: var(--bg);
        color: var(--text);
        font-size: 16px;
        line-height: 1.5;
        -webkit-font-smoothing: antialiased;
        }

        .container {
            max-width: 900px;
            margin: 40px auto;
            background: #fff;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        h1 {
            color: var(--blue-dk);
            text-align: center;
            margin-bottom: 10px;
        }

        h2 {
            color: var(--text);
            margin-top: 30px;
        }

        p {
            margin-bottom: 15px;
        }

        ul, ol {
            padding-left: 25px;
        }

        .updated {
            text-align: center;
            color: gray;
            margin-bottom: 30px;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 30px;
            padding: 10px 20px;
            background: var(--blue);
            color: #fff;
            border: none;
            border-radius: var(--radius);
            font-size: 15px;
            font-family: 'Outfit', sans-serif;
            cursor: pointer;
            text-decoration: none;
            transition: background 0.2s;
        }

        .back-btn:hover {
            background: var(--blue-dk);
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Syarat dan Ketentuan</h1>

    <p>
        Selamat datang di <strong>Thurz Shop</strong>. Dengan mengakses dan menggunakan layanan kami,
        Anda dianggap telah membaca, memahami, dan menyetujui seluruh syarat dan ketentuan yang berlaku.
    </p>
    
    <a href="javascript:history.back()" class="back-btn">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path d="M19 12H5M12 5l-7 7 7 7"/>
        </svg>
        Kembali
    </a>
    
    <h2>1. Definisi</h2>
    <ul>
        <li><strong>Platform</strong> adalah website Thurz Shop yang menyediakan layanan jual beli akun game.</li>
        <li><strong>Penjual</strong> adalah pengguna yang menawarkan akun game untuk dijual.</li>
        <li><strong>Pembeli</strong> adalah pengguna yang membeli akun game melalui platform.</li>
        <li><strong>Akun Game</strong> adalah akun digital yang menjadi objek transaksi.</li>
    </ul>

    <h2>2. Ketentuan Umum</h2>
    <ol>
        <li>Pengguna wajib memberikan informasi yang benar dan akurat.</li>
        <li>Pengguna bertanggung jawab atas aktivitas yang dilakukan melalui akun miliknya.</li>
        <li>Platform berhak menangguhkan atau menghapus akun yang melanggar ketentuan.</li>
    </ol>

    <h2>3. Ketentuan Penjualan</h2>
    <ol>
        <li>Penjual wajib memastikan akun yang dijual adalah miliknya atau memiliki hak untuk menjualnya.</li>
        <li>Deskripsi akun harus sesuai dengan kondisi sebenarnya.</li>
        <li>Dilarang memberikan informasi palsu atau menyesatkan.</li>
    </ol>

    <h2>4. Ketentuan Pembelian</h2>
    <ol>
        <li>Pembeli wajib membaca detail akun sebelum melakukan transaksi.</li>
        <li>Setelah akun diterima, pembeli disarankan segera mengganti kata sandi dan mengamankan akun.</li>
    </ol>

    <h2>5. Pembayaran</h2>
    <ol>
        <li>Pembayaran dilakukan melalui metode yang tersedia di platform.</li>
        <li>Transaksi dianggap selesai setelah pembayaran berhasil dikonfirmasi.</li>
        <li>Platform berhak menunda transaksi apabila ditemukan aktivitas mencurigakan.</li>
    </ol>

    <h2>6. Pengiriman Akun</h2>
    <ol>
        <li>Data akun akan diberikan setelah pembayaran berhasil diverifikasi.</li>
        <li>Penjual wajib menyerahkan akun sesuai deskripsi yang tertera.</li>
    </ol>

    <h2>7. Pengembalian Dana (Refund)</h2>
    <ol>
        <li>Refund dapat dilakukan apabila akun tidak sesuai deskripsi atau tidak dapat diakses.</li>
        <li>Permohonan refund harus diajukan maksimal 24 jam setelah akun diterima.</li>
        <li>Refund tidak berlaku apabila pembeli telah mengubah data akun atau terjadi kelalaian dari pihak pembeli.</li>
    </ol>

    <h2>8. Larangan</h2>
    <ul>
        <li>Melakukan penipuan atau aktivitas ilegal.</li>
        <li>Menjual akun hasil pencurian atau peretasan.</li>
        <li>Mengganggu keamanan dan operasional platform.</li>
    </ul>

    <h2>9. Batasan Tanggung Jawab</h2>
    <ol>
        <li>Platform hanya bertindak sebagai penyedia layanan.</li>
        <li>Platform tidak bertanggung jawab atas kebijakan pengembang game terkait perpindahan kepemilikan akun.</li>
        <li>Segala risiko penggunaan layanan menjadi tanggung jawab pengguna.</li>
    </ol>

    <h2>10. Keamanan Akun</h2>
    <ol>
        <li>Pengguna wajib menjaga kerahasiaan akun dan kata sandinya.</li>
        <li>Platform tidak bertanggung jawab atas kehilangan akses akibat kelalaian pengguna.</li>
    </ol>

    <h2>11. Perubahan Ketentuan</h2>
    <p>
        Platform berhak mengubah syarat dan ketentuan ini sewaktu-waktu.
        Perubahan akan berlaku sejak dipublikasikan pada website.
    </p>

    <h2>12. Hukum yang Berlaku</h2>
    <p>
        Syarat dan ketentuan ini diatur berdasarkan hukum yang berlaku di Republik Indonesia.
    </p>

</div>

</body>
</html>