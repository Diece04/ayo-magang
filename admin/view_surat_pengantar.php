<?php
session_start();
include '../koneksi/config.php';

// jika tidak login -> ke login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../authenticator/auth-login-basic.php");
    exit;
}

// --- ambil id pengajuan dari URL ---
$id_pengajuan = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// --- ambil data dari database (join pengajuan_magang, mahasiswa, jurusan, perusahaan) ---
$sql = "
    SELECT 
        id,
        mahasiswa_id,
        perusahaan_id,
        no_surat,
        nama         AS nama_mhs,
        nim,
        prodi,
        no_hp,
        waktu_magang,
        perusahaan   AS nama_perusahaan,
        tanggal_surat
    FROM surat
    WHERE id = :id
    LIMIT 1
";
$stmt = $pdo->prepare($sql);
$stmt->execute(['id' => $id_pengajuan]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

$sql_perusahaan = "
    SELECT 
        alamat
    FROM perusahaan
    WHERE id = :perusahaan_id
    LIMIT 1";
$stmt_perusahaan = $pdo->prepare($sql_perusahaan);
$stmt_perusahaan->execute(['perusahaan_id' => $data['perusahaan_id']]);
$perusahaan_data = $stmt_perusahaan->fetch(PDO::FETCH_ASSOC);
$alamat_perusahaan = $perusahaan_data['alamat'];

// data surat
$nomor_surat      = $data['no_surat'];
$nama_mhs        = $data['nama_mhs'];
$nim             = $data['nim'];
$prodi           = $data['prodi'];
$no_hp           = $data['no_hp'];
$waktu_pkl      = $data['waktu_magang'];
$perusahaan      = $data['nama_perusahaan'];
$tanggal_surat   = $data['tanggal_surat'];


// tanggal & perihal
$hal_surat     = 'Permohonan Magang';

// query notifikasi
$stmtNotif   = $pdo->query("SELECT COUNT(*) as total FROM perusahaan WHERE status = 'pending'");
$notif_count = $stmtNotif->fetch()['total'];

// mode view atau edit
$mode = $_GET['mode'] ?? 'edit';
$isViewOnly = ($mode === 'view');
?>
<!DOCTYPE html>
<html
    lang="en"
    class="light-style layout-menu-fixed"
    dir="ltr"
    data-theme="theme-default"
    data-assets-path="../assets/"
    data-template="vertical-menu-template-free">

<head>
    <meta charset="utf-8" />
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <title>Dashboard</title>

    <link rel="icon" type="image/x-icon" href="../assets/img/PNP.png" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
        rel="stylesheet" />
    <link rel="stylesheet" href="../assets/vendor/fonts/boxicons.css" />
    <link rel="stylesheet" href="../assets/vendor/css/core.css" class="template-customizer-core-css" />
    <link rel="stylesheet" href="../assets/vendor/css/theme-default.css" class="template-customizer-theme-css" />
    <link rel="stylesheet" href="../assets/css/demo.css" />
    <link rel="stylesheet" href="../assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />
    <link rel="stylesheet" href="../assets/vendor/libs/apex-charts/apex-charts.css" />

    <script src="../assets/vendor/js/helpers.js"></script>
    <script src="../assets/js/config.js"></script>

    <!-- STYLE KHUSUS PRINT A4 -->
    <style>
        #area-print {
            font-family: "Times New Roman", serif;
            color: #000;
            background: #fff;
            padding: 20mm;
            margin: 0 auto;
            width: 210mm;
            min-height: 297mm;
            box-sizing: border-box;
        }

        @media (min-width: 992px) {
            #area-print {
                border: 1px solid #ddd;
            }
        }

        @media print {
            @page {
                size: A4;
                margin: 0;
            }

            body {
                margin: 0;
                padding: 0;
                background: #fff !important;
            }

            #area-print {
                width: 210mm;
                min-height: 297mm;
                padding: 20mm;
                box-sizing: border-box;
                margin: 0 auto;
                border: none !important;
            }

            aside,
            nav,
            .layout-menu,
            .layout-navbar,
            .layout-overlay,
            .content-wrapper> :not(.container-xxl),
            .container-xxl .row>.col-lg-4,
            .btn-print-wrapper,
            .btn,
            form {
                display: none !important;
            }

            html,
            body,
            .layout-wrapper,
            .layout-container,
            .layout-page,
            .content-wrapper,
            .container-xxl {
                width: 100%;
                height: auto;
                margin: 0;
                padding: 0;
            }

            .table-daftar,
            .table-daftar th,
            .table-daftar td {
                border: 1.2pt solid #000 !important;
            }
        }

        .kop-table {
            width: 100%;
        }

        .kop-table td {
            vertical-align: middle;
        }

        .kop-text-atas {
            font-size: 11pt;
            text-align: center;
        }

        .kop-text-tengah {
            font-size: 14pt;
            font-weight: bold;
            text-align: center;
        }

        .kop-text-bawah {
            font-size: 10pt;
            text-align: center;
        }

        .line-bold {
            border-bottom: 3px solid #000;
            margin-top: 2px;
            margin-bottom: 15px;
        }

        .line-thin {
            border-bottom: 1px solid #000;
            margin-bottom: 15px;
        }

        .table-info-surat td {
            font-size: 11pt;
            padding: 2px 0;
        }

        .table-daftar {
            width: 100%;
            border-collapse: collapse;
            font-size: 11pt;
        }

        .table-daftar th,
        .table-daftar td {
            border: 1px solid #000;
            padding: 4px 6px;
        }

        .table-daftar th {
            text-align: center;
        }

        .text-justify {
            text-align: justify;
            font-size: 11pt;
        }

        .text-11 {
            font-size: 11pt;
        }
    </style>
</head>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <!-- Menu -->
            <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
                <div class="app-brand demo">
                    <a href="index.html" class="app-brand-link">
                        <span class="app-brand-text demo menu-text fw-bolder ms-2">Ayo Magang</span>
                    </a>

                    <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
                        <i class="bx bx-chevron-left bx-sm align-middle"></i>
                    </a>
                </div>

                <div class="menu-inner-shadow"></div>

                <ul class="menu-inner py-1">
                    <li class="menu-item active">
                        <a href="../index.php" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-home-circle"></i>
                            <div data-i18n="Analytics">Dashboard</div>
                        </a>
                    </li>
            </aside>
            <!-- / Menu -->

            <!-- Layout container -->
            <div class="layout-page">
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <div class="row g-4">

                            <!-- PREVIEW + AREA PRINT -->
                            <div class="col-lg-8">
                                <div class="card">
                                    <div class="card-body">
                                        <div id="area-print">

                                            <!-- KOP SURAT -->
                                            <table class="kop-table">
                                                <tr>
                                                    <td style="width: 18%; text-align:center;">
                                                        <img src="../assets/img/PNP.png" alt="Logo PNP" style="width:80px; height:80px;">
                                                    </td>
                                                    <td style="text-align:center;">
                                                        <div class="kop-text-atas">
                                                            KEMENTERIAN PENDIDIKAN TINGGI, SAINS DAN TEKNOLOGI
                                                        </div>
                                                        <div class="kop-text-tengah">
                                                            POLITEKNIK NEGERI PADANG
                                                        </div>
                                                        <div class="kop-text-bawah">
                                                            Kampus Politeknik Negeri Padang Limau Manis, Padang, Sumatera Barat<br>
                                                            Telepon : (0751) 72590, Faks. (0751) 72576<br>
                                                            Laman : http://www.pnp.ac.id, mail : info@pnp.ac.id
                                                        </div>
                                                    </td>
                                                </tr>
                                            </table>
                                            <div class="line-bold"></div>
                                            <div class="line-thin"></div>

                                            <!-- Nomor & Hal & Tanggal -->
                                            <table class="table-info-surat" style="width:100%; margin-bottom:10px;">
                                                <tr>
                                                    <td style="width:60%;">
                                                        No : <?= htmlspecialchars($nomor_surat) ?><br>
                                                        Hal : <b><?= htmlspecialchars($hal_surat) ?></b>
                                                    </td>
                                                    <td style="width:40%; text-align:right;">
                                                        Padang, <?= date('d F Y', strtotime($tanggal_surat)) ?>
                                                    </td>
                                                </tr>
                                            </table>

                                            <!-- Tujuan surat -->
                                            <p class="text-11" style="margin-bottom:0;">
                                                Kepada Yth :
                                            </p>
                                            <p class="text-11" style="margin-bottom:0;">
                                                Bapak/Ibu Pimpinan <?= htmlspecialchars($perusahaan) ?>
                                            </p>
                                            <p class="text-11" style="margin-bottom:10px;">
                                                di <?= htmlspecialchars($alamat_perusahaan) ?>
                                            </p>

                                            <!-- Paragraf pembuka -->
                                            <p class="text-justify">
                                                Dengan hormat,
                                            </p>
                                            <p class="text-justify">
                                                Sebelumnya kami mendoakan Bapak/Ibu semoga sehat wal afiat dan sukses selalu
                                                dalam aktifitas sehari-hari. Amin.
                                            </p>
                                            <p class="text-justify">
                                                Politeknik Negeri Padang merupakan institusi pendidikan vocasional yang bertujuan
                                                untuk menciptakan lulusan profesional dan terampil dibidangnya, serta mampu mandiri
                                                dan bersaing ditingkat nasional maupun internasional. Untuk mewujudkan hal tersebut
                                                maka dari itu setiap mahasiswa tingkat akhir diwajibkan mengikuti Magang untuk
                                                menambah pengetahuan dan wawasan agar mereka betul-betul siap pakai.
                                            </p>
                                            <p class="text-justify">
                                                Sehubungan dengan itu, kami memohon kepada Bapak/Ibu untuk dapat menerima mahasiswa
                                                kami melaksanakan Magang untuk tahun ajaran 2025/2026 dari bulan
                                                <?= htmlspecialchars($waktu_pkl) ?> pada perusahaan/instansi yang Bapak/Ibu pimpin.
                                                Daftar nama mahasiswa kami sebagai berikut:
                                            </p>

                                            <!-- Tabel daftar mahasiswa -->
                                            <table class="table-daftar" style="margin-top:5px; margin-bottom:10px;">
                                                <thead>
                                                    <tr>
                                                        <th style="width:5%;">No</th>
                                                        <th style="width:30%;">Nama</th>
                                                        <th style="width:20%;">NIM</th>
                                                        <th style="width:25%;">Program Studi</th>
                                                        <th style="width:20%;">No HP</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td style="text-align:center;">1</td>
                                                        <td><?= htmlspecialchars($nama_mhs) ?></td>
                                                        <td><?= htmlspecialchars($nim) ?></td>
                                                        <td><?= htmlspecialchars($prodi) ?></td>
                                                        <td><?= htmlspecialchars($no_hp) ?></td>
                                                    </tr>
                                                </tbody>
                                            </table>

                                            <!-- Paragraf penutup -->
                                            <p class="text-justify">
                                                Demikianlah hal ini kami sampaikan atas bantuan dan kerjasamanya kami ucapkan terima kasih.
                                            </p>

                                            <!-- Tanda tangan -->
                                            <div style="margin-top:30px; text-align:right; font-size:11pt;">
                                                Hormat kami<br>
                                                Wakil Direktur Bidang Kerjasama<br><br><br>
                                                <span style="font-weight:bold; text-decoration:underline;">
                                                    Ihsan Lumasa Rimra
                                                </span><br>
                                                Nip. 197811252003121002
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- TOMBOL PRINT -->
                    <div class="card-header d-flex justify-content-between align-items-center btn-print-wrapper">
                        <h5 class="mb-0"></h5>
                        <div class="btn-group" role="group">
                            <a href="../index.php"
                                class="btn btn-outline-primary flex-fill me-1">
                                <i class="bx bx-eye me-1"></i>Kembali
                            </a>
                            <button type="button" class="btn btn-primary btn-sm" id="btnPrint">
                                <i class="bx bx-printer"></i> Print A4
                            </button>
                        </div>
                    </div>

                    <div class="content-backdrop fade"></div>
                </div>
            </div>
        </div>
        <div class="layout-overlay layout-menu-toggle"></div>
    </div>

    <!-- JS -->
    <script>
        // Klik Print A4: simpan ke DB + setelah reload auto print
        document.getElementById('btnPrint').addEventListener('click', function() {
            window.print();
        });
    </script>

    <script src="../assets/vendor/libs/jquery/jquery.js"></script>
    <script src="../assets/vendor/libs/popper/popper.js"></script>
    <script src="../assets/vendor/js/bootstrap.js"></script>
    <script src="../assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="../assets/vendor/js/menu.js"></script>
    <script src="../assets/vendor/libs/apex-charts/apexcharts.js"></script>
    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/dashboards-analytics.js"></script>
    <script async defer src="https://buttons.github.io/buttons.js"></script>
</body>

</html>