# Production Blueprint: Sistem Manajemen Turnamen Futsal (Laravel)

Dokumen ini adalah panduan spesifik dan komprehensif untuk membangun aplikasi web manajemen turnamen futsal kustom menggunakan **Laravel**. Sistem ini dirancang untuk mendukung operasional turnamen dari hulu ke hilir: manajemen data master, penjadwalan, lini masa pertandingan (*live match timeline*), papan skor waktu nyata (*real-time live score*), klasemen, hingga statistik individu pemain (top skorer, *assist*, kartu kuning, dan kartu merah).

---

## 1. Arsitektur Sistem & Teknologi Utama

* **Backend Framework:** Laravel 11 (PHP 8.2+)
* **Database:** PostgreSQL / MySQL 8.0+
* **Real-time Engine:** Laravel Reverb (WebSocket) untuk pembaruan *live score* dan lini masa otomatis di sisi klien tanpa *refresh*.
* **Frontend Stack:** 
  * *Admin Dashboard:* Laravel Livewire 3 / Tailwind CSS (untuk pengelolaan data secara interaktif dan reaktif).
  * *Public View / Live Stream Page:* Blade + Alpine.js + Laravel Reverb client (untuk tampilan penonton yang ringan dan responsif di HP/desktop).

---

## 2. Struktur Database & Skema Relasi (Database Schema)

Berikut adalah rancangan tabel utama yang diperlukan untuk mengakomodasi seluruh fitur turnamen futsal:

### A. Master Kelompok (`categories`)
Digunakan untuk mengelompokkan turnamen berdasarkan kategori umur atau instansi (Contoh: U-12, U-16, Kategori Umum, Instansi).
* `id` (Primary Key)
* `name` (string): Nama kelompok, misal "U-17 Putra"
* `description` (text, nullable)
* `timestamps`

### B. Tim (`teams`)
* `id` (Primary Key)
* `category_id` (Foreign Key ke `categories`)
* `name` (string): Nama tim futsal
* `logo` (string, nullable): Path file logo tim
* `manager_name` (string, nullable)
* `timestamps`

### C. Pemain (`players`)
Master data pemain yang terdaftar dalam suatu tim.
* `id` (Primary Key)
* `team_id` (Foreign Key ke `teams`)
* `name` (string): Nama lengkap pemain
* `jersey_number` (integer): Nomor punggung
* `position` (enum): `GK` (Kiper), `FLA` (Flank), `PIV` (Pivot), `DEF` (Defender)
* `photo` (string, nullable)
* `timestamps`

### D. Grup / Babak (`stages` & `groups`)
* `stages`: Menyimpan tahapan turnamen (Contoh: "Babak Grup", "Babak 16 Besar", "Semifinal", "Final").
* `groups`: Jika menggunakan sistem grup (Contoh: "Grup A", "Grup B"). Relasi ke `stage_id`.

### E. Pertandingan (`matches`)
* `id` (Primary Key)
* `stage_id`, `group_id` (Foreign Keys, nullable jika sistem gugur langsung)
* `home_team_id` (Foreign Key ke `teams`)
* `away_team_id` (Foreign Key ke `teams`)
* `home_score` (integer, default: 0)
* `away_score` (integer, default: 0)
* `match_date` (dateTime): Jadwal tanding
* `venue` (string): Lokasi lapangan/GOR
* `status` (enum): `scheduled`, `first_half`, `half_time`, `second_half`, `extra_time`, `finished`, `postponed`
* `timestamps`

### F. Lini Masa & Statistik Pertandingan (`match_events`)
Merekam setiap kejadian penting detik-demi-detik selama pertandingan berlangsung untuk mendukung fitur lini masa dan perhitungan statistik otomatis.
* `id` (Primary Key)
* `match_id` (Foreign Key ke `matches`)
* `team_id` (Foreign Key ke `teams`)
* `player_id` (Foreign Key ke `players`, nullable jika insiden tim)
* `event_type` (enum): 
  * `goal` (Gol normal)
  * `own_goal` (Gol bunuh diri)
  * `yellow_card` (Kartu kuning)
  * `red_card` (Kartu merah)
  * `second_yellow` (Kartu kuning kedua berujung merah)
  * `assist` (Pemberi umpan gol)
* `minute` (integer): Menit terjadinya insiden (misal: menit 14)
* `timestamps`

---

## 3. Spesifikasi Fitur Utama

### A. Master Data (Pemain & Kelompok)
* **Manajemen Kelompok Usia:** Panel admin dapat menambah, mengubah, atau menghapus kategori turnamen.
* **Manajemen Tim & Pemain:** 
  * Input data tim beserta unggah logo.
  * Pendaftaran pemain secara massal (*bulk import* via Excel/CSV) atau satu per satu dengan validasi nomor punggung unik dalam satu tim.

### B. Jadwal & Lini Masa Pertandingan (`Matches & Timeline`)
* **Penjadwalan Fleksibel:** Pengaturan tanggal, waktu, dan lokasi GOR untuk setiap laga.
* **Panel Kontrol Admin (Live Match Control Room):** Halaman khusus wasit/panitia lapangan untuk memperbarui status pertandingan secara langsung:
  * Menekan tombol "Mulai Babak 1", "Half-Time", "Mulai Babak 2", hingga "Pertandingan Selesai".
  * Mencatat *Event*: Saat gol tercipta, sistem akan meminta input **Pencetak Gol** dan **Pemain yang memberi Assist** (opsional), serta menit terjadinya.
  * Mencatat kedisiplinan: Input pemberian **Kartu Kuning** atau **Kartu Merah** kepada pemain tertentu.
* **Tampilan Lini Masa Publik:** Penonton dapat melihat kronologi pertandingan secara vertikal (*timeline stream*), contoh:
  * `[ 14' ] ⚽ Gol! oleh Rian Pratama (Tim A) - Assist: Dimas`
  * `[ 18' ] 🟨 Kartu Kuning diberikan kepada Joko (Tim B)`

### C. Live Score Real-Time
* Menggunakan **Laravel Reverb**, setiap kali panitia mengklik tombol tambah gol atau mengubah skor di panel admin, *event broadcast* dikirimkan via WebSocket.
* Halaman penonton di *browser* (tanpa instal aplikasi apa pun) akan memperbarui skor secara otomatis tanpa perlu memuat ulang (*refresh*) halaman.

### D. Tabel Turnamen & Klasemen Otomatis
* **Sistem Grup (Round-Robin):** Sistem otomatis menghitung poin berdasarkan hasil pertandingan:
  * Menang = 3 poin
  * Seri = 1 poin
  * Kalah = 0 poin
  * Kolom otomatis: Main (M), Menang (Mg), Seri (S), Kalah (K), Gol Memasukkan (GM), Gol Kemasukan (GK), Selisih Gol (SG), Poin (P).
* **Bagan Sistem Gugur (Knockout Bracket):** Visualisasi bagan dari babak 16 besar hingga final yang terisi otomatis begitu pemenang babak sebelumnya ditentukan.

### E. Papan Statistik Individu (Top Scorer, Assist, Kartu)
Sistem secara otomatis mengagregasi data dari tabel `match_events` untuk menampilkan halaman leaderboard khusus:
1. **Pencetak Gol Terbanyak (Top Scorers):** Diurutkan berdasarkan jumlah gol terbanyak (mengecualikan *own goal*).
2. **Penyumbang Assist Terbanyak (Top Assists):** Diurutkan berdasarkan total *assist* terbanyak.
3. **Kartu Kuning Terbanyak:** Daftar pemain dengan akumulasi kartu kuning terbanyak (berguna untuk informasi skorsing akumulasi kartu).
4. **Kartu Merah Terbanyak:** Daftar pemain yang pernah mendapatkan kartu merah langsung atau akumulasi dua kartu kuning.

---

## 4. Alur Kerja Pengembangan (Development Roadmap)

1. **Fase 1: Setup Backend & Migrasi Database**
   * Inisialisasi projek Laravel 11 dan konfigurasi koneksi database.
   * Pembuatan *migration* untuk seluruh tabel di atas beserta relasi Eloquent-nya.
2. **Fase 2: Modul Data Master (CRUD)**
   * Membangun fitur manajemen Kategori, Tim, dan Pemain menggunakan Laravel Livewire.
3. **Fase 3: Modul Jadwal & Pengundian (Fixtures)**
   * Logika pembuatan jadwal pertandingan per kelompok dan grup.
4. **Fase 4: Live Match Control & Real-Time Events**
   * Pembuatan panel kontrol admin untuk mencatat gol, kartu, dan lini masa.
   * Integrasi Laravel Reverb untuk *broadcast* perubahan data.
5. **Fase 5: Tampilan Publik (Public View & Klasemen)**
   * Membangun halaman antarmuka web publik yang ramah pengguna seluler (mobile-friendly) untuk melihat *live score*, lini masa, klasemen grup, dan tabel top statistik (skor, assist, kartu).
6. **Fase 6: Testing & Deployment**
   * Uji coba beban WebSocket dan *deployment* ke server produksi (VPS / Cloud hosting).
